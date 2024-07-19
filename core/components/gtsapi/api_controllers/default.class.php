<?php

class defaultAPIController{
    public $config = [];
    public $modx;
    public $pdo;
    public $models;
    public $triggers = [];

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            
        ], $config);

        if ($this->pdo = $this->modx->getService('pdoFetch')) {
            $this->pdo->setConfig($this->config);
        }
    }
    public function route($rule, $uri, $method, $request, $id){
        $req = json_decode(file_get_contents('php://input'), true);
        if(is_array($req)) $request = array_merge($request,$req);    
        switch($method){
            case 'GET':
                if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['api_action'] = 'read';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'PUT':
                // if($id) $request['id'] = $id;
                $request['api_action'] = 'create';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'PATCH':
                if($id) $request['id'] = $id;
                $request['api_action'] = 'update';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'DELETE':
                if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['api_action'] = 'delete';
                return $this->route_post($rule, $uri, $method, $request);
            break;
        }
        return $this->route_post($rule, $uri, $method, $request);
    }
    public function route_post($rule, $uri, $method, $request){
        if(empty($request['api_action'])) $request['api_action'] = 'create';
        if(!isset($rule['aсtions'][$request['api_action']])){
            $this->error("Not api action!");
        }
        $resp = $this->checkPermissions($rule);

        if($resp['success'] == 'error'){
            header('HTTP/1.1 401 Unauthorized2');
            return $resp;
        }
        $resp = $this->checkPermissions($rule['aсtions'][$request['api_action']]);

        if($resp['success'] == 'error'){
            header('HTTP/1.1 401 Unauthorized1');
            return $resp;
        }
        if(!empty($rule['packages'])) $this->addPackages($rule['packages']);
        if(!$request['skip_sanitize']) $request = $this->modx->sanitize($request, $this->modx->sanitizePatterns);
        
        if(empty($rule['aсtions'][$request['api_action']])){
            header('HTTP/1.1 404 Not found');
            return $this->error('Not found action!');
        }

        switch($request['api_action']){
            case 'create':
                $request = $this->request_array_to_json($request);
                return $this->create($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'read':
                return $this->read($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'update':
                $request = $this->request_array_to_json($request);
                return $this->update($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'delete':
                return $this->delete($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
        }
        return $this->error("test2!");
    }
    public function request_array_to_json($request){
        $req = [];
        foreach($request as $k=>$v){
            if(is_array($v)){
                $req[$k] = json_encode($v);
            }else{
                $req[$k] = $v;
            }
        }
        return $req;
    }
    
    
    public function delete($rule,$request,$action){
        if(!empty($request['ids'])){
            if(is_string($request['ids'])) $request['ids'] = explode(',',$request['ids']);
            $objs = $this->modx->getIterator($rule['class'],['id:IN'=>$request['ids']]);
            foreach($objs as $obj){
                $object_old = $obj->toArray();
                $resp = $this->run_triggers($rule['class'], 'before', 'remove', [], $object_old);
                if(!$resp['success']) return $resp;

                if($obj->remove()){
                    $resp = $this->run_triggers($rule['class'], 'after', 'remove', [], $object_old);
                    if(!$resp['success']) return $resp;
                }
            }
            return $this->success('delete',['ids'=>$request['ids']]);
        }
        return $this->error('delete_error');
    }

    public function create($rule,$request,$action){
        if(!empty($action['processor'])){
            $modx_response = $this->modx->runProcessor($action['processors'], $request);
            if ($modx_response->isError()) {
                return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
            }else{
                $object = $modx_response->response['object'];
                header('HTTP/1.1 201 Created');
                return $this->success('created',$object);
            }
        }else{
            $obj = $this->modx->newObject($rule['class'],$request);

            $object_old = $obj->toArray();
            $object = $obj->fromArray($request);
            $object_new = $obj->toArray();

            // $this->modx->log(1,"create triggers".print_r($this->triggers,1));

            $resp = $this->run_triggers($rule['class'], 'before', 'create', $request, $object_old,$object_new,$obj);
            if(!$resp['success']) return $resp;

            if($obj->save()){
                $object = $obj->toArray();

                $resp = $this->run_triggers($rule['class'], 'after', 'create', $request, $object_old,$object,$obj);
                if(!$resp['success']) return $resp;
                // $object = $obj->toArray();
                header('HTTP/1.1 201 Created');
                return $this->success('created',$object);
            }
        }
        return $this->error('create_error');
    }
    public function update($rule,$request,$action){
        if(!empty($action['processor'])){
            $modx_response = $this->modx->runProcessor($action['processors'], $request);
            if ($modx_response->isError()) {
                return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
            }else{
                $object = $modx_response->response['object'];
                return $this->success('update',$object);
            }
        }else{
            if($obj = $this->modx->getObject($rule['class'],(int)$request['id'])){
                $object_old = $obj->toArray();
                $object = $obj->fromArray($request);
                $object_new = $obj->toArray();

                $resp = $this->run_triggers($rule['class'], 'before', 'update', $request, $object_old,$object_new,$obj);
                if(!$resp['success']) return $resp;
                if($obj->save()){
                    $object = $obj->toArray();

                    $resp = $this->run_triggers($rule['class'], 'after', 'update', $request, $object_old,$object,$obj);
                    if(!$resp['success']) return $resp;

                    return $this->success('update',$object);
                }
            }
        }
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
    public function read($rule,$request,$action){
        
        if(!empty($rule['pdoTools'])){
            $default = $rule['pdoTools'];
        }else{
            $default = [
                'class' => $rule['class'],
                'select' => [
                    $rule['class'] => '*',
                ],
                'sortby' => [
                    "{$rule['class']}.id" => 'DESC',
                ],
                'return' => 'data',
                'limit' => 0
            ];
        }
        if(!empty($request['filters'])){
            if(empty($default['where'])) $default['where'] = [];
            $default['where'] = array_merge($default['where'],$this->aplyFilters($rule,$request['filters']));
        }
        $default['decodeJSON'] = 1;
        
        if(!empty($request['ids'])){
            $default['where']["{$rule['class']}.id:IN"] = $request['ids'];
        }
        if(isset($request['limit'])){
            $default['limit'] = $request['limit'];
        }
        if(isset($request['offset'])){
            $default['offset'] = $request['offset'];
        }
        
        if($request['setTotal']){
            $default['setTotal'] = true;
        }
        if($request['sortField']){
            $default['sortby'] = [
                "`{$rule['class']}`.`{$request['sortField']}`" => $request['sortOrder'] == 1 ?'ASC':'DESC',
            ];
        }
        $this->pdo->setConfig($default);
        $rows0 = $this->pdo->run();
        if($request['setTotal']){
            $total = (int)$this->modx->getPlaceholder('total');
        }
        // $rows = [];
        // foreach($rows0 as $row){
        //     $rows[$row['id']] = $row;
        // }
        return $this->success('',['rows'=>$rows0,'total'=>$total,'log'=>$this->pdo->getTime()]);
    }
    public function aplyFilter($rule, $name, $filter){
        $where = [];
        $field = "{$rule['class']}.$name";
        if(isset($filter['class']))  $field = "{$filter['class']}.$name";
        switch($filter['matchMode']){
            case "startsWith":
                $where[$field.':LIKE'] = "{$filter['value']}%";
            break;
            case "contains":
                $where[$field.':LIKE'] = "%{$filter['value']}%";
            break;
            case "notContains":
                $where[$field.':NOT LIKE'] = "%{$filter['value']}%";
            break;
            case "endsWith":
                $where[$field.':LIKE'] = "%{$filter['value']}";
            break;
            case "equals":
                $where[$field] = $filter['value'];
            break;
            case "in":
                $where[$field.':IN'] = $filter['value'];
            break;
            case "lt":
                $where[$field.':<'] = $filter['value'];
            break;
            case "lte":
                $where[$field.':<='] = $filter['value'];
            break;
            case "gt":
                $where[$field.':>'] = $filter['value'];
            break;
            case "gte":
                $where[$field.':>='] = $filter['value'];
            break;
            case "dateIs":
                $where[$field] = date('Y-m-d',strtotime($filter['value']));
            break;
            case "dateBefore":
                $where[$field.':<='] = date('Y-m-d',strtotime($filter['value']));
            break;
            case "dateAfter":
                $where[$field.':>='] = date('Y-m-d',strtotime($filter['value']));
            break;
        }
        return $where;
    }
    public function aplyFilters($rule, $filters){
        $where = [];
        //constraints
        foreach($filters as $name=>$filter){
            if(isset($filter['constraints'])){
                if($filter['operator'] == 'and'){
                    foreach($filter['constraints'] as $filter2){
                        $where2 = $this->aplyFilter($rule, $name, $filter2);
                        $where = array_merge($where,$where2);
                    }
                }else if($filter['operator'] == 'or'){
                    $where2 = [];$where4 = [];
                    foreach($filter['constraints'] as $filter2){
                        $where3 = $this->aplyFilter($rule, $name, $filter2);
                        $where2 = array_merge($where2,$where3);
                    }
                    foreach($where2 as $field=>$value){
                        if(empty($where4)){
                            $where4[$field] = $value;
                        }else{
                            $where4['OR:'.$field] = $value;
                        }
                    }
                    $where[] = $where4;
                }
            }else{
                $where2 = $this->aplyFilter($rule, $name, $filter);
                $where = array_merge($where,$where2);
            }
        }
        return $where;
    }
    public function checkPermissions($rule_action){
        if($rule_action['authenticated']){
            if(!$this->modx->user->id > 0) return $this->error("Not api authenticated!",['user_id'=>$this->modx->user->id]);
        }
        if($rule_action['groups']){
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if($rule_action['permitions']){
            $permitions = array_map('trim', explode(',', $rule_action['permitions']));
            foreach($permitions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }
    public function success($message = "",$data = []){
        //return array('success'=>1,'message'=>$message,'data'=>$data);
        header("HTTP/1.1 200 OK");
        return array('success'=>1,'message'=>$message,'data'=>$data);;
    }
    public function error($message = "",$data = []){
        return array('success'=>'error','message'=>$message,'data'=>$data);
    }
    public function addPackages($packages){
        $packages = array_map('trim', explode(',', $packages));
        foreach($packages as $package){
            $this->modx->addPackage($package, MODX_CORE_PATH . "components/$package/model/");
            $this->getService($package);
        }
    }
    // public function addPackages($package_id){
    //     if($gtsAPIPackage = $this->modx->getObject('gtsAPIPackage',$package_id)){
    //         $this->getService($gtsAPIPackage->name);
    //     }
    // }
    public function getService($package){
        
        $class = strtolower($package);
        $path = MODX_CORE_PATH."/components/$class/model/";
        if(file_exists($path."$class.class.php")){
            if(!$this->models[$package] = $this->modx->getService($package,$class,$path,[])) {
                return $this->error("Компонент $package не найден!");
            }
        }else if(file_exists($path."$class/"."$class.class.php")){
            if(!$this->models[$package] = $this->modx->getService($package,$class,$path."$class/",[])) {
                return $this->error("Компонент $package не найден!");
            }
        }
        $service = $this->models[$package];

        if(!empty($service) and method_exists($service,'regTriggers')){ 
            $triggers =  $service->regTriggers();
            foreach($triggers as &$trigger){
                $trigger['model'] = $package;
            }
            $this->triggers = array_merge($this->triggers,$triggers);
        }
        return $this->success();
    }
    public function run_triggers($class, $type, $method, $fields, $object_old, $object_new =[], $object = null)
    {
        if(empty($class)) return $this->success('Выполнено успешно');
        // $getTablesRunTriggers = $this->modx->invokeEvent('gtsAPIRunTriggers', [
        //     'class'=>$class,
        //     'type'=>$type,
        //     'method'=>$method,
        //     'fields'=>$fields,
        //     'object_old'=>$object_old,
        //     'object_new'=>$object_new,
        //     'object'=>&$object,
        // ]);
        // if (is_array($getTablesRunTriggers)) {
        //     $canSave = false;
        //     foreach ($getTablesRunTriggers as $msg) {
        //         if (!empty($msg)) {
        //             $canSave .= $msg."\n";
        //         }
        //     }
        // } else {
        //     $canSave = $getTablesRunTriggers;
        // }
        // if(!empty($canSave)) return $this->error($canSave);
        
        $triggers = $this->triggers;
        if(isset($triggers[$class]['function']) and isset($triggers[$class]['model'])){
            // $this->modx->log(1,"create triggers $class {$triggers[$class]['function']}");
            
            $service = $this->models[$triggers[$class]['model']];
            if(method_exists($service,$triggers[$class]['function'])){ 
                // $this->modx->log(1,"create triggers 2 {$triggers[$class]['function']}");
                return  $service->{$triggers[$class]['function']}($class, $type, $method, $fields, $object_old, $object_new);
            }
        }
        if(isset($triggers[$class]['gtsfunction']) and isset($triggers[$class]['model'])){
            $service = $this->models[$triggers[$class]['model']];
            if(method_exists($service,$triggers[$class]['gtsfunction'])){ 
                //$this->getTables->addTime("run_triggers gtsfunction");
                return  $service->{$triggers[$class]['gtsfunction']}(null,$class, $type, $method, $fields, $object_old, $object_new);
            }
        }
        if(isset($triggers[$class]['gtsfunction2']) and isset($triggers[$class]['model'])){
            $service = $this->models[$triggers[$class]['model']];
            if(method_exists($service,$triggers[$class]['gtsfunction2'])){ 
                $params = [
                    'class'=>$class,
                    'type'=>$type,
                    'method'=>$method,
                    'fields'=>$fields,
                    'object_old'=>$object_old,
                    'object_new'=>$object_new,
                    'object'=>&$object,
                ];
                return  $service->{$triggers[$class]['gtsfunction2']}($params);
            }
        }
        return $this->success('Выполнено успешно');
    }
}
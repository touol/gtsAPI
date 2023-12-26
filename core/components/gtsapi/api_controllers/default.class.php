<?php

class defaultAPIController{
    public $config = [];
    public $modx;
    public $pdo;

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
                $request['action'] = 'read';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'PUT':
                // if($id) $request['id'] = $id;
                $request['action'] = 'create';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'PATCH':
                if($id) $request['id'] = $id;
                $request['action'] = 'update';
                return $this->route_post($rule, $uri, $method, $request);
            break;
            case 'DELETE':
                if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['action'] = 'delete';
                return $this->route_post($rule, $uri, $method, $request);
            break;
        }
        return $this->route_post($rule, $uri, $method, $request);
    }
    public function route_post($rule, $uri, $method, $request){
        if(empty($request['action'])) $request['action'] = 'create';
        if(!isset($rule['aсtions'][$request['action']])){
            $this->error("Not api action!");
        }
        $resp = $this->checkPermissions($rule);

        if($resp['success'] == 'error'){
            header('HTTP/1.1 401 Unauthorized2');
            return $resp;
        }
        $resp = $this->checkPermissions($rule['aсtions'][$request['action']]);

        if($resp['success'] == 'error'){
            header('HTTP/1.1 401 Unauthorized1');
            return $resp;
        }
        if(!empty($rule['packages'])) $this->addPackages($rule['packages']);
        if(!$request['skip_sanitize']) $request = $this->modx->sanitize($request, $this->modx->sanitizePatterns);
        
        if(empty($rule['aсtions'][$request['action']])){
            header('HTTP/1.1 404 Not found');
            return $this->error('Not found action!');
        }

        switch($request['action']){
            case 'create':
                return $this->create($rule,$request,$rule['aсtions'][$request['action']]);
            break;
            case 'read':
                return $this->read($rule,$request,$rule['aсtions'][$request['action']]);
            break;
            case 'update':
                return $this->update($rule,$request,$rule['aсtions'][$request['action']]);
            break;
            case 'delete':
                return $this->delete($rule,$request,$rule['aсtions'][$request['action']]);
            break;
        }
        return $this->error("test2!");
    }
    public function addPackages($packages){
        $packages = array_map('trim', explode(',', $packages));
        foreach($packages as $package){
            $this->modx->addPackage($package, MODX_CORE_PATH . "components/$package/model/");
        }
    }
    
    public function delete($rule,$request,$action){
        if(!empty($request['ids'])){
            if(is_string($request['ids'])) $request['ids'] = explode(',',$request['ids']);
            $objs = $this->modx->getIterator($rule['class'],['id:IN'=>$request['ids']]);
            foreach($objs as $obj){
                $obj->remove();
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
            if($obj->save()){
                $object = $obj->toArray();
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
                $object = $obj->fromArray($request);
                if($obj->save()){
                    $object = $obj->toArray();
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
        return $data;
    }
    public function error($message = "",$data = []){
        return array('success'=>'error','message'=>$message,'data'=>$data);
    }
}
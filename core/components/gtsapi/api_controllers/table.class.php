<?php

class tableAPIController{
    public $config = [];
    public $modx;
    public $pdo;
    public $pdoTools;
    public $models = [];
    public $triggers = [];

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            
        ], $config);

        if ($this->pdo = $this->modx->getService('myPdo','myPdo',$corePath.'classes/',[])) {
            $this->pdo->setConfig($this->config);
        }
        $this->pdoTools = $this->modx->getService('pdoFetch');
    }
    public function route($gtsAPITable, $uri, $method, $request){
        $req = json_decode(file_get_contents('php://input'), true);
        if(is_array($req)) $request = array_merge($request,$req);    
        switch($method){
            case 'GET':
                // if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['api_action'] = 'read';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'PUT':
                // if($id) $request['id'] = $id;
                $request['api_action'] = 'create';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'PATCH':
                //if($id) $request['id'] = $id;
                $request['api_action'] = 'update';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'DELETE':
                //if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['api_action'] = 'delete';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'OPTIONS':
                //if($id and empty($request['ids'])) $request['ids'] = [$id];
                $request['api_action'] = 'options';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
        }
        return $this->route_post($gtsAPITable, $uri, $method, $request);
    }
    public function route_post($gtsAPITable, $uri, $method, $request){
        // $this->modx->log(1,"route_post ".print_r($request,1));
        if(empty($request['api_action'])) $request['api_action'] = 'create';
        $rule = $gtsAPITable->toArray();
        if(empty($rule['class'])) $rule['class'] = $rule['table'];

        $resp = $this->checkPermissions($rule);

        if(!$resp['success']){
            // header('HTTP/1.1 401 Unauthorized2');
            return $resp;
        }

        $properties = false;
        if($rule['properties']){
            $properties = json_decode($rule['properties'],1);
        }
        if($properties and is_array($properties)){
            $rule['properties'] = $properties;
        }else{
            $rule['properties'] = [];
        }
        // $this->modx->log(1,"route_post ".print_r($rule['properties'],1).print_r($request,1));
        $action = explode('/',$request['api_action']);
        if(count($action) == 1 and !in_array($request['api_action'],['options','autocomplete']) and isset($rule['properties']['actions'])){
            if(!isset($rule['properties']['actions'][$request['api_action']]) and !isset($rule['properties']['hide_actions'][$request['api_action']])){
                return $this->error("Not api action!");
            }

            if(isset($rule['properties']['actions'][$request['api_action']])){
                $resp = $this->checkPermissions($rule['properties']['actions'][$request['api_action']]);
                if(!$resp['success']){
                    // header('HTTP/1.1 401 Unauthorized1');
                    return $resp;
                }
            }
            if(isset($rule['properties']['hide_actions'][$request['api_action']])){
                $resp = $this->checkPermissions($rule['properties']['hide_actions'][$request['api_action']]);
                if(!$resp['success']){
                    // header('HTTP/1.1 401 Unauthorized1');
                    return $resp;
                }
            }
        }
        if(in_array($request['api_action'],['autocomplete'])){
            if(empty($rule['properties']['autocomplete'])) return $this->error("Not api autocomplete!");
        }
        $this->addPackages($rule['package_id']);
        
        if(isset($rule['properties']['loadModels'])){
            $loadModels = explode(',',$rule['properties']['loadModels']);
            foreach($loadModels as $package){
                $resp = $this->getService($package);
                if(!$resp['success']){
                    return $resp;
                }
            }
        }

        if(!isset($rule['properties']['aсtions'][$request['api_action']]['skip_sanitize']))
            $request = $this->modx->sanitize($request, $this->modx->sanitizePatterns);
        
        // if(empty($rule['aсtions'][$request['api_action']])){
        //     header('HTTP/1.1 404 Not found');
        //     return $this->error('Not found action!');
        // }

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
            case 'options':
                return $this->options($rule,$request,$rule['aсtions'][$request['api_action']]);
            case 'autocomplete':
                return $this->get_autocomplete($rule,$request);
            break;
            default:
                $action = explode('/',$request['api_action']);
                // $this->modx->log(1,"route_post {$request['api_action']}");
                // return $this->error("test11!".print_r(array_keys($this->models),1));
                if(count($action) == 2 and isset($this->models[strtolower($action[0])])){
                    
                    $service = $this->models[strtolower($action[0])];

                    if(method_exists($service,'handleRequest')){ 
                        return $service->handleRequest($action[1], $request);
                    }
                }
        }
        return $this->error("test2!");
    }
    public function get_autocomplete($rule,$request){
        
        $default = [
            'class' => $rule['class'],
            'select' => [
                $rule['class'] => '*',
            ],
            'sortby' => [
                "{$rule['class']}.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => 0
        ];
        
        $autocomplete = $rule['properties']['autocomplete'];
        if(isset($autocomplete['query']) and is_array($autocomplete['query'])) 
            $default = array_merge($default,$autocomplete['query']);

        if(isset($autocomplete['select'])){
            $selects_fields = [];
            foreach($autocomplete['select'] as $field){
                $selects_fields[] = $rule['class'].'.'.$field;
            }
            $default['select'][$rule['class']] = implode(',',$selects_fields);
        }
        

        if(!empty($request['query']) or !empty($request['parent'])){
            if(empty($default['where'])) $default['where'] = [];
            $where = [];
            foreach($autocomplete['where'] as $field=>$value){
                if(!empty($request['query']) and strpos($value,'query') !== false){
                    $value = str_replace('query',$request['query'],$value);
                    $where[$field] = $value;
                }
                if(!empty($request['parent'])){
                    foreach($request['parent'] as $pfield=>$pval){
                        if($value == $pfield){
                            $where[$field] = $pval;
                        }
                    }
                }
            }
            $default['where'] = array_merge($default['where'],$where);
        }
        $default['decodeJSON'] = 1;
        if(!empty($request['id'])){
            $default['where']["{$rule['class']}.id"] = $request['id'];
        }
        if(isset($autocomplete['limit'])){
            $default['limit'] = $autocomplete['limit'];
        }
        if(isset($request['offset'])){
            $default['offset'] = $request['offset'];
        }else{
            $request['offset'] = 0;
        }
        
        
        $default['setTotal'] = true;
        
        if($request['sortField']){
            $default['sortby'] = [
                "{$request['sortField']}" => $request['sortOrder'] == 1 ?'ASC':'DESC',
            ];
        }
        if($request['multiSortMeta']){
            $default['sortby'] = [];
            foreach($request['multiSortMeta'] as $sort){
                $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ?'ASC':'DESC';
            }
        }
        $this->pdo->setConfig($default);
        $rows0 = $this->pdo->run();
        if(!empty($autocomplete['tpl'])){
            foreach($rows0 as $k=>$row){
                $rows0[$k]['content'] = $this->pdoTools->getChunk("@INLINE ".$autocomplete['tpl'],$row);
            }
        }
        
        $total = (int)$this->modx->getPlaceholder('total');
        
        // $rows = [];
        // foreach($rows0 as $row){
        //     $rows[$row['id']] = $row;
        // }
        $default = '';
        if(isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])){
            if($obj = $this->modx->getObject($rule['class'],$autocomplete['default_row'])){
                $default = $obj->id;
            }
        }
        $out = [
            'rows'=>$rows0,
            'total'=>$total,
            'default'=>$default,
            // 'log'=>$this->pdo->getTime()
        ];
        if($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();

        return $this->success('',$out);
    }
    public function request_array_to_json($request){
        $req = [];
        foreach($request as $k=>$v){
            if(is_array($v)){
                $req[$k] = json_encode($v,JSON_PRETTY_PRINT);
            }else{
                $req[$k] = $v;
            }
        }
        return $req;
    }
    public function options($rule,$request,$action){
        
        if(empty($rule['properties']['fields'])){
            $fields = $this->gen_fields($rule);
        }else{
            $fields = $rule['properties']['fields'];
        }
        $actions = [];
        if(isset($rule['properties']['actions'])){
            foreach($rule['properties']['actions'] as $action =>$v){
                $resp = $this->checkPermissions($rule['properties']['actions'][$action]);

                if($resp['success']){
                    $actions[$action] = $v;
                }
            }
        }
        foreach($fields as $k =>$v){
            if(isset($v['default'])){
                if($v['default'] == 'user_id') $fields[$k]['default'] = $this->modx->user->id;
                if($v['type'] == 'date') $fields[$k]['default'] = date('Y-m-d',strtotime($v['default']));
            }
            if(isset($v['readonly']) and is_array($v['readonly'])){
                if(isset($v['readonly']['authenticated']) and $v['readonly']['authenticated'] == 1){
                    if($this->modx->user->id > 0) $fields[$k]['readonly'] = 0; 
                }
        
                if(isset($v['readonly']['groups']) and !empty($v['readonly']['groups'])){
                    // $this->modx->log(1,"checkPermissions groups".print_r($rule_action['groups'],1));
                    $groups = array_map('trim', explode(',', $v['readonly']['groups']));
                    if($this->modx->user->isMember($groups)) $fields[$k]['readonly'] = 0;
                }
                if(isset($v['readonly']['permitions'])and !empty($v['readonly']['permitions'])){
                    $permitions = array_map('trim', explode(',', $v['readonly']['permitions']));
                    foreach($permitions as $pm){
                        if($this->modx->hasPermission($pm)) $fields[$k]['readonly'] = 0;
                    }
                }
                if(is_array($fields[$k]['readonly'])) $fields[$k]['readonly'] = 1;
            }
            if(isset($v['disabled']) and is_array($v['disabled'])){
                if(isset($v['disabled']['authenticated']) and $v['disabled']['authenticated'] == 1){
                    if($this->modx->user->id > 0) unset($fields[$k]['disabled']); 
                }
        
                if(isset($v['disabled']['groups']) and !empty($v['disabled']['groups'])){
                    // $this->modx->log(1,"checkPermissions groups".print_r($rule_action['groups'],1));
                    $groups = array_map('trim', explode(',', $v['disabled']['groups']));
                    if($this->modx->user->isMember($groups)) unset($fields[$k]['disabled']);
                }
                if(isset($v['disabled']['permitions'])and !empty($v['disabled']['permitions'])){
                    $permitions = array_map('trim', explode(',', $v['disabled']['permitions']));
                    foreach($permitions as $pm){
                        if($this->modx->hasPermission($pm)) unset($fields[$k]['disabled']);
                    }
                }
            }
            if(isset($fields[$k]['disabled'])) unset($fields[$k]);
        }
        $selects = $this->getSelects($fields);

        $filters = [];
        if(!empty($rule['properties']['filters'])){
            foreach($rule['properties']['filters'] as $field=>$filter){
                if($filter['type'] == 'autocomplete'){
                    if($gtsAPITable = $this->modx->getObject('gtsAPITable',['table'=>$filter['table'],'active'=>1])){
                        $properties = json_decode($gtsAPITable->properties,1);
                        if(is_array($properties) and isset($properties['autocomplete'])){
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if(isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $filter['table'];
                            $autocomplete['class'] = $gtsAPITable->class?$gtsAPITable->class:$filter['table'];
                            $tmp = $this->autocomplete($autocomplete,[]);
                            $filter['rows'] = $tmp['rows'];
                    
                            if(isset($filter['default_row']) and is_array($filter['default_row'])){
                                if($obj = $this->modx->getObject($autocomplete['class'],$filter['default_row'])){
                                    $filter['default'] = $obj->id;
                                }
                            }
                        }
                    }
                }
                
                $filters[$field] = $filter;
            }
        }
        return $this->success('options',[
            'fields'=>$fields,
            'actions'=>$actions,
            'selects'=>$selects,
            'filters'=>$filters,
        ]);
    }
    public function getSelects($fields){
        $selects = [];
        foreach($fields as $field =>$v){
            if($v['type'] == 'select'){
                if($gtsAPISelect = $this->modx->getObject('gtsAPISelect',['field'=>$field])){
                    $rows0 = json_decode($gtsAPISelect->rows,1);
                    $rows = [];
                    if(!is_array($rows0)){
                        $rows0 = array_map('trim',explode(',',$gtsAPISelect->rows));
                    }
                    foreach($rows0 as $row){
                        if(count($row) == 2){
                            $rows[] = $row;
                        }else{
                            $rows[] = [$row,$row];
                        }
                    }
                    $rowsEnd = [];
                    foreach($rows as $row){
                        $rowsEnd[] = [
                            'id'=>$row[0],
                            'content'=>$row[1],
                        ];
                    }
                    $selects[$field]['rows'] = $rowsEnd;
                }
            }
        }
        return $selects;
    }
    public function gen_fields_class($class,$select = '*'){
        $fields0 = [];
        $fields = [];
        $selects = [];
        // $this->modx->log(1,"gen_fields_class0 $class $select");
        if($select == '*'){
            if ($className = $this->modx->loadClass($class)){
                if (isset ($this->modx->map[$class])) {
                    foreach($this->modx->map[$class]['fieldMeta'] as $field=>$meta){
                        $selects[$field] = 1;
                    }
                }
            }
        }else{
            $select = preg_replace_callback('/\(.*?\bAS\b/i', function($matches) {
                return str_replace(",", "|", $matches[0]);
            }, $select);
            
            $selects0 = explode(',', $select);
            foreach ($selects0 as $k=>$select) {
                $select = str_replace('|', ',', $select);
                // $this->modx->log(1,"gen_fields_class01 $class $select");
                if(strpos($select, '(') !== false){
                    $tmp = array_map('trim',preg_split('/\bAS\b/i',$select));
                    if(isset($tmp[1])){
                        $selects[$tmp[1]] = 2;
                    } 
                }else{
                    $tmp = array_map('trim',preg_split('/\bAS\b/i',$select));
                    if(isset($tmp[1])){
                        $selects[$tmp[1]] = 2;
                    }else{
                        // $this->modx->log(1,"gen_fields_class $class $select");
                        $select = str_replace(['`',$class.'.','.'], '', $select);
                        if($select != 'id') $selects[$select] = 1;
                        // $this->modx->log(1,"gen_fields_class2 $class $select");
                    }
                }
            }
        }
        if ($className = $this->modx->loadClass($class)){
            if (isset ($this->modx->map[$class])) {
                foreach($this->modx->map[$class]['fieldMeta'] as $field=>$meta){
                    if(!isset($selects[$field]) or $selects[$field] == 2) continue;
                    switch($meta['dbtype']){
                        case 'varchar':
                            $fields[$field] = ['type'=>'text'];
                        break;
                        case 'text': case 'longtext':
                            $fields[$field] = ['type'=>'textarea'];
                        break;
                        case 'int':
                            $fields[$field] = ['type'=>'number'];
                        break;
                        case 'double': case 'decimal':
                            $fields[$field] = ['type'=>'decimal','FractionDigits'=>2];
                        break;
                        case 'tinyint':
                            if($meta['phptype'] == 'boolean'){
                                $fields[$field] = ['type'=>'boolean'];
                            }else{
                                $fields[$field] = ['type'=>'number'];
                            }
                        break;
                        case 'date':
                            $fields[$field] = ['type'=>'date'];
                        break;
                        case 'datetime':
                            $fields[$field] = ['type'=>'datetime'];
                        break;
                    }
                }
            }
        }
        foreach($selects as $field=>$select){
            if($gtsAPITable = $this->modx->getObject('gtsAPITable',['autocomplete_field'=>$field])){
                $fields0[$field] = [
                    'type'=>'autocomplete',
                    'table'=>$gtsAPITable->table
                ];
                if(isset($fields[$field])){
                    $fields0[$field]['class'] = $class;
                }else{
                    $fields0[$field] = ['type'=>'text','readonly'=>1];
                }
            }else if($gtsAPISelect = $this->modx->getObject('gtsAPISelect',['field'=>$field])){
                // $rows0 = json_decode($gtsAPISelect->rows,1);
                // $rows = [];
                // if(!is_array($rows0)){
                //     $rows0 = array_map('trim',explode(',',$gtsAPISelect->rows));
                // }
                // foreach($rows0 as $row){
                //     if(count($row) == 2){
                //         $rows[] = $row;
                //     }else{
                //         $rows[] = [$row,$row];
                //     }
                // }
                
                $fields0[$field] = [
                    'type'=>'select',
                    //'rows'=>$rows
                ];
                if(isset($fields[$field])){
                    $fields0[$field]['class'] = $class;
                }else{
                    $fields0[$field] = ['type'=>'text','readonly'=>1];
                } 
            }else if(isset($fields[$field])){
                $fields[$field]['class'] = $class;
                $fields0[$field] = $fields[$field];
            }else{
                $fields0[$field] = ['type'=>'text','readonly'=>1];
            }

        }
        return $fields0;
    }
    public function gen_fields($rule){
        
        $fields = ['id'=>['type'=>'view','class'=>$rule['class']]];
        if(empty($rule['properties']['query']) or empty($rule['properties']['query']['select'])){
            $fields = array_merge($fields,$this->gen_fields_class($rule['class']));
        }else{
            if(is_array($rule['properties']['query']['select'])){
                foreach($rule['properties']['query']['select'] as $class=>$select){
                    $fields = array_merge($fields,$this->gen_fields_class($class, $select));
                }
            }
        }
        if($gtsAPITable = $this->modx->getObject('gtsAPITable',$rule['id'])){
            $rule['properties']['fields'] = $fields;
            $gtsAPITable->properties = json_encode($rule['properties'],JSON_PRETTY_PRINT);
            $gtsAPITable->save();
        }

        return $fields;
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
        
        $obj = $this->modx->newObject($rule['class']);
        
        $object_old = $obj->toArray();
        if(isset($request['id'])){
            $object = $obj->fromArray($request,'',true);
        }else{
            $object = $obj->fromArray($request);
        }
        
        $object_new = $obj->toArray();

        // $this->modx->log(1,"create triggers".print_r($this->triggers,1));

        $resp = $this->run_triggers($rule['class'], 'before', 'create', $request, $object_old,$object_new,$obj);
        if(!$resp['success']) return $resp;

        if($obj->save()){
            $object = $obj->toArray();

            $resp = $this->run_triggers($rule['class'], 'after', 'create', $request, $object_old,$object,$obj);
            if(!$resp['success']) return $resp;

            header('HTTP/1.1 201 Created');
            return $this->success('created',$object);
        }
        return $this->error('create_error',$request);
    }
    public function update($rule,$request,$action){
        
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
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
    public function read($rule,$request,$action){
        $resp = $this->run_triggers($rule['class'], 'before', 'read', $request);
        if(!$resp['success']) return $resp;

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
        
        if(!empty($request['query'])){
            if(empty($rule['properties']['queryes'][$request['query']]))
                return $this->error('not query');
            $default = array_merge($default, $rule['properties']['queryes'][$request['query']]);
        }
        if(!empty($rule['properties']['query'])){
            $default = array_merge($default, $rule['properties']['query']);
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
        }else{
            $request['offset'] = 0;
        }
        
        if($request['setTotal']){
            $default['setTotal'] = true;
        }
        if($request['sortField']){
            $default['sortby'] = [
                "{$request['sortField']}" => $request['sortOrder'] == 1 ?'ASC':'DESC',
            ];
        }
        if($request['multiSortMeta']){
            $default['sortby'] = [];
            foreach($request['multiSortMeta'] as $sort){
                $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ?'ASC':'DESC';
            }
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
        $row_setting = [];
        if(isset($rule['properties']['row_setting'])){
            if(isset($rule['properties']['row_setting']['class'])){
                foreach($rows0 as $row){
                    $row_setting[$row['id']]['class'] = $this->pdoTools->getChunk("@INLINE ".$rule['properties']['row_setting']['class'],$row);
                }
            }
        }
        
        if(isset($rule['properties']['fields'])){
            $get_html = false;
            foreach($rule['properties']['fields'] as $field=>$v){
                if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) $get_html = true;
            }
            if($get_html){
                foreach($rows0 as $k=>$row){
                    foreach($rule['properties']['fields'] as $field=>$v){
                        if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])){
                            $rows0[$k][$field] = $this->pdoTools->getChunk("@INLINE ".$v['tpl'],$row);
                        }
                    }
                }
            }
        }
        
        $out = [
            'rows'=>$rows0,
            'total'=>$total,
            // 'autocomplete'=>$autocompletes,
            'row_setting'=>$row_setting,
            // 'log'=>$this->pdo->getTime()
        ];
        if($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();

        $out['autocomplete'] = $this->autocompletes($rule['properties']['fields'],$rows0,$request['offset']);

        $resp = $this->run_triggers($rule['class'], 'after', 'read', $request, $out);
        if(!$resp['success']) return $resp;
        if(!empty($resp['data']['out'])) $out = $resp['data']['out'];

        return $this->success('',$out);
    }
    public function autocompletes($fields, $rows0, $offset){
        //return $fields;
        if(empty($fields)) return [];
        $autocompletes = [];
        foreach($fields as $field=>$desc){
            if(isset($desc['type'])){
                if($desc['type'] == 'autocomplete' and isset($desc['table'])){
                    
                    if($gtsAPITable = $this->modx->getObject('gtsAPITable',['table'=>$desc['table'],'active'=>1])){
                        $properties = json_decode($gtsAPITable->properties,1);
                        if(is_array($properties) and isset($properties['autocomplete'])){
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if(isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $desc['table'];
                            $autocomplete['class'] = $gtsAPITable->class?$gtsAPITable->class:$desc['table'];
                            $autocompletes[$field] = $this->autocomplete($autocomplete,$rows0);
                        }
                    }
                }
            }
        }
        return $autocompletes;
    }
    public function autocomplete($autocomplete, $rows0){
        if(!isset($autocomplete['limit'])) $autocomplete['limit'] = 15;
        $default = [
            'class' => $autocomplete['class'],
            'select' => [
                $autocomplete['class'] => '*',
            ],
            'sortby' => [
                "{$autocomplete['class']}.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => $autocomplete['limit']
        ];
        if(isset($autocomplete['select'])){
            $selects_fields = [];
            foreach($autocomplete['select'] as $field){
                $selects_fields[] = $autocomplete['class'].'.'.$field;
            }
            $default['select'][$autocomplete['class']] = implode(',',$selects_fields);
        }
        if(isset($autocomplete['query']) and is_array($autocomplete['query'])) 
            $default = array_merge($default,$autocomplete['query']);
        if($autocomplete['limit'] > 0){
            $ids = [];
            foreach($rows0 as $row){
                if((int)$row[$autocomplete['field']] > 0) $ids[$row[$autocomplete['field']]] = $row[$autocomplete['field']];
            }
            if(!empty($ids)){
                $default['where'][$autocomplete['class'].'.id:IN'] = $ids;
                $default['limit'] = 0;
            }
        }
        $default['setTotal'] = true;
        $this->pdo->setConfig($default);
        $autocomplete['rows'] = $this->pdo->run();
        if(!empty($autocomplete['tpl'])){
            foreach($autocomplete['rows'] as $k=>$row){
                $autocomplete['rows'][$k]['content'] = $this->pdoTools->getChunk("@INLINE ".$autocomplete['tpl'],$row);
            }
        }
        $autocomplete['log'] = $this->pdo->getTime();
        $autocomplete['total'] = (int)$this->modx->getPlaceholder('total');
        return $autocomplete;
    }
    public function aplyFilter($rule, $name, $filter){
        
        $where = [];
        if($filter['value'] == null) return $where;
        
        $field = "{$rule['class']}.$name";
        if(isset($filter['class']))  $field = "{$filter['class']}.$name";
        if(strpos($name,'.') !== false) $field = $name;
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
    // public function getFields($class){
    //     echo '<pre>'.print_r($modx->map['modResource'],1).'</pre>';
    // }
    public function checkPermissions($rule_action){
        // $this->modx->log(1,"checkPermissions ".print_r($rule_action,1));
        if(isset($rule_action['authenticated']) and $rule_action['authenticated'] == 1){
            if(!$this->modx->user->id > 0) return $this->error("Not api authenticated!",['user_id'=>$this->modx->user->id]);
        }

        if(isset($rule_action['groups']) and !empty($rule_action['groups'])){
            // $this->modx->log(1,"checkPermissions groups".print_r($rule_action['groups'],1));
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if(isset($rule_action['permitions'])and !empty($rule_action['permitions'])){
            $permitions = array_map('trim', explode(',', $rule_action['permitions']));
            foreach($permitions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }
    public function success($message = "",$data = []){
        header("HTTP/1.1 200 OK");
        return ['success'=>1,'message'=>$message,'data'=>$data];
        
        return $data;
    }
    public function error($message = "",$data = []){
        return ['success'=>0,'message'=>$message,'data'=>$data];
    }

    public function addPackages($package_id){
        if($gtsAPIPackage = $this->modx->getObject('gtsAPIPackage',$package_id)){
            $this->getService($gtsAPIPackage->name);
        }
    }
    public function getService($package){
        // $this->modx->log(1,"getService $package ");
        $class = strtolower($package);
        if($class == 'modx') return $this->success();

        $path = MODX_CORE_PATH."/components/$class/model/";
        if(file_exists($path."$class.class.php")){
            if(!$this->models[$class] = $this->modx->getService($class,$class,$path,[])) {
                return $this->error("Компонент $package не найден!");
            }
        }else if(file_exists($path."$class/"."$class.class.php")){
            if(!$this->models[$class] = $this->modx->getService($class,$class,$path."$class/",[])) {
                return $this->error("Компонент $package не найден!");
            }
        }else{
            $this->modx->addPackage($class, MODX_CORE_PATH . "components/{$class}/model/");
            return $this->success("Компонент $package не имеет сервиса!");
        }
        $service = $this->models[$class];

        if(method_exists($service,'regTriggers')){ 
            $triggers =  $service->regTriggers();
            foreach($triggers as &$trigger){
                $trigger['model'] = $class;
            }
            $this->triggers = array_merge($this->triggers,$triggers);
        }
        // $this->modx->log(1,"getService $package "."test2!".print_r(array_keys($this->models),1));
        return $this->success();
    }
    public function run_triggers($class, $type, $method, $fields, $object_old = [], $object_new =[], $object = null)
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
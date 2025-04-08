<?php

class treeAPIController{
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
        $this->pdo->setConfig([
            'class'=>'gtsAPIUniTreeClass',
            'where'=>[
                'table_id'=>$gtsAPITable->id,
            ],
            'return'=>'data',
            'limit'=>0,
        ]);
        $gtsAPIUniTreeClasses = $this->pdo->run();
        $gtsAPIUniTreeClass = [];
        if(is_array($gtsAPIUniTreeClasses) and count($gtsAPIUniTreeClasses)>0){
            foreach($gtsAPIUniTreeClasses as $tmp){
                if(empty($tmp['class'])) $tmp['class'] = $tmp['table'];
                $gtsAPIUniTreeClass[$tmp['table']] = $tmp;
            }
        }
        $rule['gtsAPIUniTreeClass'] = $gtsAPIUniTreeClass;

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
        if(count($action) == 1 and !in_array($request['api_action'],['options','autocomplete','nodedrop']) and isset($rule['properties']['actions'])){
            $api_action = $request['api_action'];
            if($api_action == 'watch_form') $api_action = $request['watch_action'];

            if(!isset($rule['properties']['actions'][$api_action]) and !isset($rule['properties']['hide_actions'][$api_action])){
                return $this->error("Not api action!");
            }

            if(isset($rule['properties']['actions'][$api_action])){
                $resp = $this->checkPermissions($rule['properties']['actions'][$api_action]);
                if(!$resp['success']){
                    // header('HTTP/1.1 401 Unauthorized1');
                    return $resp;
                }
            }
            if(isset($rule['properties']['hide_actions'][$api_action])){
                $resp = $this->checkPermissions($rule['properties']['hide_actions'][$api_action]);
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
                return $this->create($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'insert':
                return $this->create($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'insert_child':
                return $this->create($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'read':
                return $this->read($rule,$request,$rule['aсtions'][$request['api_action']]);
            break;
            case 'update':
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
            case 'watch_form':
                return $this->watch_form($rule,$request);
            break;
            case 'nodedrop':
                return $this->nodedrop($rule,$request);
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
        return $this->error("Не найдено действие!".print_r($this->models,1));
    }
    public function get_slTreeSettings($rule){
        $slTreeSettings = [
            'rootIds'=>$rule['properties']['rootIds']?$rule['properties']['rootIds']:0,
            'idField'=>$rule['properties']['idField']?$rule['properties']['idField']:'id',
            'parentIdField'=>$rule['properties']['parentIdField']?$rule['properties']['parentIdField']:'parent_id',
            'parents_idsField'=>$rule['properties']['parents_idsField']?$rule['properties']['parents_idsField']:'parents_ids',
            'isLeaf' => $rule['properties']['isLeaf'] ? $rule['properties']['isLeaf'] : [],
            'menuindexField'=>$rule['properties']['menuindexField']?$rule['properties']['menuindexField']:'menuindex',
            'extendedModResource'=>$rule['properties']['extendedModResource']?$rule['properties']['extendedModResource']:false,
        ];
        return $slTreeSettings;
    }

    public function nodedrop($rule,$request){
        $class = $rule['class'];
        $slTreeSettings = $this->get_slTreeSettings($rule);
        $position = $request['position1'];
        
        if($rule['properties']['useUniTree']){
            foreach($request['nodes1'] as $node){
                if($obj = $this->modx->getObject($class,$node['id'])){
                    if($position['node']['parent_id'] != $node['parent_id']){
                        $obj->set($slTreeSettings['parentIdField'],$position['node']['parent_id']);
                        if($position['node']['parent_id'] == 0){
                            if($position['placement'] != 'inside'){
                                $patents_ids_old = $obj->get($slTreeSettings['parents_idsField']);
                                if($patents_ids_old != ''){
                                    $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = REPLACE({$slTreeSettings['parents_idsField']}, $patents_ids_old, $patents_ids)
                                        WHERE {$slTreeSettings['parents_idsField']} LIKE '$patents_ids_old%'");
                                    $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = ''
                                        WHERE {$slTreeSettings['parents_idsField']} = '#'");
                                }
                            }else{
                                if($parentObj = $this->modx->getObject($class,$position['node']['id'])){
                                    $parents_ids = $parentObj->get($slTreeSettings['parents_idsField']);
                                    $patents_ids_old = $obj->get($slTreeSettings['parents_idsField']);
                                    
                                    if($parents_ids == '') $parents_ids = '#';
                                    $parents_ids .= $parentObj->id.'#';
                                    if($patents_ids_old != ''){
                                        $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = REPLACE({$slTreeSettings['parents_idsField']}, $patents_ids_old, $patents_ids)
                                            WHERE {$slTreeSettings['parents_idsField']} LIKE '$patents_ids_old%'");
                                    }else{
                                        $patents_ids_old .= '#'.$obj->id.'#';
                                        $parents_ids .= $obj->id.'#';
                                        $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = REPLACE({$slTreeSettings['parents_idsField']}, $patents_ids_old, $patents_ids)
                                            WHERE {$slTreeSettings['parents_idsField']} LIKE '$patents_ids_old%'");
                                        $obj->set($slTreeSettings['parents_idsField'],'');
                                    }
                                }
                            }
                        }else{
                            if($parentObj = $this->modx->getObject($class,$position['node']['parent_id'])){
                                if($position['placement'] == 'inside'){
                                    $parentObj = $this->modx->getObject($class,$position['node']['id']);
                                }
                                $parents_ids = $parentObj->get($slTreeSettings['parents_idsField']);
                                $patents_ids_old = $obj->get($slTreeSettings['parents_idsField']);
                                
                                if($parents_ids == '') $parents_ids = '#';
                                $parents_ids .= $parentObj->id.'#';
                                if($patents_ids_old != ''){
                                    $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = REPLACE({$slTreeSettings['parents_idsField']}, $patents_ids_old, $patents_ids)
                                        WHERE {$slTreeSettings['parents_idsField']} LIKE '$patents_ids_old%'");
                                }else{
                                    $patents_ids_old .= '#'.$obj->id.'#';
                                    $parents_ids .= $obj->id.'#';
                                    $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['parents_idsField']} = REPLACE({$slTreeSettings['parents_idsField']}, $patents_ids_old, $patents_ids)
                                        WHERE {$slTreeSettings['parents_idsField']} LIKE '$patents_ids_old%'");
                                    $obj->set($slTreeSettings['parents_idsField'],'');
                                }
                            }
                        }
                    }
                    switch($position['placement']){
                        case 'before':
                            $obj->set($slTreeSettings['menuindexField'],$position['node']['menuindex']);
                            $obj->save();

                            $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
                                WHERE {$slTreeSettings['parentIdField']} = {$position['node']['parent_id']} 
                                AND {$slTreeSettings['menuindexField']} >= {$position['node']['menuindex']}");
                        
                            if($slTreeSettings['extendedModResource']){
                                if($source = $this->modx->getObject('modResource', $obj->get('target_id'))
                                    and $targetObj = $this->modx->getObject($class, $position['node']['id'])
                                    and $target = $this->modx->getObject('modResource', $targetObj->get('target_id'))
                                ){
                                    $sort = [
                                        'target' => $target->get('context_key').'_'.$target->get('id'),
                                        'source' => $source->get('context_key').'_'.$source->get('id'),
                                        'point' => 'above',
                                        'data' => urlencode($this->modx->toJSON(['web_0'=>['web_1'=>[]]])),
                                    ];
                                    $modx_response = $this->modx->runProcessor('resource/sort', $sort);
                                    if ($modx_response->isError()) {
                                        return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
                                    }
                                }
                            }
                        break;
                        case 'after':
                            $obj->set($slTreeSettings['menuindexField'],$position['node']['menuindex'] + 1);
                            

                            $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
                                WHERE {$slTreeSettings['parentIdField']} = {$position['node']['parent_id']} 
                                AND {$slTreeSettings['menuindexField']} > {$position['node']['menuindex']}");
                            $obj->save();
                            if($slTreeSettings['extendedModResource']){
                                if($source = $this->modx->getObject('modResource', $obj->get('target_id'))
                                    and $targetObj = $this->modx->getObject($class, $position['node']['id'])
                                    and $target = $this->modx->getObject('modResource', $targetObj->get('target_id'))
                                ){
                                    // return $this->error('Ошибка nodedrop 2');
                                    $sort = [
                                        'target' => $target->get('context_key').'_'.$target->get('id'),
                                        'source' => $source->get('context_key').'_'.$source->get('id'),
                                        'point' => 'below',
                                        'data' => urlencode($this->modx->toJSON(['web_0'=>['web_1'=>[]]])),
                                    ];
                                    $modx_response = $this->modx->runProcessor('resource/sort', $sort);
                                    if ($modx_response->isError()) {
                                        return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
                                    }
                                }
                            }
                        break;
                        case 'inside':
                            // $obj->set($slTreeSettings['menuindexField'],$position['node']['menuindex']);
                            $obj->save();

                            // $this->modx->exec("UPDATE {$class} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
                            //     WHERE {$slTreeSettings['parentIdField']} = {$position['node']['parent_id']} 
                            //     AND {$slTreeSettings['menuindexField']} >= {$position['node']['menuindex']}");
                        
                            if($slTreeSettings['extendedModResource']){
                                if($source = $this->modx->getObject('modResource', $obj->get('target_id'))
                                    and $targetObj = $this->modx->getObject($class, $position['node']['id'])
                                    and $target = $this->modx->getObject('modResource', $targetObj->get('target_id'))
                                ){
                                    // return $this->error('Ошибка nodedrop 2');
                                    $sort = [
                                        'target' => $target->get('context_key').'_'.$target->get('id'),
                                        'source' => $source->get('context_key').'_'.$source->get('id'),
                                        'point' => 'append',
                                        'data' => urlencode($this->modx->toJSON(['web_0'=>['web_1'=>[]]])),
                                    ];
                                    $modx_response = $this->modx->runProcessor('resource/sort', $sort);
                                    if ($modx_response->isError()) {
                                        return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
                                    }
                                }
                                $source->menuindex = 0;
                                $source->save();
                            }
                        break;
                    }
                }
                
            }

        }
        return $this->success('success');
        // return $this->error('Ошибка nodedrop 2');
    }
    public function watch_form($rule,$request){
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;
            // $this->modx->log(1,'gtsAPI run '.print_r($triggers,1));
            if(isset($triggers[$class]['gtsapi_watch_form']) and isset($triggers[$class]['model'])){
                $service = $this->models[$triggers[$class]['model']];
                if(method_exists($service,$triggers[$class]['gtsapi_watch_form'])){ 
                    $params = [
                        'rule'=>$rule,
                        'class'=>$class,
                        'request'=>$request,
                        'fields' => $this->addFields($rule,$rule['properties']['fields'],$request['watch_action']),
                        'trigger'=>'gtsapi_watch_form',
                    ];
                    // $this->modx->log(1,'gtsAPI run '.$triggers[$class]['gtsapifunc']);
                    return  $service->{$triggers[$class]['gtsapi_watch_form']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1,'gtsAPI Ошибка триггера '.$e->getMessage());
            return $this->error('Ошибка триггера '.$e->getMessage());
        }
        return $this->error('Ошибка триггера 2');
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
        

        if(isset($request['query']) or !empty($request['parent'])){
            if(empty($default['where'])) $default['where'] = [];
            $where = [];
            foreach($autocomplete['where'] as $field=>$value){
                if(strpos($value,'query') !== false){
                    if(!empty($request['query'])){
                        $value = str_replace('query',$request['query'],$value);
                        $where[$field] = $value;
                    }
                }else{
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
        if(isset($request['ids']) and is_array($request['ids'])){
            if(empty($default['where'])) $default['where'] = [];
            $default['where']["{$rule['class']}.id:IN"] = $request['ids'];
        }
        $default['decodeJSON'] = 1;
        if(!empty($request['id'])){
            $default['where']["{$rule['class']}.id"] = $request['id'];
        }
        if(!empty($request['show_id']) and isset($autocomplete['show_id_where'])){
            $default['where'][1001] = "({$rule['class']}.id = {$request['show_id']} or {$autocomplete['show_id_where']} = {$request['show_id']})";
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
            'log'=>$this->pdo->getTime()
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
    public function addFields($rule,$fields,$action){

        $gtsAPIFieldTableCount = $this->modx->getCount('gtsAPIFieldTable',['name_table'=>$rule['table'],'add_table'=>1]);
        if($gtsAPIFieldTableCount == 0) return $fields;

        $this->modx->addPackage('gtsshop', $this->modx->getOption('core_path') . 'components/gtsshop/model/');

        $gtsAPIFieldTables = $this->modx->getIterator('gtsAPIFieldTable',['name_table'=>$rule['table'],'add_table'=>1]);
        $addFields = [];
        foreach($gtsAPIFieldTables as $gtsAPIFieldTable){
            $gtsAPIFieldGroupTableLinks = $this->modx->getIterator('gtsAPIFieldGroupTableLink',['table_field_id'=>$gtsAPIFieldTable->id]);
            foreach($gtsAPIFieldGroupTableLinks as $gtsAPIFieldGroupTableLink){
                $gtsAPIFieldGroups = $this->modx->getIterator('gtsAPIFieldGroup',['id'=>$gtsAPIFieldGroupTableLink->group_field_id]);
                foreach($gtsAPIFieldGroups as $gtsAPIFieldGroup){
                    if($gtsAPIFieldGroup->all){
                        $c = $this->modx->newQuery($gtsAPIFieldGroup->from_table);
                        $c->sortby('rank','ASC');
                        $gtsAPIFields = $this->modx->getIterator($gtsAPIFieldGroup->from_table,$c);
                        foreach($gtsAPIFields as $gtsAPIField){
                            $addFields[$gtsAPIField->name] = $gtsAPIField->toArray();
                            $addFields[$gtsAPIField->name]['from_table'] = $gtsAPIFieldGroup->from_table;
                            if(empty($addFields[$gtsAPIField->name]['after_field'])) $addFields[$gtsAPIField->name]['after_field'] = $gtsAPIFieldTable->after_field;
                            $addFields[$gtsAPIField->name]['gtsapi_config'] = json_decode($addFields[$gtsAPIField->name]['gtsapi_config'],1);
                        }
                    }else{
                        $this->pdo->setConfig([
                            'class'=>$gtsAPIFieldGroup->link_group_table,
                            'leftJoin'=>[
                                $gtsAPIFieldGroup->from_table=>[
                                    'class'=>$gtsAPIFieldGroup->from_table,
                                    'on'=>$gtsAPIFieldGroup->from_table.'.id = '.$gtsAPIFieldGroup->link_group_table.'.field_id'
                                ]
                            ],
                            'where'=>[
                                $gtsAPIFieldGroup->link_group_table.'.group_field_id'=>$gtsAPIFieldGroup->id
                            ],
                            'sortby'=>[
                                $gtsAPIFieldGroup->from_table.'.rank'=>'ASC'
                            ],
                            'select'=>[
                                $gtsAPIFieldGroup->from_table=>'*'
                            ],
                            'return' => 'data',
                            'limit' => 0
                        ]);
                        $rows = $this->pdo->run();
                        
                        foreach($rows as $row){
                            $addFields[$row['name']] = $row;
                            $addFields[$row['name']]['from_table'] = $gtsAPIFieldGroup->from_table;
                            if(empty($row['after_field'])) $addFields[$row['name']]['after_field'] = $gtsAPIFieldTable->after_field;
                        }
                    }
                }
            }
        }
        if(empty($addFields)) return $fields;
        $keys = [];
        
        foreach($addFields as $k=>$addField){
            $field = [
                'label'=>$addField['title']?$addField['title']:$k,
                'type' => $addField['field_type']?$addField['field_type']:'text',
            ];
            if(!empty($addField['default'])) $field['default'] = $addField['default'];
            if(!empty($addField['modal_only'])) $field['modal_only'] = $addField['modal_only'];
            if(!empty($addField['table_only'])) $field['table_only'] = $addField['table_only'];
            if(!empty($addField['gtsapi_config'])) $field = array_merge($field,$addField['gtsapi_config']);
            if($field['type'] == 'decimal' and !isset($field['FractionDigits'])) $field['FractionDigits'] = 2;
            if(!empty($addField['list_select'])){
                $field['type'] = 'select';
                
                $this->pdo->setConfig([
                    'class'=>'gsParamListSelect',
                    'where'=>[
                        'gsParamListSelect.param_id'=>$addField['id']
                    ],
                    'sortby'=>[
                        'gsParamListSelect.id'=>'ASC'
                    ],
                    'return' => 'data',
                    'limit' => 0
                ]);
                $rows = $this->pdo->run();
                $select_data = [];
                $select_data[] = ['id'=>'','content'=>''];
                foreach($rows as $row){
                    $select_data[] = ['id'=>$row['name'],'content'=>$row['name']];
                }
                $field['select_data'] = $select_data;
            }

            if(empty($keys[$addField['after_field']])){
                $keys[$addField['after_field']] = $addField['after_field'];
            }
            $fields = $this->insertToArray($fields,[$k=>$field],$keys[$addField['after_field']]);
            $keys[$addField['after_field']] = $addField['name'];
        }
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;

            if(isset($triggers[$class]['gtsapi_addfields']) and isset($triggers[$class]['model'])){
                $service = $this->models[$triggers[$class]['model']];
                if(method_exists($service,$triggers[$class]['gtsapi_addfields'])){ 
                    $params = [
                        'rule'=>$rule,
                        'class'=>$class,
                        'method'=>$action,
                        'fields'=>&$fields,
                        'trigger'=>'gtsapi_addfields',
                    ];
                    // $this->modx->log(1,'gtsAPI run '.$triggers[$class]['gtsapifunc']);
                    $service->{$triggers[$class]['gtsapi_addfields']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1,'gtsAPI Ошибка триггера '.$e->getMessage());
            // return $this->error('Ошибка триггера '.$e->getMessage());
        }    
        return $fields;
    }
    public function options($rule,$request,$action){
        $actions = [];
        if(isset($rule['properties']['actions'])){
            foreach($rule['properties']['actions'] as $action =>$v){
                $resp = $this->checkPermissions($rule['properties']['actions'][$action]);

                if($resp['success']){
                    $actions[$action] = $v;
                }
            }
        }
        if(empty($rule['properties']['fields'])){
            $fields = $this->gen_fields($rule);
        }else{
            $fields = $rule['properties']['fields'];
        }
        $fields = $this->addFields($rule,$fields,'options');
        // $limit = false;
        // if(isset($rule['properties']['limit'])) $limit = $rule['properties']['limit'];
        $resp = $this->read($rule,$request,$action);
        if(!$resp['success']) return $resp;
        if(empty($rule['properties']['nodeclick'])) $rule['properties']['nodeclick'] = [];
        return $this->success('options',[
            'actions'=>$actions,
            'nodeclick'=>$rule['properties']['nodeclick'],
            'fields'=>$fields,
            'classField'=>$rule['properties']['classField']?$rule['properties']['classField']:'class',
            'useUniTree'=>$rule['properties']['useUniTree']?$rule['properties']['useUniTree']:false,
            'out'=>$resp['data'],
        ]);
    }
    public function read($rule,$request,$action, $where = []){
        $resp = $this->run_triggers($rule, 'before', 'read', $request);
        if(!$resp['success']) return $resp;
        $menuindexField = $rule['properties']['menuindexField'] ? $rule['properties']['menuindexField'] : 'id';
        $default = [
            'class' => $rule['class'],
            'select' => [
                $rule['class'] => '*',
            ],
            'sortby' => [
                "{$rule['class']}.$menuindexField" => 'ASC',
            ],
            'return' => 'data',
            'limit' => 0
        ];

        if(!empty($rule['properties']['query'])){
            if(isset($rule['properties']['query']['parents_option'])){//parents_option
                $parents = $this->modx->getOption($rule['properties']['query']['parents_option']);
                $rule['properties']['query']['parents'] = $parents;
            }
            if(!empty($rule['properties']['query']['where'])){
                foreach($rule['properties']['query']['where'] as $k=>$v1){
                    if($v1 === 'modx_user_id'){
                        $rule['properties']['query']['where'][$k] = $this->modx->user->id;
                    }
                }
            }
            $default = array_merge($default, $rule['properties']['query']);
        }

        $slTreeSettings = [
            'rootIds'=>$rule['properties']['rootIds']?$rule['properties']['rootIds']:0,
            'idField'=>$rule['properties']['idField']?$rule['properties']['idField']:'id',
            'parentIdField'=>$rule['properties']['parentIdField']?$rule['properties']['parentIdField']:'parent_id',
            'parents_idsField'=>$rule['properties']['parents_idsField']?$rule['properties']['parents_idsField']:'parents_ids',
            'isLeaf' => $rule['properties']['isLeaf'] ? $rule['properties']['isLeaf'] : [],
            'menuindexField'=>$rule['properties']['menuindexField']?$rule['properties']['menuindexField']:'menuindex',
        ];
        $rootIds = [];
        if($slTreeSettings['rootIds'] !== 0){
            if(strpos($slTreeSettings['rootIds'],'option') !== false) {
                $slTreeSettings['rootIds'] = $this->pdo->getChunk('@INLINE '.$slTreeSettings['rootIds']);
            }
            $rootIds = array_map('trim', explode(',', $slTreeSettings['rootIds']));
            if(!empty($rootIds)){
                $default1 = $default2 =$default;
                $default1['where'] = ['id:IN' => $rootIds];
                $this->pdo->setConfig($default1);
                $rows0 = $this->pdo->run();
                
                if($rule['class'] == 'modResource'){
                    $default2['parents'] = implode(',',$rootIds);
                }else{
                    $where1 = [];
                    foreach($rootIds as $id){
                        $where1[] = $slTreeSettings['parents_idsField']." LIKE '%#$id#%" ;
                    }
                    $default2['where'] = [100=>implode(' OR ', $where1)];
                }
                $this->pdo->setConfig($default1);
                $rows0 = array_merge($rows0,$this->pdo->run());
            }
        }else{
            $this->pdo->setConfig($default);
            $rows0 = $this->pdo->run();
        }
        
        $out = [
            'rows'=>$rows0,
        ];
        if($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();
        
        $out['slTree'] = $this->getslTree($slTreeSettings,$rows0,$rootIds);
        
        $resp = $this->run_triggers($rule, 'after', 'read', $request, $out);
        
        if(!$resp['success']) return $resp;
        
        if(!empty($resp['data']['out'])) $out = $resp['data']['out'];
        // unset($out['rows']);
        return $this->success('',$out);
    }
    public function getslTree($slTreeSettings, $rows, $parents = []){
        foreach($rows as &$row){
            // $row['title'] = $this->pdoTools->getChunk("@INLINE ".$slTreeSettings['title'],$row);
            $isLeaf = true;
            foreach($slTreeSettings['isLeaf'] as $field=>$v){
                if($row[$field] != $v) $isLeaf = false;
            }
            $row['isLeaf'] = $isLeaf;
            $row[$slTreeSettings['idField']] = (int)$row[$slTreeSettings['idField']];
            $row[$slTreeSettings['parentIdField']] = (int)$row[$slTreeSettings['parentIdField']];
        }
        $tree0 = $this->buildTree($rows,$slTreeSettings['idField'],$slTreeSettings['parentIdField'], $parents);
        if(empty($parents)){
            $tree = [];
            foreach($tree0 as $v){
                $tree[] = $this->prepareTree($v);
            }
        }else{
            $tree = [];
            $tree[] = $this->prepareTree($tree0[(int)$parents]);
        }
        return $tree;
    }
    public function prepareTree($node0){
        $node = [];
        if(!empty($node0['children'])){
            $children = $node0['children'];
            usort($children, function ($item1, $item2) {
                return $item1['menuindex'] >= $item2['menuindex'];
            });
            unset($node0['children']);
        }
        
        $node = [
            'title'=>$node0['title'],
            'data'=>$node0
        ];
        if($node0['isLeaf']){
            $node['isLeaf'] = true;
        }else{
            $node['isExpanded'] = false;
        }
        if(isset($children)){
            foreach($children as $child){
                $node['children'][] = $this->prepareTree($child);
            }
        }
        return $node;
    }
    public function buildTree($tmp = array(), $id = 'id', $parent = 'parent', array $roots = array())
    {

        if (empty($id)) {
            $id = 'id';
        }
        if (empty($parent)) {
            $parent = 'parent';
        }

        if (count($tmp) == 1) {
            $row = current($tmp);
            $tree = array(
                $row[$parent] => array(
                    'children' => array(
                        $row[$id] => $row,
                    ),
                ),
            );
        } else {
            $rows = $tree = array();
            foreach ($tmp as $v) {
                $rows[$v[$id]] = $v;
            }

            foreach ($rows as $id => &$row) {
                if (empty($row[$parent]) || (!isset($rows[$row[$parent]]) && in_array($id, $roots))) {
                    $tree[$id] = &$row;
                } else {
                    $rows[$row[$parent]]['children'][$id] = &$row;
                }
            }
        }

        return $tree;
    }
    // public function getSelects($fields){
    //     $selects = [];
    //     foreach($fields as $field =>$v){
    //         if($v['type'] == 'select'){
    //             if($gtsAPISelect = $this->modx->getObject('gtsAPISelect',['field'=>$field])){
    //                 $rows0 = json_decode($gtsAPISelect->rows,1);
    //                 $rows = [];
    //                 if(!is_array($rows0)){
    //                     $rows0 = array_map('trim',explode(',',$gtsAPISelect->rows));
    //                 }
    //                 foreach($rows0 as $row){
    //                     if(count($row) == 2){
    //                         $rows[] = $row;
    //                     }else{
    //                         $rows[] = [$row,$row];
    //                     }
    //                 }
    //                 $rowsEnd = [];
    //                 foreach($rows as $row){
    //                     $rowsEnd[] = [
    //                         'id'=>$row[0],
    //                         'content'=>$row[1],
    //                     ];
    //                 }
    //                 $selects[$field]['rows'] = $rowsEnd;
    //             }
    //         }
    //     }
    //     return $selects;
    // }
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
        foreach($fields as $field=>$v){
            switch($field){
                case 'parent_id': case 'parents_ids': case 'class': case 'target_id':  case 'menuindex':
                    $fields[$field]['type'] = 'hidden';
                break;
                case 'title': case 'pagetitle':
                    $fields[$field]['label'] = 'Заголовок';
                break;
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
                $resp = $this->run_triggers($rule, 'before', 'remove', [], $object_old);
                if(!$resp['success']) return $resp;
                if($rule['properties']['useUniTree']){
                    $count = $this->modx->getObject($rule['class'],['target_id'=>$obj->target_id]);
                    if($count == 1 and $target = $this->modx->getObject($obj->class,$obj->target_id)){
                        $target->remove();
                    }
                    $childs = $this->modx->getIterator($rule['class'],['parent_id'=>$obj->id]);
                    foreach($childs as $child){
                        $this->delete($rule,['ids'=>$child->id],$action);
                    }
                }
                if($obj->remove()){
                    $resp = $this->run_triggers($rule, 'after', 'remove', [], $object_old);
                    if(!$resp['success']) return $resp;
                }
            }
            return $this->success('delete',['ids'=>$request['ids']]);
        }
        return $this->error('delete_error');
    }
    public function addDefaultFields($rule,$request){
        $where = [];
        $data = [];
        $filters = [];
        $tabs_where = [];
        $data_filters = [];
        if(!empty($request['filters'])){
            $filters = $this->aplyFilters($rule,$request['filters']);
        }
        if(!empty($rule['actions']['subtabs'])){
            foreach($rule['actions']['subtabs'] as $t=>$tabs){
                foreach($tabs as $tab){
                    if($tab['name'] == $rule['table']){
                        if(isset($tab['where'])){
                            foreach($tab['where'] as $k=>$v){
                                $k = str_replace('`','',$k);
                                $arr = explode('.',$k);
                                if(count($arr) == 1){
                                    $field = $arr[0];
                                }else{
                                    $field = $arr[1];
                                }
                                $tabs_where[$field] = $v;
                            }
                        }
                    }
                }
            }
        }
        if(!empty($rule['properties']['table_tree'])){//table_tree
            $tabs_where[$rule['properties']['table_tree']['parentIdField']] = 1;
        }
        if(!empty($filters)){
            foreach($filters as $k=>$v){
                $k = str_replace('`','',$k);
                $arr = explode('.',$k);
                if(count($arr) == 1){
                    $field = $arr[0];
                }else{
                    $field = $arr[1];
                }
                // if(isset($tabs_where[$field])) 
                $data_filters[$field] = $v;
                if(isset($request[$field])){
                    if(isset($tabs_where[$field])){
                        if((int)$tabs_where[$field] == 0) $data_filters[$field] = $request[$field];
                    }else{
                        $data_filters[$field] = $request[$field];
                    }
                    
                }
            }
        }
        if(!empty($rule['properties']['query'])){
            if(!empty($rule['properties']['query']['where'])){
                $where = array_merge($where,$rule['properties']['query']['where']);
            }
        }
        $fields = [];
        if ($className = $this->modx->loadClass($rule['class'])){
            if (isset ($this->modx->map[$rule['class']])) {
                foreach($this->modx->map[$rule['class']]['fieldMeta'] as $field=>$meta){
                    $fields[$field] = 1;
                }
            }
        }
        if(!empty($where)){
            foreach($where as $k=>$value){
                $k = str_replace('`','',$k);
                $arr = explode('.',$k);
                if(count($arr) == 1){
                    $field = $arr[0];
                }else{
                    $field = $arr[1];
                }
                if(isset($fields[$field])) $data[$field] = $value;
            }
        }
        if(!empty($request['parent_id'])){
            $data['parent_id'] = $request['parent_id'];
        }
        // $this->modx->log(1,"request".print_r($request,1));
        // $this->modx->log(1,"data_filters".print_r($data_filters,1));
        return array_merge($data,$data_filters);
    }
    public function create($rule,$request,$action){
        if($request['form'] == 'UniTree'){
            if(isset($rule['gtsAPIUniTreeClass'][$request['table']])){
                if(!$parentObj = $this->modx->getObject($rule['class'], (int)$request['parent_id'])){
                    return $this->error("Не найден родительский элемент {$request['parent_id']} в таблице {$rule['class']}");
                }
                if($rule['gtsAPIUniTreeClass'][$request['table']]['exdended_modresource'] == 1){
                    $res = [
                        'pagetitle'=>$request['title'],
                        'parent'=>$parentObj->target_id,
                        'class_key'=>$rule['gtsAPIUniTreeClass'][$request['table']]['class'],
                        'content'=>'',
                    ];
                    if(isset($rule['properties']['actions']['create']['tables'][$request['table']]['add_fields'])){
                        // $this->modx->log(1,"table ".print_r($rule['properties']['actions'],1).
                            // print_r($rule['properties']['actions']['create']['tables'][$request['table']]['add_fields'],1));
                        foreach($rule['properties']['actions']['create']['tables'][$request['table']]['add_fields'] as $field=>$val){
                            if(isset($request[$field])) $res[$field] = $request[$field];
                        }
                    }
                    $modx_response = $this->modx->runProcessor('resource/create', $res);
                    if ($modx_response->isError()) {
                        return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
                    }else{
                        $data = [
                            'target_id'=>$modx_response->response['object']['id'],
                            'title'=>$request['title'],
                            'class'=>$rule['gtsAPIUniTreeClass'][$request['table']]['class'],
                        ];
                        $slTreeSettings = $this->get_slTreeSettings($rule);
                        $data[$slTreeSettings['parentIdField']] = $request['parent_id'];
                        
                            if(empty($parentObj->{$slTreeSettings['parents_idsField']})){
                                $parents_ids = '#';
                            }else{
                                $parents_ids = $parentObj->{$slTreeSettings['parents_idsField']};
                            }
                            $data[$slTreeSettings['parents_idsField']] = $parents_ids.$data[$slTreeSettings['parentIdField']].'#';
                            if($count = $this->modx->getCount($rule['class'], [$slTreeSettings['parentIdField'] => (int)$request['parent_id']])){
                                $data[$slTreeSettings['menuindexField']] = $count + 1;
                            }
                        $request = $data;
                    }
                }
            }
        }
        $data = $this->addDefaultFields($rule,$request);
        $request = $this->request_array_to_json($request);
        $obj = $this->modx->newObject($rule['class'],$data);
        // $this->modx->log(1,"create {$rule['class']} ".print_r($data,1));
        //class link Редактирование 2 таблиц одновременно
        $set_data[$rule['class']] = [];
        $fields = [];
        if(!empty($rule['properties']['fields'])){
            $fields = $this->addFields($rule,$rule['properties']['fields'],'create');
            $ext_fields = [];
            foreach($fields as $field=>$desc){
                if(isset($request[$field])){
                    $field_arr = explode('.',$field);
                    if(count($field_arr) == 1){
                        if(empty($desc['class']) or $desc['class'] == $rule['class']){
                            $set_data[$rule['class']][$field] = $request[$field];
                        }else{
                            $set_data[$desc['class']][$field] = $request[$field];
                        }
                    }else if(count($field_arr) == 2){
                        if(empty($desc['class']) or $desc['class'] == $rule['class']){
                            $ext_fields[$field_arr[0]] = $rule['class'];
                            $set_data[$rule['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                        }else{
                            $ext_fields[$field_arr[0]] = $desc['class'];
                            $set_data[$desc['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                        }
                    }else if(count($field_arr) == 3){
                        if(empty($desc['class']) or $desc['class'] == $rule['class']){
                            $ext_fields[$field_arr[0]] = $rule['class'];
                            $set_data[$rule['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                        }else{
                            $ext_fields[$field_arr[0]] = $desc['class'];
                            $set_data[$desc['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                        }
                    }
                }
            }
            foreach($ext_fields as $field=>$class){
                $set_data[$class][$field] = json_encode($set_data[$class][$field]);
            }
        }else{
            $set_data[$rule['class']] = $request;
        }
        
        
        $object_old = $obj->toArray();
        if(isset($request['id'])){
            $object = $obj->fromArray($set_data[$rule['class']],'',true);
        }else{
            $object = $obj->fromArray($set_data[$rule['class']]);
        }
        
        $object_new = $obj->toArray();

        //class link Редактирование 2 таблиц одновременно
        if(!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])){
            foreach($rule['properties']['class_link'] as $class=>$class_link){
                foreach($fields as $field=>$desc){
                    if(isset($desc['class']) and $desc['class'] == $class and isset($set_data[$class][$field])){
                        $object_new[$field] = $set_data[$class][$field];
                    }
                }
            }
        }
        // $this->modx->log(1,"create triggers".print_r($this->triggers,1));

        $resp = $this->run_triggers($rule, 'before', $request['api_action'], $request, $object_old,$object_new,$obj);
        if(!$resp['success']) return $resp;

        if($obj->save()){
            $object = $obj->toArray();
            //class link Редактирование 2 таблиц одновременно
            if(!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])){
                foreach($rule['properties']['class_link'] as $class=>$class_link){
                    if(!empty($set_data[$class])){
                        $search = [];
                        foreach($class_link as $field=>$v){
                            if(isset($object[$v])){
                                $search[$field] = $object[$v];
                            }else if(is_number($v)){
                                $search[$field] = $v;
                            }
                        }
                    }
                    if(!$link_obj = $this->modx->getObject($class,$search)){
                        $link_obj = $this->modx->newObject($class,$search);
                    }
                    if($link_obj){
                        $link_obj->fromArray($set_data[$class]);
                        $link_obj->save();
                        foreach($fields as $field=>$desc){
                            if(isset($desc['class']) and $desc['class'] == $class){
                                $object[$field] = $link_obj->get($field);
                            }
                        }
                    }
                }
            }

            $resp = $this->run_triggers($rule, 'after', $request['api_action'], $request, $object_old,$object,$obj);
            $resp['data']['object'] = $obj->toArray();
            if(!empty($rule['properties']['table_tree'])){//table_tree
                $where = [
                    $rule['class'].'.'.$rule['properties']['table_tree']['parentIdField'] => $resp['data']['object'][$rule['properties']['table_tree']['idField']]
                ];
                $resp['data']['object']['gtsapi_children_count'] = $this->modx->getCount($rule['class'],$where);
            }
            if(!$resp['success']) return $resp;
            
            $data = $resp['data'];

            header('HTTP/1.1 201 Created');
            return $this->success('created',$data);
        }
        return $this->error('create_error',$request);
    }
    public function update($rule,$request,$action){
        
        if($obj = $this->modx->getObject($rule['class'],(int)$request['id'])){
            $object_old = $obj->toArray();
            // $data = $this->addDefaultFields($rule,$request);
            $data = [];
            $request = $this->request_array_to_json($request);
            $request = array_merge($request,$data);
            
            //class link Редактирование 2 таблиц одновременно
            $set_data[$rule['class']] = [];
            $fields = [];
            if(!empty($rule['properties']['fields'])){
                $fields = $this->addFields($rule,$rule['properties']['fields'],'update');
                $ext_fields = [];
                foreach($fields as $field=>$desc){
                    if(isset($request[$field])){
                        $field_arr = explode('.',$field);
                        if(count($field_arr) == 1){
                            if(empty($desc['class']) or $desc['class'] == $rule['class']){
                                $set_data[$rule['class']][$field] = $request[$field];
                            }else{
                                $set_data[$desc['class']][$field] = $request[$field];
                            }
                        }else if(count($field_arr) == 2){
                            if(empty($desc['class']) or $desc['class'] == $rule['class']){
                                $ext_fields[$field_arr[0]] = $rule['class'];
                                $set_data[$rule['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                            }else{
                                $ext_fields[$field_arr[0]] = $desc['class'];
                                $set_data[$desc['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                            }
                        }else if(count($field_arr) == 3){
                            if(empty($desc['class']) or $desc['class'] == $rule['class']){
                                $ext_fields[$field_arr[0]] = $rule['class'];
                                $set_data[$rule['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                            }else{
                                $ext_fields[$field_arr[0]] = $desc['class'];
                                $set_data[$desc['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                            }
                        }
                    }
                }
                if(!empty($rule['properties']['class_link'])){
                    foreach($rule['properties']['class_link'] as $class=>$class_link){
                        if(!isset($set_data[$class])) continue;
                        $search = [];
                        foreach($class_link as $field=>$v){
                            if(isset($object[$v])){
                                $search[$field] = $object[$v];
                            }else if(is_number($v)){
                                $search[$field] = $v;
                            }
                        }
                        if($link_obj = $this->modx->getObject($class,$search)){
                            foreach($ext_fields as $field=>$class2){
                                if($class == $class2){
                                    if(is_array($link_obj->{$field})){
                                        $arr = $link_obj->{$field};
                                    }else if(is_string($link_obj->{$field})){
                                        $arr = json_decode($link_obj->{$field});
                                    }
                                    if(is_array($arr)){
                                        $set_data[$class2][$field] = array_merge($arr,$set_data[$class2][$field]);
                                    }
                                    $set_data[$class2][$field] = json_encode($set_data[$class2][$field]);
                                }
                            }
                        }
                    }
                }
                foreach($ext_fields as $field=>$class){
                    if($class == $rule['class']){
                        if(is_array($object_old[$field])){
                            $arr = $object_old[$field];
                        }else if(is_string($object_old[$field])){
                            $arr = json_decode($object_old[$field]);
                        }
                        if(is_array($arr)){
                            $set_data[$class][$field] = array_merge($arr,$set_data[$class][$field]);
                        }
                        $set_data[$class][$field] = json_encode($set_data[$class][$field]);
                    }
                }
            }else{
                $set_data[$rule['class']] = $request;
            }
            

            $object = $obj->fromArray($set_data[$rule['class']]);
            $object_new = $obj->toArray();
            
            //class link Редактирование 2 таблиц одновременно
            if(!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])){
                foreach($rule['properties']['class_link'] as $class=>$class_link){
                    foreach($fields as $field=>$desc){
                        if(isset($desc['class']) and $desc['class'] == $class and isset($set_data[$class][$field])){
                            $object_new[$field] = $set_data[$class][$field];
                        }
                    }
                }
            }

            $resp = $this->run_triggers($rule, 'before', 'update', $request, $object_old,$object_new,$obj);
            if(!$resp['success']) return $resp;
            
            if($obj->save()){
                $object = $obj->toArray();
                
                //class link Редактирование 2 таблиц одновременно
                if(!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])){
                    foreach($rule['properties']['class_link'] as $class=>$class_link){
                        if(!empty($set_data[$class])){
                            $search = [];
                            foreach($class_link as $field=>$v){
                                if(isset($object[$v])){
                                    $search[$field] = $object[$v];
                                }else if(is_number($v)){
                                    $search[$field] = $v;
                                }
                            }
                        }
                        if(!$link_obj = $this->modx->getObject($class,$search)){
                            $link_obj = $this->modx->newObject($class,$search);
                        }
                        if($link_obj){
                            $link_obj->fromArray($set_data[$class]);
                            $link_obj->save();
                            foreach($fields as $field=>$desc){
                                if(isset($desc['class']) and $desc['class'] == $class){
                                    $object[$field] = $link_obj->get($field);
                                }
                            }
                        }
                    }
                }

                $resp = $this->run_triggers($rule, 'after', 'update', $request, $object_old,$object,$obj);
                
                $resp['data']['object'] = $obj->toArray();
                if(!empty($rule['properties']['table_tree'])){//table_tree
                    $where = [
                        $rule['class'].'.'.$rule['properties']['table_tree']['parentIdField'] => $resp['data']['object'][$rule['properties']['table_tree']['idField']]
                    ];
                    $resp['data']['object']['gtsapi_children_count'] = $this->modx->getCount($rule['class'],$where);
                }
                if(!$resp['success']) return $resp;
                $data = $resp['data'];

                return $this->success('update',$data);
            }
        }
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
    
    // public function setSelectRow($rule,$rows0){
    //     $select_row = [];
    //     foreach($rule['properties']['group']['select'] as $field => $v){
    //         switch($v['type_aggs']){
    //             case 'count':
    //                 $select_row[$field] = 0;
    //             break;
    //             case 'sum':
    //                 $select_row[$field] = 0;
    //             break;
    //             case 'max':
    //                 $select_row[$field] = $rows0[0][$field];
    //             break;
    //             case 'min':
    //                 $select_row[$field] = $rows0[0][$field];
    //             break;
    //             default:
    //                 $select_row[$field] = $v;
    //         }
    //     }
    //     return $select_row;
    // }

    
    
    /**
     * Builds a hierarchical tree from given array
     *
     * @param array $tmp Array with rows
     * @param string $id Name of primary key
     * @param string $parent Name of parent key
     * @param array $roots Allowed roots of nodes
     *
     * @return array
     */
    
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
            case "notEquals":
                $where[$field.':!='] = $filter['value'];
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
    public function run_triggers($rule, $type, $method, $fields, $object_old = [], &$object_new =[], $object = null)
    {
        $class = $rule['class'];
        if(empty($class)) return $this->success('Выполнено успешно');
        
        // Событие для плагинов
        $gtsAPIRunTriggers = $this->modx->invokeEvent('gtsAPIRunTriggers', [
            'class'=>$class,
            'rule'=>$rule,
            'type'=>$type,
            'method'=>$method,
            'fields'=>$fields,
            'object_old'=>$object_old,
            'object_new'=>$object_new,
            'object'=>$object,
        ]);
        if (is_array($gtsAPIRunTriggers)) {
            $canSave = false;
            foreach ($gtsAPIRunTriggers as $msg) {
                if (!empty($msg)) {
                    $canSave .= $msg."\n";
                }
            }
        } else {
            $canSave = $gtsAPIRunTriggers;
        }
        if(!empty($canSave)) return $this->error($canSave);
        if(isset($modx->event->returnedValues['object'])){
            $object = $modx->event->returnedValues['object'];
        }

        try {
            $triggers = $this->triggers;
            
            // if(isset($triggers[$class]['function']) and isset($triggers[$class]['model'])){
            //     // $this->modx->log(1,"create triggers $class {$triggers[$class]['function']}");
                
            //     $service = $this->models[$triggers[$class]['model']];
            //     if(method_exists($service,$triggers[$class]['function'])){ 
            //         // $this->modx->log(1,"create triggers 2 {$triggers[$class]['function']}");
            //         return  $service->{$triggers[$class]['function']}($class, $type, $method, $fields, $object_old, $object_new);
            //     }
            // }
            // if(isset($triggers[$class]['gtsfunction']) and isset($triggers[$class]['model'])){
            //     $service = $this->models[$triggers[$class]['model']];
            //     if(method_exists($service,$triggers[$class]['gtsfunction'])){ 
            //         $gettables_core_path = $this->modx->getOption('gettables_core_path',null, MODX_CORE_PATH . 'components/gettables/core/');
            //         $gettables_core_path = str_replace('[[+core_path]]', MODX_CORE_PATH, $gettables_core_path);
            //         if ($this->modx->loadClass('gettables', $gettables_core_path, false, true)) {
            //             $getTables = new getTables($this->modx, []);
            //             if ($getTables) {
            //                 return  $service->{$triggers[$class]['gtsfunction']}($getTables,$class, $type, $method, $fields, $object_old, $object_new);
            //             }
            //         }
            //     }
            // }
            if(isset($triggers[$class]['gtsapifunc']) and isset($triggers[$class]['model'])){
                $service = $this->models[$triggers[$class]['model']];
                if(method_exists($service,$triggers[$class]['gtsapifunc'])){ 
                    $params = [
                        'rule'=>$rule,
                        'class'=>$class,
                        'type'=>$type,
                        'method'=>$method,
                        'fields'=>$fields,
                        'object_old'=>$object_old,
                        'object_new'=>&$object_new,
                        'object'=>&$object,
                        'trigger'=>'gtsapifunc',
                    ];
                    // $this->modx->log(1,'gtsAPI run '.$triggers[$class]['gtsapifunc']);
                    return  $service->{$triggers[$class]['gtsapifunc']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1,'gtsAPI Ошибка триггера '.$e->getMessage().print_r($e->getTrace()[0],1));
            return $this->error('Ошибка триггера '.$e->getMessage());
        }
        
        return $this->success('Выполнено успешно');
    }
    public function insertToArray($array=array(), $new=array(), $after='') {
        $res = array();
        $res1 = array();
        $res2 = array();
        $c = 0;
        $n = 0;
        foreach ($array as $k => $v) {
            if ($k == $after) { 
            $n = $c;
            } 
            $c++;
        }
        $c = 0;
        foreach ($array as $i => $a) {
            if ($c > $n) { 
            $res1[$i] = $a;
            } else {
            $res2[$i] = $a;
            }
            $c++;
        }
        $res = $res2 + $new + $res1;
        return $res;
    }
}
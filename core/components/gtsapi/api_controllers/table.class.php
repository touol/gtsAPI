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
        // $this->modx->log(1, 'gtsAPI route $req: ' . print_r($req, 1));
        if(isset($req['filters']) and isset($request['filters'])) $req['filters'] = array_merge($req['filters'],$request['filters']);
        if(isset($request['is_virtual'])) $req['is_virtual'] = $request['is_virtual'];
        if(is_array($req)) $request = array_merge($request,$req);
          
        switch($method){
            case 'GET':
                // if($id and empty($request['ids'])) $request['ids'] = [$id];
                if(empty($request['api_action'])) $request['api_action'] = 'read';
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
        
        // Декодируем filters если это JSON строка
        if (isset($request['filters']) && is_string($request['filters'])) {
            $decodedFilters = json_decode($request['filters'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request['filters'] = $decodedFilters;
                // $this->modx->log(1, 'Excel export: decoded filters from JSON - ' . print_r($request['filters'], 1));
            }
        }
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

        //добавь здесь вызов триггера при помощи которого можно заменить $rule
        //Добавь вызов плагина с которым можно заменить $rule на событие gtsAPIRunTriggers с параметром gtsapi_rule
        
        // Вызов события для плагинов - позволяет изменить $rule
        $gtsAPIRunTriggersRule = $this->modx->invokeEvent('gtsAPIRunTriggers', [
            'class'=>$rule['class'],
            'rule'=>&$rule,
            'request'=>$request,
            'trigger'=>'gtsapi_rule',
        ]);
        if (is_array($gtsAPIRunTriggersRule)) {
            $canSave = '';
            foreach ($gtsAPIRunTriggersRule as $msg) {
                if (!empty($msg)) {
                    $canSave .= $msg."\n";
                }
            }
        } else {
            $canSave = $gtsAPIRunTriggersRule;
        }
        if(!empty($canSave)) return $this->error($canSave);
        
        // Проверяем, был ли изменен $rule через returnedValues
        if(isset($this->modx->event->returnedValues['rule'])){
            $rule = $this->modx->event->returnedValues['rule'];
        }
        
        // Внутренний механизм триггеров через сервисы
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;
            
            if(isset($triggers[$class]['gtsapi_rule']) and isset($triggers[$class]['model'])){
                $service = $this->models[$triggers[$class]['model']];
                if(method_exists($service,$triggers[$class]['gtsapi_rule'])){ 
                    $params = [
                        'rule'=>&$rule,
                        'class'=>$class,
                        'request'=>$request,
                        'trigger'=>'gtsapi_rule',
                    ];
                    $resp = $service->{$triggers[$class]['gtsapi_rule']}($params);
                    if(!$resp['success']) return $resp;
                }
            }
        } catch (Error $e) {
            $this->modx->log(1,'gtsAPI Ошибка триггера gtsapi_rule '.$e->getMessage());
            return $this->error('Ошибка триггера gtsapi_rule '.$e->getMessage());
        }
        
        // Добавляем действие excel_export если оно не отключено
        if (!isset($rule['properties']['actions']['excel_export']) || $rule['properties']['actions']['excel_export'] !== false) {
            if (!isset($rule['properties']['actions']['excel_export'])) {
                $rule['properties']['actions']['excel_export'] = [
                    'head' => true,
                    'icon' => 'pi pi-file-excel',
                    'class' => 'p-button-rounded p-button-success',
                    'label' => 'Excel'
                ];
            }
        }
        
        // Добавляем действие print если оно не отключено
        if (!isset($rule['properties']['actions']['print']) || $rule['properties']['actions']['print'] !== false) {
            if (!isset($rule['properties']['actions']['print'])) {
                $rule['properties']['actions']['print'] = [
                    'head' => true,
                    'icon' => 'pi pi-print',
                    'class' => 'p-button-rounded p-button-info',
                    'label' => 'Печать'
                ];
            }
        }
        
        // $this->modx->log(1,"route_post ".print_r($rule['properties'],1).print_r($request,1));
        $action = explode('/',$request['api_action']);
        if(count($action) == 1 and !in_array($request['api_action'],['options','autocomplete','save_fields_style','reset_fields_style']) and isset($rule['properties']['actions'])){
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
            case 'excel_export':
                return $this->excel_export($rule,$request);
            break;
            case 'print':
                return $this->print($rule,$request);
            break;
            case 'save_fields_style':
                return $this->save_fields_style($rule,$request);
            break;
            case 'reset_fields_style':
                return $this->reset_fields_style($rule,$request);
            break;
            default:
                $action = explode('/',$request['api_action']);
                // $this->modx->log(1,"route_post {$request['api_action']}");
                // return $this->error("test11!".print_r(array_keys($this->models),1));
                if(count($action) == 2){
                    $resp = $this->getService(strtolower($action[0]));
                    if(!$resp['success']){
                        return $resp;
                    }
                    // $this->modx->log(1,"route_post {$request['api_action']}". print_r($resp,1));
                    $service = $this->models[strtolower($action[0])];

                    if(method_exists($service,'handleRequest')){ 
                        return $service->handleRequest($action[1], $request);
                    }
                }
        }
        return $this->error("Не найдено действие!");
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
        

        if(isset($request['query']) or !empty($request['parent']) or !empty($request['search'])){
            if(empty($default['where'])) $default['where'] = [];
            $where = [];
            
            // Обработка стандартных where условий
            if(isset($autocomplete['where'])){
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
            }
            
            // Обработка множественных полей поиска для multiautocomplete
            if(!empty($request['search'])){
                foreach($request['search'] as $searchField => $searchConfig){
                    if(isset($searchConfig['value']) && !empty($searchConfig['value'])){
                        $where[$searchField] = $searchConfig['value'];
                    }
                }
            }
            
            $default['where'] = array_merge($default['where'],$where);
        }
        
        // Обработка where условий из поля автокомплита (только из конфигурации, безопасно)
        if (isset($autocomplete['field']) && 
            isset($rule['properties']['fields'][$autocomplete['field']]['where']) && 
            is_array($rule['properties']['fields'][$autocomplete['field']]['where'])) {
            
            $fieldWhere = $rule['properties']['fields'][$autocomplete['field']]['where'];
            
            // Обработка Fenom-шаблонов в значениях where (только модификатор date для безопасности)
            foreach ($fieldWhere as $key => $value) {
                if (is_string($value) && preg_match('/^\{[^}]*\|\s*date\s*:\s*["\'][^"\']*["\']\s*\}$/', $value)) {
                    // Используем pdoTools для обработки Fenom-шаблона только с модификатором date
                    $fieldWhere[$key] = $this->pdoTools->getChunk("@INLINE " . $value, []);
                }
            }
            
            if (empty($default['where'])) $default['where'] = [];
            $default['where'] = array_merge($default['where'], $fieldWhere);
        }

        // Обработка where из запроса (только модификатор date для безопасности)
        if (!empty($request['where']) && is_array($request['where'])) {
            $requestWhere = $request['where'];
            
            // Обработка Fenom-шаблонов в значениях where (только модификатор date)
            foreach ($requestWhere as $key => $value) {
                if (is_string($value) && preg_match('/^\{[^}]*\|\s*date\s*:\s*["\'][^"\']*["\']\s*\}$/', $value)) {
                    // Используем pdoTools для обработки Fenom-шаблона только с модификатором date
                    $requestWhere[$key] = $this->pdoTools->getChunk("@INLINE " . $value, []);
                }
            }
            
            if (empty($default['where'])) $default['where'] = [];
            $default['where'] = array_merge($default['where'], $requestWhere);
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
        
        // Добавляем поддержку limit из запроса для виртуального скроллинга
        if(isset($request['limit'])){
            $default['limit'] = $request['limit'];
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
        
        // Добавляем шаблон из конфигурации autocomplete для динамического отображения
        if(!empty($autocomplete['template'])){
            $out['template'] = $autocomplete['template'];
        }
        
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

        if (is_dir( $this->modx->getOption('core_path') . 'components/gtsshop/model/' )) {
            $this->modx->addPackage('gtsshop', $this->modx->getOption('core_path') . 'components/gtsshop/model/');
        }

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
                            if($gtsAPIFieldTable->only_text){
                                $addFields[$gtsAPIField->name]['field_type'] = 'text';
                                unset($addFields[$gtsAPIField->name]['list_select']);
                            }
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
                            if($gtsAPIFieldTable->only_text){
                                $addFields[$row['name']]['field_type'] = 'text';
                                unset($addFields[$row['name']]['list_select']);
                            }
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
        $row_class_trigger = [];
        $table_tree = false;
        if(!empty($rule['properties']['row_class_trigger'])){
            $row_class_trigger = $rule['properties']['row_class_trigger'];
        }
        if(!empty($rule['properties']['table_tree'])){
            $table_tree = $rule['properties']['table_tree'];
        }
        if(empty($rule['properties']['fields'])){
            if($rule['type'] == 1) $fields = $this->gen_fields($rule);
        }else{
            $fields = $rule['properties']['fields'];
        }
        $fields = $this->addFields($rule,$fields,'options');
        foreach($fields as $k=>$field){
            if(empty($field['type'])){
                if($k == 'id'){
                    $fields[$k]['type'] = 'view';
                }else{
                    $fields[$k]['type'] = 'text';
                }
            }
        }
        $actions = [];
        if(isset($rule['properties']['actions'])){
            foreach($rule['properties']['actions'] as $action =>$v){
                $resp = $this->checkPermissions($rule['properties']['actions'][$action]);

                if($resp['success']){
                    if(!$v['hide']) $actions[$action] = $v;
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
        $limit = false;
        if(isset($rule['properties']['limit'])) $limit = $rule['properties']['limit'];

        $options = [
            'fields'=>$fields,
            'actions'=>$actions,
            'selects'=>$selects,
            'filters'=>$filters,
            'row_class_trigger'=>$row_class_trigger,
            'table_tree'=>$table_tree,
            'limit'=>$limit,
            'fields_style'=>json_decode($rule['fields_style'],1),
        ];
        if(isset($rule['properties']['rowGroupMode'])){
            $options['rowGroupMode'] = $rule['properties']['rowGroupMode'];
            $options['groupRowsBy'] = $rule['properties']['groupRowsBy'];
        }
        if(isset($rule['properties']['data_fields'])){
            $options['data_fields'] = $rule['properties']['data_fields'];
        }
        if(isset($rule['properties']['hide_id'])){
            $options['hide_id'] = $rule['properties']['hide_id'];
        }
        if(isset($rule['properties']['form'])){
            $options['form'] = $rule['properties']['form'];
        }
        
        return $this->success('options',$options);
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
        
        if(!empty($request['ids']) || (!empty($rule['properties']['data_fields']) && !empty($request['data_fields_values']))){
            $where = [];
            
            // Если есть data_fields и data_fields_values, формируем where на основе полей
            if(!empty($rule['properties']['data_fields']) && !empty($request['data_fields_values'])){
                $dataFields = $rule['properties']['data_fields'];
                $dataFieldsValues = $request['data_fields_values'];
                
                // Формируем OR условие для каждой строки, но внутри каждой строки AND условие
                $orConditions = [];
                foreach($dataFieldsValues as $rowData){
                    $andCondition = [];
                    foreach($dataFields as $field){
                        if(isset($rowData[$field])){
                            $andCondition[$field] = $rowData[$field];
                        }
                    }
                    if(!empty($andCondition)){
                        $orConditions[] = $andCondition;
                    }
                }
                
                if(!empty($orConditions)){
                    if(count($orConditions) == 1){
                        // Если только одна строка, используем простое AND условие
                        $where = $orConditions[0];
                    } else {
                        // Если несколько строк, используем OR между группами AND условий
                        $where = [];
                        foreach($orConditions as $index => $condition){
                            if($index == 0){
                                $where = array_merge($where, $condition);
                            } else {
                                foreach($condition as $field => $value){
                                    $where['OR:'.$field.':'.$index] = $value;
                                }
                            }
                        }
                    }
                }
            } else {
                // Используем стандартную логику с ids
                if(is_string($request['ids'])) $request['ids'] = explode(',',$request['ids']);
                $where = ['id:IN'=>$request['ids']];
            }
            
            if(!empty($where)){
                $objs = $this->modx->getIterator($rule['class'], $where);
                
                foreach($objs as $obj){
                    $object_old = $obj->toArray();
                    $resp = $this->run_triggers($rule, 'before', 'remove', [], $object_old);
                    if(!$resp['success']) return $resp;

                    if($obj->remove()){
                        $resp = $this->run_triggers($rule, 'after', 'remove', [], $object_old);
                        if(!$resp['success']) return $resp;
                    }
                }
                return $this->success('delete',['ids'=>$request['ids']]);
            }
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
        $data = $this->addDefaultFields($rule,$request);
        $request = $this->request_array_to_json($request);
        if(!$obj = $this->modx->newObject($rule['class'],$data)) return $this->error('Ошибка. Возможно таблица не существует!',$request);
        
        // $this->modx->log(1,"create {$rule['class']} ".print_r($data,1).print_r($request,1));
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
                if(isset($request['filters'])){
                    if(is_string($request['filters'])) $request['filters'] = json_decode($request['filters'],1);
                }
                if(!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])){
                    // $this->modx->log(1, 'gtsAPI create class_link: ' . print_r($rule['properties']['class_link'], 1));
                    if(isset($request['filters']['insert_menu_id'])){
                    $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                    // unset($request['filters']['insert_menu_id']);
                    foreach($rule['properties']['class_link'] as $class=>$class_link){
                        foreach($class_link as $field=>$v){
                            if($v == 'insert_menu_id'){
                                $rule['properties']['class_link'][$class][$field] = $insert_menu_id;
                            }
                        }
                    }
                }
                // $this->modx->log(1, 'gtsAPI create class_link2: ' . print_r($rule['properties']['class_link'], 1));
                foreach($rule['properties']['class_link'] as $class=>$class_link){
                    if(!empty($set_data[$class])){
                        $search = [];
                        foreach($class_link as $field=>$v){
                            if(isset($object[$v])){
                                $search[$field] = $object[$v];
                            }else if(is_numeric($v)){
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
            $readRequest = ['ids' => $obj->get('id'), 'setTotal' => false, 'limit' => 1];
            $readRequest['filters'] = $request['filters'];
            $readResp = $this->read($rule, $readRequest, null, [], 'create');
            if($readResp['success'] && !empty($readResp['data']['rows'])){
                $resp['data']['object'] = $readResp['data']['rows'][0];
            } else {
                $resp['data']['object'] = [];
            }
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

    public function setUniTreeTitle($rule,$obj){
        $table = '';
        $gtsAPIUniTreeClasses = $this->modx->getIterator('gtsAPIUniTreeClass',['table'=>$rule['table']]);
        foreach($gtsAPIUniTreeClasses as $gtsAPIUniTreeClass){
            if($gtsAPITable = $this->modx->getObject('gtsAPITable',$gtsAPIUniTreeClass->table_id)){
                if(empty($gtsAPITable->class)) $gtsAPITable->class = $gtsAPITable->table;
                if($gtsAPITable->properties){
                    $properties = json_decode($gtsAPITable->properties,1);
                    if(!isset($properties['useUniTree']) or $properties['useUniTree'] == false){
                        $table = $gtsAPITable->table;
                        continue;
                    }
                    $this->addPackages($gtsAPITable->package_id);
                    $treeNodes = $this->modx->getIterator($gtsAPITable->class,['class'=>$rule['class'],'target_id'=>$obj->get('id')]);
                    foreach($treeNodes as $treeNode){
                        if(empty($gtsAPIUniTreeClass->title_field)){
                            if($gtsAPIUniTreeClass->exdended_modresource){
                                $gtsAPIUniTreeClass->title_field = 'pagetitle';
                            }else{
                                $gtsAPIUniTreeClass->title_field = 'name';
                            }
                        }
                        $treeNode->title = $obj->get($gtsAPIUniTreeClass->title_field);
                        
                        // Проверяем поле active в объекте $obj
                        if($obj->get('active') !== null) {
                            $treeNode->active = $obj->get('active');
                        }
                        
                        if($treeNode->save()) $table = $gtsAPITable->table;
                    }
                }
            }
        }
        return $table;
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
                // $this->modx->log(1,"table".print_r($rule['properties']['fields'],1).print_r($fields,1));
                $ext_fields = [];
                foreach($fields as $field=>$desc){
                    if(isset($request[$field])){
                        $field_arr = explode('.',$field);
                        $desc['field'] = $desc['field']?$desc['field']:$field;
                        if(count($field_arr) == 1){
                            if(empty($desc['class']) or $desc['class'] == $rule['class']){
                                $set_data[$rule['class']][$desc['field']] = $request[$field];
                            }else{
                                $set_data[$desc['class']][$desc['field']] = $request[$field];
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
            
            // $this->modx->log(1,"table".print_r($set_data[$rule['class']],1));
            $object = $obj->fromArray($set_data[$rule['class']]);
            $object_new = $obj->toArray();
            if(isset($request['filters'])){
                if(is_string($request['filters'])) $request['filters'] = json_decode($request['filters'],1);
            }
            if(!empty($rule['properties']['class_link'])){
                // $this->modx->log(1, 'gtsAPI create class_link1: !'.print_r($request['filters'],1).'!' . print_r($rule['properties']['class_link'], 1));
                if(isset($request['filters']['insert_menu_id'])){
                    // $this->modx->log(1, 'gtsAPI create class_link: ' . print_r($rule['properties']['class_link'], 1));
                    $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                    foreach($rule['properties']['class_link'] as $class=>$class_link){
                        foreach($class_link as $field=>$v){
                            if($v == 'insert_menu_id'){
                                $rule['properties']['class_link'][$class][$field] = $insert_menu_id;
                            }
                        }
                    }
                }
            }
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
                    // $this->modx->log(1, 'gtsAPI create class_link2: ' . print_r($rule['properties']['class_link'], 1));
                    foreach($rule['properties']['class_link'] as $class=>$class_link){
                        if(!empty($set_data[$class])){
                            $search = [];
                            foreach($class_link as $field=>$v){
                                if($field == 'fenom_tpl'){
                                    $chunk = $this->pdoTools->getChunk("@INLINE $v",$object);
                                    $arr = json_decode($chunk,1);
                                    if(is_array($arr)){
                                        $search = array_merge($search,$arr);
                                    }
                                    
                                }else if(isset($object[$v])){
                                    $search[$field] = $object[$v];
                                }else if(is_numeric($v)){
                                    $search[$field] = $v;
                                }
                            }
                            // $this->modx->log(1, 'gtsAPI create search: ' . print_r($search, 1).print_r($set_data[$class],1));
                            if(empty($search)) continue;
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
                }

                $resp = $this->run_triggers($rule, 'after', 'update', $request, $object_old,$object,$obj);

                $readRequest = ['ids' => $obj->get('id'), 'setTotal' => false, 'limit' => 1];
                if(isset($request['filters'])){
                    $readRequest['filters'] = $request['filters'];
                }
                $readResp = $this->read($rule, $readRequest, null, [], 'update');
                
                if($readResp['success'] && !empty($readResp['data']['rows'])){
                    $resp['data']['object'] = $readResp['data']['rows'][0];
                } else {
                    $resp['data']['object'] = [];
                }
                if(!empty($rule['properties']['table_tree'])){//table_tree
                    $where = [
                        $rule['class'].'.'.$rule['properties']['table_tree']['parentIdField'] => $resp['data']['object'][$rule['properties']['table_tree']['idField']]
                    ];
                    $resp['data']['object']['gtsapi_children_count'] = $this->modx->getCount($rule['class'],$where);
                }
                if(!$resp['success']) return $resp;
                $data = $resp['data'];
                //uniTree
                $uniTreeTable = $this->setUniTreeTitle($rule,$obj);
                if(!empty($uniTreeTable)) $data['uniTreeTable'] = $uniTreeTable;
                return $this->success('update',$data);
            }
        }
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
    public function read($rule,$request,$action, $where = [], $internal_action = ''){
        if(isset($rule['properties']['actions']['read']['custom'])){
            $custom_action = explode('/',$rule['properties']['actions']['read']['custom']);
            if(count($custom_action) == 2 and isset($this->models[strtolower($custom_action[0])])){
                $service = $this->models[strtolower($custom_action[0])];

                if(method_exists($service,'handleRequest')){ 
                    return $service->handleRequest($custom_action[1], $request);
                }
            }
        }
        $object_new =[];
        $resp = $this->run_triggers($rule, 'before', 'read', $request, [], $object_new, null, $internal_action);
        // public function run_triggers($rule, $type, $method, $fields, $object_old = [], &$object_new =[], $object = null, $internal_action = '')
        if(!$resp['success']) return $resp;
        
        $parents = 0;
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
        if(!empty($request['filters'])){
            if(empty($default['where'])) $default['where'] = [];
            if(isset($request['filters']['insert_menu_id'])){
                $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                unset($request['filters']['insert_menu_id']);
                //Замена в значениях в $default в строках содержащих insert_menu_id на значение $insert_menu_id
                $default = $this->replaceInsertMenuIdInArray($default, $insert_menu_id);

            }
            $default['where'] = array_merge($default['where'],$this->aplyFilters($rule,$request['filters']));
        }
        if(!empty($where)){
            $default['where'] = array_merge($default['where'],$where);
        }
        $default['decodeJSON'] = 1;
        if(!empty($request['ids'])){
            $default['where']["{$rule['class']}.id:IN"] = explode(',',$request['ids']);
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
        
        // Получаем список полей из select для проверки сортировки
        $selectFields = $this->getSelectFieldsList($default, $rule);
        
        if($request['sortField']){
            // Проверяем наличие поля в select
            if(in_array($request['sortField'], $selectFields)){
                $default['sortby'] = [
                    "{$request['sortField']}" => $request['sortOrder'] == 1 ?'ASC':'DESC',
                ];
            }
        }
        if($request['multiSortMeta']){
            $default['sortby'] = [];
            foreach($request['multiSortMeta'] as $sort){
                // Проверяем наличие поля в select
                if(in_array($sort['field'], $selectFields)){
                    $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ?'ASC':'DESC';
                }
            }
        }
        if(isset($rule['properties']['group'])){
            $default['sortby'] = [];
            foreach($rule['properties']['group']['fields'] as $field => $v){
                $default['sortby'][$field] = $v['order'];
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
        
        if(isset($rule['properties']['group']) and count($rows0) > 0){
            $rows1 = $check_row = [];
            $select_row = $this->setSelectRow($rule,$rows0);
            $select_row_all = $this->setSelectRow($rule,$rows0);
            foreach($rows0 as $row){
                if(empty($check_row)) $check_row = $row;
                $check = true;
                $check_count = 0;
                $rows_current = [];
                foreach($rule['properties']['group']['fields'] as $field => $v){
                    if($row[$field] != $check_row[$field]){
                        $check = false;
                    }
                }
                if(!$check){
                    $check_row = $row;
                    $add_sum_row = false;
                    foreach($rows_current as $row_current){
                        foreach($rule['properties']['group']['select'] as $field => $v){
                            if($v['type_select'] == 'group'){
                                $row_current[$v['alias']] = $select_row[$field];
                            }else{
                                $add_sum_row = true;
                            }
                        }
                        $rows1[] = $row_current;
                    }
                    if($check_count > 1 and $add_sum_row){
                        $sum_row = [];
                        foreach($rule['properties']['group']['select'] as $field => $v){
                            if($v['type_select'] != 'group'){
                                $sum_row[$field] = $select_row[$field];
                            }
                        }
                        $rows1[] = $sum_row;
                    }
                    $check_count = 0;
                    $select_row = $this->setSelectRow($rule);
                }
                $rows_current[] = $row;
                $check_count++;
                foreach($rule['properties']['group']['select'] as $field => $v){
                    switch($v['type_aggs']){
                        case 'count':
                            $select_row[$field]++;
                            $select_row_all[$field]++;
                        break;
                        case 'sum':
                            $select_row[$field] += $row[$field];
                            $select_row_all[$field] += $row[$field];
                        break;
                        case 'max':
                            if($row[$field] > $select_row[$field]) $select_row[$field] = $row[$field];
                            if($row[$field] > $select_row_all[$field]) $select_row_all[$field] = $row[$field];
                        break;
                        case 'min':
                            if($row[$field] < $select_row[$field]) $select_row[$field] = $row[$field];
                            if($row[$field] < $select_row_all[$field]) $select_row_all[$field] = $row[$field];
                        break;
                    }
                }
            }
            if($add_sum_row){
                $sum_row = [];
                foreach($rule['properties']['group']['select'] as $field => $v){
                    if($v['type_select'] != 'group'){
                        $sum_row[$field] = $select_row[$field];
                    }
                }
                array_unshift($rows1, $sum_row);
            }
            $rows0 = $rows1;
        }
        if(isset($rule['properties']['reset_id']) and $rule['properties']['reset_id'] == 1 and count($rows0) > 0){
            foreach($rows0 as $k=>$row){
                $rows0[$k]['id'] = $request['offset'] + $k + 1;
            }
        }
        $filter_list = [];
        
        // Составляем список уникальных значений для каждого поля
        if(!empty($rule['properties']['fields']) && !empty($rows0)){
            foreach($rule['properties']['fields'] as $fieldName => $fieldConfig){
                // Пропускаем поля, которые не нужно фильтровать
                if(isset($fieldConfig['modal_only']) && $fieldConfig['modal_only']) continue;
                if(isset($fieldConfig['no_filter']) && $fieldConfig['no_filter']) continue;
                
                // Собираем уникальные значения для поля
                $uniqueValues = [];
                foreach($rows0 as $row){
                    if(isset($row[$fieldName])){
                        $value = $row[$fieldName];
                        // Преобразуем null и пустые строки в ''
                        if($value === null || $value === ''){
                            $value = '';
                        }
                        // Используем значение как ключ для автоматической уникальности
                        $uniqueValues[$value] = $value;
                    }
                }
                
                // Если есть уникальные значения, добавляем их в filter_list
                if(!empty($uniqueValues)){
                    // Сортируем значения
                    ksort($uniqueValues);
                    $filter_list[$fieldName] = array_values($uniqueValues);
                }
            }
        }
        
        $out = [
            'rows'=>$rows0,
            'total'=>$total,
            // 'autocomplete'=>$autocompletes,
            'row_setting'=>$row_setting,
            'log'=>$this->pdo->getTime(),
            'filter_list'=>$filter_list
        ];
        if($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();
        // $this->modx->log(1,"read".$this->pdo->getTime());
        $out['autocomplete'] = $this->autocompletes($this->addFields($rule,$rule['properties']['fields'],'autocomplete'),$rows0,$request['offset']);
        
        if(!empty($rule['properties']['slTree'])){
            $out['slTree'] = $this->getslTree($rule['properties']['slTree'],$rows0,$parents);
        }
        $resp = $this->run_triggers($rule, 'after', 'read', $request, $out, $object_new, null, $internal_action);
        
        if(!$resp['success']) return $resp;
        
        if(!empty($resp['data']['out'])) $out = $resp['data']['out'];
        if(!empty($resp['data']['timings'])) $out['timings'] = $resp['data']['timings'];
        
        if(!empty($rule['properties']['table_tree'])){//table_tree
            foreach($out['rows'] as $k=>$row){
                $where = [
                    $rule['class'].'.'.$rule['properties']['table_tree']['parentIdField'] => $row[$rule['properties']['table_tree']['idField']]
                ];
                $out['rows'][$k]['gtsapi_children_count'] = $this->modx->getCount($rule['class'],$where);
            }
        }
        return $this->success('',$out);
    }
    public function setSelectRow($rule,$rows0){
        $select_row = [];
        foreach($rule['properties']['group']['select'] as $field => $v){
            switch($v['type_aggs']){
                case 'count':
                    $select_row[$field] = 0;
                break;
                case 'sum':
                    $select_row[$field] = 0;
                break;
                case 'max':
                    $select_row[$field] = $rows0[0][$field];
                break;
                case 'min':
                    $select_row[$field] = $rows0[0][$field];
                break;
                default:
                   $select_row[$field] = $v;
            }
        }
        return $select_row;
    }

    public function getslTree($slTreeSettings, $rows, $parents){
        foreach($rows as &$row){
            $row['title'] = $this->pdoTools->getChunk("@INLINE ".$slTreeSettings['title'],$row);
            $isLeaf = true;
            foreach($slTreeSettings['isLeaf'] as $field=>$v){
                if($row[$field] != $v) $isLeaf = false;
            }
            if($isLeaf) $row['isLeaf'] = true;
            $row[$slTreeSettings['idField']] = (int)$row[$slTreeSettings['idField']];
            $row[$slTreeSettings['parentIdField']] = (int)$row[$slTreeSettings['parentIdField']];
        }
        $tree0 = $this->buildTree($rows,$slTreeSettings['idField'],$slTreeSettings['parentIdField'], [(int)$parents]);
        $tree = [];
        $tree[] = $this->prepareTree($tree0[(int)$parents]);
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
                } else if($desc['type'] == 'multiautocomplete' and isset($desc['table']) and isset($desc['search'])){
                    // Обработка multiautocomplete
                    if($gtsAPITable = $this->modx->getObject('gtsAPITable',['table'=>$desc['table'],'active'=>1])){
                        $properties = json_decode($gtsAPITable->properties,1);
                        if(is_array($properties) and isset($properties['autocomplete'])){
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if(isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $desc['table'];
                            $autocomplete['class'] = $gtsAPITable->class?$gtsAPITable->class:$desc['table'];
                            
                            // Добавляем данные для полей поиска
                            $searchFieldsData = [];
                            foreach($desc['search'] as $searchFieldKey => $searchFieldConfig){
                                if(isset($searchFieldConfig['table'])){
                                    if($searchGtsAPITable = $this->modx->getObject('gtsAPITable',['table'=>$searchFieldConfig['table'],'active'=>1])){
                                        $searchProperties = json_decode($searchGtsAPITable->properties,1);
                                        if(is_array($searchProperties) and isset($searchProperties['autocomplete'])){
                                            $this->addPackages($searchGtsAPITable->package_id);
                                            $searchAutocomplete = $searchProperties['autocomplete'];
                                            $searchAutocomplete['field'] = $searchFieldKey;
                                            $searchAutocomplete['table'] = $searchFieldConfig['table'];
                                            $searchAutocomplete['class'] = $searchGtsAPITable->class?$searchGtsAPITable->class:$searchFieldConfig['table'];
                                            
                                            // Получаем значения для полей поиска из текущих строк
                                            $searchFieldValues = [];
                                            foreach($rows0 as $row){
                                                if(isset($row[$searchFieldKey]) && !empty($row[$searchFieldKey])){
                                                    $searchFieldValues[$row[$searchFieldKey]] = $row[$searchFieldKey];
                                                }
                                            }
                                            
                                            if(!empty($searchFieldValues)){
                                                $searchFieldsData[$searchFieldKey] = $this->autocomplete($searchAutocomplete, []);
                                                // Фильтруем только нужные значения
                                                $filteredRows = [];
                                                foreach($searchFieldsData[$searchFieldKey]['rows'] as $searchRow){
                                                    if(in_array($searchRow['id'], $searchFieldValues)){
                                                        $filteredRows[] = $searchRow;
                                                    }
                                                }
                                                $searchFieldsData[$searchFieldKey]['rows'] = $filteredRows;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $autocompleteResult = $this->autocomplete($autocomplete,$rows0);
                            $autocompleteResult['searchFields'] = $searchFieldsData;
                            $autocompletes[$field] = $autocompleteResult;
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
        $default = '';
        if(isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])){
            if($obj = $this->modx->getObject($rule['class'],$autocomplete['default_row'])){
                $autocomplete['default_value'] = $obj->id;
            }
        }
        $autocomplete['log'] = $this->pdo->getTime();
        $autocomplete['total'] = (int)$this->modx->getPlaceholder('total');
        return $autocomplete;
    }
    public function aplyFilter($rule, $name, $filter){
        
        $where = [];
        if($filter['value'] == null) return $where;
        
        // Проверяем, есть ли поле в select запроса
        $fieldExistsInSelect = false;
        $selectFields = [];
        
        if(isset($rule['properties']['query']['select']) && !empty($rule['properties']['query']['select'])){
            // Есть настроенный select
            foreach($rule['properties']['query']['select'] as $class => $fields){
                if($fields == '*'){
                    // Получаем все поля класса через MODX
                    if($this->modx->loadClass($class) && isset($this->modx->map[$class])){
                        foreach($this->modx->map[$class]['fieldMeta'] as $fieldName => $meta){
                            $selectFields[] = $fieldName;
                        }
                    }
                } else {
                    // Парсим строку select
                    $fieldsArray = array_map('trim', explode(',', $fields));
                    foreach($fieldsArray as $fieldStr){
                        // Убираем обратные кавычки
                        $fieldStr = str_replace('`', '', $fieldStr);
                        // Убираем класс с точкой (с кавычками и без)
                        $fieldStr = preg_replace('/^' . preg_quote($class, '/') . '\./', '', $fieldStr);
                        // Проверяем на AS
                        if(stripos($fieldStr, ' AS ') !== false){
                            $parts = preg_split('/\s+AS\s+/i', $fieldStr);
                            if(isset($parts[1])){
                                $selectFields[] = trim($parts[1]);
                            }
                        } else {
                            $selectFields[] = $fieldStr;
                        }
                    }
                }
            }
        } else {
            // Нет настроенного select - получаем поля основного класса
            
            // Исключения для системных классов MODX с неполной fieldMeta
            $systemClassFields = [
                'modAccessResourceGroup' => ['id', 'target', 'principal_class', 'principal', 'authority', 'policy', 'context_key'],
                'modAccessContext' => ['id', 'target', 'principal_class', 'principal', 'authority', 'policy'],
                'modAccessCategory' => ['id', 'target', 'principal_class', 'principal', 'authority', 'policy', 'context_key'],
            ];
            
            if(isset($systemClassFields[$rule['class']])){
                $selectFields = $systemClassFields[$rule['class']];
            } elseif($this->modx->loadClass($rule['class']) && isset($this->modx->map[$rule['class']])){
                foreach($this->modx->map[$rule['class']]['fieldMeta'] as $fieldName => $meta){
                    $selectFields[] = $fieldName;
                }
            }
            
        }
        $selectFields[] = 'id';
        // Проверяем наличие поля в списке
        if(in_array($name, $selectFields)){
            $fieldExistsInSelect = true;
        }
        
        // Если поля нет в select, пропускаем фильтр
        if(!$fieldExistsInSelect){
            return $where;
        }
        
        if(isset($rule['properties']['filters'][$name]) and is_array($rule['properties']['filters'][$name])){
            $filter = array_merge($rule['properties']['filters'][$name],$filter);
        }
        if(isset($rule['properties']['fields'][$name]) and is_array($rule['properties']['fields'][$name])){
            $filter = array_merge($rule['properties']['fields'][$name],$filter);
        }
        $field = "{$rule['class']}.$name";
        if(isset($filter['class']))  $field = "{$filter['class']}.$name";
        if(isset($filter['as']) and isset($filter['class']))  $field = "{$filter['class']}.{$filter['as']}";
        if(isset($filter['field']) and isset($filter['class']))  $field = "{$filter['class']}.{$filter['field']}";
        

        if(strpos($name,'.') !== false) $field = $name;

        if($filter['value'] == 'true'){
            $filter['value'] = 1;
        }else if($filter['value'] == 'false'){
            $filter['value'] = 0;
        }
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
                if($name == 'parents_ids'){
                    $where["{$rule['class']}.parents_ids:LIKE"] = '%#'.$filter['value'].'#%';
                }else if(isset($filter['where'])){
                    $where[100] = "{$filter['where']} = '{$filter['value']}'";
                }else{
                    $where[$field] = $filter['value'];
                }
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
        if(isset($rule_action['permissions'])and !empty($rule_action['permissions'])){
            $permissions = array_map('trim', explode(',', $rule_action['permissions']));
            foreach($permissions as $pm){
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
    public function run_triggers($rule, $type, $method, $fields, $object_old = [], &$object_new =[], $object = null, $internal_action = '')
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
            'trigger'=>'gtsapifunc',
            'object'=>$object,
            'internal_action'=>$internal_action,
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
                        'internal_action'=>$internal_action,
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

    public function excel_export($rule, $request) {
        // Проверяем, включено ли действие excel_export
        if (isset($rule['properties']['actions']['excel_export']) && $rule['properties']['actions']['excel_export'] === false) {
            return $this->error('Excel export is disabled');
        }

        // Подключаем PHPExcel
        require_once MODX_CORE_PATH . '/components/gettables/vendor/PHPOffice/PHPExcel.php';

        try {
            // Создаем новый объект PHPExcel
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()
                ->setCreator("gtsAPI")
                ->setLastModifiedBy("gtsAPI")
                ->setTitle("Export Data")
                ->setSubject("Export Data")
                ->setDescription("Data exported from gtsAPI");

            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle('Data');

            // Получаем поля
            $fields = [];
            if (!empty($rule['properties']['fields'])) {
                $fields = $this->addFields($rule, $rule['properties']['fields'], 'read');
            } else {
                if ($rule['type'] == 1) $fields = $this->gen_fields($rule);
            }

            // Если есть form.fields в настройках excel_export, добавляем данные формы
            $formData = [];
            
            if (isset($rule['properties']['actions']['excel_export']['form']['fields']) && !empty($request['filters'])) {
                $formFields = $rule['properties']['actions']['excel_export']['form']['fields'];
                $currentRow = 1;
                
                foreach ($formFields as $fieldName => $fieldConfig) {
                    // Определяем имя поля для поиска в фильтрах
                    $filterFieldName = $fieldName;
                    if (isset($fieldConfig['class']) && isset($fieldConfig['as'])) {
                        $filterFieldName = $fieldConfig['class'] . '.' . $fieldConfig['as'];
                    }
                    
                    if (isset($request['filters'][$filterFieldName])) {
                        // $this->modx->log(1, 'Excel export: found filter for field ' . $filterFieldName . ' - ' . print_r($request['filters'][$filterFieldName], 1));
                        
                        // Обработка формата constraints
                        if (isset($request['filters'][$filterFieldName]['constraints']) && is_array($request['filters'][$filterFieldName]['constraints'])) {
                            // Берем первое значение из constraints
                            $value = $request['filters'][$filterFieldName]['constraints'][0]['value'] ?? '';
                        } else {
                            $value = $request['filters'][$filterFieldName]['value'] ?? $request['filters'][$filterFieldName];
                        }
                        
                        // Обработка autocomplete полей
                        if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'autocomplete' && isset($fieldConfig['table'])) {
                            if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $fieldConfig['table'], 'active' => 1])) {
                                $properties = json_decode($gtsAPITable->properties, 1);
                                if (is_array($properties) && isset($properties['autocomplete'])) {
                                    $this->addPackages($gtsAPITable->package_id);
                                    $class = $gtsAPITable->class ? $gtsAPITable->class : $fieldConfig['table'];
                                    if ($obj = $this->modx->getObject($class, $value)) {
                                        // Проверяем наличие tpl шаблона
                                        if (!empty($properties['autocomplete']['tpl'])) {
                                            $value = $this->pdoTools->getChunk("@INLINE " . $properties['autocomplete']['tpl'], $obj->toArray());
                                        } else {
                                            $displayField = 'name';
                                            $value = $obj->get($displayField);
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Обработка multiautocomplete полей
                        if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'multiautocomplete' && isset($fieldConfig['table'])) {
                            // Сначала получаем основной объект multiautocomplete
                            if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $fieldConfig['table'], 'active' => 1])) {
                                $properties = json_decode($gtsAPITable->properties, 1);
                                if (is_array($properties) && isset($properties['autocomplete'])) {
                                    $this->addPackages($gtsAPITable->package_id);
                                    $class = $gtsAPITable->class ? $gtsAPITable->class : $fieldConfig['table'];
                                    if ($obj = $this->modx->getObject($class, $value)) {
                                        // Проверяем наличие tpl шаблона для основного объекта
                                        if (!empty($properties['autocomplete']['tpl'])) {
                                            $mainDisplayValue = $this->pdoTools->getChunk("@INLINE " . $properties['autocomplete']['tpl'], $obj->toArray());
                                        } else {
                                            $displayField = 'name';
                                            $mainDisplayValue = $obj->get($displayField);
                                        }
                                        
                                        $label = $fieldConfig['label'] ?? $fieldName;
                                        $sheet->setCellValue('A' . $currentRow, $label . ':');
                                        $sheet->setCellValue('B' . $currentRow, $mainDisplayValue);
                                        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                                        $currentRow++;
                                        
                                        // Теперь обрабатываем search поля
                                        if (isset($fieldConfig['search'])) {
                                            foreach ($fieldConfig['search'] as $searchFieldKey => $searchFieldConfig) {
                                                // Определяем имя поля для поиска в фильтрах для search полей
                                                $searchFilterFieldName = $searchFieldKey;
                                                
                                                if (isset($searchFieldConfig['table'])) {
                                                    // Ищем значение в основном объекте
                                                    $searchValue = $obj->get($searchFieldKey) ?? '';
                                                    
                                                    if (!empty($searchValue)) {
                                                        if ($searchGtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $searchFieldConfig['table'], 'active' => 1])) {
                                                            $searchProperties = json_decode($searchGtsAPITable->properties, 1);
                                                            if (is_array($searchProperties) && isset($searchProperties['autocomplete'])) {
                                                                $this->addPackages($searchGtsAPITable->package_id);
                                                                $searchClass = $searchGtsAPITable->class ? $searchGtsAPITable->class : $searchFieldConfig['table'];
                                                                if ($searchObj = $this->modx->getObject($searchClass, $searchValue)) {
                                                                    // Проверяем наличие tpl шаблона
                                                                    if (!empty($searchProperties['autocomplete']['tpl'])) {
                                                                        $searchDisplayValue = $this->pdoTools->getChunk("@INLINE " . $searchProperties['autocomplete']['tpl'], $searchObj->toArray());
                                                                    } else {
                                                                        $searchDisplayField = 'name';
                                                                        $searchDisplayValue = $searchObj->get($searchDisplayField);
                                                                    }
                                                                    
                                                                    $searchLabel = $searchFieldConfig['label'] ?? $searchFieldKey;
                                                                    $sheet->setCellValue('A' . $currentRow, $searchLabel . ':');
                                                                    $sheet->setCellValue('B' . $currentRow, $searchDisplayValue);
                                                                    $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                                                                    $currentRow++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Для multiautocomplete не выводим основное поле внизу
                        if (!(isset($fieldConfig['type']) && $fieldConfig['type'] === 'multiautocomplete')) {
                            $label = $fieldConfig['label'] ?? $fieldName;
                            $sheet->setCellValue('A' . $currentRow, $label . ':');
                            $sheet->setCellValue('B' . $currentRow, $value);
                            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
                            $currentRow++;
                        }
                    }
                }
                
                if ($currentRow > 1) {
                    $currentRow++; // Пустая строка между формой и таблицей
                }
                $formData['startRow'] = $currentRow;
            }

            // Подготавливаем заголовки столбцов
            $headers = [];
            $columnIndex = 0;
            $startRow = isset($formData['startRow']) ? $formData['startRow'] : 1;

            foreach ($fields as $fieldName => $fieldConfig) {
                if (isset($fieldConfig['modal_only']) && $fieldConfig['modal_only']) continue;
                
                $label = $fieldConfig['label'] ?? $fieldName;
                
                // Обработка autocomplete полей - создаем два столбца
                if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'autocomplete') {
                    $headers[] = [
                        'field' => $fieldName,
                        'label' => $label . ' ID',
                        'type' => 'autocomplete_id'
                    ];
                    $headers[] = [
                        'field' => $fieldName,
                        'label' => $label,
                        'type' => 'autocomplete_display',
                        'config' => $fieldConfig
                    ];
                } 
                // Обработка multiautocomplete полей
                else if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'multiautocomplete') {
                    if (isset($fieldConfig['search'])) {
                        foreach ($fieldConfig['search'] as $searchField => $searchConfig) {
                            $searchLabel = $searchConfig['label'] ?? $searchField;
                            $headers[] = [
                                'field' => $fieldName . '_' . $searchField,
                                'label' => $label . ' - ' . $searchLabel,
                                'type' => 'multiautocomplete',
                                'config' => $searchConfig,
                                'parent_field' => $fieldName,
                                'search_field' => $searchField
                            ];
                        }
                    }
                } else {
                    $headers[] = [
                        'field' => $fieldName,
                        'label' => $label,
                        'type' => $fieldConfig['type'] ?? 'text'
                    ];
                }
            }

            // Записываем заголовки
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $startRow, $header['label']);
                $sheet->getStyle($col . $startRow)->getFont()->setBold(true);
                $col++;
            }

            // Применяем автофильтр к заголовкам
            $lastCol = chr(ord('A') + count($headers) - 1);
            $sheet->setAutoFilter('A' . $startRow . ':' . $lastCol . $startRow);

            // Получаем данные с limit = 0
            $exportRequest = $request;
            $exportRequest['limit'] = 0;
            $exportRequest['setTotal'] = true;
            
            $dataResponse = $this->read($rule, $exportRequest, null);
            if (!$dataResponse['success']) {
                return $dataResponse;
            }

            $rows = $dataResponse['data']['rows'];
            $autocompletes = $dataResponse['data']['autocomplete'] ?? [];
            $currentRow = $startRow + 1;

            // Записываем данные
            foreach ($rows as $row) {
                $col = 'A';
                
                foreach ($headers as $header) {
                    $value = '';
                    
                    switch ($header['type']) {
                        case 'autocomplete_id':
                            $value = $row[$header['field']] ?? '';
                            break;
                            
                        case 'autocomplete_display':
                            $fieldName = $header['field'];
                            $fieldValue = $row[$fieldName] ?? '';
                            
                            if (!empty($fieldValue) && isset($autocompletes[$fieldName])) {
                                // Ищем значение в загруженных автокомплитах
                                foreach ($autocompletes[$fieldName]['rows'] as $autocompleteRow) {
                                    if ($autocompleteRow['id'] == $fieldValue) {
                                        $value = $autocompleteRow['content'] ?? $autocompleteRow['name'] ?? $autocompleteRow['title'] ?? $fieldValue;
                                        break;
                                    }
                                }
                            }
                            break;
                            
                        case 'multiautocomplete':
                            $parentField = $header['parent_field'];
                            $searchField = $header['search_field'];
                            $value = $row[$searchField] ?? '';
                            
                            // Используем данные из autocompletes для multiautocomplete
                            if (!empty($value) && isset($autocompletes[$parentField]['searchFields'][$searchField])) {
                                foreach ($autocompletes[$parentField]['searchFields'][$searchField]['rows'] as $autocompleteRow) {
                                    if ($autocompleteRow['id'] == $value) {
                                        $value = $autocompleteRow['content'] ?? $autocompleteRow['name'] ?? $autocompleteRow['title'] ?? $value;
                                        break;
                                    }
                                }
                            }
                            break;
                            
                        case 'date':
                            $value = $row[$header['field']] ?? '';
                            if (!empty($value)) {
                                $timestamp = strtotime($value);
                                if ($timestamp !== false) {
                                    $value = PHPExcel_Shared_Date::PHPToExcel($timestamp);
                                    $sheet->getStyle($col . $currentRow)->getNumberFormat()->setFormatCode('dd.mm.yyyy');
                                }
                            }
                            break;
                            
                        default:
                            $value = $row[$header['field']] ?? '';
                            break;
                    }
                    
                    $sheet->setCellValue($col . $currentRow, $value);
                    $col++;
                }
                $currentRow++;
            }

            // Применяем границы к ячейкам
            $lastRow = $currentRow - 1;
            $lastCol = chr(ord('A') + count($headers) - 1);
            $range = 'A' . $startRow . ':' . $lastCol . $lastRow;
            
            $styleArray = [
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    ]
                ]
            ];
            $sheet->getStyle($range)->applyFromArray($styleArray);

            // Автоподбор ширины столбцов
            foreach (range('A', $lastCol) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Создаем writer и отправляем файл
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            
            $filename = 'export_' . $rule['table'] . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $objWriter->save('php://output');
            exit;
            
        } catch (Exception $e) {
            return $this->error('Excel export error: ' . $e->getMessage());
        }
    }

    public function print($rule, $request) {
        // Проверяем, включено ли действие print
        if (isset($rule['properties']['actions']['print']) && $rule['properties']['actions']['print'] === false) {
            return $this->error('Print is disabled');
        }

        try {
            // Получаем поля
            $fields = [];
            if (!empty($rule['properties']['fields'])) {
                $fields = $this->addFields($rule, $rule['properties']['fields'], 'read');
            } else {
                if ($rule['type'] == 1) $fields = $this->gen_fields($rule);
            }

            // Подготавливаем заголовки столбцов
            $headers = [];
            foreach ($fields as $fieldName => $fieldConfig) {
                if (isset($fieldConfig['modal_only']) && $fieldConfig['modal_only']) continue;
                if($fieldName == 'id' and isset($rule['properties']['hide_id'])) continue;
                if (isset($fieldConfig['type']) && $fieldConfig['type'] == 'hidden') continue;
                if (isset($fieldConfig['no_print'])) continue;

                $label = $fieldConfig['label'] ?? $fieldName;
                
                // Обработка autocomplete полей
                if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'autocomplete') {
                    $headers[] = [
                        'field' => $fieldName,
                        'label' => $label,
                        'type' => 'autocomplete',
                        'config' => $fieldConfig
                    ];
                } 
                // Обработка multiautocomplete полей
                else if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'multiautocomplete') {
                    if (isset($fieldConfig['search'])) {
                        foreach ($fieldConfig['search'] as $searchField => $searchConfig) {
                            $searchLabel = $searchConfig['label'] ?? $searchField;
                            $headers[] = [
                                'field' => $fieldName . '_' . $searchField,
                                'label' => $label . ' - ' . $searchLabel,
                                'type' => 'multiautocomplete',
                                'config' => $searchConfig,
                                'parent_field' => $fieldName,
                                'search_field' => $searchField
                            ];
                        }
                    }
                } else {
                    $headers[] = [
                        'field' => $fieldName,
                        'label' => $label,
                        'type' => $fieldConfig['type'] ?? 'text'
                    ];
                }
            }

            // Получаем данные с limit = 0
            $printRequest = $request;
            $printRequest['limit'] = 0;
            $printRequest['setTotal'] = true;
            
            $dataResponse = $this->read($rule, $printRequest, null);
            if (!$dataResponse['success']) {
                return $dataResponse;
            }

            $rows = $dataResponse['data']['rows'];
            $autocompletes = $dataResponse['data']['autocomplete'] ?? [];

            // Генерируем HTML
            $html = $this->generatePrintHTML($rule, $headers, $rows, $autocompletes, $request);

            // Проверяем is_virtual
            // $this->modx->log(1, 'gtsAPI print $request: ' . print_r($request, 1));
            $isVirtual = isset($request['is_virtual']) ? (int)$request['is_virtual'] : 0;

            if ($isVirtual === 1) {
                // Возвращаем HTML в браузер для генерации PDF
                return $this->success('print', ['html' => $html]);
            } else {
                // Печатаем через PVPrint
                $PVPrint = $this->modx->getService('PVPrint', 'PVPrint', 
                    MODX_CORE_PATH . 'components/pvprint/model/'
                );

                if (!$PVPrint) {
                    return $this->error('Ошибка загрузки PVPrint');
                }

                $printerId = isset($request['printer_id']) ? (int)$request['printer_id'] : null;
                if (!$printerId) {
                    return $this->error('Не указан принтер');
                }

                $printOptions = isset($request['printOptions']) ? $request['printOptions'] : [];

                $result = $PVPrint->printHTML($html, $printerId, $printOptions);

                if ($result['success']) {
                    return $this->success('print', $result['data']);
                } else {
                    return $this->error($result['message']);
                }
            }
            
        } catch (Exception $e) {
            return $this->error('Print error: ' . $e->getMessage());
        }
    }

    private function generatePrintHTML($rule, $headers, $rows, $autocompletes, $request) {
        if(isset($request['no_html_tag'])){
            $html = '';
        }else{
            $html = '<html><head><meta charset="UTF-8"><style>';
            $html .= 'body { font-family: Arial, sans-serif; font-size: 12px; }';
            $html .= 'table { width: 100%; border-collapse: collapse; margin-top: 20px; }';
            $html .= 'th, td { border: 1px solid #000; padding: 5px; text-align: left; }';
            $html .= 'th { background-color: #f0f0f0; font-weight: bold; }';
            $html .= 'h1 { text-align: center; }';
            $html .= '</style></head><body>';
            
            // Заголовок документа
            $title = $rule['name'] ?? $rule['table'];
            $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
        }
        // Если есть form.fields в настройках print, добавляем данные формы
        if (isset($rule['properties']['actions']['print']['form']['fields']) && !empty($request['filters'])) {
            $formFields = $rule['properties']['actions']['print']['form']['fields'];
            $html .= '<div style="margin-bottom: 20px;">';
            
            foreach ($formFields as $fieldName => $fieldConfig) {
                $filterFieldName = $fieldName;
                if (isset($fieldConfig['class']) && isset($fieldConfig['as'])) {
                    $filterFieldName = $fieldConfig['class'] . '.' . $fieldConfig['as'];
                }
                
                if (isset($request['filters'][$filterFieldName])) {
                    if (isset($request['filters'][$filterFieldName]['constraints']) && is_array($request['filters'][$filterFieldName]['constraints'])) {
                        $value = $request['filters'][$filterFieldName]['constraints'][0]['value'] ?? '';
                    } else {
                        $value = $request['filters'][$filterFieldName]['value'] ?? $request['filters'][$filterFieldName];
                    }
                    
                    // Обработка autocomplete полей
                    if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'autocomplete' && isset($fieldConfig['table'])) {
                        if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $fieldConfig['table'], 'active' => 1])) {
                            $properties = json_decode($gtsAPITable->properties, 1);
                            if (is_array($properties) && isset($properties['autocomplete'])) {
                                $this->addPackages($gtsAPITable->package_id);
                                $class = $gtsAPITable->class ? $gtsAPITable->class : $fieldConfig['table'];
                                if ($obj = $this->modx->getObject($class, $value)) {
                                    if (!empty($properties['autocomplete']['tpl'])) {
                                        $value = $this->pdoTools->getChunk("@INLINE " . $properties['autocomplete']['tpl'], $obj->toArray());
                                    } else {
                                        $displayField = 'name';
                                        $value = $obj->get($displayField);
                                    }
                                }
                            }
                        }
                    }
                    
                    $label = $fieldConfig['label'] ?? $fieldName;
                    $html .= '<p><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars($value) . '</p>';
                }
            }
            
            $html .= '</div>';
        }
        
        // Таблица с данными
        $html .= '<table>';
        $html .= '<thead><tr>';
        
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header['label']) . '</th>';
        }
        
        $html .= '</tr></thead><tbody>';
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            
            foreach ($headers as $header) {
                $value = '';
                
                switch ($header['type']) {
                    case 'autocomplete':
                        $fieldName = $header['field'];
                        $fieldValue = $row[$fieldName] ?? '';
                        
                        if (!empty($fieldValue) && isset($autocompletes[$fieldName])) {
                            foreach ($autocompletes[$fieldName]['rows'] as $autocompleteRow) {
                                if ($autocompleteRow['id'] == $fieldValue) {
                                    $value = $autocompleteRow['content'] ?? $autocompleteRow['name'] ?? $autocompleteRow['title'] ?? $fieldValue;
                                    break;
                                }
                            }
                        }
                        break;
                        
                    case 'multiautocomplete':
                        $searchField = $header['search_field'];
                        $parentField = $header['parent_field'];
                        $value = $row[$searchField] ?? '';
                        
                        if (!empty($value) && isset($autocompletes[$parentField]['searchFields'][$searchField])) {
                            foreach ($autocompletes[$parentField]['searchFields'][$searchField]['rows'] as $autocompleteRow) {
                                if ($autocompleteRow['id'] == $value) {
                                    $value = $autocompleteRow['content'] ?? $autocompleteRow['name'] ?? $autocompleteRow['title'] ?? $value;
                                    break;
                                }
                            }
                        }
                        break;
                        
                    default:
                        $value = $row[$header['field']] ?? '';
                        break;
                }
                
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        if(!isset($request['no_html_tag'])) $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Получает список полей из select запроса
     * @param array $config Конфигурация запроса
     * @param array $rule Правило таблицы
     * @return array Список полей
     */
    private function getSelectFieldsList($config, $rule) {
        $selectFields = [];
        
        if(isset($config['select']) && !empty($config['select'])){
            // Есть настроенный select
            foreach($config['select'] as $class => $fields){
                if($fields == '*'){
                    // Получаем все поля класса через MODX
                    if($this->modx->loadClass($class) && isset($this->modx->map[$class])){
                        foreach($this->modx->map[$class]['fieldMeta'] as $fieldName => $meta){
                            $selectFields[] = $fieldName;
                        }
                    }
                } else {
                    // Парсим строку select
                    $fieldsArray = array_map('trim', explode(',', $fields));
                    foreach($fieldsArray as $fieldStr){
                        // Убираем обратные кавычки
                        $fieldStr = str_replace('`', '', $fieldStr);
                        // Убираем класс с точкой (с кавычками и без)
                        $fieldStr = preg_replace('/^' . preg_quote($class, '/') . '\./', '', $fieldStr);
                        // Проверяем на AS
                        if(stripos($fieldStr, ' AS ') !== false){
                            $parts = preg_split('/\s+AS\s+/i', $fieldStr);
                            if(isset($parts[1])){
                                $selectFields[] = trim($parts[1]);
                            }
                        } else {
                            $selectFields[] = $fieldStr;
                        }
                    }
                }
            }
        } else {
            // Нет настроенного select - получаем поля основного класса
            if($this->modx->loadClass($rule['class']) && isset($this->modx->map[$rule['class']])){
                foreach($this->modx->map[$rule['class']]['fieldMeta'] as $fieldName => $meta){
                    $selectFields[] = $fieldName;
                }
            }
        }
        $selectFields[] = 'id';
        return $selectFields;
    }
    
    /**
     * Рекурсивно заменяет все строковые значения, содержащие 'insert_menu_id', на значение $insert_menu_id
     *
     * @param array $array Массив для обработки
     * @param int $insert_menu_id Значение для замены
     * @return array Обработанный массив
     */
    public function replaceInsertMenuIdInArray($array, $insert_menu_id) {
        if (!is_array($array)) {
            return $array;
        }
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Рекурсивно обрабатываем вложенные массивы
                $array[$key] = $this->replaceInsertMenuIdInArray($value, $insert_menu_id);
            } elseif (is_string($value) && strpos($value, 'insert_menu_id') !== false) {
                // Заменяем строку 'insert_menu_id' на значение переменной
                $array[$key] = str_replace('insert_menu_id', $insert_menu_id, $value);
            }
        }
        
        return $array;
    }
    /**
     * Сохранение стилей колонок на сервере
     * Доступно только для группы Administrator
     */
    public function save_fields_style($rule, $request) {
        // Проверка прав доступа - только Administrator
        if (!$this->modx->user->isMember('Administrator')) {
            return $this->error('Доступ запрещен. Требуются права администратора.');
        }
        
        if (empty($request['fields_style'])) {
            return $this->error('Не переданы стили полей');
        }
        
        // Получаем объект таблицы
        if (!$gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $rule['table'], 'active' => 1])) {
            return $this->error('Таблица не найдена');
        }
        
        // Сохраняем стили как JSON
        $gtsAPITable->set('fields_style', json_encode($request['fields_style']));
        
        if ($gtsAPITable->save()) {
            return $this->success('Стили полей сохранены', [
                'fields_style' => $request['fields_style']
            ]);
        }
        
        return $this->error('Ошибка сохранения стилей');
    }

    /**
     * Сброс стилей колонок на сервере
     * Доступно только для группы Administrator
     */
    public function reset_fields_style($rule, $request) {
        // Проверка прав доступа - только Administrator
        if (!$this->modx->user->isMember('Administrator')) {
            return $this->error('Доступ запрещен. Требуются права администратора.');
        }
        
        // Получаем объект таблицы
        if (!$gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $rule['table'], 'active' => 1])) {
            return $this->error('Таблица не найдена');
        }
        
        // Очищаем стили
        $gtsAPITable->set('fields_style', null);
        
        if ($gtsAPITable->save()) {
            return $this->success('Стили полей сброшены', [
                'fields_style' => null
            ]);
        }
        
        return $this->error('Ошибка сброса стилей');
    }
}

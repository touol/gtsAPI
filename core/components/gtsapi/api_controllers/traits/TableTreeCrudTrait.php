<?php

require_once __DIR__ . '/TableLogTrait.php';

/**
 * CRUD-действия UniTree/дерева, вынесенные из treeAPIController.
 * Методы работают в контексте treeAPIController ($this->modx, run_triggers,
 * get_slTreeSettings, addFields, addDefaultFields, success/error и т.д.).
 */
trait TableTreeCrudTrait
{
    use TableLogTrait;

    public function nodedrop($rule,$request){
        $class = $rule['class'];
        // Получаем имя таблицы из класса
        $table_name = $this->modx->getTableName($class);

        $slTreeSettings = $this->get_slTreeSettings($rule);
        $position = $request['position1'];
        $isCopy  = $request['copy'];

        if($slTreeSettings['useUniTree']){
            foreach($request['nodes1'] as $node){
                if($obj = $this->modx->getObject($class,$node['id'])){
                    if($isCopy == 'true' or $isCopy === true){
                        if($newObj = $this->modx->newObject($class)){
                            $newObj->fromArray($obj->toArray());
                            $newObj->save();
                        }
                    }
                    if($position['node']['parent_id'] != $node['parent_id'] 
                     or ($position['placement'] == 'inside' and $position['node']['id'] != $node['parent_id'])
                    ){
                        // $this->modx->log(1,'placement0');
                        if($position['placement'] == 'inside'){
                            $obj->set($slTreeSettings['parentIdField'], $position['node']['id']);
                        }else{
                            $obj->set($slTreeSettings['parentIdField'], $position['node']['parent_id']);
                        }
                        
                        
                        // Получаем текущий parents_ids перетаскиваемого узла
                        $current_node_parents_ids = $obj->get($slTreeSettings['parents_idsField']);
                        
                        // Формируем новый parents_ids для перетаскиваемого узла
                        $new_parents_ids = '';
                        
                        // Если перемещаем в корень
                        if($position['node']['parent_id'] == 0) {
                            // Если размещаем внутри другого узла
                            if($position['placement'] == 'inside' && $parentObj = $this->modx->getObject($class, $position['node']['id'])) {
                                $parent_parents_ids = $parentObj->get($slTreeSettings['parents_idsField']);
                                $new_parents_ids = ($parent_parents_ids == '' ? '#' : $parent_parents_ids) . $parentObj->id . '#';
                            } else {
                                // Если размещаем в корне
                                $new_parents_ids = '';
                            }
                        } else {
                            // Если перемещаем не в корень
                            if($parentObj = $this->modx->getObject($class, $position['node']['parent_id'])) {
                                // Если размещаем внутри другого узла
                                if($position['placement'] == 'inside') {
                                    $parentObj = $this->modx->getObject($class, $position['node']['id']);
                                }
                                
                                if($parentObj) {
                                    $parent_parents_ids = $parentObj->get($slTreeSettings['parents_idsField']);
                                    $new_parents_ids = ($parent_parents_ids == '' ? '#' : $parent_parents_ids) . $parentObj->id . '#';
                                }
                            }
                        }
                        
                        // Устанавливаем новый parents_ids для перетаскиваемого узла
                        $obj->set($slTreeSettings['parents_idsField'], $new_parents_ids);
                        $obj->save();
                        
                        // Если у узла есть дочерние элементы, обновляем их parents_ids с помощью SQL запроса
                        if($current_node_parents_ids != '') {
                            // Формируем шаблон для поиска дочерних элементов
                            $search_pattern = $current_node_parents_ids . $obj->id . '#%';
                            
                            // Формируем SQL запрос для обновления всех дочерних элементов
                            $sql = "UPDATE {$table_name} 
                                   SET {$slTreeSettings['parents_idsField']} = REPLACE(
                                       {$slTreeSettings['parents_idsField']}, 
                                       '{$current_node_parents_ids}{$obj->id}#', 
                                       '{$new_parents_ids}{$obj->id}#'
                                   )
                                   WHERE {$slTreeSettings['parents_idsField']} LIKE '{$search_pattern}'";
                            
                            // $this->modx->log(1, "Updating children with SQL: {$sql}");
                            $this->modx->exec($sql);
                        }
                    }
                    switch($position['placement']){
                        case 'before':
                            $obj->set($slTreeSettings['menuindexField'],$position['node']['menuindex']);
                            

                            $this->modx->exec("UPDATE {$table_name} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
                                WHERE {$slTreeSettings['parentIdField']} = {$position['node']['parent_id']} 
                                AND {$slTreeSettings['menuindexField']} >= {$position['node']['menuindex']}");
                            $obj->save();
                            
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
                            

                            $this->modx->exec("UPDATE {$table_name} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
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

                            // $this->modx->exec("UPDATE {$table_name} SET {$slTreeSettings['menuindexField']} = {$slTreeSettings['menuindexField']} + 1 
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
                                    }else{
                                        $source->menuindex = 0;
                                        $source->save();
                                    }
                                }
                                
                            }
                        break;
                    }

                    // После перемещения узла дёргаем триггер на классе target'а
                    // (например osEmployee) — нужно для синков и побочных действий.
                    // method='nodedrop' — триггер видит что это именно перемещение в дереве.
                    $this->fireNodedropTrigger($rule, $request, $obj, $slTreeSettings);
                }

            }

        }else{
            foreach($request['nodes1'] as $node){
                if($source = $this->modx->getObject($class,$node['id'])){
                    if($isCopy == 'true' or $isCopy === true){
                        if($newObj = $this->modx->newObject($class)){
                            $newObj->fromArray($source->toArray());
                            $newObj->save();
                        }
                    }
                    switch($position['placement']){
                        case 'before':
                            if($slTreeSettings['extendedModResource']){
                                if($target = $this->modx->getObject('modResource', $position['node']['id'])
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
                            if($slTreeSettings['extendedModResource']){
                                if($target = $this->modx->getObject('modResource', $position['node']['id'])
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
                            if($slTreeSettings['extendedModResource']){
                                if($target = $this->modx->getObject('modResource', $position['node']['id'])
                                ){
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

                    // Тот же триггер для legacy non-UniTree варианта
                    $this->fireNodedropTrigger($rule, $request, $source, $slTreeSettings);
                }
            }
        }
        return $this->success('success');
    }

    /**
     * Дёргаем gtsapifunc-триггер на классе target'а узла после nodedrop.
     * Для UniTree (osTree, osDepartment-tree и т.п.) target класс берётся из
     * поля classField. Для не-UniTree — это сам класс таблицы дерева.
     */
    protected function fireNodedropTrigger($rule, $request, $obj, $slTreeSettings) {
        if (empty($slTreeSettings['useUniTree'])) {
            // Не-UniTree: target класс == класс таблицы (нет колонки 'class')
            $targetClass = $rule['class'];
            $targetObj = $obj;
        } else {
            $targetClass = (string)$obj->get($slTreeSettings['classField']);
            $targetId    = (int)$obj->get('target_id');
            if (!$targetClass || !$targetId) return;
            $targetObj = $this->modx->getObject($targetClass, $targetId);
            if (!$targetObj) return;
        }
        $targetNew = $targetObj->toArray();
        $this->run_triggers($rule, 'after', 'nodedrop', $request, [], $targetNew, $targetObj, $targetClass);
        // Логируем перемещение узла (новое расположение: parent/parents_ids/menuindex).
        $this->writeLog($rule, 'nodedrop', (int)$obj->get('id'), null, $obj->toArray());
    }

    /**
     * Дёргает gtsapifunc-триггер КЛАССА TARGET'а UniTree-узла (create/update).
     * Через $class2 в run_triggers → диспатч по классу target'а (напр. osEmployee →
     * syncEmployeeToLegacy), а не по классу таблицы дерева (osTree). Это закрывает пробел:
     * при заведении/правке сотрудника через дерево обратная синхра в legacy теперь срабатывает.
     * Пропускаем, если target — тот же класс, что таблица дерева (иначе дубль с основным
     * run_triggers), или target не резолвится.
     */
    protected function fireTargetTrigger($rule, $request, $method, $targetClass, $targetId) {
        $targetClass = (string)$targetClass;
        $targetId    = (int)$targetId;
        if (!$targetClass || !$targetId || $targetClass === $rule['class']) return;
        if (!$targetObj = $this->modx->getObject($targetClass, $targetId)) return;
        $targetNew = $targetObj->toArray();
        $this->run_triggers($rule, 'after', $method, $request, [], $targetNew, $targetObj, $targetClass);
    }

    public function delete($rule,$request,$action){
        
        if(!empty($request['ids'])){
            $slTreeSettings = $this->get_slTreeSettings($rule);
            if(is_string($request['ids'])) $request['ids'] = explode(',',$request['ids']);
            // getCollection, НЕ getIterator: удаление внутри цикла мутирует ту же таблицу
            // под живым курсором → cursor-skip (часть строк не удаляется). Особенно опасно
            // здесь из-за вложенного каскадного delete детей ниже.
            $objs = $this->modx->getCollection($rule['class'],['id:IN'=>$request['ids']]);

            foreach($objs as $obj){
                $object_old = $obj->toArray();
                $resp = $this->run_triggers($rule, 'before', 'delete', [], $object_old);
                if(!$resp['success']) return $resp;
                
                if($rule['properties']['useUniTree']){
                    $count = $this->modx->getCount($rule['class'],['target_id'=>$obj->target_id]);
                    // $this->modx->log(1,"delete $count");
                    if($count == 1 and $target = $this->modx->getObject($obj->class,$obj->target_id) and $obj->class != $rule['class']){
                        // Обратная синхра legacy: триггер класса target'а на 'delete'
                        // (напр. syncEmployeeToLegacy деактивирует gtsBStaff) — до удаления.
                        $targetOld = $target->toArray();
                        $target->remove();
                        $dummyNew = [];
                        $this->run_triggers($rule, 'after', 'delete', [], $targetOld, $dummyNew, null, $obj->class);
                    }
                    $childs = $this->modx->getCollection($rule['class'],[$slTreeSettings['parentIdField']=>$obj->id]);
                    foreach($childs as $child){
                        $this->delete($rule,['ids'=>"{$child->id}"],$action);
                    }
                }
                
                if($obj->remove()){
                    $resp = $this->run_triggers($rule, 'after', 'delete', [], $object_old);
                    if(!$resp['success']) return $resp;
                    $this->writeLog($rule, 'delete', (int)($object_old['id'] ?? 0), $object_old, null);
                }
            }
            return $this->success('delete',['ids'=>$request['ids']]);
        }
        return $this->error('delete_error');
    }

    public function create($rule,$request,$action){
        $slTreeSettings = $this->get_slTreeSettings($rule);
        $set_target_id = false;
        // Класс target'а UniTree-узла, чей триггер надо дёрнуть В КОНЦЕ create (когда узел
        // уже создан → resolveLegacyDeptId найдёт отдел). Ставится только в plain-ветке;
        // modResource-ветка дёргает свой триггер сама (двойного вызова не будет).
        $fireTargetClass = '';
        if($request['form'] == 'UniTree'){
            if(isset($rule['gtsAPIUniTreeClass'][$request['table']])){
                $parent = 0;
                if(!empty($request['parent_id']) and $parentObj = $this->modx->getObject($rule['class'], (int)$request['parent_id'])){
                    $parent = $parentObj->target_id;
                    if(!$slTreeSettings['useUniTree']) $parent = $parentObj->id;
                }
                if($rule['gtsAPIUniTreeClass'][$request['table']]['extended_modresource'] == 1){
                    if(!empty($request['parent_id']) and $parentObj = $this->modx->getObject('modResource', (int)$request['parent_id'])){
                        $parent = $parentObj->id;
                    }
                    $res = [
                        'pagetitle'=>$request['title'],
                        'parent'=>$parent,
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
                    $resp = $this->run_triggers($rule, 'before', $request['api_action'], $request, [],$res,null,$rule['gtsAPIUniTreeClass'][$request['table']]['class']);
                    if(!$resp['success']) return $resp;

                    $modx_response = $this->modx->runProcessor('resource/create', $res);
                    if ($modx_response->isError()) {
                        return $this->error('runProcessor ',$this->modx->error->failure($modx_response->getMessage()));
                    }else{
                        $data = [
                            'target_id'=>$modx_response->response['object']['id'],
                            'title'=>$request['title'],
                            'class'=>$rule['gtsAPIUniTreeClass'][$request['table']]['class'],
                        ];
                        $resp = $this->run_triggers($rule, 'after', $request['api_action'], $request, [],$modx_response->response['object'],null,$rule['gtsAPIUniTreeClass'][$request['table']]['class']);
                        if(!$slTreeSettings['useUniTree']){
                            header('HTTP/1.1 201 Created');
                            return $this->success('created',['object'=>$data]);
                        } 

                        $data[$slTreeSettings['parentIdField']] = $request['parent_id'];
                        if($parentObj){
                            if(empty($parentObj->{$slTreeSettings['parents_idsField']})){
                                $parents_ids = '#';
                            }else{
                                $parents_ids = $parentObj->{$slTreeSettings['parents_idsField']};
                            }
                            $data[$slTreeSettings['parents_idsField']] = $parents_ids.$data[$slTreeSettings['parentIdField']].'#';
                        }else{
                            $data[$slTreeSettings['parents_idsField']] = '';
                        }
                            
                        
                        if($count = $this->modx->getCount($rule['class'], [$slTreeSettings['parentIdField'] => $parent])){
                            $data[$slTreeSettings['menuindexField']] = $count + 1;
                        }
                        $request = $data;
                    }
                }else if(!empty($rule['gtsAPIUniTreeClass'][$request['table']]['class'])){
                    if($obj = $this->modx->newObject($rule['gtsAPIUniTreeClass'][$request['table']]['class'])){
                        $res = [
                            $rule['gtsAPIUniTreeClass'][$request['table']]['title_field']=>$request['title'],
                        ];
                        if(isset($rule['properties']['actions']['create']['tables'][$request['table']]['add_fields'])){
                            foreach($rule['properties']['actions']['create']['tables'][$request['table']]['add_fields'] as $field=>$val){
                                if(isset($request[$field])) $res[$field] = $request[$field];
                            }
                        }
                        // $this->modx->log(1,print_r($res,1));
                        $obj->fromArray($res);
                        if($obj->save()){
                            // target создан напрямую (newObject+save) без run_triggers —
                            // дёрнем его триггер в конце create, когда появится узел дерева.
                            $fireTargetClass = $rule['gtsAPIUniTreeClass'][$request['table']]['class'];
                            $data = [
                                'target_id'=>$obj->id,
                                'title'=>$request['title'],
                                'class'=>$rule['gtsAPIUniTreeClass'][$request['table']]['class'],
                            ];
                            $data[$slTreeSettings['parentIdField']] = $request['parent_id'];
                            if($parentObj){
                                if(empty($parentObj->{$slTreeSettings['parents_idsField']})){
                                    $parents_ids = '#';
                                }else{
                                    $parents_ids = $parentObj->{$slTreeSettings['parents_idsField']};
                                }
                                $data[$slTreeSettings['parents_idsField']] = $parents_ids.$data[$slTreeSettings['parentIdField']].'#';
                            }else{
                                $data[$slTreeSettings['parents_idsField']] = '';
                            }

                            if($count = $this->modx->getCount($rule['class'], [$slTreeSettings['parentIdField'] => $parent])){
                                $data[$slTreeSettings['menuindexField']] = $count + 1;
                            }
                            $request = $data;
                        }
                    }
                    
                }
            }else{
                if($slTreeSettings['useUniTree']){
                    if($gtsAPITable0 = $this->modx->getObject('gtsAPITable',['table'=>$request['table']])){
                        if(empty($gtsAPITable0->class)) $gtsAPITable0->class = $gtsAPITable0->table;
                        $data = [
                            'class'=> $gtsAPITable0->class,
                            'title'=>$request['title'],
                        ];
                        $data[$slTreeSettings['parentIdField']] = $request['parent_id'];
                        $set_target_id = true;
                        $parent = 0;
                        if(!empty($request['parent_id']) and $parentObj = $this->modx->getObject($rule['class'], (int)$request['parent_id'])){
                            $parent = $parentObj->id;
                        }
                        if($parentObj){
                            if(empty($parentObj->{$slTreeSettings['parents_idsField']})){
                                $parents_ids = '#';
                            }else{
                                $parents_ids = $parentObj->{$slTreeSettings['parents_idsField']};
                            }
                            $data[$slTreeSettings['parents_idsField']] = $parents_ids.$data[$slTreeSettings['parentIdField']].'#';
                        }else{
                            $data[$slTreeSettings['parents_idsField']] = '';
                        }
                        if($count = $this->modx->getCount($rule['class'], [$slTreeSettings['parentIdField'] => $parent])){
                            $data[$slTreeSettings['menuindexField']] = $count + 1;
                        }
                        $request = array_merge($request,$data);
                    }
                }
            }
        }
        $data = $this->addDefaultFields($rule,$request);
        $request = $this->request_array_to_json($request);
        if(!$obj = $this->modx->newObject($rule['class'],$data)) return $this->error('Ошибка. Возможно таблица не существует!',$request);
        
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
            // Без whitelist полей запись запрещена (mass-assignment — любой столбец из запроса)
            return $this->error('Запись запрещена: для таблицы "' . $rule['class'] . '" не настроены поля (properties.fields).');
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
            if($set_target_id){
                $obj->target_id = $obj->id;
                $obj->save();
            }

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

            $this->writeLog($rule, 'create', $obj->get('id'), $object_old, $object);
            // Обратная синхра legacy для target'а UniTree (узел уже создан → dept резолвится).
            $this->fireTargetTrigger($rule, $request, 'create', $fireTargetClass, $obj->get('target_id'));

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
                // Без whitelist полей запись запрещена (mass-assignment — любой столбец из запроса)
                return $this->error('Запись запрещена: для таблицы "' . $rule['class'] . '" не настроены поля (properties.fields).');
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

                $this->writeLog($rule, 'update', $obj->get('id'), $object_old, $object);
                // Обратная синхра legacy для target'а UniTree при правке узла через дерево.
                $slTreeSettingsU = $this->get_slTreeSettings($rule);
                if (!empty($slTreeSettingsU['useUniTree'])) {
                    $this->fireTargetTrigger($rule, $request, 'update', $obj->get($slTreeSettingsU['classField']), $obj->get('target_id'));
                }

                $data = $resp['data'];

                return $this->success('update',$data);
            }
        }
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
}

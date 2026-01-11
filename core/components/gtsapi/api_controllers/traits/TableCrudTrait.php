<?php

/**
 * Trait для CRUD операций таблицы
 * Содержит методы создания, чтения, обновления и удаления записей
 */
trait TableCrudTrait
{
    /**
     * Создание новой записи
     */
    public function create($rule, $request, $action)
    {
        $data = $this->addDefaultFields($rule, $request);
        $request = $this->request_array_to_json($request);
        if (!$obj = $this->modx->newObject($rule['class'], $data)) return $this->error('Ошибка. Возможно таблица не существует!', $request);
        
        // class link Редактирование 2 таблиц одновременно
        $set_data[$rule['class']] = [];
        $fields = [];
        if (!empty($rule['properties']['fields'])) {
            $fields = $rule['properties']['fields'];
            $ext_fields = [];
            foreach ($fields as $field => $desc) {
                if (isset($request[$field])) {
                    $field_arr = explode('.', $field);
                    if (count($field_arr) == 1) {
                        if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                            $set_data[$rule['class']][$field] = $request[$field];
                        } else {
                            $set_data[$desc['class']][$field] = $request[$field];
                        }
                    } else if (count($field_arr) == 2) {
                        if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                            $ext_fields[$field_arr[0]] = $rule['class'];
                            $set_data[$rule['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                        } else {
                            $ext_fields[$field_arr[0]] = $desc['class'];
                            $set_data[$desc['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                        }
                    } else if (count($field_arr) == 3) {
                        if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                            $ext_fields[$field_arr[0]] = $rule['class'];
                            $set_data[$rule['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                        } else {
                            $ext_fields[$field_arr[0]] = $desc['class'];
                            $set_data[$desc['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                        }
                    }
                }
            }
            foreach ($ext_fields as $field => $class) {
                $set_data[$class][$field] = json_encode($set_data[$class][$field]);
            }
        } else {
            $set_data[$rule['class']] = $request;
        }
        
        
        $object_old = $obj->toArray();
        if (isset($request['id'])) {
            $object = $obj->fromArray($set_data[$rule['class']], '', true);
        } else {
            $object = $obj->fromArray($set_data[$rule['class']]);
        }
        
        $object_new = $obj->toArray();

        //class link Редактирование 2 таблиц одновременно
        if (!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])) {
            foreach ($rule['properties']['class_link'] as $class => $class_link) {
                foreach ($fields as $field => $desc) {
                    if (isset($desc['class']) and $desc['class'] == $class and isset($set_data[$class][$field])) {
                        $object_new[$field] = $set_data[$class][$field];
                    }
                }
            }
        }

        $resp = $this->run_triggers($rule, 'before', $request['api_action'], $request, $object_old, $object_new, $obj);
        if (!$resp['success']) return $resp;

        if ($obj->save()) {
            $object = $obj->toArray();
            //class link Редактирование 2 таблиц одновременно
            if (isset($request['filters'])) {
                if (is_string($request['filters'])) $request['filters'] = json_decode($request['filters'], 1);
            }
            if (!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])) {
                if (isset($request['filters']['insert_menu_id'])) {
                    $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                    foreach ($rule['properties']['class_link'] as $class => $class_link) {
                        foreach ($class_link as $field => $v) {
                            if ($v == 'insert_menu_id') {
                                $rule['properties']['class_link'][$class][$field] = $insert_menu_id;
                            }
                        }
                    }
                }
                foreach ($rule['properties']['class_link'] as $class => $class_link) {
                    if (!empty($set_data[$class])) {
                        $search = [];
                        foreach ($class_link as $field => $v) {
                            if (isset($object[$v])) {
                                $search[$field] = $object[$v];
                            } else if (is_numeric($v)) {
                                $search[$field] = $v;
                            }
                        }
                    }
                    if (!$link_obj = $this->modx->getObject($class, $search)) {
                        $link_obj = $this->modx->newObject($class, $search);
                    }
                    if ($link_obj) {
                        $link_obj->fromArray($set_data[$class]);
                        $link_obj->save();
                        foreach ($fields as $field => $desc) {
                            if (isset($desc['class']) and $desc['class'] == $class) {
                                $object[$field] = $link_obj->get($field);
                            }
                        }
                    }
                }
            }

            $resp = $this->run_triggers($rule, 'after', $request['api_action'], $request, $object_old, $object, $obj);
            $readRequest = ['ids' => $obj->get('id'), 'setTotal' => false, 'limit' => 1];
            $readRequest['filters'] = $request['filters'];
            $readResp = $this->read($rule, $readRequest, null, [], 'create');
            if ($readResp['success'] && !empty($readResp['data']['rows'])) {
                $resp['data']['object'] = $readResp['data']['rows'][0];
            } else {
                $resp['data']['object'] = [];
            }
            if (!empty($rule['properties']['table_tree'])) {//table_tree
                $where = [
                    $rule['class'] . '.' . $rule['properties']['table_tree']['parentIdField'] => $resp['data']['object'][$rule['properties']['table_tree']['idField']]
                ];
                $resp['data']['object']['gtsapi_children_count'] = $this->modx->getCount($rule['class'], $where);
            }
            if (!$resp['success']) return $resp;
            
            $data = $resp['data'];

            header('HTTP/1.1 201 Created');
            return $this->success('created', $data);
        }
        return $this->error('create_error', $request);
    }

    /**
     * Чтение записей
     */
    public function read($rule, $request, $action, $where = [], $internal_action = '')
    {
        if (isset($rule['properties']['actions']['read']['custom'])) {
            $custom_action = explode('/', $rule['properties']['actions']['read']['custom']);
            if (count($custom_action) == 2 and isset($this->models[strtolower($custom_action[0])])) {
                $service = $this->models[strtolower($custom_action[0])];

                if (method_exists($service, 'handleRequest')) {
                    return $service->handleRequest($custom_action[1], $request);
                }
            }
        }
        $object_new = [];
        $resp = $this->run_triggers($rule, 'before', 'read', $request, [], $object_new, null, $internal_action);
        if (!$resp['success']) return $resp;
        
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
        
        if (!empty($request['query'])) {
            if (empty($rule['properties']['queryes'][$request['query']]))
                return $this->error('not query');
            $default = array_merge($default, $rule['properties']['queryes'][$request['query']]);
        }
        if (!empty($rule['properties']['query'])) {
            if (isset($rule['properties']['query']['parents_option'])) {//parents_option
                $parents = $this->modx->getOption($rule['properties']['query']['parents_option']);
                $rule['properties']['query']['parents'] = $parents;
            }
            if (!empty($rule['properties']['query']['where'])) {
                foreach ($rule['properties']['query']['where'] as $k => $v1) {
                    if ($v1 === 'modx_user_id') {
                        $rule['properties']['query']['where'][$k] = $this->modx->user->id;
                    }
                }
            }
            $default = array_merge($default, $rule['properties']['query']);
        }
        if (!empty($request['filters'])) {
            if (empty($default['where'])) $default['where'] = [];
            if (isset($request['filters']['insert_menu_id'])) {
                $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                unset($request['filters']['insert_menu_id']);
                //Замена в значениях в $default в строках содержащих insert_menu_id на значение $insert_menu_id
                $default = $this->replaceInsertMenuIdInArray($default, $insert_menu_id);

            }
            $default['where'] = array_merge($default['where'], $this->aplyFilters($rule, $request['filters']));
        }
        if (!empty($where)) {
            $default['where'] = array_merge($default['where'], $where);
        }
        $default['decodeJSON'] = 1;
        if (!empty($request['ids'])) {
            $default['where']["{$rule['class']}.id:IN"] = explode(',', $request['ids']);
        }
        if (isset($request['limit'])) {
            $default['limit'] = $request['limit'];
        }
        if (isset($request['offset'])) {
            $default['offset'] = $request['offset'];
        } else {
            $request['offset'] = 0;
        }
        
        if ($request['setTotal']) {
            $default['setTotal'] = true;
        }
        
        // Получаем список полей из select для проверки сортировки
        $selectFields = $this->getSelectFieldsList($default, $rule);
        
        if ($request['sortField']) {
            // Проверяем наличие поля в select
            if (in_array($request['sortField'], $selectFields)) {
                $default['sortby'] = [
                    "{$request['sortField']}" => $request['sortOrder'] == 1 ? 'ASC' : 'DESC',
                ];
            }
        }
        if ($request['multiSortMeta']) {
            $default['sortby'] = [];
            foreach ($request['multiSortMeta'] as $sort) {
                // Проверяем наличие поля в select
                if (in_array($sort['field'], $selectFields)) {
                    $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ? 'ASC' : 'DESC';
                }
            }
        }
        if (isset($rule['properties']['group'])) {
            $default['sortby'] = [];
            foreach ($rule['properties']['group']['fields'] as $field => $v) {
                $default['sortby'][$field] = $v['order'];
            }
        }
        $this->pdo->setConfig($default);
        $rows0 = $this->pdo->run();
        if ($request['setTotal']) {
            $total = (int)$this->modx->getPlaceholder('total');
        }
        
        $row_setting = [];
        if (isset($rule['properties']['row_setting'])) {
            if (isset($rule['properties']['row_setting']['class'])) {
                foreach ($rows0 as $row) {
                    $row_setting[$row['id']]['class'] = $this->pdoTools->getChunk("@INLINE " . $rule['properties']['row_setting']['class'], $row);
                }
            }
        }
        
        if (isset($rule['properties']['fields'])) {
            $get_html = false;
            foreach ($rule['properties']['fields'] as $field => $v) {
                if (isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) $get_html = true;
            }
            if ($get_html) {
                foreach ($rows0 as $k => $row) {
                    foreach ($rule['properties']['fields'] as $field => $v) {
                        if (isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) {
                            $rows0[$k][$field] = $this->pdoTools->getChunk("@INLINE " . $v['tpl'], $row);
                        }
                    }
                }
            }
        }
        
        if (isset($rule['properties']['group']) and count($rows0) > 0) {
            $rows1 = $check_row = [];
            $select_row = $this->setSelectRow($rule, $rows0);
            $select_row_all = $this->setSelectRow($rule, $rows0);
            foreach ($rows0 as $row) {
                if (empty($check_row)) $check_row = $row;
                $check = true;
                $check_count = 0;
                $rows_current = [];
                foreach ($rule['properties']['group']['fields'] as $field => $v) {
                    if ($row[$field] != $check_row[$field]) {
                        $check = false;
                    }
                }
                if (!$check) {
                    $check_row = $row;
                    $add_sum_row = false;
                    foreach ($rows_current as $row_current) {
                        foreach ($rule['properties']['group']['select'] as $field => $v) {
                            if ($v['type_select'] == 'group') {
                                $row_current[$v['alias']] = $select_row[$field];
                            } else {
                                $add_sum_row = true;
                            }
                        }
                        $rows1[] = $row_current;
                    }
                    if ($check_count > 1 and $add_sum_row) {
                        $sum_row = [];
                        foreach ($rule['properties']['group']['select'] as $field => $v) {
                            if ($v['type_select'] != 'group') {
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
                foreach ($rule['properties']['group']['select'] as $field => $v) {
                    switch ($v['type_aggs']) {
                        case 'count':
                            $select_row[$field]++;
                            $select_row_all[$field]++;
                            break;
                        case 'sum':
                            $select_row[$field] += $row[$field];
                            $select_row_all[$field] += $row[$field];
                            break;
                        case 'max':
                            if ($row[$field] > $select_row[$field]) $select_row[$field] = $row[$field];
                            if ($row[$field] > $select_row_all[$field]) $select_row_all[$field] = $row[$field];
                            break;
                        case 'min':
                            if ($row[$field] < $select_row[$field]) $select_row[$field] = $row[$field];
                            if ($row[$field] < $select_row_all[$field]) $select_row_all[$field] = $row[$field];
                            break;
                    }
                }
            }
            if ($add_sum_row) {
                $sum_row = [];
                foreach ($rule['properties']['group']['select'] as $field => $v) {
                    if ($v['type_select'] != 'group') {
                        $sum_row[$field] = $select_row[$field];
                    }
                }
                array_unshift($rows1, $sum_row);
            }
            $rows0 = $rows1;
        }
        if (isset($rule['properties']['reset_id']) and $rule['properties']['reset_id'] == 1 and count($rows0) > 0) {
            foreach ($rows0 as $k => $row) {
                $rows0[$k]['id'] = $request['offset'] + $k + 1;
            }
        }
        $filter_list = [];
        
        // Составляем список уникальных значений для каждого поля
        if (!empty($rule['properties']['fields']) && !empty($rows0)) {
            foreach ($rule['properties']['fields'] as $fieldName => $fieldConfig) {
                // Пропускаем поля, которые не нужно фильтровать
                if (isset($fieldConfig['modal_only']) && $fieldConfig['modal_only']) continue;
                if (isset($fieldConfig['no_filter']) && $fieldConfig['no_filter']) continue;
                
                // Собираем уникальные значения для поля
                $uniqueValues = [];
                foreach ($rows0 as $row) {
                    if (isset($row[$fieldName])) {
                        $value = $row[$fieldName];
                        // Преобразуем null и пустые строки в ''
                        if ($value === null || $value === '') {
                            $value = '';
                        }
                        // Используем значение как ключ для автоматической уникальности
                        $uniqueValues[$value] = $value;
                    }
                }
                
                // Если есть уникальные значения, добавляем их в filter_list
                if (!empty($uniqueValues)) {
                    // Сортируем значения
                    ksort($uniqueValues);
                    $filter_list[$fieldName] = array_values($uniqueValues);
                }
            }
        }
        
        $out = [
            'rows' => $rows0,
            'total' => $total,
            'row_setting' => $row_setting,
            'log' => $this->pdo->getTime(),
            'filter_list' => $filter_list
        ];
        if ($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();
        $out['autocomplete'] = $this->autocompletes($rule['properties']['fields'], $rows0, $request['offset']);
        
        if (!empty($rule['properties']['slTree'])) {
            $out['slTree'] = $this->getslTree($rule['properties']['slTree'], $rows0, $parents);
        }
        $resp = $this->run_triggers($rule, 'after', 'read', $request, $out, $object_new, null, $internal_action);
        
        if (!$resp['success']) return $resp;
        
        if (!empty($resp['data']['out'])) $out = $resp['data']['out'];
        if (!empty($resp['data']['timings'])) $out['timings'] = $resp['data']['timings'];
        
        if (!empty($rule['properties']['table_tree'])) {//table_tree
            foreach ($out['rows'] as $k => $row) {
                $where = [
                    $rule['class'] . '.' . $rule['properties']['table_tree']['parentIdField'] => $row[$rule['properties']['table_tree']['idField']]
                ];
                $out['rows'][$k]['gtsapi_children_count'] = $this->modx->getCount($rule['class'], $where);
            }
        }
        return $this->success('', $out);
    }

    /**
     * Обновление записи
     */
    public function update($rule, $request, $action)
    {
        if ($obj = $this->modx->getObject($rule['class'], (int)$request['id'])) {
            $object_old = $obj->toArray();
            $data = [];
            $request = $this->request_array_to_json($request);
            $request = array_merge($request, $data);
            
            //class link Редактирование 2 таблиц одновременно
            $set_data[$rule['class']] = [];
            $fields = [];
            if (!empty($rule['properties']['fields'])) {
                $fields = $rule['properties']['fields'];
                $ext_fields = [];
                foreach ($fields as $field => $desc) {
                    if (isset($request[$field])) {
                        $field_arr = explode('.', $field);
                        $desc['field'] = $desc['field'] ? $desc['field'] : $field;
                        if (count($field_arr) == 1) {
                            if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                                $set_data[$rule['class']][$desc['field']] = $request[$field];
                            } else {
                                $set_data[$desc['class']][$desc['field']] = $request[$field];
                            }
                        } else if (count($field_arr) == 2) {
                            if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                                $ext_fields[$field_arr[0]] = $rule['class'];
                                $set_data[$rule['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                            } else {
                                $ext_fields[$field_arr[0]] = $desc['class'];
                                $set_data[$desc['class']][$field_arr[0]][$field_arr[1]] = $request[$field];
                            }
                        } else if (count($field_arr) == 3) {
                            if (empty($desc['class']) or $desc['class'] == $rule['class']) {
                                $ext_fields[$field_arr[0]] = $rule['class'];
                                $set_data[$rule['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                            } else {
                                $ext_fields[$field_arr[0]] = $desc['class'];
                                $set_data[$desc['class']][$field_arr[0]][$field_arr[1]][$field_arr[2]] = $request[$field];
                            }
                        }
                    }
                }
                
                
                foreach ($ext_fields as $field => $class) {
                    if ($class == $rule['class']) {
                        if (is_array($object_old[$field])) {
                            $arr = $object_old[$field];
                        } else if (is_string($object_old[$field])) {
                            $arr = json_decode($object_old[$field]);
                        }
                        if (is_array($arr)) {
                            $set_data[$class][$field] = array_merge($arr, $set_data[$class][$field]);
                        }
                        $set_data[$class][$field] = json_encode($set_data[$class][$field]);
                    }
                }
            } else {
                $set_data[$rule['class']] = $request;
            }
            
            $object = $obj->fromArray($set_data[$rule['class']]);
            $object_new = $obj->toArray();
            if (isset($request['filters'])) {
                if (is_string($request['filters'])) $request['filters'] = json_decode($request['filters'], 1);
            }
            if (!empty($rule['properties']['class_link'])) {
                if (isset($request['filters']['insert_menu_id'])) {
                    $insert_menu_id = (int)$request['filters']['insert_menu_id']['constraints'][0]['value'];
                    foreach ($rule['properties']['class_link'] as $class => $class_link) {
                        foreach ($class_link as $field => $v) {
                            if ($v == 'insert_menu_id') {
                                $rule['properties']['class_link'][$class][$field] = $insert_menu_id;
                            }
                        }
                    }
                }
            }
            //class link Редактирование 2 таблиц одновременно
            if (!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])) {
                foreach ($rule['properties']['class_link'] as $class => $class_link) {
                    foreach ($fields as $field => $desc) {
                        if (isset($desc['class']) and $desc['class'] == $class and isset($set_data[$class][$field])) {
                            $object_new[$field] = $set_data[$class][$field];
                        }
                    }
                }
            }

            $resp = $this->run_triggers($rule, 'before', 'update', $request, $object_old, $object_new, $obj);
            
            if (!$resp['success']) return $resp;
            
            if ($obj->save()) {
                $object = $obj->toArray();
                
                //class link Редактирование 2 таблиц одновременно
                if (!empty($rule['properties']['fields']) and !empty($rule['properties']['class_link'])) {
                    foreach ($rule['properties']['class_link'] as $class => $class_link) {
                        if (!empty($set_data[$class])) {
                            $search = [];
                            foreach ($class_link as $field => $v) {
                                if ($field == 'fenom_tpl') {
                                    $chunk = $this->pdoTools->getChunk("@INLINE $v", $object);
                                    $arr = json_decode($chunk, 1);
                                    if (is_array($arr)) {
                                        $search = array_merge($search, $arr);
                                    }
                                    
                                } else if (isset($object[$v])) {
                                    $search[$field] = $object[$v];
                                } else if (is_numeric($v)) {
                                    $search[$field] = $v;
                                }
                            }
                            if (empty($search)) continue;
                            if (!$link_obj = $this->modx->getObject($class, $search)) {
                                $link_obj = $this->modx->newObject($class, $search);
                            }
                            if ($link_obj) {
                                
                                $link_obj->fromArray($set_data[$class]);
                                $link_obj->save();
                                foreach ($fields as $field => $desc) {
                                    if (isset($desc['class']) and $desc['class'] == $class) {
                                        $object[$field] = $link_obj->get($field);
                                    }
                                }
                            }
                        }
                    }
                }

                $resp = $this->run_triggers($rule, 'after', 'update', $request, $object_old, $object, $obj);

                $readRequest = ['ids' => $obj->get('id'), 'setTotal' => false, 'limit' => 1];
                if (isset($request['filters'])) {
                    $readRequest['filters'] = $request['filters'];
                }
                $readResp = $this->read($rule, $readRequest, null, [], 'update');
                
                if ($readResp['success'] && !empty($readResp['data']['rows'])) {
                    $resp['data']['object'] = $readResp['data']['rows'][0];
                } else {
                    $resp['data']['object'] = [];
                }
                if (!empty($rule['properties']['table_tree'])) {//table_tree
                    $where = [
                        $rule['class'] . '.' . $rule['properties']['table_tree']['parentIdField'] => $resp['data']['object'][$rule['properties']['table_tree']['idField']]
                    ];
                    $resp['data']['object']['gtsapi_children_count'] = $this->modx->getCount($rule['class'], $where);
                }
                if (!$resp['success']) return $resp;
                $data = $resp['data'];
                //uniTree
                $uniTreeTable = $this->setUniTreeTitle($rule, $obj);
                if (!empty($uniTreeTable)) $data['uniTreeTable'] = $uniTreeTable;
                return $this->success('update', $data);
            }
        }
        return $this->error('update_error', ['action' => $action, 'rule' => $rule, 'request' => $request]);
    }

    /**
     * Удаление записей
     */
    public function delete($rule, $request, $action)
    {
        if (!empty($request['ids']) || (!empty($rule['properties']['data_fields']) && !empty($request['data_fields_values']))) {
            $where = [];
            
            // Если есть data_fields и data_fields_values, формируем where на основе полей
            if (!empty($rule['properties']['data_fields']) && !empty($request['data_fields_values'])) {
                $dataFields = $rule['properties']['data_fields'];
                $dataFieldsValues = $request['data_fields_values'];
                
                // Формируем OR условие для каждой строки, но внутри каждой строки AND условие
                $orConditions = [];
                foreach ($dataFieldsValues as $rowData) {
                    $andCondition = [];
                    foreach ($dataFields as $field) {
                        if (isset($rowData[$field])) {
                            $andCondition[$field] = $rowData[$field];
                        }
                    }
                    if (!empty($andCondition)) {
                        $orConditions[] = $andCondition;
                    }
                }
                
                if (!empty($orConditions)) {
                    if (count($orConditions) == 1) {
                        // Если только одна строка, используем простое AND условие
                        $where = $orConditions[0];
                    } else {
                        // Если несколько строк, используем OR между группами AND условий
                        $where = [];
                        foreach ($orConditions as $index => $condition) {
                            if ($index == 0) {
                                $where = array_merge($where, $condition);
                            } else {
                                foreach ($condition as $field => $value) {
                                    $where['OR:' . $field . ':' . $index] = $value;
                                }
                            }
                        }
                    }
                }
            } else {
                // Используем стандартную логику с ids
                if (is_string($request['ids'])) $request['ids'] = explode(',', $request['ids']);
                $where = ['id:IN' => $request['ids']];
            }
            
            if (!empty($where)) {
                $objs = $this->modx->getIterator($rule['class'], $where);
                
                foreach ($objs as $obj) {
                    $object_old = $obj->toArray();
                    $resp = $this->run_triggers($rule, 'before', 'remove', [], $object_old);
                    if (!$resp['success']) return $resp;

                    if ($obj->remove()) {
                        $resp = $this->run_triggers($rule, 'after', 'remove', [], $object_old);
                        if (!$resp['success']) return $resp;
                    }
                }
                return $this->success('delete', ['ids' => $request['ids']]);
            }
        }
        return $this->error('delete_error');
    }
}
<?php

/**
 * Trait для работы с полями таблицы
 * Содержит методы управления полями, генерации полей и получения опций
 */
trait TableFieldsTrait
{
    /**
     * Добавление динамических полей к таблице
     */
    public function addFields($rule, $fields, $action)
    {
        $gtsAPIFieldTableCount = $this->modx->getCount('gtsAPIFieldTable', ['name_table' => $rule['table'], 'add_table' => 1]);
        if ($gtsAPIFieldTableCount == 0) return $fields;

        if (is_dir($this->modx->getOption('core_path') . 'components/gtsshop/model/')) {
            $this->modx->addPackage('gtsshop', $this->modx->getOption('core_path') . 'components/gtsshop/model/');
        }

        $gtsAPIFieldTables = $this->modx->getIterator('gtsAPIFieldTable', ['name_table' => $rule['table'], 'add_table' => 1]);
        $addFields = [];
        foreach ($gtsAPIFieldTables as $gtsAPIFieldTable) {
            $gtsAPIFieldGroupTableLinks = $this->modx->getIterator('gtsAPIFieldGroupTableLink', ['table_field_id' => $gtsAPIFieldTable->id]);
            foreach ($gtsAPIFieldGroupTableLinks as $gtsAPIFieldGroupTableLink) {
                $gtsAPIFieldGroups = $this->modx->getIterator('gtsAPIFieldGroup', ['id' => $gtsAPIFieldGroupTableLink->group_field_id]);
                foreach ($gtsAPIFieldGroups as $gtsAPIFieldGroup) {
                    if ($gtsAPIFieldGroup->all) {
                        $c = $this->modx->newQuery($gtsAPIFieldGroup->from_table);
                        $c->sortby('rank', 'ASC');
                        $gtsAPIFields = $this->modx->getIterator($gtsAPIFieldGroup->from_table, $c);
                        foreach ($gtsAPIFields as $gtsAPIField) {
                            $addFields[$gtsAPIField->name] = $gtsAPIField->toArray();
                            if ($gtsAPIFieldTable->only_text) {
                                $addFields[$gtsAPIField->name]['field_type'] = 'text';
                                unset($addFields[$gtsAPIField->name]['list_select']);
                            }
                            $addFields[$gtsAPIField->name]['from_table'] = $gtsAPIFieldGroup->from_table;
                            if (empty($addFields[$gtsAPIField->name]['after_field'])) $addFields[$gtsAPIField->name]['after_field'] = $gtsAPIFieldTable->after_field;
                            $addFields[$gtsAPIField->name]['gtsapi_config'] = json_decode($addFields[$gtsAPIField->name]['gtsapi_config'], 1);
                        }
                    } else {
                        $this->pdo->setConfig([
                            'class' => $gtsAPIFieldGroup->link_group_table,
                            'leftJoin' => [
                                $gtsAPIFieldGroup->from_table => [
                                    'class' => $gtsAPIFieldGroup->from_table,
                                    'on' => $gtsAPIFieldGroup->from_table . '.id = ' . $gtsAPIFieldGroup->link_group_table . '.field_id'
                                ]
                            ],
                            'where' => [
                                $gtsAPIFieldGroup->link_group_table . '.group_field_id' => $gtsAPIFieldGroup->id
                            ],
                            'sortby' => [
                                $gtsAPIFieldGroup->from_table . '.rank' => 'ASC'
                            ],
                            'select' => [
                                $gtsAPIFieldGroup->from_table => '*'
                            ],
                            'return' => 'data',
                            'limit' => 0
                        ]);
                        $rows = $this->pdo->run();
                        
                        foreach ($rows as $row) {
                            $addFields[$row['name']] = $row;
                            if ($gtsAPIFieldTable->only_text) {
                                $addFields[$row['name']]['field_type'] = 'text';
                                unset($addFields[$row['name']]['list_select']);
                            }
                            $addFields[$row['name']]['from_table'] = $gtsAPIFieldGroup->from_table;
                            if (empty($row['after_field'])) $addFields[$row['name']]['after_field'] = $gtsAPIFieldTable->after_field;
                        }
                    }
                }
            }
        }
        if (empty($addFields)) return $fields;
        $keys = [];
        
        foreach ($addFields as $k => $addField) {
            $field = [
                'label' => $addField['title'] ? $addField['title'] : $k,
                'type' => $addField['field_type'] ? $addField['field_type'] : 'text',
            ];
            if (!empty($addField['default'])) $field['default'] = $addField['default'];
            if (!empty($addField['modal_only'])) $field['modal_only'] = $addField['modal_only'];
            if (!empty($addField['table_only'])) $field['table_only'] = $addField['table_only'];
            if (!empty($addField['gtsapi_config'])) $field = array_merge($field, $addField['gtsapi_config']);
            if ($field['type'] == 'decimal' and !isset($field['FractionDigits'])) $field['FractionDigits'] = 2;
            if (!empty($addField['list_select'])) {
                $field['type'] = 'select';
                
                $this->pdo->setConfig([
                    'class' => 'gsParamListSelect',
                    'where' => [
                        'gsParamListSelect.param_id' => $addField['id']
                    ],
                    'sortby' => [
                        'gsParamListSelect.id' => 'ASC'
                    ],
                    'return' => 'data',
                    'limit' => 0
                ]);
                $rows = $this->pdo->run();
                $select_data = [];
                $select_data[] = ['id' => '', 'content' => ''];
                foreach ($rows as $row) {
                    $select_data[] = ['id' => $row['name'], 'content' => $row['name']];
                }
                $field['select_data'] = $select_data;
            }

            if (empty($keys[$addField['after_field']])) {
                $keys[$addField['after_field']] = $addField['after_field'];
            }
            $fields = $this->insertToArray($fields, [$k => $field], $keys[$addField['after_field']]);
            $keys[$addField['after_field']] = $addField['name'];
        }
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;

            if (isset($triggers[$class]['gtsapi_addfields']) and isset($triggers[$class]['model'])) {
                $service = $this->models[$triggers[$class]['model']];
                if (method_exists($service, $triggers[$class]['gtsapi_addfields'])) {
                    $params = [
                        'rule' => $rule,
                        'class' => $class,
                        'method' => $action,
                        'fields' => &$fields,
                        'trigger' => 'gtsapi_addfields',
                    ];
                    $service->{$triggers[$class]['gtsapi_addfields']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1, 'gtsAPI Ошибка триггера ' . $e->getMessage());
        }
        return $fields;
    }

    /**
     * Получение опций таблицы
     */
    public function options($rule, $request, $action)
    {
        $row_class_trigger = [];
        $table_tree = false;
        if (!empty($rule['properties']['row_class_trigger'])) {
            $row_class_trigger = $rule['properties']['row_class_trigger'];
        }
        if (!empty($rule['properties']['table_tree'])) {
            $table_tree = $rule['properties']['table_tree'];
        }
        if (empty($rule['properties']['fields'])) {
            if ($rule['type'] == 1) $fields = $this->gen_fields($rule);
        } else {
            $fields = $rule['properties']['fields'];
        }
        $fields = $this->addFields($rule, $fields, 'options');
        foreach ($fields as $k => $field) {
            if (empty($field['type'])) {
                if ($k == 'id') {
                    $fields[$k]['type'] = 'view';
                } else {
                    $fields[$k]['type'] = 'text';
                }
            }
        }
        $actions = [];
        if (isset($rule['properties']['actions'])) {
            foreach ($rule['properties']['actions'] as $action => $v) {
                $resp = $this->checkPermissions($rule['properties']['actions'][$action]);

                if ($resp['success']) {
                    if (!$v['hide']) $actions[$action] = $v;
                }
            }
        }
        foreach ($fields as $k => $v) {
            if (isset($v['default'])) {
                if ($v['default'] == 'user_id') $fields[$k]['default'] = $this->modx->user->id;
                if ($v['type'] == 'date') $fields[$k]['default'] = date('Y-m-d', strtotime($v['default']));
            }
            if (isset($v['readonly']) and is_array($v['readonly'])) {
                if (isset($v['readonly']['authenticated']) and $v['readonly']['authenticated'] == 1) {
                    if ($this->modx->user->id > 0) $fields[$k]['readonly'] = 0;
                }
        
                if (isset($v['readonly']['groups']) and !empty($v['readonly']['groups'])) {
                    $groups = array_map('trim', explode(',', $v['readonly']['groups']));
                    if ($this->modx->user->isMember($groups)) $fields[$k]['readonly'] = 0;
                }
                if (isset($v['readonly']['permitions']) and !empty($v['readonly']['permitions'])) {
                    $permitions = array_map('trim', explode(',', $v['readonly']['permitions']));
                    foreach ($permitions as $pm) {
                        if ($this->modx->hasPermission($pm)) $fields[$k]['readonly'] = 0;
                    }
                }
                if (is_array($fields[$k]['readonly'])) $fields[$k]['readonly'] = 1;
            }
            if (isset($v['disabled']) and is_array($v['disabled'])) {
                if (isset($v['disabled']['authenticated']) and $v['disabled']['authenticated'] == 1) {
                    if ($this->modx->user->id > 0) unset($fields[$k]['disabled']);
                }
        
                if (isset($v['disabled']['groups']) and !empty($v['disabled']['groups'])) {
                    $groups = array_map('trim', explode(',', $v['disabled']['groups']));
                    if ($this->modx->user->isMember($groups)) unset($fields[$k]['disabled']);
                }
                if (isset($v['disabled']['permitions']) and !empty($v['disabled']['permitions'])) {
                    $permitions = array_map('trim', explode(',', $v['disabled']['permitions']));
                    foreach ($permitions as $pm) {
                        if ($this->modx->hasPermission($pm)) unset($fields[$k]['disabled']);
                    }
                }
            }
            if (isset($fields[$k]['disabled'])) unset($fields[$k]);
        }
        $selects = $this->getSelects($fields);

        $filters = [];
        if (!empty($rule['properties']['filters'])) {
            foreach ($rule['properties']['filters'] as $field => $filter) {
                if ($filter['type'] == 'autocomplete') {
                    if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $filter['table'], 'active' => 1])) {
                        $properties = json_decode($gtsAPITable->properties, 1);
                        if (is_array($properties) and isset($properties['autocomplete'])) {
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if (isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $filter['table'];
                            $autocomplete['class'] = $gtsAPITable->class ? $gtsAPITable->class : $filter['table'];
                            $tmp = $this->autocomplete($autocomplete, []);
                            $filter['rows'] = $tmp['rows'];
                    
                            if (isset($filter['default_row']) and is_array($filter['default_row'])) {
                                if ($obj = $this->modx->getObject($autocomplete['class'], $filter['default_row'])) {
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
        if (isset($rule['properties']['limit'])) $limit = $rule['properties']['limit'];

        $options = [
            'fields' => $fields,
            'actions' => $actions,
            'selects' => $selects,
            'filters' => $filters,
            'row_class_trigger' => $row_class_trigger,
            'table_tree' => $table_tree,
            'limit' => $limit,
            'fields_style' => json_decode($rule['fields_style'], 1),
        ];
        if (isset($rule['properties']['rowGroupMode'])) {
            $options['rowGroupMode'] = $rule['properties']['rowGroupMode'];
            $options['groupRowsBy'] = $rule['properties']['groupRowsBy'];
        }
        if (isset($rule['properties']['data_fields'])) {
            $options['data_fields'] = $rule['properties']['data_fields'];
        }
        if (isset($rule['properties']['hide_id'])) {
            $options['hide_id'] = $rule['properties']['hide_id'];
        }
        if (isset($rule['properties']['form'])) {
            $options['form'] = $rule['properties']['form'];
        }
        
        return $this->success('options', $options);
    }

    /**
     * Получение списков выбора для полей
     */
    public function getSelects($fields)
    {
        $selects = [];
        foreach ($fields as $field => $v) {
            if ($v['type'] == 'select') {
                if ($gtsAPISelect = $this->modx->getObject('gtsAPISelect', ['field' => $field])) {
                    $rows0 = json_decode($gtsAPISelect->rows, 1);
                    $rows = [];
                    if (!is_array($rows0)) {
                        $rows0 = array_map('trim', explode(',', $gtsAPISelect->rows));
                    }
                    foreach ($rows0 as $row) {
                        if (count($row) == 2) {
                            $rows[] = $row;
                        } else {
                            $rows[] = [$row, $row];
                        }
                    }
                    $rowsEnd = [];
                    foreach ($rows as $row) {
                        $rowsEnd[] = [
                            'id' => $row[0],
                            'content' => $row[1],
                        ];
                    }
                    $selects[$field]['rows'] = $rowsEnd;
                }
            }
        }
        return $selects;
    }

    /**
     * Генерация полей для класса
     */
    public function gen_fields_class($class, $select = '*')
    {
        $fields0 = [];
        $fields = [];
        $selects = [];
        
        if ($select == '*') {
            if ($className = $this->modx->loadClass($class)) {
                if (isset($this->modx->map[$class])) {
                    foreach ($this->modx->map[$class]['fieldMeta'] as $field => $meta) {
                        $selects[$field] = 1;
                    }
                }
            }
        } else {
            $select = preg_replace_callback('/\(.*?\bAS\b/i', function ($matches) {
                return str_replace(",", "|", $matches[0]);
            }, $select);
            
            $selects0 = explode(',', $select);
            foreach ($selects0 as $k => $select) {
                $select = str_replace('|', ',', $select);
                if (strpos($select, '(') !== false) {
                    $tmp = array_map('trim', preg_split('/\bAS\b/i', $select));
                    if (isset($tmp[1])) {
                        $selects[$tmp[1]] = 2;
                    }
                } else {
                    $tmp = array_map('trim', preg_split('/\bAS\b/i', $select));
                    if (isset($tmp[1])) {
                        $selects[$tmp[1]] = 2;
                    } else {
                        $select = str_replace(['`', $class . '.', '.'], '', $select);
                        if ($select != 'id') $selects[$select] = 1;
                    }
                }
            }
        }
        if ($className = $this->modx->loadClass($class)) {
            if (isset($this->modx->map[$class])) {
                foreach ($this->modx->map[$class]['fieldMeta'] as $field => $meta) {
                    if (!isset($selects[$field]) or $selects[$field] == 2) continue;
                    switch ($meta['dbtype']) {
                        case 'varchar':
                            $fields[$field] = ['type' => 'text'];
                            break;
                        case 'text':
                        case 'longtext':
                            $fields[$field] = ['type' => 'textarea'];
                            break;
                        case 'int':
                            $fields[$field] = ['type' => 'number'];
                            break;
                        case 'double':
                        case 'decimal':
                            $fields[$field] = ['type' => 'decimal', 'FractionDigits' => 2];
                            break;
                        case 'tinyint':
                            if ($meta['phptype'] == 'boolean') {
                                $fields[$field] = ['type' => 'boolean'];
                            } else {
                                $fields[$field] = ['type' => 'number'];
                            }
                            break;
                        case 'date':
                            $fields[$field] = ['type' => 'date'];
                            break;
                        case 'datetime':
                            $fields[$field] = ['type' => 'datetime'];
                            break;
                    }
                }
            }
        }
        foreach ($selects as $field => $select) {
            if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['autocomplete_field' => $field])) {
                $fields0[$field] = [
                    'type' => 'autocomplete',
                    'table' => $gtsAPITable->table
                ];
                if (isset($fields[$field])) {
                    $fields0[$field]['class'] = $class;
                } else {
                    $fields0[$field] = ['type' => 'text', 'readonly' => 1];
                }
            } else if ($gtsAPISelect = $this->modx->getObject('gtsAPISelect', ['field' => $field])) {
                $fields0[$field] = [
                    'type' => 'select',
                ];
                if (isset($fields[$field])) {
                    $fields0[$field]['class'] = $class;
                } else {
                    $fields0[$field] = ['type' => 'text', 'readonly' => 1];
                }
            } else if (isset($fields[$field])) {
                $fields[$field]['class'] = $class;
                $fields0[$field] = $fields[$field];
            } else {
                $fields0[$field] = ['type' => 'text', 'readonly' => 1];
            }

        }
        return $fields0;
    }

    /**
     * Генерация полей для правила
     */
    public function gen_fields($rule)
    {
        $fields = ['id' => ['type' => 'view', 'class' => $rule['class']]];
        if (empty($rule['properties']['query']) or empty($rule['properties']['query']['select'])) {
            $fields = array_merge($fields, $this->gen_fields_class($rule['class']));
        } else {
            if (is_array($rule['properties']['query']['select'])) {
                foreach ($rule['properties']['query']['select'] as $class => $select) {
                    $fields = array_merge($fields, $this->gen_fields_class($class, $select));
                }
            }
        }
        if ($gtsAPITable = $this->modx->getObject('gtsAPITable', $rule['id'])) {
            $rule['properties']['fields'] = $fields;
            $gtsAPITable->properties = json_encode($rule['properties'], JSON_PRETTY_PRINT);
            $gtsAPITable->save();
        }

        return $fields;
    }

    /**
     * Сохранение стилей полей
     */
    public function save_fields_style($rule, $request)
    {
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
     * Сброс стилей полей
     */
    public function reset_fields_style($rule, $request)
    {
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
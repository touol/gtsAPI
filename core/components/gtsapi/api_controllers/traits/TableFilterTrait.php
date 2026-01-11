<?php

/**
 * Trait для работы с фильтрацией данных
 * Содержит методы применения фильтров к запросам
 */
trait TableFilterTrait
{
    /**
     * Применение одного фильтра
     */
    public function aplyFilter($rule, $name, $filter)
    {
        $where = [];
        
        // Если $filter не массив или это простое значение без структуры, преобразуем его
        if (!is_array($filter)) {
            $filter = ['value' => $filter, 'matchMode' => 'equals'];
        } elseif (!isset($filter['value']) && !isset($filter['matchMode'])) {
            // Если это массив, но без ключей value/matchMode, считаем его простым значением
            $filter = ['value' => $filter, 'matchMode' => 'equals'];
        }
        
        if ($filter['value'] == null) return $where;
        
        // Проверяем, есть ли поле в select запроса
        $fieldExistsInSelect = false;
        $selectFields = [];
        
        if (isset($rule['properties']['query']['select']) && !empty($rule['properties']['query']['select'])) {
            // Есть настроенный select
            foreach ($rule['properties']['query']['select'] as $class => $fields) {
                if ($fields == '*') {
                    // Получаем все поля класса через MODX
                    if ($this->modx->loadClass($class) && isset($this->modx->map[$class])) {
                        foreach ($this->modx->map[$class]['fieldMeta'] as $fieldName => $meta) {
                            $selectFields[] = $fieldName;
                        }
                    }
                } else {
                    // Парсим строку select
                    $fieldsArray = array_map('trim', explode(',', $fields));
                    foreach ($fieldsArray as $fieldStr) {
                        // Убираем обратные кавычки
                        $fieldStr = str_replace('`', '', $fieldStr);
                        // Убираем класс с точкой (с кавычками и без)
                        $fieldStr = preg_replace('/^' . preg_quote($class, '/') . '\./', '', $fieldStr);
                        // Проверяем на AS
                        if (stripos($fieldStr, ' AS ') !== false) {
                            $parts = preg_split('/\s+AS\s+/i', $fieldStr);
                            if (isset($parts[1])) {
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
            
            if (isset($systemClassFields[$rule['class']])) {
                $selectFields = $systemClassFields[$rule['class']];
            } elseif ($this->modx->loadClass($rule['class']) && isset($this->modx->map[$rule['class']])) {
                foreach ($this->modx->map[$rule['class']]['fieldMeta'] as $fieldName => $meta) {
                    $selectFields[] = $fieldName;
                }
            }
            
        }
        $selectFields[] = 'id';
        // Проверяем наличие поля в списке
        if (in_array($name, $selectFields)) {
            $fieldExistsInSelect = true;
        }
        
        // Если поля нет в select, пропускаем фильтр
        if (!$fieldExistsInSelect) {
            return $where;
        }
        
        if (isset($rule['properties']['filters'][$name]) and is_array($rule['properties']['filters'][$name])) {
            if (is_array($filter)) {
                $filter = array_merge($rule['properties']['filters'][$name], $filter);
            }
        }
        if (isset($rule['properties']['fields'][$name]) and is_array($rule['properties']['fields'][$name])) {
            if (is_array($filter)) {
                $filter = array_merge($rule['properties']['fields'][$name], $filter);
            }
        }
        $field = "{$rule['class']}.$name";
        if (isset($filter['class'])) $field = "{$filter['class']}.$name";
        if (isset($filter['as']) and isset($filter['class'])) $field = "{$filter['class']}.{$filter['as']}";
        if (isset($filter['field']) and isset($filter['class'])) $field = "{$filter['class']}.{$filter['field']}";
        

        if (strpos($name, '.') !== false) $field = $name;

        if ($filter['value'] == 'true') {
            $filter['value'] = 1;
        } else if ($filter['value'] == 'false') {
            $filter['value'] = 0;
        }
        switch ($filter['matchMode']) {
            case "startsWith":
                $where[$field . ':LIKE'] = "{$filter['value']}%";
                break;
            case "contains":
                $where[$field . ':LIKE'] = "%{$filter['value']}%";
                break;
            case "notContains":
                $where[$field . ':NOT LIKE'] = "%{$filter['value']}%";
                break;
            case "endsWith":
                $where[$field . ':LIKE'] = "%{$filter['value']}";
                break;
            case "equals":
                if ($name == 'parents_ids') {
                    $where["{$rule['class']}.parents_ids:LIKE"] = '%#' . $filter['value'] . '#%';
                } else if (isset($filter['where'])) {
                    $where[100] = "{$filter['where']} = '{$filter['value']}'";
                } else {
                    $where[$field] = $filter['value'];
                }
                break;
            case "in":
                $where[$field . ':IN'] = $filter['value'];
                break;
            case "notEquals":
                $where[$field . ':!='] = $filter['value'];
                break;
            case "lt":
                $where[$field . ':<'] = $filter['value'];
                break;
            case "lte":
                $where[$field . ':<='] = $filter['value'];
                break;
            case "gt":
                $where[$field . ':>'] = $filter['value'];
                break;
            case "gte":
                $where[$field . ':>='] = $filter['value'];
                break;
            case "dateIs":
                $where[$field] = date('Y-m-d', strtotime($filter['value']));
                break;
            case "dateBefore":
                $where[$field . ':<='] = date('Y-m-d', strtotime($filter['value']));
                break;
            case "dateAfter":
                $where[$field . ':>='] = date('Y-m-d', strtotime($filter['value']));
                break;
        }
        return $where;
    }

    /**
     * Применение множественных фильтров
     */
    public function aplyFilters($rule, $filters)
    {
        $where = [];
        
        foreach ($filters as $name => $filter) {
            
            if (isset($filter['constraints'])) {
                if ($filter['operator'] == 'and') {
                    foreach ($filter['constraints'] as $filter2) {
                        $where2 = $this->aplyFilter($rule, $name, $filter2);
                        $where = array_merge($where, $where2);
                    }
                } else if ($filter['operator'] == 'or') {
                    $where2 = [];
                    $where4 = [];
                    foreach ($filter['constraints'] as $filter2) {
                        $where3 = $this->aplyFilter($rule, $name, $filter2);
                        $where2 = array_merge($where2, $where3);
                    }
                    foreach ($where2 as $field => $value) {
                        if (empty($where4)) {
                            $where4[$field] = $value;
                        } else {
                            $where4['OR:' . $field] = $value;
                        }
                    }
                    $where[] = $where4;
                }
            } else {
                $where2 = $this->aplyFilter($rule, $name, $filter);
                $where = array_merge($where, $where2);
            }
        }
        return $where;
    }

    /**
     * Добавление полей по умолчанию
     */
    public function addDefaultFields($rule, $request)
    {
        $where = [];
        $data = [];
        $filters = [];
        $tabs_where = [];
        $data_filters = [];
        if (!empty($request['filters'])) {
            $filters = $this->aplyFilters($rule, $request['filters']);
        }
        if (!empty($rule['actions']['subtabs'])) {
            foreach ($rule['actions']['subtabs'] as $t => $tabs) {
                foreach ($tabs as $tab) {
                    if ($tab['name'] == $rule['table']) {
                        if (isset($tab['where'])) {
                            foreach ($tab['where'] as $k => $v) {
                                $k = str_replace('`', '', $k);
                                $arr = explode('.', $k);
                                if (count($arr) == 1) {
                                    $field = $arr[0];
                                } else {
                                    $field = $arr[1];
                                }
                                $tabs_where[$field] = $v;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($rule['properties']['table_tree'])) {//table_tree
            $tabs_where[$rule['properties']['table_tree']['parentIdField']] = 1;
        }
        if (!empty($filters)) {
            foreach ($filters as $k => $v) {
                $k = str_replace('`', '', $k);
                $arr = explode('.', $k);
                if (count($arr) == 1) {
                    $field = $arr[0];
                } else {
                    $field = $arr[1];
                }
                $data_filters[$field] = $v;
                if (isset($request[$field])) {
                    if (isset($tabs_where[$field])) {
                        if ((int)$tabs_where[$field] == 0) $data_filters[$field] = $request[$field];
                    } else {
                        $data_filters[$field] = $request[$field];
                    }
                    
                }
            }
        }
        if (!empty($rule['properties']['query'])) {
            if (!empty($rule['properties']['query']['where'])) {
                $where = array_merge($where, $rule['properties']['query']['where']);
            }
        }
        $fields = [];
        if ($className = $this->modx->loadClass($rule['class'])) {
            if (isset($this->modx->map[$rule['class']])) {
                foreach ($this->modx->map[$rule['class']]['fieldMeta'] as $field => $meta) {
                    $fields[$field] = 1;
                }
            }
        }
        if (!empty($where)) {
            foreach ($where as $k => $value) {
                $k = str_replace('`', '', $k);
                $arr = explode('.', $k);
                if (count($arr) == 1) {
                    $field = $arr[0];
                } else {
                    $field = $arr[1];
                }
                if (isset($fields[$field])) $data[$field] = $value;
            }
        }
        if (!empty($request['parent_id'])) {
            $data['parent_id'] = $request['parent_id'];
        }
        return array_merge($data, $data_filters);
    }
}
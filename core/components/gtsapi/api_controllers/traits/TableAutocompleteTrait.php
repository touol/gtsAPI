
<?php

/**
 * Trait для работы с автокомплитом
 * Содержит методы обработки автокомплита для полей таблицы
 */
trait TableAutocompleteTrait
{
    /**
     * Получение данных автокомплита
     */
    public function get_autocomplete($rule, $request)
    {
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
        if (isset($autocomplete['query']) and is_array($autocomplete['query']))
            $default = array_merge($default, $autocomplete['query']);

        if (isset($autocomplete['select'])) {
            $selects_fields = [];
            foreach ($autocomplete['select'] as $field) {
                $selects_fields[] = $rule['class'] . '.' . $field;
            }
            $default['select'][$rule['class']] = implode(',', $selects_fields);
        }
        

        if (isset($request['query']) or !empty($request['parent']) or !empty($request['search'])) {
            if (empty($default['where'])) $default['where'] = [];
            $where = [];
            
            // Обработка стандартных where условий
            if (isset($autocomplete['where'])) {
                foreach ($autocomplete['where'] as $field => $value) {
                    if (strpos($value, 'query') !== false) {
                        if (!empty($request['query'])) {
                            $value = str_replace('query', $request['query'], $value);
                            $where[$field] = $value;
                        }
                    } else {
                        $where[$field] = $value;
                    }
                    if (!empty($request['parent'])) {
                        foreach ($request['parent'] as $pfield => $pval) {
                            if ($value == $pfield) {
                                $where[$field] = $pval;
                            }
                        }
                    }
                }
            }
            
            // Обработка множественных полей поиска для multiautocomplete
            if (!empty($request['search'])) {
                foreach ($request['search'] as $searchField => $searchConfig) {
                    if (isset($searchConfig['value']) && !empty($searchConfig['value'])) {
                        $where[$searchField] = $searchConfig['value'];
                    }
                }
            }
            
            $default['where'] = array_merge($default['where'], $where);
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
        if (isset($request['ids']) and is_array($request['ids'])) {
            if (empty($default['where'])) $default['where'] = [];
            $default['where']["{$rule['class']}.id:IN"] = $request['ids'];
        }
        $default['decodeJSON'] = 1;
        if (!empty($request['id'])) {
            $default['where']["{$rule['class']}.id"] = $request['id'];
        }
        if (!empty($request['show_id']) and isset($autocomplete['show_id_where'])) {
            $default['where'][1001] = "({$rule['class']}.id = {$request['show_id']} or {$autocomplete['show_id_where']} = {$request['show_id']})";
        }
        if (isset($autocomplete['limit'])) {
            $default['limit'] = $autocomplete['limit'];
        }
        if (isset($request['offset'])) {
            $default['offset'] = $request['offset'];
        } else {
            $request['offset'] = 0;
        }
        
        // Добавляем поддержку limit из запроса для виртуального скроллинга
        if (isset($request['limit'])) {
            $default['limit'] = $request['limit'];
        }
        
        $default['setTotal'] = true;
        
        if ($request['sortField']) {
            $default['sortby'] = [
                "{$request['sortField']}" => $request['sortOrder'] == 1 ? 'ASC' : 'DESC',
            ];
        }
        if ($request['multiSortMeta']) {
            $default['sortby'] = [];
            foreach ($request['multiSortMeta'] as $sort) {
                $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ? 'ASC' : 'DESC';
            }
        }
        $this->pdo->setConfig($default);
        $rows0 = $this->pdo->run();
        if (!empty($autocomplete['tpl'])) {
            foreach ($rows0 as $k => $row) {
                $rows0[$k]['content'] = $this->pdoTools->getChunk("@INLINE " . $autocomplete['tpl'], $row);
            }
        }
        
        $total = (int)$this->modx->getPlaceholder('total');
        
        $default = '';
        if (isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])) {
            if ($obj = $this->modx->getObject($rule['class'], $autocomplete['default_row'])) {
                $default = $obj->id;
            }
        }
        $out = [
            'rows' => $rows0,
            'total' => $total,
            'default' => $default,
            'log' => $this->pdo->getTime()
        ];
        
        // Добавляем шаблон из конфигурации autocomplete для динамического отображения
        if (!empty($autocomplete['template'])) {
            $out['template'] = $autocomplete['template'];
        }
        
        if ($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();

        return $this->success('', $out);
    }

    /**
     * Обработка множественных автокомплитов
     */
    public function autocompletes($fields, $rows0, $offset)
    {
        if (empty($fields)) return [];
        $autocompletes = [];
        foreach ($fields as $field => $desc) {
            if (isset($desc['type'])) {
                if ($desc['type'] == 'autocomplete' and isset($desc['table'])) {
                    
                    if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $desc['table'], 'active' => 1])) {
                        $properties = json_decode($gtsAPITable->properties, 1);
                        if (is_array($properties) and isset($properties['autocomplete'])) {
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if (isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $desc['table'];
                            $autocomplete['class'] = $gtsAPITable->class ? $gtsAPITable->class : $desc['table'];
                            $autocompletes[$field] = $this->autocomplete($autocomplete, $rows0);
                        }
                    }
                } else if ($desc['type'] == 'multiautocomplete' and isset($desc['table']) and isset($desc['search'])) {
                    // Обработка multiautocomplete
                    if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $desc['table'], 'active' => 1])) {
                        $properties = json_decode($gtsAPITable->properties, 1);
                        if (is_array($properties) and isset($properties['autocomplete'])) {
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if (isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $desc['table'];
                            $autocomplete['class'] = $gtsAPITable->class ? $gtsAPITable->class : $desc['table'];
                            
                            // Добавляем данные для полей поиска
                            $searchFieldsData = [];
                            foreach ($desc['search'] as $searchFieldKey => $searchFieldConfig) {
                                if (isset($searchFieldConfig['table'])) {
                                    if ($searchGtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $searchFieldConfig['table'], 'active' => 1])) {
                                        $searchProperties = json_decode($searchGtsAPITable->properties, 1);
                                        if (is_array($searchProperties) and isset($searchProperties['autocomplete'])) {
                                            $this->addPackages($searchGtsAPITable->package_id);
                                            $searchAutocomplete = $searchProperties['autocomplete'];
                                            $searchAutocomplete['field'] = $searchFieldKey;
                                            $searchAutocomplete['table'] = $searchFieldConfig['table'];
                                            $searchAutocomplete['class'] = $searchGtsAPITable->class ? $searchGtsAPITable->class : $searchFieldConfig['table'];
                                            
                                            // Получаем значения для полей поиска из текущих строк
                                            $searchFieldValues = [];
                                            foreach ($rows0 as $row) {
                                                if (isset($row[$searchFieldKey]) && !empty($row[$searchFieldKey])) {
                                                    $searchFieldValues[$row[$searchFieldKey]] = $row[$searchFieldKey];
                                                }
                                            }
                                            
                                            if (!empty($searchFieldValues)) {
                                                $searchFieldsData[$searchFieldKey] = $this->autocomplete($searchAutocomplete, []);
                                                // Фильтруем только нужные значения
                                                $filteredRows = [];
                                                foreach ($searchFieldsData[$searchFieldKey]['rows'] as $searchRow) {
                                                    if (in_array($searchRow['id'], $searchFieldValues)) {
                                                        $filteredRows[] = $searchRow;
                                                    }
                                                }
                                                $searchFieldsData[$searchFieldKey]['rows'] = $filteredRows;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $autocompleteResult = $this->autocomplete($autocomplete, $rows0);
                            $autocompleteResult['searchFields'] = $searchFieldsData;
                            $autocompletes[$field] = $autocompleteResult;
                        }
                    }
                }
            }
        }
        return $autocompletes;
    }

    /**
     * Обработка одного автокомплита
     */
    public function autocomplete($autocomplete, $rows0)
    {
        if (!isset($autocomplete['limit'])) $autocomplete['limit'] = 15;
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
        if (isset($autocomplete['select'])) {
            $selects_fields = [];
            foreach ($autocomplete['select'] as $field) {
                $selects_fields[] = $autocomplete['class'] . '.' . $field;
            }
            $default['select'][$autocomplete['class']] = implode(',', $selects_fields);
        }
        if (isset($autocomplete['query']) and is_array($autocomplete['query']))
            $default = array_merge($default, $autocomplete['query']);
        if ($autocomplete['limit'] > 0) {
            $ids = [];
            foreach ($rows0 as $row) {
                if ((int)$row[$autocomplete['field']] > 0) $ids[$row[$autocomplete['field']]] = $row[$autocomplete['field']];
            }
            if (!empty($ids)) {
                $default['where'][$autocomplete['class'] . '.id:IN'] = $ids;
                $default['limit'] = 0;
            }
        }
        $default['setTotal'] = true;
        $this->pdo->setConfig($default);
        $autocomplete['rows'] = $this->pdo->run();
        if (!empty($autocomplete['tpl'])) {
            foreach ($autocomplete['rows'] as $k => $row) {
                $autocomplete['rows'][$k]['content'] = $this->pdoTools->getChunk("@INLINE " . $autocomplete['tpl'], $row);
            }
        }
        $default = '';
        if (isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])) {
            if ($obj = $this->modx->getObject($rule['class'], $autocomplete['default_row'])) {
                $autocomplete['default_value'] = $obj->id;
            }
        }
        $autocomplete['log'] = $this->pdo->getTime();
        $autocomplete['total'] = (int)$this->modx->getPlaceholder('total');
        return $autocomplete;
    }
}
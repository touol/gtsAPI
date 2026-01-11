
<?php

/**
 * Trait для экспорта и печати данных
 * Содержит методы экспорта в Excel и печати таблиц
 */
trait TableExportTrait
{
    /**
     * Экспорт данных в Excel
     */
    public function excel_export($rule, $request)
    {
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
                $fields = $rule['properties']['fields'];
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

    /**
     * Печать данных
     */
    public function print($rule, $request)
    {
        // Проверяем, включено ли действие print
        if (isset($rule['properties']['actions']['print']) && $rule['properties']['actions']['print'] === false) {
            return $this->error('Print is disabled');
        }

        try {
            // Получаем поля
            $fields = [];
            if (!empty($rule['properties']['fields'])) {
                $fields = $rule['properties']['fields'];
            } else {
                if ($rule['type'] == 1) $fields = $this->gen_fields($rule);
            }

            // Подготавливаем заголовки столбцов
            $headers = [];
            foreach ($fields as $fieldName => $fieldConfig) {
                if (isset($fieldConfig['modal_only']) && $fieldConfig['modal_only']) continue;
                if ($fieldName == 'id' and isset($rule['properties']['hide_id'])) continue;
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

    /**
     * Генерация HTML для печати
     */
    private function generatePrintHTML($rule, $headers, $rows, $autocompletes, $request)
    {
        if (isset($request['no_html_tag'])) {
            $html = '';
        } else {
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
        if (!isset($request['no_html_tag'])) $html .= '</body></html>';
        
        return $html;
    }
}
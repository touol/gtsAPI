<?php

/**
 * Trait для утилитарных методов
 * Содержит вспомогательные функции общего назначения
 */
trait TableUtilsTrait
{
    /**
     * Конвертация массивов в JSON
     */
    public function request_array_to_json($request)
    {
        $req = [];
        foreach ($request as $k => $v) {
            if (is_array($v)) {
                $req[$k] = json_encode($v, JSON_PRETTY_PRINT);
            } else {
                $req[$k] = $v;
            }
        }
        return $req;
    }

    /**
     * Вставка элементов в массив после указанного ключа
     */
    public function insertToArray($array = array(), $new = array(), $after = '')
    {
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

    /**
     * Рекурсивная замена insert_menu_id в массиве
     *
     * @param array $array Массив для обработки
     * @param int $insert_menu_id Значение для замены
     * @return array Обработанный массив
     */
    public function replaceInsertMenuIdInArray($array, $insert_menu_id)
    {
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
     * Получение списка полей из select запроса
     * 
     * @param array $config Конфигурация запроса
     * @param array $rule Правило таблицы
     * @return array Список полей
     */
    public function getSelectFieldsList($config, $rule)
    {
        $selectFields = [];
        
        if (isset($config['select']) && !empty($config['select'])) {
            // Есть настроенный select
            foreach ($config['select'] as $class => $fields) {
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
            if ($this->modx->loadClass($rule['class']) && isset($this->modx->map[$rule['class']])) {
                foreach ($this->modx->map[$rule['class']]['fieldMeta'] as $fieldName => $meta) {
                    $selectFields[] = $fieldName;
                }
            }
        }
        $selectFields[] = 'id';
        return $selectFields;
    }
}
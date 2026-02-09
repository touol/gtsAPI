<?php

/**
 * Trait для операции копирования в tree контроллере
 * Содержит методы копирования записей и связанных данных
 */
trait TreeCopyTrait
{
    /**
     * Копирование записей
     */
    public function copy($rule, $request, $action)
    {
        $saved = [];
        
        // Определяем класс для работы
        $class = !empty($request['class_key']) ? $request['class_key'] : $rule['class'];
        
        // Получаем записи для копирования
        if (!empty($request['ids'])) {
            if (is_string($request['ids'])) {
                $request['ids'] = explode(',', $request['ids']);
            } else if (is_numeric($request['ids'])) {
                $request['ids'] = [(int)$request['ids']];
            } else if (!is_array($request['ids'])) {
                $request['ids'] = [$request['ids']];
            }
            
            // Проверяем, что записи существуют
            $count = $this->modx->getCount($class, ['id:IN' => $request['ids']]);
            if ($count == 0) {
                return $this->error('Записи для копирования не найдены');
            }
            
            $old_rows = $this->modx->getIterator($class, ['id:IN' => $request['ids']]);
        } else {
            return $this->error('Не указаны ID записей для копирования');
        }
        
        // Получаем конфигурацию копирования из action
        $copy_config = $action;
        
        foreach ($old_rows as $old_obj) {
            $old_row = $old_obj->toArray();
            $new_row = $old_row;
            unset($new_row['id']);
            
            // Применяем значения по умолчанию из add_fields
            if (!empty($copy_config['add_fields'])) {
                foreach ($copy_config['add_fields'] as $field => $field_config) {
                    if (isset($request[$field])) {
                        $new_row[$field] = $request[$field];
                    } else if (isset($field_config['default'])) {
                        $new_row[$field] = $field_config['default'];
                    }
                }
            }
            
            // Устанавливаем шаблон по умолчанию
            if (!empty($copy_config['default_template'])) {
                $new_row['template'] = $copy_config['default_template'];
            }
            
            // Обработка title для UniTree
            if (!empty($request['title']) && !empty($rule['gtsAPIUniTreeClass'][$class]['title_field'])) {
                $title_field = $rule['gtsAPIUniTreeClass'][$class]['title_field'];
                $new_row[$title_field] = $request['title'];
            }
            
            // Создаем новый объект
            if (!$new_obj = $this->modx->newObject($class, $new_row)) {
                $saved[] = $this->error('Ошибка создания объекта', ['class' => $class]);
                continue;
            }
            
            $object_old = [];
            $object_new = $new_obj->toArray();
            
            // Запускаем триггеры before
            $resp = $this->run_triggers($rule, 'before', 'copy', $request, $object_old, $object_new, $new_obj);
            if (!$resp['success']) {
                $saved[] = $resp;
                continue;
            }
            
            if ($new_obj->save()) {
                // Обработка extended_modresource для генерации алиаса и обновления кеша
                if (!empty($rule['gtsAPIUniTreeClass'][$class]['extended_modresource'])) {
                    if ($new_obj instanceof modResource) {
                        // Генерируем новый алиас
                        $alias = $new_obj->cleanAlias($new_obj->get('pagetitle'));
                        $new_obj->set('alias', $alias);
                        // Обновляем кеш URL
                        $new_obj->getOne('Context');
                        $new_obj->set('uri', $new_obj->getAliasPath($alias));
                        $new_obj->set('uri_override', 0);
                        $new_obj->save();
                    }
                }
                
                $new_row = $new_obj->toArray();
                $resp = $this->success('Объект скопирован', ['id' => $new_obj->get('id'), 'class' => $class]);
                $saved[] = $resp;
                
                // Копируем связанные таблицы many
                if (!empty($copy_config['child']['many'])) {
                    foreach ($copy_config['child']['many'] as $child_class => $child_alias) {
                        $resp1 = $this->copy_many($class, $child_class, $child_alias, $old_row, $new_row);
                        $resp1['subtables'] = 1;
                        $resp1['subtable_name'] = $child_class;
                        $saved[] = $resp1;
                    }
                }
                
                // Копируем связанные таблицы one
                if (!empty($copy_config['child']['one'])) {
                    foreach ($copy_config['child']['one'] as $child_class => $child_alias) {
                        $resp1 = $this->copy_one($class, $child_class, $child_alias, $old_row, $new_row);
                        $resp1['subtables'] = 1;
                        $resp1['subtable_name'] = $child_class;
                        $saved[] = $resp1;
                    }
                }
                
                // Запускаем триггеры after
                $resp = $this->run_triggers($rule, 'after', 'copy', $request, $old_row, $new_row, $new_obj);
                if (!$resp['success']) {
                    $saved[] = $resp;
                }
            } else {
                $saved[] = $this->error('Ошибка сохранения объекта', ['class' => $class]);
            }
        }
        
        // Проверяем наличие ошибок
        $error = '';
        foreach ($saved as $s) {
            if (!$s['success']) {
                if (!empty($s['subtables'])) {
                    $error .= $s['subtable_name'] . ' ' . $s['message'] . "\r\n";
                } else {
                    $error .= "Объект {$s['class']} не скопирован: {$s['message']}\r\n";
                }
            }
        }
        
        if (!$error) {
            return $this->success('Записи успешно скопированы', ['saved' => $saved]);
        }
        return $this->error($error, $saved);
        
    }
    
    /**
     * Копирование связанной записи (one)
     */
    public function copy_one($class, $child_class, $child_alias, $old_row, $new_row)
    {
        $saved = [];
        
        if (!$source = $this->modx->getObject($class, (int)$old_row['id'])) {
            return $this->error('Исходный объект не найден', ['class' => $class, 'id' => $old_row['id']]);
        }
        
        if (!$dest = $this->modx->getObject($class, (int)$new_row['id'])) {
            return $this->error('Целевой объект не найден', ['class' => $class, 'id' => $new_row['id']]);
        }
        
        if (!$ch = $source->getOne($child_alias)) {
            return $this->success('Связанный объект не найден', ['child_alias' => $child_alias]);
        }
        
        if ($newchild = $this->modx->newObject($child_class, $ch->toArray())) {
            $newchild->addOne($dest);
            $newchild->save();
        }
        
        return $this->success('Связанный объект скопирован', $saved);
    }
    
    /**
     * Копирование связанных записей (many)
     */
    public function copy_many($class, $child_class, $child_alias, $old_row, $new_row)
    {
        $saved = [];
        
        if (!$source = $this->modx->getObject($class, (int)$old_row['id'])) {
            return $this->error('Исходный объект не найден', ['class' => $class, 'id' => $old_row['id']]);
        }
        
        if (!$dest = $this->modx->getObject($class, (int)$new_row['id'])) {
            return $this->error('Целевой объект не найден', ['class' => $class, 'id' => $new_row['id']]);
        }
        
        if (!$childs = $source->getMany($child_alias)) {
            return $this->success('Связанные объекты не найдены', ['child_alias' => $child_alias]);
        }
        
        // Удаляем существующие связанные объекты у целевого объекта
        if ($dest_childs = $dest->getMany($child_alias)) {
            foreach ($dest_childs as $dch) {
                $dch->remove();
            }
        }
        
        // Копируем связанные объекты
        foreach ($childs as $ch) {
            if ($newchild = $this->modx->newObject($child_class, $ch->toArray())) {
                $newchild->addOne($dest);
                $newchild->save();
            }
        }
        
        return $this->success('Связанные объекты скопированы', $saved);
    }
}

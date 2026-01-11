<?php

/**
 * Trait для работы с триггерами и событиями
 * Содержит методы запуска триггеров и обработки событий
 */
trait TableTriggerTrait
{
    /**
     * Запуск триггеров
     */
    public function run_triggers($rule, $type, $method, $fields, $object_old = [], &$object_new = [], $object = null, $internal_action = '')
    {
        $class = $rule['class'];
        if (empty($class)) return $this->success('Выполнено успешно');
        
        // Событие для плагинов
        $gtsAPIRunTriggers = $this->modx->invokeEvent('gtsAPIRunTriggers', [
            'class' => $class,
            'rule' => $rule,
            'type' => $type,
            'method' => $method,
            'fields' => $fields,
            'object_old' => $object_old,
            'object_new' => $object_new,
            'trigger' => 'gtsapifunc',
            'object' => $object,
            'internal_action' => $internal_action,
        ]);
        if (is_array($gtsAPIRunTriggers)) {
            $canSave = false;
            foreach ($gtsAPIRunTriggers as $msg) {
                if (!empty($msg)) {
                    $canSave .= $msg . "\n";
                }
            }
        } else {
            $canSave = $gtsAPIRunTriggers;
        }
        if (!empty($canSave)) return $this->error($canSave);
        if (isset($modx->event->returnedValues['object'])) {
            $object = $modx->event->returnedValues['object'];
        }

        try {
            $triggers = $this->triggers;
            
            if (isset($triggers[$class]['gtsapifunc']) and isset($triggers[$class]['model'])) {
                $service = $this->models[$triggers[$class]['model']];
                if (method_exists($service, $triggers[$class]['gtsapifunc'])) {
                    $params = [
                        'rule' => $rule,
                        'class' => $class,
                        'type' => $type,
                        'method' => $method,
                        'fields' => $fields,
                        'object_old' => $object_old,
                        'object_new' => &$object_new,
                        'object' => &$object,
                        'trigger' => 'gtsapifunc',
                        'internal_action' => $internal_action,
                    ];
                    return $service->{$triggers[$class]['gtsapifunc']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1, 'gtsAPI Ошибка триггера ' . $e->getMessage() . print_r($e->getTrace()[0], 1));
            return $this->error('Ошибка триггера ' . $e->getMessage());
        }
        
        return $this->success('Выполнено успешно');
    }

    /**
     * Отслеживание формы
     */
    public function watch_form($rule, $request)
    {
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;
            
            if (isset($triggers[$class]['gtsapi_watch_form']) and isset($triggers[$class]['model'])) {
                $service = $this->models[$triggers[$class]['model']];
                if (method_exists($service, $triggers[$class]['gtsapi_watch_form'])) {
                    $params = [
                        'rule' => $rule,
                        'class' => $class,
                        'request' => $request,
                        'fields' => $this->addFields($rule, $rule['properties']['fields'], $request['watch_action']),
                        'trigger' => 'gtsapi_watch_form',
                    ];
                    return $service->{$triggers[$class]['gtsapi_watch_form']}($params);
                }
            }
        } catch (Error $e) {
            $this->modx->log(1, 'gtsAPI Ошибка триггера ' . $e->getMessage());
            return $this->error('Ошибка триггера ' . $e->getMessage());
        }
        return $this->error('Ошибка триггера 2');
    }

    /**
     * Установка заголовка UniTree
     */
    public function setUniTreeTitle($rule, $obj)
    {
        $table = '';
        $gtsAPIUniTreeClasses = $this->modx->getIterator('gtsAPIUniTreeClass', ['table' => $rule['table']]);
        foreach ($gtsAPIUniTreeClasses as $gtsAPIUniTreeClass) {
            if ($gtsAPITable = $this->modx->getObject('gtsAPITable', $gtsAPIUniTreeClass->table_id)) {
                if (empty($gtsAPITable->class)) $gtsAPITable->class = $gtsAPITable->table;
                if ($gtsAPITable->properties) {
                    $properties = json_decode($gtsAPITable->properties, 1);
                    if (!isset($properties['useUniTree']) or $properties['useUniTree'] == false) {
                        $table = $gtsAPITable->table;
                        continue;
                    }
                    $this->addPackages($gtsAPITable->package_id);
                    $treeNodes = $this->modx->getIterator($gtsAPITable->class, ['class' => $rule['class'], 'target_id' => $obj->get('id')]);
                    foreach ($treeNodes as $treeNode) {
                        if (empty($gtsAPIUniTreeClass->title_field)) {
                            if ($gtsAPIUniTreeClass->exdended_modresource) {
                                $gtsAPIUniTreeClass->title_field = 'pagetitle';
                            } else {
                                $gtsAPIUniTreeClass->title_field = 'name';
                            }
                        }
                        $treeNode->title = $obj->get($gtsAPIUniTreeClass->title_field);
                        
                        // Проверяем поле active в объекте $obj
                        if ($obj->get('active') !== null) {
                            $treeNode->active = $obj->get('active');
                        }
                        
                        if ($treeNode->save()) $table = $gtsAPITable->table;
                    }
                }
            }
        }
        return $table;
    }
}
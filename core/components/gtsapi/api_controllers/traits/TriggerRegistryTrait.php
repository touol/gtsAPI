<?php

/**
 * Реестр триггеров.
 *
 * До этого триггеры хранились как `$triggers[$class] = ['gtsapifunc' => 'метод', ...]`
 * и склеивались через array_merge. Из-за этого на класс выживал ТОЛЬКО ОДИН набор:
 * если два компонента вешали обработчики на одну таблицу, последний загруженный
 * молча затирал предыдущий — вместе со всеми его видами триггеров.
 *
 * Ловилось это плохо: ничего не падало, просто переставал работать чужой пересчёт
 * или watch_form, и concов было не найти.
 *
 * Теперь на класс хранится СПИСОК регистраций, и вызываются все по порядку загрузки.
 * Формат regTriggers в компонентах не менялся.
 */
trait TriggerRegistryTrait
{
    /**
     * Забрать триггеры у сервиса компонента.
     *
     * @param object $service сервис компонента с методом regTriggers()
     * @param string $model   имя пакета (ключ в $this->models)
     */
    public function addServiceTriggers($service, $model)
    {
        if (empty($service) || !method_exists($service, 'regTriggers')) return;

        $triggers = $service->regTriggers();
        if (!is_array($triggers)) return;

        foreach ($triggers as $class => $trigger) {
            if (!is_array($trigger)) continue;
            $trigger['model'] = $model;
            if (!isset($this->triggers[$class])) $this->triggers[$class] = [];

            // Тот же компонент мог уже зарегистрироваться (повторный addPackages) —
            // второй раз вызывать его обработчики не нужно.
            foreach ($this->triggers[$class] as $exists) {
                if ($exists === $trigger) continue 2;
            }
            $this->triggers[$class][] = $trigger;
        }
    }

    /**
     * Обработчики одного вида для класса, в порядке регистрации.
     *
     * @param string $class класс таблицы
     * @param string $kind  gtsapifunc | gtsapi_rule | gtsapi_watch_form | gtsapi_addfields
     * @return array список ['service' => объект, 'method' => имя метода]
     */
    public function triggerHandlers($class, $kind)
    {
        $handlers = [];
        if (empty($this->triggers[$class])) return $handlers;

        foreach ($this->triggers[$class] as $trigger) {
            if (empty($trigger[$kind]) || empty($trigger['model'])) continue;
            if (empty($this->models[$trigger['model']])) continue;

            $service = $this->models[$trigger['model']];
            if (!method_exists($service, $trigger[$kind])) continue;

            $handlers[] = ['service' => $service, 'method' => $trigger[$kind]];
        }
        return $handlers;
    }
}

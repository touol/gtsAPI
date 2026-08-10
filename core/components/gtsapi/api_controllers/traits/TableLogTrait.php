<?php

/**
 * Логирование CRUD-действий в gtsAPILog.
 * Общий трейт для контроллеров: TableCrudTrait (обычные таблицы) и
 * treeAPIController/TableTreeCrudTrait (дерево/UniTree) — чтобы дерево тоже писало лог.
 */
trait TableLogTrait
{
    /**
     * Запись действия в лог gtsAPILog.
     * Отключается через properties: { log: false } в gtsapipackages.
     * Срок хранения задаётся системной настройкой gtsapi_log_retention_days (по умолчанию 30 дней).
     */
    protected function writeLog($rule, $action, $objectId, $dataBefore, $dataAfter)
    {
        if (isset($rule['properties']['log']) && $rule['properties']['log'] === false) return;

        try {
            // Очистка устаревших записей раз в сутки через кеш MODX
            $today     = date('Y-m-d');
            $cacheKey  = 'gtsapi_log_cleanup_date';
            $lastClean = $this->modx->cacheManager->get($cacheKey, ['cache_prefix' => 'gtsapi/']);
            if ($lastClean !== $today) {
                $days   = (int)$this->modx->getOption('gtsapi_log_retention_days', null, 30);
                $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
                $table  = $this->modx->getTableName('gtsAPILog');
                $this->modx->exec("DELETE FROM {$table} WHERE created_at < '{$cutoff}'");
                $this->modx->cacheManager->set($cacheKey, $today, 0, ['cache_prefix' => 'gtsapi/']);
            }

            $log = $this->modx->newObject('gtsAPILog');
            if (!$log) return;
            $log->set('user_id',     (int)$this->modx->user->id);
            $log->set('log_table',   $rule['table'] ?? '');
            $log->set('log_action',  $action);
            $log->set('object_id',   (int)$objectId);
            $log->set('data_before', $dataBefore ? json_encode($dataBefore, JSON_UNESCAPED_UNICODE) : null);
            $log->set('data_after',  $dataAfter  ? json_encode($dataAfter,  JSON_UNESCAPED_UNICODE) : null);
            $log->set('created_at',  date('Y-m-d H:i:s'));
            $log->save();
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'gtsAPI writeLog error: ' . $e->getMessage());
        }
    }
}

<?php

/**
 * Trait версионирования записей таблицы (аналог versionX).
 *
 * Данные берём из существующего лога gtsAPILog (data_before/data_after),
 * который пишется на каждый create/update/delete в TableCrudTrait::writeLog.
 * Отдельного хранилища нет; версии живут в окне ретеншна (gtsapi_log_retention_days).
 *
 * Включается per-table флагом properties.save_version_row: true.
 * Действия: versions (список версий строки), restore_version (откат к версии).
 */
trait TableVersionTrait
{
    /**
     * Включено ли версионирование для таблицы.
     */
    protected function versioningEnabled($rule)
    {
        return !empty($rule['properties']['save_version_row']);
    }

    /**
     * Список версий строки из gtsAPILog.
     * Вход: request[id] — id записи.
     * Возврат: версии (новые сверху) со снимком data_after, изменёнными полями, кто/когда.
     */
    public function versions($rule, $request)
    {
        if (!$this->versioningEnabled($rule)) {
            return $this->error('Версионирование не включено для этой таблицы');
        }
        $objectId = isset($request['id']) ? (int)$request['id'] : 0;
        if ($objectId <= 0) return $this->error('Не указан id записи');

        $limit = isset($request['limit']) ? (int)$request['limit'] : 50;
        if ($limit <= 0 || $limit > 500) $limit = 50;

        $table = $this->modx->getTableName('gtsAPILog');
        $sql = "SELECT * FROM {$table} WHERE `log_table` = :t AND `object_id` = :id ORDER BY `created_at` DESC, `id` DESC LIMIT {$limit}";
        $stmt = $this->modx->prepare($sql);
        $stmt->execute([':t' => $rule['table'], ':id' => $objectId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Имена пользователей одним запросом
        $userIds = array_values(array_unique(array_filter(array_map(function ($r) {
            return (int)$r['user_id'];
        }, $rows))));
        $userNames = $this->loadUserNames($userIds);

        $versions = [];
        foreach ($rows as $r) {
            $before = $r['data_before'] ? json_decode($r['data_before'], true) : null;
            $after  = $r['data_after']  ? json_decode($r['data_after'], true)  : null;
            $versions[] = [
                'version_id'   => (int)$r['id'],
                'action'       => $r['log_action'],
                'created_at'   => $r['created_at'],
                'user_id'      => (int)$r['user_id'],
                'user_name'    => isset($userNames[(int)$r['user_id']]) ? $userNames[(int)$r['user_id']] : '',
                'data_before'  => $before,
                'data_after'   => $after,
                'changed'      => $this->diffChangedFields($before, $after),
            ];
        }

        return $this->success('', ['versions' => $versions]);
    }

    /**
     * Откат записи к версии.
     * Вход: request[version_id] — id записи лога; request[which] = 'after'|'before' (что считать снимком версии, по умолч. 'after').
     * Снимок берётся НА СЕРВЕРЕ из gtsAPILog (не из клиента), затем прогоняется через обычный update().
     */
    public function restore_version($rule, $request)
    {
        if (!$this->versioningEnabled($rule)) {
            return $this->error('Версионирование не включено для этой таблицы');
        }
        $versionId = isset($request['version_id']) ? (int)$request['version_id'] : 0;
        if ($versionId <= 0) return $this->error('Не указан version_id');
        // По умолчанию откат = «как было ДО этой правки» (data_before).
        $which = (isset($request['which']) && $request['which'] === 'after') ? 'after' : 'before';

        $table = $this->modx->getTableName('gtsAPILog');
        $stmt = $this->modx->prepare("SELECT * FROM {$table} WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $versionId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$log) return $this->error('Версия не найдена');

        // Защита: версия должна принадлежать этой таблице
        if ($log['log_table'] !== $rule['table']) {
            return $this->error('Версия не относится к этой таблице');
        }

        $snapshot = $which === 'before'
            ? ($log['data_before'] ? json_decode($log['data_before'], true) : null)
            : ($log['data_after'] ? json_decode($log['data_after'], true) : null);
        if (empty($snapshot) || !is_array($snapshot)) {
            return $this->error('У версии нет снимка данных (' . $which . ')');
        }

        // Готовим запрос как обычный update: значения полей из снимка, id записи из лога.
        $restoreReq = array_merge($request, $snapshot);
        $restoreReq['id'] = (int)$log['object_id'];
        $restoreReq['api_action'] = 'update';
        unset($restoreReq['version_id'], $restoreReq['which']);

        return $this->update($rule, $restoreReq, []);
    }

    /**
     * Карта user_id => username.
     */
    protected function loadUserNames(array $userIds)
    {
        $map = [];
        if (empty($userIds)) return $map;
        $q = $this->modx->newQuery('modUser', ['id:IN' => $userIds]);
        $q->select('id,username');
        if ($q->prepare() && $q->stmt->execute()) {
            while ($u = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $map[(int)$u['id']] = $u['username'];
            }
        }
        return $map;
    }

    /**
     * Имена полей, значения которых отличаются между before и after.
     */
    protected function diffChangedFields($before, $after)
    {
        if (!is_array($before) || !is_array($after)) return [];
        $changed = [];
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        foreach ($keys as $k) {
            $b = array_key_exists($k, $before) ? $before[$k] : null;
            $a = array_key_exists($k, $after) ? $after[$k] : null;
            if (json_encode($b) !== json_encode($a)) {
                $changed[] = $k;
            }
        }
        return $changed;
    }
}

<?php

/**
 * Учёт вызовов методов компонентов (api_action вида «пакет/метод»).
 *
 * Такой вызов проверяет только права самой таблицы, через которую пришёл, —
 * права конкретного действия не проверяются, и через любую доступную таблицу
 * можно вызвать метод любого компонента. gtsAPI не знает, какие права нужны
 * чужому методу, поэтому правило будет такое: «пакет/метод» проходит, только
 * если объявлен в actions/hide_actions этой таблицы (ключом или action: 'пакет/метод'),
 * и с правами из объявления.
 *
 * Сейчас режим «только лог»: ничего не блокируем, копим сводку, что вызывается
 * без объявления (или без прав по объявлению), чтобы дописать конфиги до
 * включения блокировки. Сводка: core/cache/logs/gtsapi_undeclared_actions.json.
 */
trait ServiceActionAuditTrait
{
    /**
     * @return string 'ok' | 'undeclared' | 'denied'
     */
    public function serviceActionStatus($rule, $apiAction)
    {
        $declared = false;
        foreach (['actions', 'hide_actions'] as $key) {
            if (empty($rule['properties'][$key]) or !is_array($rule['properties'][$key])) continue;
            foreach ($rule['properties'][$key] as $name => $cfg) {
                $match = ($name === $apiAction)
                    || (is_array($cfg) && isset($cfg['action']) && $cfg['action'] === $apiAction);
                if (!$match) continue;
                $declared = true;
                $resp = $this->checkPermissions(is_array($cfg) ? $cfg : []);
                if ($resp['success']) return 'ok';
            }
        }
        return $declared ? 'denied' : 'undeclared';
    }

    /**
     * Записать вызов в сводку, если он не «ok». Ошибки записи вызов не ломают.
     */
    public function auditServiceAction($rule, $apiAction)
    {
        try {
            $status = $this->serviceActionStatus($rule, $apiAction);
            if ($status === 'ok') return $status;

            $dir = MODX_CORE_PATH . 'cache/logs/';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $file = $dir . 'gtsapi_undeclared_actions.json';

            $fp = @fopen($file, 'c+');
            if (!$fp) return $status;
            if (flock($fp, LOCK_EX)) {
                $raw = stream_get_contents($fp);
                $log = json_decode($raw ?: '[]', true);
                if (!is_array($log)) $log = [];

                $table = isset($rule['table']) ? $rule['table'] : '';
                $key = $table . ' | ' . $apiAction;
                $user = $this->modx->user;
                $who = $user && $user->id ? ($user->id . ':' . $user->get('username')) : '0:anonymous';
                $now = date('Y-m-d H:i:s');

                if (!isset($log[$key])) {
                    $log[$key] = ['table' => $table, 'action' => $apiAction, 'status' => $status,
                        'count' => 0, 'first' => $now, 'last' => $now, 'users' => []];
                }
                $log[$key]['status'] = $status;
                $log[$key]['count']++;
                $log[$key]['last'] = $now;
                if (!in_array($who, $log[$key]['users'], true) and count($log[$key]['users']) < 20) {
                    $log[$key]['users'][] = $who;
                }

                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                fflush($fp);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
            return $status;
        } catch (\Throwable $e) {
            return 'ok';
        }
    }
}

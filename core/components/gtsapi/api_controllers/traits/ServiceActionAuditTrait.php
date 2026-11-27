<?php

/**
 * Вызовы методов компонентов (api_action вида «пакет/метод») через таблицы чужих пакетов.
 *
 * Такой вызов проверяет только права самой таблицы, через которую пришёл, поэтому метод
 * любого компонента достижим через любую доступную таблицу. Правило (решение пользователя
 * 28.11.2026): метод пакета зовётся только через таблицы своего пакета, через чужие — если
 * пара пакетов разрешена в gtsAPIPackageCall (ставится из gtsapipackages пакета,
 * ключ api_call_packages). Права самого метода проверяет метод — это отдельно.
 *
 * Свой пакет таблицы — package_id (gtsAPIPackage.name) или install_package: из-за общих
 * таблиц в рефакторинге конфиг одной таблицы ставят разные пакеты.
 * Явное объявление в actions/hide_actions таблицы ('action' => 'пакет/метод') тоже пропускает.
 *
 * Сейчас режим «только лог»: ничего не блокируем, копим сводку чужих вызовов,
 * чтобы дописать api_call_packages до включения блокировки.
 * Сводка: core/cache/logs/gtsapi_foreign_calls.json.
 */
trait ServiceActionAuditTrait
{
    /** @var array|null пары пакетов из gtsAPIPackageCall ('a|b' => true), читаются один раз на объект */
    protected $gtsPackageCallPairs = null;

    /**
     * @return string 'ok' | 'foreign'
     */
    public function serviceActionStatus($rule, $apiAction)
    {
        $pkg = strtolower(explode('/', $apiAction, 2)[0]);

        foreach (['actions', 'hide_actions'] as $key) {
            if (empty($rule['properties'][$key]) or !is_array($rule['properties'][$key])) continue;
            foreach ($rule['properties'][$key] as $name => $cfg) {
                if ($name === $apiAction || (is_array($cfg) && isset($cfg['action']) && $cfg['action'] === $apiAction)) {
                    return 'ok';
                }
            }
        }

        $owners = $this->tablePackages($rule);
        if (isset($owners[$pkg])) return 'ok';

        return $this->packageCallAllowed($pkg, $owners) ? 'ok' : 'foreign';
    }

    /** Пакеты таблицы: package_id и install_package, в нижнем регистре. */
    public function tablePackages($rule)
    {
        $out = [];
        if (!empty($rule['package_id'])) {
            static $names = [];
            $id = (int)$rule['package_id'];
            if (!array_key_exists($id, $names)) {
                $p = $this->modx->getObject('gtsAPIPackage', $id);
                $names[$id] = $p ? strtolower($p->get('name')) : '';
            }
            if ($names[$id] !== '') $out[$names[$id]] = true;
        }
        if (!empty($rule['install_package'])) $out[strtolower($rule['install_package'])] = true;
        return $out;
    }

    /** Есть ли активная пара pkg ↔ один из пакетов таблицы (в любую сторону). */
    public function packageCallAllowed($pkg, array $owners)
    {
        if (!$owners || !$this->modx->loadClass('gtsAPIPackageCall')) return false;
        $pairs = &$this->gtsPackageCallPairs;
        if ($pairs === null) {
            $pairs = [];
            try {
                $sql = 'SELECT LOWER(p.name) AS a, LOWER(c.partner) AS b FROM '
                    . $this->modx->getTableName('gtsAPIPackageCall') . ' c JOIN '
                    . $this->modx->getTableName('gtsAPIPackage') . ' p ON p.id = c.package_id WHERE c.active = 1';
                if ($stmt = $this->modx->query($sql)) {
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                        $pairs[$r['a'] . '|' . $r['b']] = true;
                        $pairs[$r['b'] . '|' . $r['a']] = true;
                    }
                }
            } catch (\Throwable $e) {
            }
        }
        foreach (array_keys($owners) as $o) {
            if (isset($pairs[$pkg . '|' . $o])) return true;
        }
        return false;
    }

    /**
     * Записать вызов в сводку, если он через чужую таблицу. Ошибки записи вызов не ломают.
     */
    public function auditServiceAction($rule, $apiAction)
    {
        try {
            $status = $this->serviceActionStatus($rule, $apiAction);
            if ($status === 'ok') return $status;

            $dir = MODX_CORE_PATH . 'cache/logs/';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $file = $dir . 'gtsapi_foreign_calls.json';

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
                        'table_packages' => implode(',', array_keys($this->tablePackages($rule))),
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

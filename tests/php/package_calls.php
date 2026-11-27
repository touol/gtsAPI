<?php
/**
 * Вызов 'пакет/метод' через таблицу: по умолчанию только свой пакет таблицы (package_id / install_package),
 * чужой — если пара пакетов есть в gtsAPIPackageCall (в обе стороны). gtsAPI >= 1.2.24, ServiceActionAuditTrait.
 *
 * 1. Статус вызова: свой → ok, чужой → foreign, с парой → ok в обе стороны, без пары снова foreign.
 *    Пара заводится временно и удаляется. Таблицы берутся из базы: две любые таблицы разных пакетов.
 * 2. Резолвер gtsapipackages.php: api_call_packages ставит строку, убрали из конфига — строка снята;
 *    чужую (ручную) строку того же пакета не трогает. Временный пакет и его каталог удаляются.
 * 3. Сквозь tableAPIController (под sudo): gtsapi/export_table через свою gtsAPITable в журнал не пишется,
 *    через таблицу другого пакета — пишется со статусом foreign (тестовая строка потом убирается).
 *
 *   php tests/php/package_calls.php        MODX_ROOT — корень сайта, по умолчанию дев vk-24.
 */
define('MODX_API_MODE', true);
require (getenv('MODX_ROOT') ?: 'V:/OSPanel/home/modx.pl/public') . '/index.php';
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');
$modx->addPackage('gtsapi', MODX_CORE_PATH . 'components/gtsapi/model/');
require_once MODX_CORE_PATH . 'components/gtsapi/api_controllers/traits/ServiceActionAuditTrait.php';

$fail = 0;
$check = function ($ok, $msg) use (&$fail) { echo ($ok ? 'OK   ' : 'FAIL ') . $msg . "\n"; if (!$ok) $fail++; };
// Пары кешируются в объекте — новый объект видит свежие пары
$auditor = function () use ($modx) {
    return new class($modx) { use ServiceActionAuditTrait; public $modx; function __construct($m) { $this->modx = $m; } };
};
if (!$modx->loadClass('gtsAPIPackageCall')) { echo "нет класса gtsAPIPackageCall — gtsAPI не 1.2.24?\n"; exit(1); }

// две таблицы разных пакетов, у которых нет общей пары и install_package не пересекается
$pick = [];
foreach ($modx->getIterator('gtsAPITable', ['active' => 1, 'package_id:>' => 0]) as $t) {
    $p = $modx->getObject('gtsAPIPackage', $t->package_id);
    if (!$p || isset($pick[$p->name])) continue;
    $pick[$p->name] = $t->toArray();
    if (count($pick) == 2) break;
}
if (count($pick) < 2) { echo "нужны таблицы двух пакетов\n"; exit(1); }
[$a, $b] = array_keys($pick);
$ta = $pick[$a]; $tb = $pick[$b];
$ta['properties'] = []; $tb['properties'] = [];   // объявления в actions не мешают проверке
$ta['install_package'] = ''; $tb['install_package'] = '';
echo "пакеты: $a ({$ta['table']}), $b ({$tb['table']})\n";

$x = $auditor();
$check($x->serviceActionStatus($ta, "$a/foo") === 'ok', "$a/foo через свою {$ta['table']} → ok");
$check($x->serviceActionStatus($ta, "$b/foo") === 'foreign', "$b/foo через чужую {$ta['table']} → foreign");
$check($x->serviceActionStatus(array_merge($ta, ['install_package' => $b]), "$b/foo") === 'ok',
    "$b/foo через {$ta['table']}, где install_package = $b → ok");
$check($x->serviceActionStatus(array_merge($ta, ['properties' => ['hide_actions' => ['x' => ['action' => "$b/foo"]]]]), "$b/foo") === 'ok',
    "$b/foo, объявленный в hide_actions таблицы → ok");

$pa = $modx->getObject('gtsAPIPackage', ['name' => $a]);
$had = $modx->getObject('gtsAPIPackageCall', ['package_id' => $pa->id, 'partner' => strtolower($b)]);
if (!$had) {
    $pair = $modx->newObject('gtsAPIPackageCall');
    $pair->fromArray(['package_id' => $pa->id, 'partner' => strtolower($b), 'install_package' => 'test', 'active' => 1]);
    $pair->save();
}
$y = $auditor();
$check($y->serviceActionStatus($ta, "$b/foo") === 'ok', "пара $a ↔ $b: $b/foo через {$ta['table']} → ok");
$check($y->serviceActionStatus($tb, "$a/foo") === 'ok', "пара $a ↔ $b: $a/foo через {$tb['table']} → ok (обратная сторона)");
if (!$had) $pair->remove();
$z = $auditor();
$check($z->serviceActionStatus($ta, "$b/foo") === 'foreign', "пару убрали → снова foreign");

// ── резолвер ─────────────────────────────────────────────────────────────
$ns = 'gtsapicalltest';
$dir = MODX_CORE_PATH . "components/$ns/";
@mkdir($dir . 'model', 0775, true);
$resolver = MODX_CORE_PATH . 'components/gtsapi/resolvers/gtsapipackages.php';
$run = function (array $cfg) use ($modx, $ns, $dir, $resolver) {
    file_put_contents($dir . 'gtsapipackages.json', json_encode([$ns => array_merge(['name' => $ns], $cfg)]));
    $transport = new stdClass();
    $transport->xpdo = $modx;
    $options = [xPDOTransport::PACKAGE_ACTION => xPDOTransport::ACTION_UPGRADE, 'namespace' => $ns];
    include $resolver;
};
if (!class_exists('xPDOTransport')) $modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);

$run(['api_call_packages' => ['PartnerOne', 'partnertwo']]);
$pkg = $modx->getObject('gtsAPIPackage', ['name' => $ns]);
$rows = function () use ($modx, $pkg) {
    $out = [];
    foreach ($modx->getIterator('gtsAPIPackageCall', ['package_id' => $pkg->id]) as $c) $out[$c->partner] = $c->install_package;
    ksort($out);
    return $out;
};
$check($pkg && $rows() === ['partnerone' => $ns, 'partnertwo' => $ns], 'резолвер: api_call_packages → две строки (имена в нижнем регистре)');

$manual = $modx->newObject('gtsAPIPackageCall');
$manual->fromArray(['package_id' => $pkg->id, 'partner' => 'manual', 'install_package' => '', 'active' => 1]);
$manual->save();
$run(['api_call_packages' => ['partnertwo']]);
$check($rows() === ['manual' => '', 'partnertwo' => $ns], 'резолвер: убрали partnerone — снята; ручная строка осталась');
$run([]);
$check($rows() === ['manual' => '', 'partnertwo' => $ns], 'резолвер: без ключа api_call_packages строки не трогает');

foreach ($modx->getIterator('gtsAPIPackageCall', ['package_id' => $pkg->id]) as $c) $c->remove();
$pkg->remove();
@unlink($dir . 'gtsapipackages.json');
@rmdir($dir . 'model'); @rmdir($dir);

// ── сквозь контроллер таблицы: чужой вызов попадает в журнал, свой — нет ──
$admin = $modx->getObject('modUser', ['sudo' => 1]);
$gtsapi = $modx->getService('gtsapi', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');
require_once MODX_CORE_PATH . 'components/gtsapi/api_controllers/table.class.php';
$log = MODX_CORE_PATH . 'cache/logs/gtsapi_foreign_calls.json';
$readLog = function () use ($log) { return is_file($log) ? (json_decode(file_get_contents($log), true) ?: []) : []; };
$own = $modx->getObject('gtsAPITable', ['table' => 'gtsAPITable']);
$foreignTable = null;
foreach ($modx->getIterator('gtsAPITable', ['active' => 1, 'type' => 1, 'tree' => 0, 'table:!=' => 'gtsAPITable']) as $t) {
    $p = $modx->getObject('gtsAPIPackage', $t->package_id);
    if ($p && strtolower($p->name) !== 'gtsapi' && strtolower($t->install_package) !== 'gtsapi') { $foreignTable = $t; break; }
}
if ($admin && $own && $foreignTable) {
    $modx->user = $admin;
    $ctl = new tableAPIController($modx, array_merge($gtsapi->config, ['internal_call' => 1]));
    $kOwn = 'gtsAPITable | gtsapi/export_table';
    $kForeign = $foreignTable->table . ' | gtsapi/export_table';
    $before = $readLog();
    $ctl->route($own, [], 'POST', ['api_action' => 'gtsapi/export_table', 'id' => $own->id]);
    $ctl->route($foreignTable, [], 'POST', ['api_action' => 'gtsapi/export_table', 'id' => $own->id]);
    $after = $readLog();
    $check(!isset($after[$kOwn]), 'контроллер: gtsapi/export_table через свою gtsAPITable — в журнал не попал');
    $check(isset($after[$kForeign]) && $after[$kForeign]['count'] === ($before[$kForeign]['count'] ?? 0) + 1
        && $after[$kForeign]['status'] === 'foreign', "контроллер: через чужую {$foreignTable->table} — в журнале foreign");
    // убрать тестовую строку из журнала (если её не было до теста)
    if (!isset($before[$kForeign]) && isset($after[$kForeign])) {
        unset($after[$kForeign]);
        file_put_contents($log, json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
} else {
    echo "пропуск сквозной проверки: нет sudo-пользователя или подходящей таблицы\n";
}

echo $fail ? "\nОшибок: $fail\n" : "\nВсё хорошо\n";
exit($fail ? 1 : 0);

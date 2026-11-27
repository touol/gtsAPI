<?php
/**
 * Действия настройки gtsAPI (export_/save_ table|rule|select, gen_*, clean_fields) — только администратору.
 * Метод 'gtsapi/...' достижим через любую таблицу, поэтому проверка внутри gtsAPI::handleRequest (1.2.23).
 *
 * Проверяет: не-админ получает отказ на каждое действие, конфиг таблицы не меняется; админ export_table проходит.
 * Ничего не пишет: save_table зовётся с битым JSON (у админа — «Не верный json!», у не-админа — отказ раньше).
 *
 *   php tests/php/admin_only_actions.php [логин-не-админа]
 *   MODX_ROOT — корень сайта, по умолчанию дев vk-24.
 */
define('MODX_API_MODE', true);
require (getenv('MODX_ROOT') ?: 'V:/OSPanel/home/modx.pl/public') . '/index.php';
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

$gtsapi = $modx->getService('gtsapi', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');
$fail = 0;
$check = function ($ok, $msg) use (&$fail) { echo ($ok ? 'OK   ' : 'FAIL ') . $msg . "\n"; if (!$ok) $fail++; };

// не-админ: из аргумента или первый активный пользователь без sudo и не в Administrator
$user = null;
if (!empty($argv[1])) {
    $user = $modx->getObject('modUser', ['username' => $argv[1]]);
} else {
    $q = $modx->newQuery('modUser', ['active' => 1, 'sudo' => 0]);
    $q->sortby('id', 'ASC');
    foreach ($modx->getIterator('modUser', $q) as $u) {
        if (!$u->isMember('Administrator')) { $user = $u; break; }
    }
}
$admin = $modx->getObject('modUser', ['sudo' => 1]) ?: null;
if (!$admin) {
    $q = $modx->newQuery('modUser', ['active' => 1]);
    foreach ($modx->getIterator('modUser', $q) as $u) if ($u->isMember('Administrator')) { $admin = $u; break; }
}
$table = $modx->getObject('gtsAPITable', ['active' => 1]);
if (!$user || !$admin || !$table) { echo "нет не-админа, админа или gtsAPITable\n"; exit(1); }
echo "не-админ: {$user->username}, админ: {$admin->username}, таблица: {$table->table} #{$table->id}\n";

$before = $table->get('properties');
$id = (int)$table->id;
$actions = [
    'export_rule' => ['id' => 1], 'save_rule' => ['id' => 1, 'rule_json' => '{'],
    'export_table' => ['id' => $id], 'save_table' => ['id' => $id, 'rule_json' => '{'],
    'export_select' => ['id' => 1], 'save_select' => ['id' => 1, 'rule_json' => '{'],
    'gen_rules' => [], 'generate_rules' => ['package' => '__none__'],
    'gen_fields' => ['id' => $id], 'clean_fields' => ['id' => 0],
];

$modx->user = $user;
foreach ($actions as $a => $data) {
    $r = $gtsapi->handleRequest($a, $data);
    $check(empty($r['success']) && strpos((string)$r['message'], 'только администратор') !== false,
        "не-админ $a → отказ" . (empty($r['success']) ? '' : ' (ПРОШЁЛ: ' . $r['message'] . ')'));
}
$table = $modx->getObject('gtsAPITable', $id);
$check($table->get('properties') === $before, 'конфиг таблицы не изменился');

$modx->user = $admin;
$r = $gtsapi->handleRequest('export_table', ['id' => $id]);
$check(!empty($r['success']), 'админ export_table → проходит');
$r = $gtsapi->handleRequest('save_table', ['id' => $id, 'rule_json' => '{']);
$check(empty($r['success']) && strpos((string)$r['message'], 'только администратор') === false, 'админ save_table с битым JSON → до проверки JSON доходит');

echo $fail ? "\nОшибок: $fail\n" : "\nВсё хорошо\n";
exit($fail ? 1 : 0);

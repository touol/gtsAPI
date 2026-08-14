<?php
/**
 * Страница админки gtsAPI внутри iframe.
 *
 * Зачем отдельная точка входа. Приложение на PVTables тянет Tailwind и PrimeVue,
 * а тема менеджера MODX — свои сбросы для голых тегов. Оба набора глобальные,
 * и в одном документе они портят друг друга: тема ломает вид таблиц и форм,
 * Tailwind ломает дерево ресурсов и панели менеджера.
 *
 * Дело не в «неудачных» селекторах, а в слоях CSS: PrimeVue и Tailwind собраны
 * в @layer, а любое правило БЕЗ слоя — а тема менеджера именно такая — сильнее
 * любого правила в слое, каким бы точным оно ни было. Победить это правками
 * селекторов нельзя, можно только развести документы. Поэтому iframe.
 *
 * Доступ: только сессия менеджера — страница вызывает сниппеты с параметрами
 * из адресной строки.
 */
define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
/** @var modX $modx */
$modx->initialize('mgr');

header('Content-Type: text/html; charset=UTF-8');
// Страница только для внутреннего окна менеджера
header('X-Frame-Options: SAMEORIGIN');

$fail = function ($message) {
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"></head><body>'
        . '<div style="font:14px/1.4 sans-serif;color:#a00;padding:15px">'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        . '</div></body></html>';
    exit();
};

if (!$modx->user || !$modx->user->hasSessionContext('mgr')) {
    $fail('Нужен вход в менеджер MODX.');
}

$raw = isset($_REQUEST['config']) ? trim((string)$_REQUEST['config']) : '';
if ($raw === '') {
    $fail('Не задан параметр config.');
}
// config — либо JSON, либо имя системной настройки, в которой он лежит
if ($raw[0] !== '{' && $raw[0] !== '[') {
    $setting = (string)$modx->getOption($raw, null, '');
    if ($setting === '') {
        $fail("Системная настройка «{$raw}» пуста или не существует.");
    }
    $raw = trim($setting);
}
$config = json_decode($raw, true);
if (!is_array($config)) {
    $fail('Параметр config не разобран как JSON: ' . json_last_error_msg());
}

$title = 'gtsAPI';
$body = '';
$errors = [];
foreach ($config as $snippet => $properties) {
    if (!is_array($properties)) {
        if ($snippet === 'title' || $snippet === 'pagetitle') {
            $title = (string)$properties;
        }
        continue;
    }
    if (!$modx->getObject('modSnippet', ['name' => $snippet])) {
        $errors[] = "Сниппет «{$snippet}» не найден.";
        continue;
    }
    $body .= $modx->runSnippet($snippet, $properties);
}
if (!$body && !$errors) {
    $errors[] = 'В конфиге нет ни одного блока вида "Сниппет": { … }';
}

// Сниппеты регистрируют стили и скрипты в modX; в обычной странице их
// подставляет шаблон, здесь собираем сами.
$startup = implode("\n", $modx->sjscripts);
$scripts = implode("\n", $modx->jscripts);

?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        /* Своя страница — свои правила. Ни сбросов темы менеджера,
           ни утечки Tailwind наружу: их разделяет граница документа. */
        html, body { margin: 0; padding: 0; background: transparent; }
        /* Документ рамки не прокручивается сам: полоса нужна одна, и она
           у таблицы внутри. Родитель подгоняет высоту рамки под окно. */
        html, body { height: 100%; overflow: hidden; }
    </style>
    <?= $startup ?>
</head>
<body>
<?php foreach ($errors as $error): ?>
    <div style="font:14px/1.4 sans-serif;color:#a00;padding:15px"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endforeach; ?>
<?= $body ?>
<?= $scripts ?>
</body>
</html>

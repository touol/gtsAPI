<?php
/**
 * Убирает мёртвые чанки PVTables из assets/components/gtsapi/js/web/pvtables/.
 *
 * Vite даёт файлам сборки хеш в имени, а установщик MODX копирует файлы поверх
 * и никогда ничего не удаляет. Поэтому в папке оседают index-*.js со всех
 * прошлых сборок: к сентябрю 2026 там лежал 31 файл, из них рабочих — два,
 * остальные 23 МБ никем не использовались.
 *
 * Резолвер называется на zz, потому что build.php подключает резолверы
 * по scandir, то есть по алфавиту: этот обязан отработать ПОСЛЕ file-резолвера,
 * который раскладывает свежую сборку, иначе он вычистит как раз её.
 *
 * Что оставляем: точки входа (pvtables.js, pvtables.cjs, pvtables.css) и всё,
 * до чего от них дотягиваются импорты — транзитивно, потому что index-*.js
 * подгружает html2canvas и jspdf отдельными чанками. Остальное удаляем.
 *
 * @var xPDOTransport $transport
 * @var array $options
 * @var modX $modx
 */
if (!$transport->xpdo) {
    return true;
}

$modx =& $transport->xpdo;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        break;
    default:
        return true;   // при удалении пакета папку сносит сам MODX
}

$dir = MODX_ASSETS_PATH . 'components/gtsapi/js/web/pvtables/';
if (!is_dir($dir)) {
    return true;
}

$entries = ['pvtables.js', 'pvtables.cjs', 'pvtables.css'];

// Ни одной точки входа — значит раскладка прошла не так, как мы думаем.
// В этом случае не удаляем ничего: пустая папка хуже замусоренной.
$found = false;
foreach ($entries as $e) {
    if (is_file($dir . $e)) {
        $found = true;
        break;
    }
}
if (!$found) {
    $modx->log(modX::LOG_LEVEL_WARN,
        '[gtsapi] чистка pvtables пропущена: не найдено ни одной точки входа в ' . $dir);
    return true;
}

// Транзитивное замыкание по импортам: начинаем с точек входа и добираем всё,
// на что они ссылаются, пока список не перестанет расти.
$keep    = array_flip($entries);
$toScan  = $entries;
$scanned = [];

while ($file = array_shift($toScan)) {
    if (isset($scanned[$file])) {
        continue;
    }
    $scanned[$file] = true;

    $path = $dir . $file;
    if (!is_file($path) || substr($file, -4) === '.css') {
        continue;
    }

    $src = file_get_contents($path);
    if ($src === false) {
        continue;
    }

    if (preg_match_all('~\./([A-Za-z0-9._-]+\.(?:js|cjs))~', $src, $m)) {
        foreach (array_unique($m[1]) as $ref) {
            if (!isset($keep[$ref])) {
                $keep[$ref] = true;
                $toScan[]   = $ref;
            }
        }
    }
}

$removed = 0;
$bytes   = 0;

foreach ((array)scandir($dir) as $file) {
    if ($file === '.' || $file === '..' || isset($keep[$file])) {
        continue;
    }
    $path = $dir . $file;
    if (!is_file($path)) {
        continue;
    }
    // Только то, что похоже на выхлоп сборки. Если кто-то положил в папку
    // руками что-то своё, оно останется на месте.
    if (!preg_match('~\.(js|cjs|css|map)$~', $file)) {
        continue;
    }

    $size = filesize($path);
    if (@unlink($path)) {
        $removed++;
        $bytes += $size;
    }
}

if ($removed > 0) {
    $modx->log(modX::LOG_LEVEL_INFO, '[gtsapi] чистка pvtables: удалено ' . $removed
        . ' устаревших файлов, освобождено ' . round($bytes / 1048576, 1) . ' МБ');
}

return true;

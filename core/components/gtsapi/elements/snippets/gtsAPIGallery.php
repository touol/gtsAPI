<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var gtsAPI $gtsAPI */
$gtsAPI = $modx->getService('gtsapi', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');
if (!$gtsAPI) {
    return 'Не удалось загрузить сервис gtsAPI';
}

/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded.');

$extensionsDir = $modx->getOption('extensionsDir', $scriptProperties, 'components/gtsapi/img/mgr/extensions/', true);
$limit = $modx->getOption('limit', $scriptProperties, 0);
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.gtsAPIGallery');

// Получаем parent объект
$parent = $modx->getOption('parent', $scriptProperties, $modx->resource->id);
// Если parent пустой (но передан в параметрах), используем ID текущего ресурса
if (empty($parent)) {
    $parent = $modx->resource->id;
}
$parentClass = $modx->getOption('parentClass', $scriptProperties, 'modResource');
$list = $modx->getOption('list', $scriptProperties, '');

// Условия для выборки файлов
$where = [
    'parent' => $parent,
    'child' => 0, // Только основные файлы, не дочерние превью
];

if (!empty($parentClass)) {
    $where['class'] = $parentClass;
}

if (!empty($list)) {
    $where['list'] = $list;
}

// Фильтр по типу файла
$filetype = $modx->getOption('filetype', $scriptProperties, '');
if (!empty($filetype)) {
    $where['type:IN'] = array_map('trim', explode(',', $filetype));
}

// Показывать только активные файлы
$showInactive = $modx->getOption('showInactive', $scriptProperties, false);
if (empty($showInactive)) {
    $where['active'] = 1;
}

$select = [
    'gtsAPIFile' => '*',
];

// Добавляем пользовательские параметры
foreach (['where'] as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = [
    'class' => 'gtsAPIFile',
    'where' => $where,
    'select' => $select,
    'limit' => $limit,
    'sortby' => '`rank`',
    'sortdir' => 'ASC',
    'fastMode' => false,
    'return' => 'data',
    'nestedChunkPrefix' => 'gtsapi_',
];

if ($scriptProperties['return'] === 'tpl') {
    unset($scriptProperties['return']);
}

// Объединяем все свойства и выполняем запрос
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$rows = $pdoFetch->run();

if ($scriptProperties['return'] === 'sql' || $scriptProperties['return'] === 'json') {
    return $rows;
}

$pdoFetch->addTime('Fetching thumbnails');

// Получаем доступные размеры превью
$resolution = [];
$thumbnailSizes = $modx->getOption('gtsapi.thumbnail_sizes', null, 'small,medium,large');
if (!empty($thumbnailSizes)) {
    $resolution = array_map('trim', explode(',', $thumbnailSizes));
}

// Обработка строк
$files = [];
foreach ($rows as $row) {
    if (isset($row['type']) && in_array($row['type'], ['image', 'jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        // Для изображений ищем дочерние файлы с превью
        $c = $modx->newQuery('gtsAPIFile', [
            'parent' => $row['id'],
            'child' => 1
        ]);
        $c->select('trumb,url');
        $tstart = microtime(true);
        if ($c->prepare() && $c->stmt->execute()) {
            $modx->queryTime += microtime(true) - $tstart;
            $modx->executedQueries++;
            while ($tmp = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($tmp['trumb'])) {
                    $row[$tmp['trumb']] = $tmp['url'];
                }
            }
        }
        
        // Если нет превью, используем оригинальное изображение
        foreach ($resolution as $size) {
            if (empty($row[$size])) {
                $row[$size] = $row['url'];
            }
        }
    } elseif (isset($row['type'])) {
        // Для других типов файлов используем иконки
        $row['thumbnail'] = file_exists(MODX_ASSETS_PATH . $extensionsDir . $row['type'] . '.png')
            ? MODX_ASSETS_URL . $extensionsDir . $row['type'] . '.png'
            : MODX_ASSETS_URL . $extensionsDir . 'other.png';
        
        foreach ($resolution as $size) {
            $row[$size] = $row['thumbnail'];
        }
    }

    // Добавляем дополнительную информацию
    // Форматируем размер файла
    $size = $row['size'];
    if ($size == 0) {
        $row['size_formatted'] = '0 Б';
    } else {
        $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
        $base = log($size, 1024);
        $index = floor($base);
        if ($index >= count($units)) {
            $index = count($units) - 1;
        }
        $formattedSize = round(pow(1024, $base - $index), 2);
        $row['size_formatted'] = $formattedSize . ' ' . $units[$index];
    }
    
    $row['createdon_formatted'] = !empty($row['createdon']) ? strftime('%d.%m.%Y %H:%M', strtotime($row['createdon'])) : '';
    
    $files[] = $row;
}

if ($scriptProperties['return'] === 'data') {
    return $files;
}

$output = $pdoFetch->getChunk($tpl, [
    'files' => $files,
    'scriptProperties' => $scriptProperties
]);

$showLog = $modx->getOption('showLog', $scriptProperties, false);
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="gtsAPIGalleryLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}

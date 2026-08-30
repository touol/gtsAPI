<?php
/**
 * Политики доступа gtsAPI на своих шаблонах.
 *
 * gtsAPIFileAccess — доступ к файлам источников gtsAPI (browser счетов, галереи).
 * Права те же, что у встроенного MediaSource MODX, но это НАША политика на НАШЕМ
 * шаблоне gtsAPIFileTemplate (см. policies.php) — встроенные политики MODX не трогаем.
 * Резолвер mediasource.php вешает её на источники файлов.
 *
 * Ключ массива → templateName (к какому шаблону крепится политика при сборке).
 */
return [
    'gtsAPIFileTemplate' => [
        'name' => 'gtsAPIFileAccess',
        'templateName' => 'gtsAPIFileTemplate',
        'description' => 'Доступ к файлам источников gtsAPI: просмотр, загрузка, управление.',
        'parent' => 0,
        'class' => '',
        'lexicon' => 'permissions',
        'data' => json_encode(array(
            'directory_list'   => true,
            'directory_create' => true,
            'directory_remove' => true,
            'directory_update' => true,
            'directory_chmod'  => true,
            'file_list'        => true,
            'file_view'        => true,
            'file_upload'      => true,
            'file_create'      => true,
            'file_update'      => true,
            'file_remove'      => true,
            'file_unpack'      => true,
        )),
    ],
];

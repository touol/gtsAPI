<?php
/**
 * Шаблон политики доступа к файлам источников gtsAPI.
 *
 * Определяет ПЕРЕЧЕНЬ прав, доступных для управления файлами через файловый API
 * (browser счетов, галереи и т.п.). Права те же, что у встроенного MediaSource
 * MODX, но это НАША политика — встроенные шаблоны MODX не трогаем.
 *
 * Сама политика на этом шаблоне и её привязка к источникам — в accesspolices.php
 * и в резолвере mediasource.php.
 */
return [
    'gtsAPIFileTemplate' => [
        'description' => 'Права доступа к файлам источников gtsAPI: просмотр, загрузка, управление.',
        'template_group_name' => 'Object',
        'lexicon' => 'permissions',
        'permissions' => array(
            // директории
            'directory_list'   => true,
            'directory_create' => true,
            'directory_remove' => true,
            'directory_update' => true,
            'directory_chmod'  => true,
            // файлы
            'file_list'        => true,
            'file_view'        => true,
            'file_upload'      => true,
            'file_create'      => true,
            'file_update'      => true,
            'file_remove'      => true,
            'file_unpack'      => true,
        ),
    ]
];

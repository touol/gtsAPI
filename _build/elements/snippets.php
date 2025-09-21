<?php

return [
    // 'gtsapi' => [
    //     'file' => 'gtsapi',
    //     'description' => '',
    //     'properties' => [
            
    //     ],
    // ],
    'mixedVue' => [
        'file' => 'mixedVue',
        'description' => '',
        'properties' => [
            
        ],
    ],
    'PVTable' => [
        'file' => 'PVTable',
        'description' => '',
        'properties' => [
            
        ],
    ],
    'PVTabs' => [
        'file' => 'PVTabs',
        'description' => '',
        'properties' => [
            
        ],
    ],
    'mixVue' => [
        'file' => 'mixVue',
        'description' => '',
        'properties' => [
            
        ],
    ],
    'gtsAPIGallery' => [
        'file' => 'gtsAPIGallery',
        'description' => 'Сниппет для отображения галереи файлов из таблицы gtsAPIFile',
        'properties' => [
            'parent' => [
                'type' => 'numberfield',
                'value' => '',
                'desc' => 'ID родительского объекта (по умолчанию ID текущего ресурса)',
            ],
            'parentClass' => [
                'type' => 'textfield',
                'value' => 'modResource',
                'desc' => 'Класс родительского объекта',
            ],
            'list' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Список файлов (дополнительная группировка)',
            ],
            'filetype' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Фильтр по типу файла (через запятую)',
            ],
            'limit' => [
                'type' => 'numberfield',
                'value' => 0,
                'desc' => 'Лимит количества файлов (0 - без ограничений)',
            ],
            'tpl' => [
                'type' => 'textfield',
                'value' => 'tpl.gtsAPIGallery',
                'desc' => 'Шаблон для вывода галереи',
            ],
            'showInactive' => [
                'type' => 'combo-boolean',
                'value' => false,
                'desc' => 'Показывать неактивные файлы',
            ],
            'showLog' => [
                'type' => 'combo-boolean',
                'value' => false,
                'desc' => 'Показывать лог выполнения (только для менеджеров)',
            ],
            'toPlaceholder' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Сохранить результат в плейсхолдер вместо вывода',
            ],
        ],
    ],
];

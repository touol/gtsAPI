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
    'gtsTheme' => [
        'file' => 'gtsTheme',
        'description' => 'Переключатель схемы (солнце/луна) и темы gtsERP. Ставит data-gts-scheme и data-gts-theme на <html>.',
        'properties' => [
            'themes' => [
                'type' => 'combo-boolean',
                'value' => false,
                'desc' => 'Показывать выбор темы. По умолчанию только схема светлая/тёмная',
            ],
            'themesPath' => [
                'type' => 'textfield',
                'value' => 'components/gtsapi/themes/',
                'desc' => 'Папки с темами, через запятую, относительно assets. Каждый *.css в них — тема, имя = имя файла',
            ],
            'themeFiles' => [
                'type' => 'textarea',
                'value' => '',
                'desc' => 'Явные файлы тем, через запятую: путь к css либо "имя:путь". Перекрывают одноимённые из папок',
            ],
            'themeList' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Какие темы показать и в каком порядке. Пусто — все найденные',
            ],
            'loadCss' => [
                'type' => 'combo-boolean',
                'value' => true,
                'desc' => 'Подключать CSS тем. 0 — если это уже делает шаблон',
            ],
            'defaultTheme' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Тема по умолчанию. Пусто — берётся из настройки gtsapi_theme_default',
            ],
            'defaultScheme' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Схема по умолчанию: auto, light или dark. Пусто — из gtsapi_scheme_default',
            ],
            'storageKey' => [
                'type' => 'textfield',
                'value' => 'gtsTheme',
                'desc' => 'Ключ localStorage, в котором хранится выбор пользователя',
            ],
            'toPlaceholder' => [
                'type' => 'textfield',
                'value' => '',
                'desc' => 'Сохранить результат в плейсхолдер вместо вывода',
            ],
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

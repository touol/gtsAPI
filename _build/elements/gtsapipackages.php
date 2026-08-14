<?php
/**
 * Таблицы самого gtsAPI на синтаксисе gtsAPI.
 *
 * Раньше админка gtsAPI описывалась ~1000 строк JSON в системной настройке
 * gtsapi_admin на синтаксисе getTables. Компонент, который задаёт формат
 * описания таблиц, сам этим форматом не пользовался.
 *
 * Сборка кладёт это в core/components/gtsapi/gtsapipackages.json, а резолвер
 * заводит из него gtsAPIPackage + gtsAPITable — ровно так же, как у любого
 * другого пакета.
 *
 * ⚠️ version у таблицы обязателен: резолвер перезаписывает существующую запись
 * ТОЛЬКО если version в конфиге больше, чем в базе. Поправил конфиг — подними
 * version, иначе правка не доедет.
 */

// Поле id показываем везде одинаково
$id = ['label' => 'ID', 'type' => 'view'];

// Свойства-редакторы: JSON-поля правятся в textarea, санитайзер к ним не применяется,
// иначе MODX выкусывает из JSON кавычки и скобки.
$json = ['type' => 'textarea', 'skip_sanitize' => 1];

return [
    'gtsapi' => [
        'name' => 'gtsapi',
        'gtsAPITables' => [

            // ── Таблицы АПИ ───────────────────────────────────────────────
            'gtsAPITable' => [
                'table' => 'gtsAPITable',
                'class' => 'gtsAPITable',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        'copy' => [],
                        'excel_export' => [],
                        // Действие вида «пакет/метод» уходит в модель, объявленную
                        // в loadModels: gtsAPI::handleRequest('export_table', …)
                        'export_table' => [
                            'action' => 'gtsapi/export_table',
                            'row' => true,
                            'icon' => 'pi pi-download',
                            'label' => 'Экспорт-импорт таблицы',
                            'class' => ' p-button-info',
                        ],
                        'gen_fields' => [
                            'action' => 'gtsapi/gen_fields',
                            'row' => true,
                            'icon' => 'pi pi-bars',
                            'label' => 'Сгенерировать поля',
                            'confirm' => 'Сгенерировать поля таблицы по её классу?',
                            'class' => ' p-button-info',
                        ],
                        // Раскрывающаяся подтаблица классов дерева
                        'subtables' => [
                            'gtsAPIUniTreeClass' => [
                                'label' => 'Классы дерева',
                                'icon' => 'pi pi-sitemap',
                                'where' => ['table_id' => 'id'],
                            ],
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPITable.table' => 'ASC']],
                    'fields' => [
                        'id' => $id,
                        'package_id' => ['label' => 'Пакет', 'type' => 'autocomplete', 'table' => 'gtsAPIPackage'],
                        'install_package' => ['label' => 'Пакет установки', 'type' => 'text'],
                        'table' => ['label' => 'Имя таблицы', 'type' => 'text'],
                        'class' => ['label' => 'Класс таблицы', 'type' => 'text'],
                        'type' => ['label' => 'Тип', 'type' => 'select'],
                        'autocomplete_field' => ['label' => 'Поле автокомплита', 'type' => 'text'],
                        'authenticated' => ['label' => 'Только авторизованным', 'type' => 'boolean', 'default' => 1],
                        'groups' => ['label' => 'Группы пользователей', 'type' => 'textarea', 'modal_only' => 1],
                        'permissions' => ['label' => 'Разрешения MODX', 'type' => 'textarea', 'modal_only' => 1],
                        'properties' => array_merge($json, ['label' => 'Свойства', 'modal_only' => 1]),
                        'version' => ['label' => 'Версия', 'type' => 'number', 'default' => 0],
                        'active' => ['label' => 'Активно', 'type' => 'boolean', 'default' => 1],
                    ],
                ],
            ],

            // ── Пакеты MODX ───────────────────────────────────────────────
            'gtsAPIPackage' => [
                'table' => 'gtsAPIPackage',
                'class' => 'gtsAPIPackage',
                // Имя пакета — то, что показывает автокомплит в таблицах АПИ
                'autocomplete_field' => 'package_id',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                    'query' => ['sortby' => ['gtsAPIPackage.name' => 'ASC']],
                    'autocomplete' => ['tpl' => '{$name}', 'limit' => 0],
                    'fields' => [
                        'id' => $id,
                        'name' => ['label' => 'Имя', 'type' => 'text'],
                    ],
                ],
            ],

            // ── Селекты ───────────────────────────────────────────────────
            'gtsAPISelect' => [
                'table' => 'gtsAPISelect',
                'class' => 'gtsAPISelect',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        'copy' => [],
                        'export_select' => [
                            'action' => 'gtsapi/export_select',
                            'row' => true,
                            'icon' => 'pi pi-download',
                            'label' => 'Экспорт-импорт селекта',
                            'class' => ' p-button-info',
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPISelect.field' => 'ASC']],
                    'fields' => [
                        'id' => $id,
                        'field' => ['label' => 'Имя поля', 'type' => 'text'],
                        'rows' => array_merge($json, ['label' => 'Опции в JSON']),
                        'active' => ['label' => 'Активно', 'type' => 'boolean', 'default' => 1],
                    ],
                ],
            ],

            // ── Таблицы допполей ──────────────────────────────────────────
            'gtsAPIFieldTable' => [
                'table' => 'gtsAPIFieldTable',
                'class' => 'gtsAPIFieldTable',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        // Какие группы допполей привязаны к этой таблице
                        'subtables' => [
                            'gtsAPIFieldGroupTableLink' => [
                                'label' => 'Группы полей',
                                'icon' => 'pi pi-link',
                                'where' => ['table_field_id' => 'id'],
                            ],
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPIFieldTable.name_table' => 'ASC']],
                    'fields' => [
                        'id' => $id,
                        'name_table' => ['label' => 'Таблица для допполей', 'type' => 'text'],
                        'add_base' => ['label' => 'Добавить в базу', 'type' => 'boolean', 'default' => 0],
                        'add_table' => ['label' => 'Добавить в конфиг таблицы', 'type' => 'boolean', 'default' => 0],
                        'only_text' => ['label' => 'Все поля текстовые', 'type' => 'boolean', 'default' => 0],
                        'after_field' => ['label' => 'После поля', 'type' => 'text'],
                        'desc' => ['label' => 'Описание', 'type' => 'textarea', 'modal_only' => 1],
                    ],
                ],
            ],

            // ── Группы допполей ───────────────────────────────────────────
            'gtsAPIFieldGroup' => [
                'table' => 'gtsAPIFieldGroup',
                'class' => 'gtsAPIFieldGroup',
                // Группа подставляется автокомплитом в связке с таблицей допполей
                'autocomplete_field' => 'group_field_id',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        // Поля, входящие в группу. Вариант для gtsShop
                        // (gtsAPIFieldShopGroupLink) здесь не объявляем: на сайте
                        // без gtsShop такой таблицы нет, её добавляет сам gtsShop.
                        'subtables' => [
                            'gtsAPIFieldGroupLink' => [
                                'label' => 'Поля группы',
                                'icon' => 'pi pi-link',
                                'where' => ['group_field_id' => 'id'],
                            ],
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPIFieldGroup.name' => 'ASC']],
                    'autocomplete' => ['tpl' => '{$name}', 'limit' => 0],
                    'fields' => [
                        'id' => $id,
                        'name' => ['label' => 'Имя группы', 'type' => 'text'],
                        'from_table' => ['label' => 'Таблица допполей', 'type' => 'text', 'default' => 'gtsAPIField'],
                        'link_group_table' => ['label' => 'Таблица связи', 'type' => 'text', 'default' => 'gtsAPIFieldGroupLink'],
                        'all' => ['label' => 'Все поля таблицы', 'type' => 'boolean', 'default' => 0],
                    ],
                ],
            ],

            // ── Допполя ───────────────────────────────────────────────────
            'gtsAPIField' => [
                'table' => 'gtsAPIField',
                'class' => 'gtsAPIField',
                // Поле подставляется автокомплитом в связке «группа — поле»
                'autocomplete_field' => 'field_id',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        'excel_export' => [],
                        // Кнопка в шапке: сносит колонки полей, удалённых из справочника.
                        // head без row — операция про всю базу, а не про строку.
                        'clean_fields' => [
                            'action' => 'gtsapi/clean_fields',
                            'head' => true,
                            'icon' => 'pi pi-eraser',
                            'label' => 'Снести колонки удалённых полей',
                            'confirm' => 'Снести из базы колонки допполей, которых больше нет в справочнике? Данные в этих колонках будут потеряны.',
                            'class' => ' p-button-warning',
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPIField.title' => 'ASC']],
                    'autocomplete' => ['tpl' => '{$title}', 'limit' => 0],
                    'fields' => [
                        'id' => $id,
                        'title' => ['label' => 'Название', 'type' => 'text'],
                        'name' => ['label' => 'Имя поля', 'type' => 'text'],
                        'dbtype' => ['label' => 'Тип в базе', 'type' => 'select'],
                        'dbprecision' => ['label' => 'Размер', 'type' => 'text'],
                        'dbnull' => ['label' => 'NULL', 'type' => 'boolean'],
                        'dbdefault' => ['label' => 'По умолчанию в базе', 'type' => 'text', 'default' => 'none'],
                        'dbindex' => ['label' => 'Индекс', 'type' => 'select'],
                        'rank' => ['label' => 'Порядок', 'type' => 'number'],
                        'default' => ['label' => 'По умолчанию', 'type' => 'text'],
                        'field_type' => ['label' => 'Тип поля', 'type' => 'select'],
                        'after_field' => ['label' => 'После поля', 'type' => 'text'],
                        'modal_only' => ['label' => 'Только в форме', 'type' => 'boolean', 'default' => 0],
                        'table_only' => ['label' => 'Только в таблице', 'type' => 'boolean', 'default' => 0],
                        'gtsapi_config' => array_merge($json, ['label' => 'gtsapi_config', 'modal_only' => 1]),
                        'properties' => array_merge($json, ['label' => 'Допсвойства в JSON', 'modal_only' => 1]),
                        'description' => ['label' => 'Описание', 'type' => 'textarea', 'modal_only' => 1],
                    ],
                ],
            ],

            // ── Лог действий ──────────────────────────────────────────────
            'gtsAPILog' => [
                'table' => 'gtsAPILog',
                'class' => 'gtsAPILog',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 100,
                    // Лог только читают: правка записи лога — это подделка лога
                    'actions' => [
                        'read' => [],
                        'excel_export' => [],
                    ],
                    'query' => ['sortby' => ['gtsAPILog.created_at' => 'DESC']],
                    'fields' => [
                        'id' => $id,
                        'created_at' => ['label' => 'Дата', 'type' => 'datetime', 'readonly' => 1],
                        'user_id' => ['label' => 'Пользователь', 'type' => 'number', 'readonly' => 1],
                        'log_table' => ['label' => 'Таблица', 'type' => 'text', 'readonly' => 1],
                        'log_action' => ['label' => 'Действие', 'type' => 'text', 'readonly' => 1],
                        'object_id' => ['label' => 'ID объекта', 'type' => 'number', 'readonly' => 1],
                        'data_before' => ['label' => 'До', 'type' => 'textarea', 'readonly' => 1, 'modal_only' => 1],
                        'data_after' => ['label' => 'После', 'type' => 'textarea', 'readonly' => 1, 'modal_only' => 1],
                    ],
                ],
            ],

            // ── Правила АПИ ───────────────────────────────────────────────
            'gtsAPIRule' => [
                'table' => 'gtsAPIRule',
                'class' => 'gtsAPIRule',
                // Правило подставляется автокомплитом в действиях правил
                'autocomplete_field' => 'rule_id',
                'version' => 2,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        // Копия правила тянет за собой действия — алиас связи из схемы xPDO
                        'copy' => ['child' => ['many' => ['gtsAPIAction' => 'gtsAPIAction']]],
                        'export_rule' => [
                            'action' => 'gtsapi/export_rule',
                            'row' => true,
                            'icon' => 'pi pi-download',
                            'label' => 'Экспорт-импорт правила',
                            'class' => ' p-button-info',
                        ],
                        'gen_rules' => [
                            'action' => 'gtsapi/gen_rules',
                            'head' => true,
                            'icon' => 'pi pi-bars',
                            'label' => 'Сгенерировать правила',
                            'confirm' => 'Сгенерировать правила для выбранных записей?',
                            'class' => ' p-button-info',
                        ],
                        'subtables' => [
                            'gtsAPIAction' => [
                                'label' => 'Действия правила',
                                'icon' => 'pi pi-bolt',
                                'where' => ['rule_id' => 'id'],
                            ],
                        ],
                    ],
                    'query' => ['sortby' => ['gtsAPIRule.point' => 'ASC']],
                    'autocomplete' => ['tpl' => '{$point}', 'limit' => 0],
                    'fields' => [
                        'id' => $id,
                        'point' => ['label' => 'Точка монтирования', 'type' => 'text'],
                        'description' => ['label' => 'Описание', 'type' => 'textarea'],
                        'packages' => ['label' => 'Подгружаемые пакеты', 'type' => 'text', 'modal_only' => 1],
                        'class' => ['label' => 'Класс таблицы', 'type' => 'text'],
                        'pdoTools' => array_merge($json, ['label' => 'pdoTools', 'modal_only' => 1]),
                        'controller_class' => ['label' => 'Контроллер', 'type' => 'text', 'modal_only' => 1],
                        'controller_path' => ['label' => 'Путь контроллера', 'type' => 'text', 'modal_only' => 1],
                        'authenticated' => ['label' => 'Только авторизованным', 'type' => 'boolean', 'default' => 1],
                        'groups' => ['label' => 'Группы пользователей', 'type' => 'textarea', 'modal_only' => 1],
                        'permitions' => ['label' => 'Разрешения MODX', 'type' => 'textarea', 'modal_only' => 1],
                        'active' => ['label' => 'Активно', 'type' => 'boolean', 'default' => 1],
                    ],
                ],
            ],

            // ── Классы дерева (подтаблица «Таблиц АПИ») ───────────────────
            'gtsAPIUniTreeClass' => [
                'table' => 'gtsAPIUniTreeClass',
                'class' => 'gtsAPIUniTreeClass',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                    'fields' => [
                        'id' => $id,
                        // Заполняется подтаблицей от родительской строки
                        'table_id' => ['label' => 'Дерево АПИ', 'type' => 'hidden'],
                        'table' => ['label' => 'Таблица', 'type' => 'text'],
                        'class' => ['label' => 'Класс', 'type' => 'text'],
                        'extended_modresource' => ['label' => 'Расширяет modResource', 'type' => 'boolean', 'default' => 0],
                        'title_field' => ['label' => 'Поле заголовка', 'type' => 'text'],
                        'svg' => ['label' => 'svg', 'type' => 'textarea', 'modal_only' => 1],
                    ],
                ],
            ],

            // ── Связка «таблица допполей — группа» ────────────────────────
            'gtsAPIFieldGroupTableLink' => [
                'table' => 'gtsAPIFieldGroupTableLink',
                'class' => 'gtsAPIFieldGroupTableLink',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                    'fields' => [
                        'id' => $id,
                        'table_field_id' => ['label' => 'Таблица допполей', 'type' => 'hidden'],
                        'group_field_id' => ['label' => 'Группа полей', 'type' => 'autocomplete', 'table' => 'gtsAPIFieldGroup'],
                        'field_class' => ['label' => 'Класс поля', 'type' => 'text'],
                    ],
                ],
            ],

            // ── Связка «группа — поле» ────────────────────────────────────
            'gtsAPIFieldGroupLink' => [
                'table' => 'gtsAPIFieldGroupLink',
                'class' => 'gtsAPIFieldGroupLink',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                    'fields' => [
                        'id' => $id,
                        'group_field_id' => ['label' => 'Группа полей', 'type' => 'hidden'],
                        'field_id' => ['label' => 'Поле', 'type' => 'autocomplete', 'table' => 'gtsAPIField'],
                    ],
                ],
            ],

            // ── Действия правил ───────────────────────────────────────────
            'gtsAPIAction' => [
                'table' => 'gtsAPIAction',
                'class' => 'gtsAPIAction',
                'version' => 1,
                'type' => 1,
                'authenticated' => true,
                'groups' => 'Administrator',
                'active' => true,
                'properties' => [
                    'loadModels' => 'gtsapi',
                    'limit' => 50,
                    'actions' => [
                        'read' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                    ],
                    'query' => ['sortby' => ['gtsAPIAction.id' => 'ASC']],
                    'fields' => [
                        'id' => $id,
                        'rule_id' => ['label' => 'Правило', 'type' => 'autocomplete', 'table' => 'gtsAPIRule'],
                        'gtsaction' => ['label' => 'Действие', 'type' => 'text'],
                        'processor' => ['label' => 'Процессор', 'type' => 'text'],
                        'authenticated' => ['label' => 'Только авторизованным', 'type' => 'boolean', 'default' => 1],
                        'skip_sanitize' => ['label' => 'Без санитайза', 'type' => 'boolean', 'default' => 0],
                        'groups' => ['label' => 'Группы пользователей', 'type' => 'textarea', 'modal_only' => 1],
                        'permitions' => ['label' => 'Разрешения MODX', 'type' => 'textarea', 'modal_only' => 1],
                        'active' => ['label' => 'Активно', 'type' => 'boolean', 'default' => 1],
                    ],
                ],
            ],
        ],
    ],
];

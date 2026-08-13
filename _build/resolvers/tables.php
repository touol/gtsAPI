<?php
/**
 * Сверка колонки в базе с описанием поля в схеме.
 *
 * Зачем: резолвер гнал ALTER TABLE по КАЖДОМУ полю КАЖДОЙ таблицы схемы, даже когда
 * менять нечего. На gtsShop это сотни перестроек таблиц на одну установку — отсюда
 * и «пакеты ставятся медленно». Теперь ALTER только при реальном расхождении.
 *
 * Сверка повторяет xPDOManager_mysql::getColumnDef(): тип с точностью и unsigned,
 * NULL, DEFAULT, extra (auto_increment). Чего не хватает для сравнения — считаем
 * расхождением и даём ALTER отработать, как раньше.
 */
if (!function_exists('gts_column_matches')) {
    function gts_column_matches($column, $meta)
    {
        if (empty($column) || empty($meta) || empty($meta['dbtype'])) return false;

        // Тип из базы: «int(10) unsigned» → база «int», точность «10», атрибуты «unsigned»
        if (!preg_match('/^([a-z]+)(?:\s*\(([^)]*)\))?\s*(.*)$/i', $column['Type'], $m)) return false;
        $curType = strtolower(trim($m[1]));
        $curPrec = isset($m[2]) ? trim($m[2]) : '';
        $curAttr = strtolower(trim($m[3]));

        // Синонимы xPDO: в схеме INTEGER, в MySQL int
        $alias = ['integer' => 'int', 'boolean' => 'tinyint', 'bool' => 'tinyint', 'numeric' => 'decimal', 'dec' => 'decimal'];
        $type = strtolower(trim($meta['dbtype']));
        if (isset($alias[$type])) $type = $alias[$type];
        if ($curType !== $type) return false;

        // Точность сверяем, только если она задана в схеме: MySQL сам дописывает
        // ширину отображения (INTEGER → int(10)), это не расхождение
        // «0» тоже считаем незаданной: схема иногда пишет precision=0 для text,
        // и xPDO собирает из этого невалидный TEXT(0) — такой ALTER всё равно падает
        $prec = isset($meta['precision']) ? trim((string)$meta['precision']) : '';
        if ($prec !== '' && $prec !== '0' && $prec !== $curPrec) return false;

        $attr = !empty($meta['attributes']) ? strtolower(trim($meta['attributes'])) : '';
        if ($attr !== $curAttr) return false;

        $notNull = !isset($meta['null']) ? false : ($meta['null'] === 'false' || empty($meta['null']));
        if (($column['Null'] === 'NO') !== $notNull) return false;

        $isLob = preg_match('/(text|blob)/i', $meta['dbtype']);
        $default = (isset($meta['default']) && !$isLob) ? $meta['default'] : null;
        if ($default !== null && strtoupper((string)$default) === 'NULL') $default = null;
        $current = $column['Default'];
        // AUTO_INCREMENT-колонке дефолт не ставится — MySQL вернёт null, схема молчит
        if (($default === null) !== ($current === null)) return false;
        if ($default !== null) {
            // Числа сравниваем как числа: схема пишет «0», MySQL для decimal(12,2) — «0.00»
            if (is_numeric($default) && is_numeric($current)) {
                if ((float)$default != (float)$current) return false;
            } elseif ((string)$default !== (string)$current) {
                return false;
            }
        }

        $extra = '';
        if (isset($meta['index']) && $meta['index'] == 'pk'
            && isset($meta['generated']) && $meta['generated'] == 'native') {
            $extra = 'auto_increment';
        }
        if ($extra === '' && !empty($meta['extra'])) $extra = strtolower(trim($meta['extra']));
        if (strtolower(trim($column['Extra'])) !== $extra) return false;

        return true;
    }
}
/**
 * Перечитать карту класса с диска, сохранив динамические доп. поля.
 *
 * Зачем: файловый резолвер уже разложил свежие файлы модели, но карта класса
 * закреплена в памяти с начала процесса — новых полей схемы в ней нет, резолвер их
 * «не видит» и колонки не создаёт. Отсюда ритуал «ставить пакет дважды».
 * loadClass тут не помогает: файлы модели подключаются через include_once, второй
 * раз он их не прочитает. Поэтому читаем файл карты напрямую.
 *
 * ⚠️ Карту НЕ заменяем, только доливаем недостающие поля: во-первых, xPDO после
 * подмены перестаёт подключать <класс>_mysql, во-вторых, в карте живут динамические
 * доп. поля gtsAPI (их домёрдживает плагин на OnMODXInit) — потеряв их, резолвер
 * счёл бы их колонки лишними и снёс. Так уже терялись данные.
 */
if (!function_exists('gts_reload_map')) {
    function gts_reload_map($modx, $class, $package)
    {
        $file = MODX_CORE_PATH . 'components/' . strtolower($package) . '/model/'
            . strtolower($package) . '/mysql/' . strtolower($class) . '.map.inc.php';
        if (!is_file($file)) return false;

        $xpdo_meta_map = array();
        include $file;                       // именно include: include_once уже отработал
        if (empty($xpdo_meta_map[$class]) || empty($xpdo_meta_map[$class]['fields'])) return false;

        // Только ДОЛИВАЕМ недостающее. Заменять карту целиком нельзя: xPDO считает класс
        // уже загруженным и перестаёт подключать <класс>_mysql — установка падает
        // «Class gtsAPIRule_mysql not found».
        $fresh = $xpdo_meta_map[$class];
        if (!isset($modx->map[$class])) return false;
        $added = 0;
        foreach (array('fields', 'fieldMeta', 'indexes') as $k) {
            if (empty($fresh[$k]) || !is_array($fresh[$k])) continue;
            foreach ($fresh[$k] as $name => $v) {
                if (!isset($modx->map[$class][$k][$name])) {
                    $modx->map[$class][$k][$name] = $v;
                    if ($k === 'fields') $added++;
                }
            }
        }

        return $added;
    }
}

/**
 * Колонки, зарегистрированные как динамические доп. поля gtsAPI для этого класса.
 * Страховка: такие колонки резолвер не удаляет НИКОГДА, даже если карта модели
 * оказалась неполной. Именно на этом месте когда-то «слетели полбазы».
 */
if (!function_exists('gts_dynamic_columns')) {
    function gts_dynamic_columns($modx, $class)
    {
        static $cache = array();
        if (isset($cache[$class])) return $cache[$class];
        $out = array();
        try {
            $modx->addPackage('gtsapi', MODX_CORE_PATH . 'components/gtsapi/model/');
            // Прямой SQL, а не xPDOQuery: резолвер работает во время установки, когда
            // модель gtsAPI может быть недогружена, и построитель запросов падает
            $sql = 'SELECT f.name'
                . ' FROM ' . $modx->getTableName('gtsAPIField') . ' f'
                . ' JOIN ' . $modx->getTableName('gtsAPIFieldGroupLink') . ' l ON l.field_id = f.id'
                . ' JOIN ' . $modx->getTableName('gtsAPIFieldGroupTableLink') . ' tl ON tl.group_field_id = l.group_field_id'
                . ' JOIN ' . $modx->getTableName('gtsAPIFieldTable') . ' ft ON ft.id = tl.table_field_id'
                . ' JOIN ' . $modx->getTableName('gtsAPITable') . ' t ON t.`table` = ft.name_table'
                . ' WHERE t.class = ? OR t.`table` = ?';
            $stmt = $modx->prepare($sql);
            if ($stmt && $stmt->execute(array($class, $class))) {
                foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
                    if ($name !== '') $out[$name] = $name;
                }
            }
        } catch (Exception $e) {
        }

        return $cache[$class] = $out;
    }
}

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->addPackage($options['namespace'], MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/');
            //$modx->addExtensionPackage($options['namespace'],  MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/');
            $manager = $modx->getManager();
            $objects = [];
            $inherited = [];
            $parentOf = [];
            $ownFields = [];
            $schemaFile = MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/schema/'.$options['namespace'].'.mysql.schema.xml';
            if (is_file($schemaFile)) {
                $schema = new SimpleXMLElement($schemaFile, 0, true);
                if (isset($schema->object)) {
                    foreach ($schema->object as $obj) {
                        $class = (string)$obj['class'];
                        $objects[] = $class;
                        // Класс, наследующий ЧУЖОЙ класс (gsProduct extends modResource),
                        // сидит в чужой таблице: modx_site_content принадлежит MODX, а не нам.
                        // Такой таблицей мы управлять не вправе — только доложить свои колонки.
                        $extends = (string)$obj['extends'];
                        $inherited[$class] = $extends !== '' && !in_array($extends, ['xPDOObject', 'xPDOSimpleObject']);
                        $parentOf[$class] = $extends;
                        $ownFields[$class] = [];
                        foreach ($obj->field as $f) {
                            $ownFields[$class][(string)$f['key']] = (string)$f['key'];
                        }
                    }
                }
                unset($schema);
            }
            $reloadPackage = $options['namespace'];
            // Свежие файлы модели уже на месте — перечитываем карту, иначе новые поля
            // схемы не будут видны до ВТОРОЙ установки пакета
            foreach ($objects as $class) {
                gts_reload_map($modx, $class, $reloadPackage);
            }
            foreach ($objects as $class) {
                $table = $modx->getTableName($class);
                $sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
                $stmt = $modx->prepare($sql);
                $newTable = true;
                if ($stmt->execute() && $stmt->fetchAll()) {
                    $newTable = false;
                }
                // Чужая таблица, которой ещё нет: создавать её не наше дело
                if ($newTable && !empty($inherited[$class])) {
                    continue;
                }
                // If the table is just created
                if ($newTable) {
                    $manager->createObjectContainer($class);
                } else {
                    // If the table exists
                    // 1. Operate with tables
                    $tableFields = [];
                    $c = $modx->prepare("SHOW COLUMNS IN {$modx->getTableName($class)}");
                    $c->execute();
                    $columns = [];
                    while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
                        $tableFields[$cl['Field']] = $cl['Field'];
                        $columns[$cl['Field']] = $cl;
                    }
                    $fieldMeta = $modx->getFieldMeta($class, true);
                    // У наследника чужого класса своими считаем только те поля, которых нет
                    // у родителя. class_key gsProduct/gsCategory переопределяют ради дефолта
                    // на уровне xPDO — но колонка-то родительская, одна на все ресурсы, и
                    // наследники вечно перебивали друг другу её DEFAULT в modx_site_content.
                    $syncFields = $modx->getFields($class);
                    if (!empty($inherited[$class])) {
                        $syncFields = array_diff_key(
                            array_intersect_key($syncFields, $ownFields[$class]),
                            $modx->getFields($parentOf[$class])
                        );
                    }
                    foreach ($syncFields as $field => $v) {
                        if (in_array($field, $tableFields)) {
                            unset($tableFields[$field]);
                            // ALTER только если колонка расходится со схемой
                            if (!gts_column_matches($columns[$field],
                                isset($fieldMeta[$field]) ? $fieldMeta[$field] : null)) {
                                $manager->alterField($class, $field);
                            }
                        } else {
                            $manager->addField($class, $field);
                        }
                    }
                    // Лишние колонки сносим только в своей таблице и никогда — динамические
                    // доп. поля gtsAPI: их в файлах модели нет, и один сбой карты стоил бы данных
                    $dynamicCols = gts_dynamic_columns($modx, $class);
                    // Ключевые колонки не сносим НИКОГДА, что бы ни лежало в карте модели.
                    // Стоило один раз испортить карту — и resolver снёс `id` у 11 таблиц.
                    $keyCols = array('id' => 'id');
                    if ($pk = $modx->getPK($class)) {
                        foreach ((array)$pk as $pkCol) $keyCols[$pkCol] = $pkCol;
                    }
                    if (empty($inherited[$class])) {
                        foreach ($tableFields as $field) {
                            if (isset($keyCols[$field]) || isset($dynamicCols[$field])) continue;
                            $manager->removeField($class, $field);
                        }
                    }
                    // 2. Operate with indexes (в чужой таблице индексы не наши)
                    if (!empty($inherited[$class])) continue;
                    $indexes = [];
                    $c = $modx->prepare("SHOW INDEX FROM {$modx->getTableName($class)}");
                    $c->execute();
                    while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
                        $name = $row['Key_name'];
                        if (!isset($indexes[$name])) {
                            $indexes[$name] = [$row['Column_name']];
                        } else {
                            $indexes[$name][] = $row['Column_name'];
                        }
                    }
                    foreach ($indexes as $name => $values) {
                        sort($values);
                        $indexes[$name] = implode(':', $values);
                    }
                    $map = $modx->getIndexMeta($class);
                    // Remove old indexes
                    foreach ($indexes as $key => $index) {
                        if (!isset($map[$key])) {
                            if ($manager->removeIndex($class, $key)) {
                                $modx->log(modX::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
                            }
                        }
                    }
                    // Add or alter existing
                    foreach ($map as $key => $index) {
                        ksort($index['columns']);
                        $index = implode(':', array_keys($index['columns']));
                        if (!isset($indexes[$key])) {
                            if ($manager->addIndex($class, $key)) {
                                $modx->log(modX::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
                            }
                        } else {
                            if ($index != $indexes[$key]) {
                                if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                    $modx->log(modX::LOG_LEVEL_INFO,
                                        "Updated index \"{$key}\" of the table \"{$class}\""
                                    );
                                }
                            }
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $rules = [
            [
                'point' => 'security/login',
                'controller_class'=>'securityAPIController',
                'controller_path'=>'[[+core_path]]components/gtsapi/api_controllers/security.class.php',
                'active'=>1,
            ],
            [
                'point' => 'security/logout',
                'controller_class'=>'securityAPIController',
                'controller_path'=>'[[+core_path]]components/gtsapi/api_controllers/security.class.php',
                'active'=>1,
            ],
            [
                'point' => 'package',
                'controller_class'=>'packageAPIController',
                'controller_path'=>'[[+core_path]]components/gtsapi/api_controllers/package.class.php',
                'authenticated'=>1,
                'groups'=>'Administrator',
                'active'=>1,
            ],
            [
                'point' => 'files',
                'controller_class'=>'filesAPIController',
                'controller_path'=>'[[+core_path]]components/gtsapi/api_controllers/files.class.php',
                'authenticated'=>0,
                'groups'=>'',
                'active'=>1,
            ],
            [
                'point' => 'file-gallery',
                'controller_class'=>'fileGalleryAPIController',
                'controller_path'=>'[[+core_path]]components/gtsapi/api_controllers/filegallery.class.php',
                'authenticated'=>1,
                'groups'=>'',
                'permitions'=>'',
                'active'=>1,
            ],
        ];
        foreach($rules as $t){
            if(!$gtsAPIRule = $modx->getObject("gtsAPIRule",['point'=>$t['point']])){
                if($gtsAPIRule = $modx->newObject("gtsAPIRule",$t)){
                    $gtsAPIRule->save();
                }
            }
        }
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}
    

return true;

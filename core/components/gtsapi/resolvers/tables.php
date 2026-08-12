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
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    // $modx->log(1,"xPDOTransport {$options['namespace']}");
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->addPackage($options['namespace'], MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/');
            $manager = $modx->getManager();
            $objects = [];
            $schemaFile = MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/schema/'.$options['namespace'].'.mysql.schema.xml';
            if (is_file($schemaFile)) {
                $schema = new SimpleXMLElement($schemaFile, 0, true);
                if (isset($schema->object)) {
                    foreach ($schema->object as $obj) {
                        $objects[] = (string)$obj['class'];
                    }
                }
                unset($schema);
            }
            foreach ($objects as $class) {
                $table = $modx->getTableName($class);
                $sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
                $stmt = $modx->prepare($sql);
                $newTable = true;
                if ($stmt->execute() && $stmt->fetchAll()) {
                    $newTable = false;
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
                    foreach ($modx->getFields($class) as $field => $v) {
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
                    foreach ($tableFields as $field) {
                        $manager->removeField($class, $field);
                    }
                    // 2. Operate with indexes
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
 

return true;
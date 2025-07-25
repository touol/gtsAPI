<?php
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
                    while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
                        $tableFields[$cl['Field']] = $cl['Field'];
                    }
                    foreach ($modx->getFields($class) as $field => $v) {
                        if (in_array($field, $tableFields)) {
                            unset($tableFields[$field]);
                            $manager->alterField($class, $field);
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
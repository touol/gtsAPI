<?php
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
            $file = MODX_CORE_PATH . 'components/'.$options['namespace'].'/gtsapipackages.json';
            $gtsapipackages = json_decode(file_get_contents($file),1);
            if(is_array($gtsapipackages)){
                foreach ($gtsapipackages as $name => $data) {
                    /** @var modPlugin $plugin */
                    if(!$gtsAPIPackage = $modx->getObject('gtsAPIPackage',['name'=>$name])){
                        $gtsAPIPackage = $modx->newObject('gtsAPIPackage');
                    }
                    if($gtsAPIPackage){
                        $gtsAPIPackage->fromArray(array_merge([
                        
                        ], $data), '', true, true);
                        if($gtsAPIPackage->save()){
                            // Вызовы 'пакет/метод' через таблицы другого пакета (gtsAPI >= 1.2.24):
                            // api_call_packages: ['gtsbalance', ...] — пара без направления.
                            // Свои прошлые строки, которых больше нет в конфиге, снимаем;
                            // ручные и поставленные другими пакетами не трогаем.
                            if (isset($data['api_call_packages']) && is_array($data['api_call_packages'])
                                && $modx->loadClass('gtsAPIPackageCall')) {
                                $partners = [];
                                foreach ($data['api_call_packages'] as $p) {
                                    $p = strtolower(trim((string)$p));
                                    if ($p !== '' && $p !== strtolower($gtsAPIPackage->name)) $partners[$p] = $p;
                                }
                                foreach ($partners as $p) {
                                    $call = $modx->getObject('gtsAPIPackageCall', ['package_id' => $gtsAPIPackage->id, 'partner' => $p]);
                                    if (!$call) {
                                        $call = $modx->newObject('gtsAPIPackageCall');
                                        $call->fromArray(['package_id' => $gtsAPIPackage->id, 'partner' => $p,
                                            'install_package' => $options['namespace'], 'active' => 1]);
                                        $call->save();
                                    }
                                }
                                foreach ($modx->getIterator('gtsAPIPackageCall', [
                                    'package_id' => $gtsAPIPackage->id, 'install_package' => $options['namespace'],
                                ]) as $call) {
                                    if (!isset($partners[$call->partner])) $call->remove();
                                }
                            }
                            if (!empty($data['gtsAPITables'])) {
                                foreach ($data['gtsAPITables'] as $k => $table) {
                                    if(isset($table['properties']) and is_array($table['properties'])) $table['properties'] = json_encode($table['properties'],JSON_PRETTY_PRINT);
                                    if($gtsAPITable = $modx->getObject('gtsAPITable',['table'=>$table['table']])){
                                        if(empty($table['version'])) $table['version'] = 0;
                                        if($table['version'] > $gtsAPITable->version){
                                            $gtsAPITable->fromArray(array_merge([
                                            ], $table), '', true, true);
                                            $gtsAPITable->package_id = $gtsAPIPackage->id;
                                            $gtsAPITable->install_package = $options['namespace'];
                                            $gtsAPITable->save();
                                        }
                                    }else{
                                        if($gtsAPITable = $modx->newObject('gtsAPITable')){
                                            $gtsAPITable->fromArray(array_merge([
                                            ], $table), '', true, true);
                                            $gtsAPITable->package_id = $gtsAPIPackage->id;
                                            $gtsAPITable->install_package = $options['namespace'];
                                            $gtsAPITable->save();
                                        }
                                    }
                                    if($gtsAPITable and !empty($table['gtsAPIUniTreeClass'])){
                                        foreach($table['gtsAPIUniTreeClass'] as $classtree=>$val)
                                        if($gtsAPIUniTreeClass = $modx->getObject('gtsAPIUniTreeClass',['table_id'=>$gtsAPITable->id, 'table'=>$classtree])){
                                            $gtsAPIUniTreeClass->fromArray(array_merge([
                                                'table_id'=>$gtsAPITable->id, 
                                                'table'=>$classtree
                                            ], $val), '', true, true);
                                            $gtsAPIUniTreeClass->save();
                                        }else{
                                            if($gtsAPIUniTreeClass = $modx->newObject('gtsAPIUniTreeClass')){
                                                $gtsAPIUniTreeClass->fromArray(array_merge([
                                                    'table_id'=>$gtsAPITable->id, 
                                                    'table'=>$classtree
                                                ], $val), '', true, true);
                                                $gtsAPIUniTreeClass->save();
                                            }
                                        }
                                    }
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

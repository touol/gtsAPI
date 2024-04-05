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
                    if(!$gtsAPIPackage = $this->modx->getObject('gtsAPIPackage',['name'=>$name])){
                        $gtsAPIPackage = $this->modx->newObject('gtsAPIPackage');
                    }
                    if($gtsAPIPackage){
                        $gtsAPIPackage->fromArray(array_merge([
                        
                        ], $data), '', true, true);
                        if($gtsAPIPackage->save()){
                            if (!empty($data['gtsAPITables'])) {
                                foreach ($data['gtsAPITables'] as $k => $table) {
                                    if($gtsAPITable = $this->modx->getObject('gtsAPITable',['class'=>$table['class']])){
                                        if(empty($table['version'])) $table['version'] = 0;
                                        if($table['version'] > $gtsAPITable->version){
                                            $gtsAPITable->fromArray(array_merge([
                                            ], $table), '', true, true);
                                            $gtsAPITable->save();
                                        }
                                    }else{
                                        if($gtsAPITable = $this->modx->newObject('gtsAPITable')){
                                            $gtsAPITable->fromArray(array_merge([
                                            ], $table), '', true, true);
                                            $gtsAPITable->save();
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
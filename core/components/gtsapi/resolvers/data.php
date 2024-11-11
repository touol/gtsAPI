<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    // $modx->log(1,"xPDOTransport {$options['namespace']}");
    if(!function_exists('add_data_package')){
        function add_data_package($modx,$package,$v){
            $modx->addPackage($package, MODX_CORE_PATH . 'components/'.$package.'/model/');
            $run_add_fields = false;
            foreach($v as $table=>$v2){
                if(in_array($table,['gtsAPIFieldTable','gtsAPIFieldGroupTableLink','gtsAPIField','gtsAPIFieldGroupLink','gtsAPIFieldGroup'])) $run_add_fields = true;

                if(isset($v2['type'])){
                    if($v2['type'] == 'link'){
                        foreach($v2['rows'] as $row){
                            $search = [];
                            $set = [];
                            foreach($row as $field=>$desc){
                                if(is_array($desc)){
                                    if(!$obj = $modx->getObject($desc['table'],[$desc['key']=>$desc[$desc['key']]])) continue 2;
                                    $search[$field] = $obj->id;
                                    $set[$field] = $obj->id;
                                }else{
                                    $set[$field] = $desc;
                                }
                            }
                            if(!$obj = $modx->getObject($table,$search)){
                                $obj = $modx->newObject($table);
                            }
                            if($obj){
                                $obj->fromArray(array_merge([], $set), '', true, true);
                                $obj->save();
                            }
                            
                        }
                    }
                }else{
                    foreach($v2['rows'] as $row){
                        if(!$obj = $modx->getObject($table,[$v2['key']=>$row[$v2['key']]])){
                            $obj = $modx->newObject($table);
                        }
                        if($obj){
                            foreach($row as $k=>$v){
                                if(is_array($v)) $row[$k] = json_encode($v);
                            }
                            $obj->fromArray(array_merge([], $row), '', true, true);
                            $obj->save();
                        }
                    }
                }
            }
            return $run_add_fields;
        }
    }
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // $modx->addPackage($options['namespace'], MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/');
            $file = MODX_CORE_PATH . 'components/'.$options['namespace'].'/data.json';

            $data = json_decode(file_get_contents($file),1);
            if(is_array($data)){
                if(isset($data['gtsapi'])){
                    if(add_data_package($modx,'gtsapi',$data['gtsapi'])){
                        $loaded = include_once(MODX_CORE_PATH . 'components/gtsapi/classes/addfields.class.php');
                        if ($loaded) {
                            $addFields = new AddFields($modx,[]);
                            $addFields->updateFields();
                        }
                    }
                    unset($data['gtsapi']);
                }
                foreach ($data as $package => $v) {
                    add_data_package($modx,$package,$v);
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
 

return true;
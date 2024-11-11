<?php

class AddFields
{
    /** @var modX $modx */
    public $modx;

    /** @var array() $config */
    public $config = array();
    public $manager = null;
    // public $gtsShop = null;
    public $pdo;
    /**
     * @param modX &$modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $this->config = array_merge([
            'corePath'=> $corePath,
        ], $config);
        if ($this->pdo = $this->modx->getService('myPdo','myPdo',$corePath.'classes/',[])) {
            $this->pdo->setConfig($this->config);
        }
    }
    /**
     * @return xPDOManager|null An xPDOManager instance for the xPDO connection, or null
     */
    private function getManager()
    {
        if ($this->manager === null) {
            //$loaded = include_once($this->config['modelPath'] . 'msfieldsmanager/' . $this->modx->config['dbtype'] . '/manager.class.php');
            if($this->modx->getVersionData()['version'] == 3){
                // $loaded = include_once($this->config['corePath'] . 'classes/manager3.class.php');
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, "gtsAPI not support modx3.");
            }else{
                $loaded = include_once($this->config['corePath'] . 'classes/manager.class.php');
            }
            
            if ($loaded) {
                $managerClass = 'gtsAPIManager_' . $this->modx->config['dbtype'];
                $this->manager = new $managerClass ($this->modx->getManager());
            }
            if (!$this->manager) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, "Could not load gtsAPIManager class.");
            }
        }
        return $this->manager;
    }
    // public function addField($data,$className,$paramTable = 'gsParam')
    // {
    //     $manager = $this->getManager();
    //     if ($manager->addField($data,$className,$data['dbafter_field'])) {
    //         if (!$okIndex = $manager->addIndex($data,$className)) {
    //             $okIndex = $this->modx->lexicon('msfieldsmanager.err_index_add');
    //         }
    //         $this->generateMap($className,$paramTable);
    //         return $okIndex;
    //     }
    //     return false;
    // }
    /**
     * @param array $data
     * @param array $oldData
     * @return bool
     */
    // public function alterField($data, $oldData = array(),$className,$paramTable = 'gsParam')
    // {
    //     $manager = $this->getManager();
    //     if ($manager->alterField($data,$className,$data['dbafter_field'])) {
    //         $okIndex = true;
    //         if (isset($data['dbindex']) && isset($oldData['dbindex'])) {
    //             if ($data['dbindex'] == 'no' && $oldData['dbindex'] != 'no') {
    //                 if(!$okIndex = $manager->removeIndex($oldData, $className)) {
    //                     $okIndex = $this->modx->lexicon('msfieldsmanager.err_index_remove');
    //                 }
    //             } else if ($data['dbindex'] != $oldData['dbindex']) {
    //                 if ($okIndex = $manager->removeIndex($oldData, $className)) {
    //                     if (!$okIndex = $manager->addIndex($data, $className)) {
    //                         $okIndex = $this->modx->lexicon('msfieldsmanager.err_index_add');
    //                     }
    //                 } else {
    //                     $okIndex = $this->modx->lexicon('msfieldsmanager.err_index_remove');
    //                 }
    //             }
    //         }
    //         $this->generateMap($className,$paramTable);
    //         return $okIndex;
    //     }
    //     return false;
    // }
    // public function removeField($name,$className,$paramTable = 'gsParam')
    // {
    //     $manager = $this->getManager();
        
    //     if ($manager->removeField($name,$className)) {
    //         $this->generateMap($className,$paramTable);
    //         return true;
    //     }
    // }
    
    
    
    // public function removeFields($object_new)
    // {
    //     $manager = $this->getManager();
        
    //     $add_table = $object_new['add_table'];
    //     $config_table = $object_new['config_table'];
    //     $resp = $this->table_exists($add_table);
    //     if($resp['success'] != 1) return;
    //     $resp = $this->table_exists($config_table);
    //     if($resp['success'] != 1) return;

        
    //     if($gsAddFields = $this->modx->getIterator($config_table,['config_id'=>$object_new['config_id']])){
    //         foreach($gsAddFields as $gsAddField){
    //             $manager->removeField($gsAddField->name,$add_table);
    //         }
    //     }
    //     if(file_exists($this->config['gsPluginsCorePath'] .strtolower($add_table).'.map.inc.php')){
    //         unlink($this->config['gsPluginsCorePath'] .strtolower($add_table).'.map.inc.php');
    //     }

    // }
    // public function triggergsAddFieldConfigLink($class, $type, $method, $fields, $object_old, $object_new)
    // {
    //     if($type == 'before'){
    //         if($object_new['config_id'] == 0){
    //             return $this->error("id конфига пустое!");
    //         }
    //         // if($object_old['config_id'] != $object_new['config_id']){
    //         //     return $this->error("Запрещено изменять id конфига!");
    //         // }
    //         if($method == 'create'){
    //             $resp = $this->table_exists($object_new['add_table']);
    //             if($resp['success'] != 1) return $resp;
    //             $resp = $this->table_exists($object_new['config_table']);
    //             if($resp['success'] != 1) return $resp;
    //         }
    //         if($method == 'remove'){
    //             $this->removeFields($object_old);
    //         }
    //     }
    //     if($type == 'after'){
    //         $this->updateFields();
    //     }
    //     return $this->success();
    // }
    // public function triggergsAddField($class, $type, $method, $fields, $object_old, $object_new)
    // {
    //     if($type == 'before'){
    //         if($method == 'update'){
    //             if($object_old['name'] != $object_new['name']){
    //                 return $this->error("Запрещено изменять ключ поля!");
    //             }
    //         }
    //     }
    //     if($type == 'after'){
    //         if($object_new['dbtype'] != $object_old['dbtype']
    //             or $object_new['dbtype'] != $object_old['dbtype']
    //             or $object_new['dbprecision'] != $object_old['dbprecision']
    //             or $object_new['dbnull'] != $object_old['dbnull']
    //             or $object_new['dbdefault'] != $object_old['dbdefault']
    //             or $object_new['dbindex'] != $object_old['dbindex']
    //             or $object_new['dbafter_field'] != $object_old['dbafter_field']
    //         ){
    //             $this->updateFields();
    //         }
            
    //     }
    //     return $this->success();
    // }
    public function triggergsAddFields(&$getTables,$class, $type, $method, $fields, $object_old, $object_new){
        $this->updateFields();
        return $this->success();
    }
    // public function triggergsAddField($class, $type, $method, $fields, $object_old, $object_new)
    // {
    //     if($type == 'before'){
    //         if($method == 'remove') return $this->success();
    //         if($object_new['config_id'] == 0){
    //             return $this->error("id конфига поля пустое!");
    //         }
    //         if($method == 'update'){
    //             if($object_old['name'] != $object_new['name']){
    //                 return $this->error("Запрещено изменять ключ поля!");
    //             }
    //             if($object_old['config_id'] != $object_new['config_id']){
    //                 return $this->error("Запрещено изменять id конфига поля!");
    //             }
    //         }

    //         if(!$gsAddFieldConfigLinkCount = $this->modx->getCount('gsAddFieldConfigLink',[
    //             'config_id'=>$object_new['config_id']])){
    //                 return $this->error("Нужно задать связь конфига с таблицами!");
    //             }
    //         $gsAddFieldConfigLinks = $this->modx->getIterator('gsAddFieldConfigLink',[
    //             'config_id'=>$object_new['config_id']]);
    //         foreach($gsAddFieldConfigLinks as $gsAddFieldConfigLink){
    //             $resp = $this->table_exists($gsAddFieldConfigLink->add_table);
    //             if($resp['success'] != 1) return $resp;
    //             $resp = $this->table_exists($gsAddFieldConfigLink->config_table);
    //             if($resp['success'] != 1) return $resp;
    //         }
    //     }
    //     if($type == 'after'){
    //         $this->updateFields();
    //     }
    //     return $this->success();
    // }
    public function getObjectFromSchema($package){
        $objects = [];
        $schemaFile = MODX_CORE_PATH . 'components/'.strtolower($package).'/model/schema/'.strtolower($package).'.mysql.schema.xml';
        if (is_file($schemaFile)) {
            $schema = new SimpleXMLElement($schemaFile, 0, true);
            if (isset($schema->object)) {
                foreach ($schema->object as $obj) {
                    $fields = [];
                    foreach ($obj->field as $field) {
                        $fields[(string)$field['key']] = (string)$field['key'];
                    }
                    $objects[(string)$obj['class']] = $fields;
                }
            }
            unset($schema);
        }
        return $objects;
    }
    public function updateFields()
    {
        $manager = $this->getManager();
        if(!$manager) return;

        $gtsAPIFieldTables = $this->modx->getIterator('gtsAPIFieldTable',['add_base'=>1]);
        $packages = [];
        $datas = [];  $objects = [];
        foreach($gtsAPIFieldTables as $gtsAPIFieldTable){
            
            if(!$gtsAPITable = $this->modx->getObject('gtsAPITable',['table'=>$gtsAPIFieldTable->name_table])) continue;
            
            
            if(empty($gtsAPITable->class)){
                $class = $gtsAPITable->table;
            }else{
                $class = $gtsAPITable->class;
            }

            //Получаем оригинальные таблицы компонента
            if(!isset($packages[$gtsAPITable->package_id])){
                if(!$gtsAPIPackage = $this->modx->getObject('gtsAPIPackage',$gtsAPITable->package_id)) continue;
                
                $package = $gtsAPIPackage->name;
                $this->modx->addPackage($package, MODX_CORE_PATH . 'components/'.strtolower($package).'/model/');

                $objects2 = $this->getObjectFromSchema($package);
                // $this->modx->log(1,"updateFields objects2 ".print_r($objects2,1));
                if(!isset($objects2[$class])) continue;
                
                foreach($objects2 as $class2=>$v){
                    if(!isset($objects[$class2])) $objects[$class2] = $v;
                }
                $packages[$gtsAPITable->package_id] = 1;
            }

            
            $resp = $this->table_exists($class);
            if($resp['success'] != 1) continue;

            $addFields = [];
            $gtsAPIFieldGroupTableLinks = $this->modx->getIterator('gtsAPIFieldGroupTableLink',['table_field_id'=>$gtsAPIFieldTable->id]);
            foreach($gtsAPIFieldGroupTableLinks as $gtsAPIFieldGroupTableLink){
                $gtsAPIFieldGroups = $this->modx->getIterator('gtsAPIFieldGroup',['id'=>$gtsAPIFieldGroupTableLink->group_field_id]);
                foreach($gtsAPIFieldGroups as $gtsAPIFieldGroup){
                    if($gtsAPIFieldGroup->all){
                        $c = $this->modx->newQuery($gtsAPIFieldGroup->from_table);
                        $c->sortby('rank','ASC');
                        $gtsAPIFields = $this->modx->getIterator($gtsAPIFieldGroup->from_table,$c);
                        foreach($gtsAPIFields as $gtsAPIField){
                            $addFields[$gtsAPIField->name] = $gtsAPIField->toArray();
                            if($gtsAPIFieldTable->only_text){
                                $addFields[$gtsAPIField->name]['dbtype'] = 'varchar';
                                $addFields[$gtsAPIField->name]['dbprecision'] = 191;
                                $addFields[$gtsAPIField->name]['dbnull'] = 1;
                                $addFields[$gtsAPIField->name]['dbdefault'] = '';
                                $addFields[$gtsAPIField->name]['dbafter_field'] = 'id';
                                $addFields[$gtsAPIField->name]['field_type'] = 'text';
                            }
                            // $addFields[$gtsAPIField->name]['from_table'] = $gtsAPIFieldGroup->from_table;
                            // if(empty($addFields[$gtsAPIField->name]['after_field'])) $addFields[$gtsAPIField->name]['after_field'] = $gtsAPIFieldTable->after_field;
                            // $addFields[$gtsAPIField->name]['gtsapi_config'] = json_decode($addFields[$gtsAPIField->name]['gtsapi_config'],1);
                        }
                    }else{
                        $this->pdo->setConfig([
                            'class'=>$gtsAPIFieldGroup->link_group_table,
                            'leftJoin'=>[
                                $gtsAPIFieldGroup->from_table=>[
                                    'class'=>$gtsAPIFieldGroup->from_table,
                                    'on'=>$gtsAPIFieldGroup->from_table.'.id = '.$gtsAPIFieldGroup->link_group_table.'.field_id'
                                ]
                            ],
                            'where'=>[
                                $gtsAPIFieldGroup->link_group_table.'.group_field_id'=>$gtsAPIFieldGroup->id
                            ],
                            'sortby'=>[
                                $gtsAPIFieldGroup->from_table.'.rank'=>'ASC'
                            ],
                            'select'=>[
                                $gtsAPIFieldGroup->from_table=>'*'
                            ],
                            'return' => 'data',
                            'limit' => 0
                        ]);
                        $rows = $this->pdo->run();
                        
                        foreach($rows as $row){
                            $addFields[$row['name']] = $row;
                            if($gtsAPIFieldTable->only_text){
                                $addFields[$row['name']]['dbtype'] = 'varchar';
                                $addFields[$row['name']]['dbprecision'] = 191;
                                $addFields[$row['name']]['dbnull'] = 1;
                                $addFields[$row['name']]['dbdefault'] = '';
                                $addFields[$row['name']]['dbafter_field'] = 'id';
                                $addFields[$row['name']]['field_type'] = 'text';
                            }
                            // $addFields[$row['name']]['from_table'] = $gtsAPIFieldGroup->from_table;
                            // if(empty($row['after_field'])) $addFields[$row['name']]['after_field'] = $gtsAPIFieldTable->after_field;
                        }
                    }
                }
            }
            
            $datas[$class] = $addFields;
        }
        if(empty($datas)) return;

        // $this->modx->log(1,"updateFields5 datas ".print_r($datas,1));

        $fields = [];
        foreach($datas as $class=>$add_fields){
            if(empty($add_fields)){
                unset($datas[$class]);
                continue;
            }
            // получаем поля в базе
            $fields[$class] = $manager->geTableFields($class);
            // $this->modx->log(1,"updateFields5 fields ".print_r($fields,1));
            // удаляем те, что есть в начальном конфиге компонента
            if(isset($objects[$class])){
                foreach($fields[$class] as $k=>$field){
                    if($field == 'id') unset($fields[$class][$k]);
                    if(isset($objects[$class][$field])) unset($fields[$class][$k]);
                }
            }
            foreach($add_fields as $field=>$add_field){
                if(isset($fields[$class][$field])){
                    $manager->alterField($add_field,$class,'id');
                    unset($fields[$class][$field]);
                }else{
                    if ($manager->addField($add_field,$class,'id')) {
                        if (!$okIndex = $manager->addIndex($add_field,$class)) {
                            $okIndex = $this->modx->lexicon('msfieldsmanager.err_index_add');
                        }
                    }
                }
            }
        }
        if(empty($datas)) return;

        // $this->modx->log(1,"updateFields6 ".print_r($fields,1));
        array_map('unlink', array_filter( 
            (array) array_merge(glob(MODX_CORE_PATH . 'components/gtsapi/plugins/*'))));
        foreach($fields as $class=>$fs){
            foreach($fs as $k=>$field){
                $manager->removeField($field,$class);
            }
            $this->generateMapDatas($class,$datas);
        }
    }
    

    public function generateMapDatas($add_table,$datas){
        $gsPluginsCorePath = MODX_CORE_PATH . 'components/gtsapi/plugins/';

        $manager = $this->getManager();
        $map = array('fields' => array(), 'fieldMeta' => array());
        if ($manager) {
            $fields = $datas[$add_table];
            foreach ($fields as $field) {
                $null = $field['dbnull'] ? 'true' : 'false';
                $key = $manager->getIndex('');
                $default = $field['dbdefault']=='none'?'':$field['dbdefault'];
                $defaultType = $this->modx->driver->getPhpType($field['dbtype']);
                $phpType = $field['xtype'] ? $this->xtypeToPhpType($field['xtype'], $defaultType) : $defaultType;
                if ($default === 'NULL') {
                    $default = null;
                }
                switch ($defaultType) {
                    case 'integer':
                    case 'boolean':
                    case 'bit':
                        $default = (integer)$default;
                        break;
                    case 'float':
                    case 'numeric':
                        $default = (float)$default;
                        break;
                    default:
                        break;
                }
                $map['fields'][$field['name']] = $default;
                $map['fieldMeta'][$field['name']] = array();
                $map['fieldMeta'][$field['name']]['dbtype'] = $field['dbtype'];
                $map['fieldMeta'][$field['name']]['precision'] = $field['dbprecision'];
                $map['fieldMeta'][$field['name']]['phptype'] = $phpType;
                $map['fieldMeta'][$field['name']]['default'] = $default;
                $map['fieldMeta'][$field['name']]['null'] = (!empty($null) && strtolower($null) !== 'false') ? true : false;
            }
            $manager->setMap($map);
            $manager->outputMap($gsPluginsCorePath,$add_table);
        }
    }

    // public function generateMap($className,$paramTable = 'gsParam')
    // {
    //     $manager = $this->getManager();
    //     $map = array('fields' => array(), 'fieldMeta' => array());
    //     if ($manager) {
    //         $q = $this->modx->newQuery($paramTable);
    //         //$q->where(array('enable' => 1));
    //         if($paramTable != 'gsParam'){
    //             if($gsAddFieldConfigLink = $this->modx->getObject('gsAddFieldConfigLink',['add_table'=>$className])){
    //                 $q->where(['config_id' => $gsAddFieldConfigLink->config_id]);
    //             }
    //         }
    //         $q->sortby('rank', 'ASC');
    //         if ($fields = $this->modx->getCollection($paramTable, $q)) {
    //             foreach ($fields as $field) {
    //                 $null = $field->dbnull ? 'true' : 'false';
    //                 $key = $manager->getIndex('');
    //                 $default = $field->dbdefault;
    //                 $defaultType = $this->modx->driver->getPhpType($field->dbtype);
    //                 $phpType = $field->xtype ? $this->xtypeToPhpType($field->xtype, $defaultType) : $defaultType;
    //                 if ($default === 'NULL') {
    //                     $default = null;
    //                 }
    //                 switch ($defaultType) {
    //                     case 'integer':
    //                     case 'boolean':
    //                     case 'bit':
    //                         $default = (integer)$default;
    //                         break;
    //                     case 'float':
    //                     case 'numeric':
    //                         $default = (float)$default;
    //                         break;
    //                     default:
    //                         break;
    //                 }
    //                 $map['fields'][$field->name] = $default;
    //                 $map['fieldMeta'][$field->name] = array();
    //                 $map['fieldMeta'][$field->name]['dbtype'] = $field->dbtype;
    //                 $map['fieldMeta'][$field->name]['precision'] = $field->dbprecision;
    //                 $map['fieldMeta'][$field->name]['phptype'] = $phpType;
    //                 $map['fieldMeta'][$field->name]['default'] = $default;
    //                 $map['fieldMeta'][$field->name]['null'] = (!empty($null) && strtolower($null) !== 'false') ? true : false;
    //             }
    //         }
    //         $manager->setMap($map);
    //         $manager->outputMap($this->config['gsPluginsCorePath'],$className);
            
    //     }
    // }
    
        /**
     * @param string $xtype
     * @param string $defaultType
     * @return string
     */
    private function xtypeToPhpType($xtype, $defaultType)
    {
        switch ($xtype) {
            case 'number':
                return 'integer';
                break;
            case 'float':
                return 'float';
                break;
            case 'input':
            case 'textarea':
            case 'editor':
            case 'browser':
                return 'string';
                break;
            case 'combobox_json':
                return 'json';
                break;
            case 'checkboxgroup':
                return 'json';
                break;
            case 'combobox_custom':
                return $defaultType;
                break;
            case 'combobox_array':
                return 'array';
                break;
            case 'combobox_boolean':
            case 'checkbox':
            case 'radiobutton':
                //    return 'boolean';
                return 'integer';
                break;
            case 'date':
                return 'date';
                break;
            case 'time':
                return 'time';
                break;
            case 'datetime':
                return 'datetime';
                break;
            case 'timestamp':
                return 'timestamp';
                break;

        }
    }
    public function table_exists($class){
        if(!$tableName = $this->modx->getTableName($class)){
            return $this->error("Класс таблицы $class не найден!"); 
        }
        $c1 = $this->modx->prepare("SELECT count(*) from {$tableName}");
        $c1->execute();
        $cl = $c1->fetchAll(PDO::FETCH_ASSOC);
        if(count($cl) == 0){
            return $this->error("Таблица класса $class с именем $tableName в базе не найдена!");
        }
        return $this->success();
    }
    public function success($message = "",$data = []){
        return array('success'=>1,'message'=>$message,'data'=>$data);
    }
    public function error($message = "",$data = []){
        return array('success'=>0,'message'=>$message,'data'=>$data);
    }
}
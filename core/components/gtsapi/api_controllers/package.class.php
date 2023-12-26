<?php

class packageAPIController{
    public $config = [];
    public $modx;
    public $pdo;
    /** @var modPackageBuilder $builder */
    public $builder;
    public $category;
    public $category_attributes;

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            
        ], $config);

        if ($this->pdo = $this->modx->getService('pdoFetch')) {
            $this->pdo->setConfig($this->config);
        }
    }
    public function checkPermissions($rule_action){
        if($rule_action['authenticated']){
            if(!$this->modx->user->isAuthenticated()) return $this->error("Not api authenticated!");
        }
        if($rule_action['groups']){
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if($rule_action['permitions']){
            $permitions = array_map('trim', explode(',', $rule_action['permitions']));
            foreach($permitions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }
    public function route($rule, $uri, $method, $request, $id){
        $resp = $this->checkPermissions($rule);
        
        if(!$resp['success']){
            // $resp['user'] = $this->modx->user->id;
            header('HTTP/1.1 401 Unauthorized2');
            return $resp;
        }
        if(empty($request['config'])) return $this->error("empty config");
        $request['config'] = json_decode($request['config'],1);
        if(empty($request['config']['name_lower'])) return $this->error("empty config name_lower",$request);
        // if(isset($request['action'])){
        //     if($request['action'] == 'set_debug'){
        //         $Setting = $this->modx->getObject('modSystemSetting', $request['config']['name_lower'].'_debug');
        //         $Setting->set('value', $request['config']['debug']);
        //         $Setting->save();
        //         $this->modx->cacheManager->refresh(array('system_settings' => array()));
        //         return $this->success("debug {$request['config']['debug']}");
        //     }
        // }
        $assets = MODX_BASE_PATH . 'assets/components/' . $request['config']['name_lower'] . '/';
        $core = MODX_BASE_PATH . 'core/components/' . $request['config']['name_lower'] . '/';

        $this->config = array_merge([
            'assets' => $assets,
            'core' => $core,
        ],$request['config'], $this->config);
        
        $this->initialize();
        $this->process($request);
        return $this->success("Пакет установлен",['request'=>$request,'files'=>$_FILES]);
        
    }

    /**
     * Update the model
     */
    protected function model()
    {
        if(!$this->config['schema']) return;
        // upload schema
        if(isset($_FILES['schema'])){
            $path = $this->config['core'] . 'model/schema/';
            if ( ! is_dir($path)) {
                mkdir($path, 0666, true);
            }
            $uploadfile = $path . $this->config['name_lower'] . '.mysql.schema.xml';
            move_uploaded_file($_FILES['schema']['tmp_name'], $uploadfile);
        }

        if (empty($this->config['core'] . 'model/schema/' . $this->config['name_lower'] . '.mysql.schema.xml')) {
            return;
        }
        /** @var xPDOCacheManager $cache */
        if ($cache = $this->modx->getCacheManager()) {
            $cache->deleteTree(
                $this->config['core'] . 'model/' . $this->config['name_lower'] . '/mysql',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
        }

        /** @var xPDOManager $manager */
        $manager = $this->modx->getManager();
        /** @var xPDOGenerator $generator */
        $generator = $manager->getGenerator();
        $generator->parseSchema(
            $this->config['core'] . 'model/schema/' . $this->config['name_lower'] . '.mysql.schema.xml',
            $this->config['core'] . 'model/'
        );

        $this->table();

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Model updated');
    }

    protected function table(){
        
        $this->modx->addPackage($this->config['name_lower'], MODX_CORE_PATH . 'components/'.$this->config['name_lower'].'/model/');
        $manager = $this->modx->getManager();
        $objects = [];
        $schemaFile = $this->config['core'] . 'model/schema/' . $this->config['name_lower'] . '.mysql.schema.xml';
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
            $table = $this->modx->getTableName($class);
            $sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
            $stmt = $this->modx->prepare($sql);
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
                $c = $this->modx->prepare("SHOW COLUMNS IN {$this->modx->getTableName($class)}");
                $c->execute();
                while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
                    $tableFields[$cl['Field']] = $cl['Field'];
                }
                foreach ($this->modx->getFields($class) as $field => $v) {
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
                $c = $this->modx->prepare("SHOW INDEX FROM {$this->modx->getTableName($class)}");
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
                $map = $this->modx->getIndexMeta($class);
                // Remove old indexes
                foreach ($indexes as $key => $index) {
                    if (!isset($map[$key])) {
                        if ($manager->removeIndex($class, $key)) {
                            $this->modx->log(modX::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
                        }
                    }
                }
                // Add or alter existing
                foreach ($map as $key => $index) {
                    ksort($index['columns']);
                    $index = implode(':', array_keys($index['columns']));
                    if (!isset($indexes[$key])) {
                        if ($manager->addIndex($class, $key)) {
                            $this->modx->log(modX::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
                        }
                    } else {
                        if ($index != $indexes[$key]) {
                            if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                $this->modx->log(modX::LOG_LEVEL_INFO,
                                    "Updated index \"{$key}\" of the table \"{$class}\""
                                );
                            }
                        }
                    }
                }
            }
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Table updated');
    }

    /**
     * Add snippets
     */
    protected function snippets($snippets,$files)
    {
        /** @noinspection PhpIncludeInspection */
        // $snippets = include($this->config['elements'] . 'snippets.php');
        if (!is_array($snippets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Snippets');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['snippets']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        $objects = [];
        foreach ($snippets as $name => $data) {
            /** @var modSnippet[] $objects */
            $objects[$name] = $this->modx->newObject('modSnippet');
            $objects[$name]->fromArray(array_merge([
                'id' => 0,
                'name' => $name,
                'description' => @$data['description'],
                'snippet' => $this::_getContent($files[$data['tmp_file']]['tmp_name']),
                // 'static' => !empty($this->config['static']['snippets']),
                'source' => 1,
                // 'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/snippets/' . $data['file'] . '.php',
            ], $data), '', true, true);
            $properties = [];
            foreach (@$data['properties'] as $k => $v) {
                $properties[] = array_merge([
                    'name' => $k,
                    'desc' => $this->config['name_lower'] . '_prop_' . $k,
                    'lexicon' => $this->config['name_lower'] . ':properties',
                ], $v);
            }
            $objects[$name]->setProperties($properties);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Snippets');
    }
    /**
     * @param $filename
     *
     * @return string
     */
    static public function _getContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));

            return preg_match('#\<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }

        return '';
    }
    /**
     * Add settings
     */
    protected function settings($settings)
    {
        /** @noinspection PhpIncludeInspection */
        // $settings = include($this->config['elements'] . 'settings.php');
        // if (!is_array($settings)) {
        //     $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in System Settings');

        //     return;
        // }
        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['settings']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        foreach ($settings as $name => $data) {
            /** @var modSystemSetting $setting */
            $setting = $this->modx->newObject('modSystemSetting');
            $setting->fromArray(array_merge([
                'key' => $this->config['name_lower'] . '_' . $name,
                'namespace' => $this->config['name_lower'],
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($setting, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($settings) . ' System Settings');
    }
    /**
     * Add gtsAPI
     */
    protected function gtsapirules($gtsapirules)
    {
        /** @noinspection PhpIncludeInspection */
        if (!is_array($gtsapirules)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in gtsapirules');

            return;
        }

        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'point',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['gtsapirules']),
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'gtsAPIAction' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => ['rule_id', 'gtsaction'],
                ],
            ],
        ];
        $objects = [];
        foreach ($gtsapirules as $name => $data) {
            /** @var modPlugin $plugin */
            $gtsAPIRule = $this->modx->newObject('gtsAPIRule');
            $gtsAPIRule->fromArray(array_merge([
                
            ], $data), '', true, true);

            $gtsactions = [];
            if (!empty($data['gtsAPIActions'])) {
                foreach ($data['gtsAPIActions'] as $k => $action) {
                    /** @var modPluginEvent $event */
                    $gtsAPIAction = $this->modx->newObject('gtsAPIAction');
                    $gtsAPIAction->fromArray(array_merge([
                    ], $action), '', true, true);
                    $gtsactions[] = $gtsAPIAction;
                }
            }
            if (!empty($gtsactions)) {
                $gtsAPIRule->addMany($gtsactions);
            }
            $vehicle = $this->builder->createVehicle($gtsAPIRule, $attributes);
            $this->builder->putVehicle($vehicle);
            // $objects[] = $gtsAPIRule;
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($gtsapirules) . ' gtsapis');
    }
    /**
     * @return modPackageBuilder
     */
    public function process($request)
    {
        if(isset($request['snippets'])){
            $snippets = json_decode($request['snippets'],1);
            if(is_array($snippets) and count($snippets) > 0 and !empty($_FILES)){
                foreach($snippets as $ks=>$snippet){
                    foreach($_FILES as $kf=>$file){
                        if($snippet['file'] == $file['name']) $snippets[$ks]['tmp_file'] = $kf; 
                    }
                    if(empty($snippets[$ks]['tmp_file'])) unset($snippets[$ks]);
                }
                if(count($snippets) > 0){
                    $this->snippets($snippets,$_FILES);
                }
            }
        }
        if(isset($request['settings'])){
            $settings = json_decode($request['settings'],1);
            if(is_array($settings) and count($settings) > 0){
                $this->settings($settings);
            }
        }
        if(isset($request['gtsapirules'])){
            $gtsapirules = json_decode($request['gtsapirules'],1);
            if(is_array($gtsapirules) and count($gtsapirules) > 0){
                $this->gtsapirules($gtsapirules);
            }
        }
        $this->model();
        // $this->assets();

        // // Add elements
        // $elements = scandir($this->config['elements']);
        // foreach ($elements as $element) {
        //     if (in_array($element[0], ['_', '.'])) {
        //         continue;
        //     }
        //     $name = preg_replace('#\.php$#', '', $element);
        //     if (method_exists($this, $name)) {
        //         $this->{$name}();
        //     }
        // }

        // Create main vehicle
        /** @var modTransportVehicle $vehicle */
        $vehicle = $this->builder->createVehicle($this->category, $this->category_attributes);

        // // Files resolvers
        $vehicle->resolve('file', [
            'source' => $this->config['core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
        ]);
        $vehicle->resolve('file', [
            'source' => $this->config['assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ]);

        // // Add resolvers into vehicle
        // $resolvers = scandir($this->config['resolvers']);

        // foreach ($resolvers as $resolver) {
        //     if (in_array($resolver[0], ['_', '.'])) {
        //         continue;
        //     }
        //     if ($vehicle->resolve('php', ['source' => $this->config['resolvers'] . $resolver])) {
        //         $this->modx->log(modX::LOG_LEVEL_INFO, 'Added resolver ' . preg_replace('#\.php$#', '', $resolver));
        //     }
        // }
        $this->builder->putVehicle($vehicle);

        // $this->builder->setPackageAttributes([
        //     'changelog' => file_get_contents($this->config['core'] . 'docs/changelog.txt'),
        //     'license' => file_get_contents($this->config['core'] . 'docs/license.txt'),
        //     'readme' => file_get_contents($this->config['core'] . 'docs/readme.txt'),
        // ]);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

        // // Шифрация пакета
        // if ($this->config['encryption_enable']) {
        //     $this->builder->putVehicle($this->builder->createVehicle(array(
        //         'source' => $this->config['resolvers'] . 'encryption.php',
        //     ), array('vehicle_class' => 'xPDOScriptVehicle')));
        // }

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
        $this->builder->pack();

        // if (!empty($this->config['install'])) {
        //     $this->install();
        // }
        $this->install();
        
        return $this->builder;
    }
    /**
     * Initialize package builder
     */
    protected function initialize()
    {
        $this->builder = $this->modx->getService('transport.modPackageBuilder');
        $this->builder->createPackage($this->config['name_lower'], $this->config['version'], $this->config['release']);

        $this->builder->registerNamespace($this->config['name_lower'], false, true, '{core_path}components/' . $this->config['name_lower'] . '/');
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

        $this->category = $this->modx->newObject('modCategory');
        $this->category->set('category', $this->config['name']);
        $this->category_attributes = [
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [],
        ];

        // if ($this->config['encryption_enable']) {
        //     // Шифрация
        //     $this->category_attributes['vehicle_class'] = 'encryptedVehicle';
        //     $this->category_attributes[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
        // }

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created main Category.');
    }
    /**
     *  Install package
     */
    protected function install()
    {
        $signature = $this->builder->getSignature();
        $sig = explode('-', $signature);
        $versionSignature = explode('.', $sig[1]);

        /** @var modTransportPackage $package */
        if (!$package = $this->modx->getObject('transport.modTransportPackage', ['signature' => $signature])) {
            $package = $this->modx->newObject('transport.modTransportPackage');
            $package->set('signature', $signature);
            $package->fromArray([
                'created' => date('Y-m-d h:i:s'),
                'updated' => null,
                'state' => 1,
                'workspace' => 1,
                'provider' => 0,
                'source' => $signature . '.transport.zip',
                'package_name' => $this->config['name'],
                'version_major' => $versionSignature[0],
                'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
                'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
            ]);
            if (!empty($sig[2])) {
                $r = preg_split('#([0-9]+)#', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
                if (is_array($r) && !empty($r)) {
                    $package->set('release', $r[0]);
                    $package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
                } else {
                    $package->set('release', $sig[2]);
                }
            }
            $package->save();
        }
        if ($package->install()) {
            $this->modx->runProcessor('system/clearcache');
        }
    }
    public function success($message = "",$data = []){
        //return array('success'=>1,'message'=>$message,'data'=>$data);
        header("HTTP/1.1 200 OK");
        return ['success'=>1,'message'=>$message,'data'=>$data];
    }
    public function error($message = "",$data = []){
        return ['success'=>0,'message'=>$message,'data'=>$data];
    }
}
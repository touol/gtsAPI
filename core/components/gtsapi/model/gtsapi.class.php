<?php

class gtsAPI
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdo;

    /** @var array() $config */
    public $config = array();


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            // 'processorsPath' => $corePath . 'processors/',
            // 'customPath' => $corePath . 'custom/',

            // 'connectorUrl' => $assetsUrl . 'connector.php',
            // 'assetsUrl' => $assetsUrl,
            // 'cssUrl' => $assetsUrl . 'css/',
            // 'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('gtsapi', MODX_CORE_PATH . 'components/gtsapi/model/');
        $this->modx->lexicon->load('gtsapi:default');
        // $this->modx->addPackage('tsklad', MODX_CORE_PATH.'components/tsklad/model/');
        // $this->modx->addPackage('gtsbalance', MODX_CORE_PATH.'components/gtsbalance/model/');

        if ($this->pdo = $this->modx->getService('pdoFetch')) {
            $this->pdo->setConfig($this->config);
        }
        require_once 'jwt_utils.php';
    }

    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties Properties for initialization.
     *
     * @return bool
     */
    public function initialize($ctx = 'web', $scriptProperties = array())
    {
        $this->config = array_merge($this->config, $scriptProperties);

        $this->config['pageId'] = $this->modx->resource->id;

        switch ($ctx) {
            case 'mgr':
                break;
            default:
                if (!defined('MODX_API_MODE') || !MODX_API_MODE) {

                    $config = $this->makePlaceholders($this->config);
                    if ($css = $this->modx->getOption('gtsapi_frontend_css')) {
                        $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
                    }

                    $config_js = preg_replace(array('/^\n/', '/\t{5}/'), '', '
                            gtsAPI = {};
                            gtsAPIConfig = ' . $this->modx->toJSON($this->config) . ';
                    ');


                    $this->modx->regClientStartupScript("<script type=\"text/javascript\">\n" . $config_js . "\n</script>", true);
                    if ($js = trim($this->modx->getOption('gtsapi_frontend_js'))) {

                        if (!empty($js) && preg_match('/\.js/i', $js)) {
                            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));

                        }
                    }

                }

                break;
        }
        return true;
    }
    public function makePlaceholders($config)
    {
        $placeholders = [];
        foreach($config as $k=>$v){
            if(is_string($v)){
                $placeholders['pl'][] = "[[+$k]]";
                $placeholders['vl'][] = $v;
            }
        }
        return $placeholders;
    }
    public function auth_from_token(){
        if($jwt = get_bearer_token()){
            
            $table = $this->modx->getTableName('gtsAPIToken');
            $query= new xPDOCriteria($this->modx, 
                "SELECT * FROM {$table} WHERE `token` = :token AND `active` = 1 LIMIT 1", [
                ':token' => $jwt,
            ]);
            // $query->prepare();
            // return $this->error("Not found user! ".$query->toSQL());
            if ($query->prepare() && $query->stmt->execute()) {
                
                $gtsAPITokens = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                if(is_array($gtsAPITokens) and count($gtsAPITokens) == 1){
                    if($user = $this->modx->getObject('modUser', $gtsAPITokens[0]['user_id'])){
                        if(!is_jwt_valid($jwt, $user->salt)){
                            return $this->error("Not found user 1!");
                        }
                        if(strtotime($gtsAPITokens[0]['valid_till']) > time()){
                            $user->addSessionContext('web');
                            $this->modx->user = $user;
                            return $this->success();
                        }else{
                            return $this->error("Not found user! valid_till");
                        }
                    }
                    
                }
            }
        }
        return $this->error("Not found user! $jwt");
    }
    public function route($uri, $method, $request){
        if($uri[1] != 'api'){
            header('HTTP/1.1 404 Not found');
            return $this->error("Not api request! 0");
        }
        if(!isset($uri[2])){
            header('HTTP/1.1 404 Not found');
            return $this->error("Not set api rule!");
        }
        $resp = $this->auth_from_token();
        // return $resp;
        if($this->modx->getOption('gtsapi_only_jwt', null, false) and !$resp['success']){
            header('HTTP/1.1 401 Unauthorized0');
            return $resp;
        }
        $point = $uri[2];
        if($gtsAPITable = $this->modx->getObject('gtsAPITable',['table:LIKE'=>$point,'active'=>1])){
            if($gtsAPITable->tree){
                $controller_class = 'treeAPIController';
                $rule['controller_path'] = $this->config['corePath'] . 'api_controllers/tree.class.php';
            }else{
                $controller_class = 'tableAPIController';
                $rule['controller_path'] = $this->config['corePath'] . 'api_controllers/table.class.php';
            }
            $loaded = include_once($rule['controller_path']);
            if ($loaded) {
                $controller = new $controller_class($this->modx,$this->config);
                return $controller->route($gtsAPITable, $uri, $method, $request);
            }else{
                return $this->error("Not load class $controller_class {$rule['controller_path']}!");
            }
        }
        $gtsAPIRules = $this->modx->getCollection("gtsAPIRule",['point:LIKE'=>$uri[2].'%','active'=>1]);
        
        foreach($gtsAPIRules as $gtsAPIRule0){
            $gtsAPIRule = $gtsAPIRule0;
            break;
        }
        $id = null;
        for($k = 3;$k <= 20;$k++){
            if($uri[$k] == (int)$uri[$k]){
                $id = (int)$uri[$k];
                break;
            }
            if(isset($uri[$k])) $point .= '/'.$uri[$k];
            if(isset($uri[$k + 1])){
                if($uri[$k + 1] == (int)$uri[$k + 1]){
                    $id = (int)$uri[$k + 1];
                    break;
                }
            }else{
                break;
            }
        }
        
        
        foreach($gtsAPIRules as $gtsAPIRule0){
            if($gtsAPIRule0->point == $point){
                $gtsAPIRule = $gtsAPIRule0;
                break;
            }
        }
        if(!$gtsAPIRule){
            header('HTTP/1.1 404 Not found');
            return $this->error("Not found API point $point!");
        }
            

        $default = array(
            'class' => 'gtsAPIAction',
            'select'=>[
                'gtsAPIAction'=>'*',
            ],
            'where' => ['rule_id'=>$gtsAPIRule->id,'active'=>1],
            'sortby' => [
                "gtsAPIAction.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => 0,
        );
        $this->pdo->setConfig($default);
        $gtsAPIActions = $this->pdo->run();
        $rule = $gtsAPIRule->toArray();
        $aсtions = [];
        foreach($gtsAPIActions as $gtsAPIAction){
            $aсtions[$gtsAPIAction['gtsaction']] = $gtsAPIAction;
        }
        $rule['aсtions'] = $aсtions;
        if(empty($rule['controller_class'])){
            $controller_class = 'defaultAPIController';
            $rule['controller_path'] = $this->config['corePath'] . 'api_controllers/default.class.php';
        }else{
            if($rule['controller_path'][0] != '[') $rule['controller_path'] = MODX_CORE_PATH . $rule['controller_path'];
            $rule['controller_path'] = str_replace('[[+core_path]]',MODX_CORE_PATH,$rule['controller_path']);
            $controller_class = $rule['controller_class'];
        }
        $loaded = include_once($rule['controller_path']);
        if ($loaded) {
            $controller = new $controller_class($this->modx,$this->config);
            return $controller->route($rule, $uri, $method, $request, $id);
        }else{
            return $this->error("Not load class $controller_class {$rule['controller_path']}!");
        }
    }
    
    public function handleRequest($action, $data = array())
    {
        $data = $this->modx->sanitize($data, $this->modx->sanitizePatterns);
        switch($action){
            case 'export_rule':
                return $this->export_rule($data);
            break;
            case 'save_rule':
                return $this->save_rule($data);
            break;
            case 'export_table':
                return $this->export_table($data);
            break;
            case 'save_table':
                return $this->save_table($data);
            break;
            case 'export_select':
                return $this->export_select($data);
            break;
            case 'save_select':
                return $this->save_select($data);
            break;
            case 'gen_rules':
                return $this->gen_rules($data);
            break;
            case 'generate_rules':
                return $this->generate_rules($data);
            break;
            case 'gen_fields':
                return $this->gen_fields($data);
            break;
            default:
                return $this->error("Not found action!");
        }
    }
    
    public function success($message = "",$data = []){
        return array('success'=>1,'message'=>$message,'data'=>$data);
    }
    public function error($message = "",$data = []){
        return array('success'=>0,'message'=>$message,'data'=>$data);
    }
    public function gen_fields($data){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPITable = $this->modx->getObject("gtsAPITable",(int)$data['id'])) 
            return $this->error("Таблица api не найдена!");
        if(!$gtsAPIPackage = $this->modx->getObject('gtsAPIPackage',$gtsAPITable->package_id)){
            return $this->error("gtsAPIPackage api не найден!"); 
        }
        $this->modx->addPackage($gtsAPIPackage->name, MODX_CORE_PATH . "components/{$gtsAPIPackage->name}/model/");
        
        $rule = $gtsAPITable->toArray();
        if($rule['properties']){
            $properties = json_decode($rule['properties'],1);
        }
        if($properties and is_array($properties)){
            $rule['properties'] = $properties;
        }else{
            $rule['properties'] = [];
        }
        if(!empty($rule['properties']['fields'])){
            return $this->error("Поля уже заданы");
        }
        if(empty($rule['class'])){
            $rule['class'] = $rule['table'];
        }
        if($gtsAPITable->tree){
            $controller_class = 'treeAPIController';
            $rule['controller_path'] = $this->config['corePath'] . 'api_controllers/tree.class.php';
        }else{
            $controller_class = 'tableAPIController';
            $rule['controller_path'] = $this->config['corePath'] . 'api_controllers/table.class.php';
        }
        $loaded = include_once($rule['controller_path']);
        if ($loaded) {
            $controller = new $controller_class($this->modx,$this->config);
            $fields = $controller->gen_fields($rule);
        }else{
            return $this->error("Not load class $controller_class {$rule['controller_path']}!");
        }
        return $this->success('options',['fields'=>$fields]);
    }
    public function save_rule($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPIRule = $this->modx->getObject("gtsAPIRule",(int)$data['id'])) 
            return $this->error("Правило api не найдено!");
        $rule = json_decode($data['rule_json'],1);
        if(!is_array($rule)) return $this->error("Не верный json!");
        $gtsAPIRule->fromArray($rule);
        $gtsAPIRule->save();
        if(!is_array($rule['gtsAPIActions'])) return $this->error("Не верный json2!");
        foreach($rule['gtsAPIActions'] as $action){
            if(!$gtsAPIAction = $this->modx->getObject('gtsAPIAction',['gtsaction'=>$action['gtsaction'],'rule_id'=>$gtsAPIRule->id])){
                $gtsAPIAction = $this->modx->newObject('gtsAPIAction',['gtsaction'=>$action['gtsaction'],'rule_id'=>$gtsAPIRule->id]);
            }
            if($gtsAPIAction){
                $gtsAPIAction->fromArray($action);
                $gtsAPIAction->rule_id = $gtsAPIRule->id;
                $gtsAPIAction->save();
            }
        }
        return $this->success("Успешно!");
    }
    public function export_rule($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPIRule = $this->modx->getObject("gtsAPIRule",(int)$data['id'])) 
            return $this->error("Правило АПИ не найдено!");
        $default = array(
            'class' => 'gtsAPIAction',
            'select'=>[
                'gtsAPIAction'=>'*',
            ],
            'where' => ['rule_id'=>$gtsAPIRule->id,'active'=>1],
            'sortby' => [
                "gtsAPIAction.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => 0,
        );
        $this->pdo->setConfig($default);
        $gtsAPIActions = $this->pdo->run();
        $rule = $gtsAPIRule->toArray();
        $rule['gtsAPIActions'] = $gtsAPIActions;
        $modal = $this->pdo->getChunk('tpl.gtsAPI.Modal',[
            'action'=>'gtsapi/save_rule',
            'id'=>$rule['id'],
            'hash'=>$data['hash'],
            'rule_json'=>json_encode($rule,JSON_PRETTY_PRINT)
        ]);
        return $this->success('',['modal'=>$modal]);
    }

    public function export_table($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPITable = $this->modx->getObject("gtsAPITable",(int)$data['id'])) 
            return $this->error("gtsAPITable не найдено!");
        $rule = $gtsAPITable->toArray();
        $rule['properties'] = json_decode($rule['properties'],1);
        $modal = $this->pdo->getChunk('tpl.gtsAPI.Modal',[
            'action'=>'gtsapi/save_table',
            'id'=>$rule['id'],
            'hash'=>$data['hash'],
            'rule_json'=>json_encode($rule,JSON_PRETTY_PRINT)
        ]);
        return $this->success('',['modal'=>$modal]);
    }
    public function save_table($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPITable = $this->modx->getObject("gtsAPITable",(int)$data['id'])) 
            return $this->error("gtsAPITable не найдено!");
        $rule = json_decode($data['rule_json'],1);
        if(!is_array($rule)) return $this->error("Не верный json!");
        if(!empty($rule['properties'])) $rule['properties'] = json_encode($rule['properties'],JSON_PRETTY_PRINT);
        $gtsAPITable->fromArray($rule);
        $gtsAPITable->save();
        return $this->success("Успешно!");
    }
    public function export_select($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPISelect = $this->modx->getObject("gtsAPISelect",(int)$data['id'])) 
            return $this->error("gtsAPISelect не найдено!");
        $rule = $gtsAPISelect->toArray();
        $rule['rows'] = json_decode($rule['rows'],1);
        $modal = $this->pdo->getChunk('tpl.gtsAPI.Modal',[
            'action'=>'gtsapi/save_select',
            'id'=>$rule['id'],
            'hash'=>$data['hash'],
            'rule_json'=>json_encode($rule,JSON_PRETTY_PRINT)
        ]);
        return $this->success('',['modal'=>$modal]);
    }
    public function save_select($data = []){
        if(isset($data['trs_data'][0]['id'])) $data['id'] = $data['trs_data'][0]['id'];
        if(!$gtsAPISelect = $this->modx->getObject("gtsAPISelect",(int)$data['id'])) 
            return $this->error("gtsAPISelect не найдено!");
        $rule = json_decode($data['rule_json'],1);
        if(!is_array($rule)) return $this->error("Не верный json!");
        if(!is_array($rule['rows'])) $rule['rows'] = json_encode($rule['rows'],JSON_PRETTY_PRINT);
        $gtsAPITable->fromArray($rule);
        $gtsAPITable->save();
        return $this->success("Успешно!");
    }
    public function gen_rules($data = []){
        $modal = $this->pdo->getChunk('tpl.gtsAPI.Modal.GenRules',[
            'id'=>$data['id'],
            'hash'=>$data['hash']
        ]);
        return $this->success('',['modal'=>$modal]);
    }
    public function generate_rules($data = []){
        if(empty($data['package'])) return $this->error("Empty package!");
        
        $mapFile = MODX_CORE_PATH . "components/{$data['package']}/model/{$data['package']}/" . 'metadata.mysql.php';
        if (file_exists($mapFile)) {
            include $mapFile;
            if (!empty($xpdo_meta_map)) {
                foreach ($xpdo_meta_map as $className => $extends) {
                    foreach($extends as $class){
                        if(!$gtsAPIRule = $this->modx->getObject('gtsAPIRule',['point'=>$class])){
                            if($gtsAPIRule = $this->modx->newObject('gtsAPIRule',[
                                'point'=>$class,
                                'package'=>$data['package'],
                                'class'=>$class,
                            ])){
                                if($gtsAPIRule->save()){
                                    foreach(['create','read','update','delete'] as $action){
                                        if($gtsAPIAction = $this->modx->newObject('gtsAPIAction',[
                                            'rule_id'=>$gtsAPIRule->id,
                                            'gtsaction'=>$action,
                                            'authenticated'=>1,
                                        ])){
                                            $gtsAPIAction->save();
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }
                }
            }
        }else{
            $this->error("mapFile не найден $mapFile!");
        }
        return $this->success('Успешно');
    }
}
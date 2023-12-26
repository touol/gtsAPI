<?php

class securityAPIController{
    public $config = [];
    public $modx;
    public $pdo;

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
    public function route($rule, $uri, $method, $request, $id){
        
            
        switch($rule['point']){
            case 'security/login':
                $req = json_decode(file_get_contents('php://input'), true);
                $data = array(
                    'username' => $req['username'],
                    'password' => $req['password'],
                    'rememberme' => 0,
                    'login_context' => 'web',
                );
                $response = $this->modx->runProcessor('/security/login', $data);
                if ($response->isError()) {
                    header('HTTP/1.1 404 Not found');
                    return $this->error("Not found user!");
                }
                if(!$user = $this->modx->getObject('modUser', array('username' => $req['username']))){
                    header('HTTP/1.1 404 Not found');
                    return $this->error("Not found user!");
                }
                $headers = array('alg'=>'HS256','typ'=>'JWT');
                $day_exp = $this->modx->getOption('gtsapi_day_exp', null, 30);
                $payload = array('user_id'=>$user->id, 'exp'=>(strtotime("+$day_exp days")));

                $jwt = generate_jwt($headers, $payload, $user->salt);
                $table = $this->modx->getTableName('gtsAPIToken');
                
                $query= new xPDOCriteria($this->modx, 
                        "INSERT INTO {$table} (`user_id`,`token`,`valid_till`,`created_at`,`ip`,`active`)
                        VALUES (:user_id, :token, :valid_till, :created_at, :ip, 1)", [
                        ':user_id' => $user->id,
                        ':token' => $jwt,
                        ':valid_till' => date('Y-m-d H:i:s', strtotime("+$day_exp days")),
                        ':created_at' => date('Y-m-d H:i:s'),
                        ':ip' => $_SERVER['REMOTE_ADDR'],
                ]);
                if ($query->prepare() && $query->stmt->execute()) {
                    return $this->success('',['token'=>$jwt,'day_exp'=>$day_exp]);
                }
            break;
            case 'security/logout':
                if($jwt = get_bearer_token()){
                    if(!is_jwt_valid($jwt, $this->modx->user->salt)){
                        header('HTTP/1.1 404 Not found');
                        return $this->error("Not found user!");
                    }
                    $table = $this->modx->getTableName('gtsAPIToken');
                    $query= new xPDOCriteria($this->modx, 
                        "SELECT * FROM {$table} WHERE `user_id` = :user_id AND `token` = :token AND `active` = 1 LIMIT 1", [
                        ':user_id' => $this->modx->user->id,
                        ':token' => $jwt,
                    ]);
                    if ($query->prepare() && $query->stmt->execute()) {
                        $gtsAPITokens = $query->stmt->fetchAll(PDO::FETCH_ASSOC);
                        if(is_array($gtsAPITokens) and count($gtsAPITokens) == 1){
                            $query= new xPDOCriteria($this->modx, 
                                "UPDATE * {$table} SET `active` = 0 WHERE `user_id` = :user_id AND `token` = :token", [
                                ':user_id' => $this->modx->user->id,
                                ':token' => $jwt,
                            ]);
                            if ($query->prepare() && $query->stmt->execute()) {
                                $resp = $this->modx->runProcessor('/security/logout');
                                return $this->success();
                            }
                        }
                    }
                }
                header('HTTP/1.1 404 Not found');
                return $this->error("Not found user!");
            break;
        }
        
    }

    public function success($message = "",$data = []){
        //return array('success'=>1,'message'=>$message,'data'=>$data);
        header("HTTP/1.1 200 OK");
        return $data;
    }
    public function error($message = "",$data = []){
        return array('success'=>0,'message'=>$message,'data'=>$data);
    }
}
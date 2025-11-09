<?php
require_once (dirname(__FILE__) . '/table.class.php');
class jsonTableAPIController extends tableAPIController{


    function __construct(modX &$modx, array $config = [])
    {
        parent :: __construct(
            $modx,
            $config
        );
    }

    public function delete($rule,$request,$action){
        
        if(!empty($request['ids'])){
            if(is_string($request['ids'])) $ids = explode(',',$request['ids']);
            $resp = $this->getJSON($rule,$request);
            if(!$resp['success']) return $resp;
            $rows0 = $rows2 = $resp['data']['where']['json'];
            $keys = [];
            if(!empty($rule['properties']['json_path']['key'])){
                $key_path = $this->replaceKeyPlaceholders($rule['properties']['json_path']['key'], $request);
                $keys = explode('.', $key_path);
                foreach($keys as $key){
                    if(isset($rows0[$key]) and !empty($rows0[$key])){
                        $rows0 = $rows0[$key];
                    }else{
                        $rows0 = [];
                    }
                }
            }
            $obj = $resp['data']['where']['obj'];
            foreach($rows0 as $k1=>$row){
                if(in_array((string)$row['id'], $ids)){
                    $object_old = $row;
                    $resp = $this->run_triggers($rule, 'before', 'remove', [], $object_old);
                    if(!$resp['success']) return $resp;
                    switch(count($keys)){
                        case 0:
                            unset($rows2[$k1]);
                        break;
                        case 1:
                            unset($rows2[$keys[0]][$k1]);
                        break;
                        case 2:
                            unset($rows2[$keys[0]][$keys[1]][$k1]);
                        break;
                        case 3:
                            unset($rows2[$keys[0]][$keys[1]][$keys[2]][$k1]);
                        break;
                        case 4:
                            unset($rows2[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$k1]);
                        break;
                        default:
                        return $this->error('long key');
                    }
                    
                }
            }
            $obj->set($rule['properties']['json_path']['field'],json_encode($rows2));
            if($obj->save()){
                $resp = $this->run_triggers($rule, 'after', 'remove', [], $object_old);
                if(!$resp['success']) return $resp;
                return $this->success('delete',['ids'=>$request['ids']]);
            }
        }
        return $this->error('delete_error');
    }
    
    public function create($rule,$request,$action){
        $resp = $this->getJSON($rule,$request);
        if(!$resp['success']) return $resp;
        $rows0 = $rows2 = $resp['data']['json'];
        $keys = [];
        if(!empty($rule['properties']['json_path']['key'])){
            $key_path = $this->replaceKeyPlaceholders($rule['properties']['json_path']['key'], $request);
            $keys = explode('.', $key_path);
            foreach($keys as $key){
                if(isset($rows0[$key]) and !empty($rows0[$key])){
                    $rows0 = $rows0[$key];
                }else{
                    $rows0 = [];
                }
            }
        }
        $obj = $resp['data']['obj'];
        $max_id = 0;
        foreach($rows0 as $k1=>$row){
            if($row['id'] > $max_id) $max_id = $row['id'];
        }
        $object_old =  [];
        $object_new = [];
        $object_new = array_merge($object_new,$resp['data']['where']);

        $data = $this->addDefaultFields($rule,$request);
        $request = $this->request_array_to_json($request);
        $request = array_merge($request,$data);
        foreach($rule['properties']['fields'] as $field=>$v){
            if(isset($request[$field])){
                $object_new[$field] = $request[$field];
            }else if(isset($v['default'])){
                $object_new[$field] = $v['default'];
            }
        }
        $object_new['id'] = $max_id + 1;
        $resp = $this->run_triggers($rule, 'before', 'create', $request, $object_old,$object_new,$obj);
        if(!$resp['success']) return $resp;
        switch(count($keys)){
            case 0:
                $rows2[] = $object_new;
            break;
            case 1:
                $rows2[$keys[0]][] = $object_new;
            break;
            case 2:
                $rows2[$keys[0]][$keys[1]][] = $object_new;
            break;
            case 3:
                $rows2[$keys[0]][$keys[1]][$keys[2]][] = $object_new;
            break;
            case 4:
                $rows2[$keys[0]][$keys[1]][$keys[2]][$keys[3]][] = $object_new;
            break;
            default:
            return $this->error('long key');
        }
        $obj->set($rule['properties']['json_path']['field'],json_encode($rows2));
        if($obj->save()){
            $resp = $this->run_triggers($rule, 'after', 'create', $request, $object_old,$object_new,$obj);
            if(isset($rule['properties']['fields'])){
                $get_html = false;
                foreach($rule['properties']['fields'] as $field=>$v){
                    if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) $get_html = true;
                }
                if($get_html){
                    foreach($rule['properties']['fields'] as $field=>$v){
                        if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])){
                            $object_new[$field] = $this->pdoTools->getChunk("@INLINE ".$v['tpl'],$object_new);
                        }
                    }
                }
            }
            $resp['data']['object'] = $object_new;
            if(!$resp['success']) return $resp;
            $data = $resp['data'];

            return $this->success('update',$data);
        }

        return $this->error('create_error',$request);
    }
    public function update($rule,$request,$action){
        
        $resp = $this->getJSON($rule,$request);
        if(!$resp['success']) return $resp;
        $rows0 = $rows2 = $resp['data']['json'];
        $keys = [];
        if(!empty($rule['properties']['json_path']['key'])){
            $key_path = $this->replaceKeyPlaceholders($rule['properties']['json_path']['key'], $request);
            $keys = explode('.', $key_path);
            foreach($keys as $key){
                if(isset($rows0[$key]) and !empty($rows0[$key])){
                    $rows0 = $rows0[$key];
                }else{
                    $rows0 = [];
                }
            }
        }
        $obj = $resp['data']['obj'];
        foreach($rows0 as $k1=>$row){
            if($row['id'] == (int)$request['id']){
                $object_old = $row;
                $object_new = json_decode(json_encode($row),1);
                $object_new = array_merge($object_new,$resp['data']['where']);
                $data = [];
                $request = $this->request_array_to_json($request);
                $request = array_merge($request,$data);
                foreach($rule['properties']['fields'] as $field=>$v){
                    if(isset($request[$field])) $object_new[$field] = $request[$field];
                }
                $resp = $this->run_triggers($rule, 'before', 'update', $request, $object_old,$object_new,$obj);
                
                if(!$resp['success']) return $resp;
                switch(count($keys)){
                    case 0:
                        $rows2[$k1] = $object_new;
                    break;
                    case 1:
                        $rows2[$keys[0]][$k1] = $object_new;
                    break;
                    case 2:
                        $rows2[$keys[0]][$keys[1]][$k1] = $object_new;
                    break;
                    case 3:
                        $rows2[$keys[0]][$keys[1]][$keys[2]][$k1] = $object_new;
                    break;
                    case 4:
                        $rows2[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$k1] = $object_new;
                    break;
                    default:
                    return $this->error('long key');
                }
                $obj->set($rule['properties']['json_path']['field'],json_encode($rows2));
                if($obj->save()){
                    $resp = $this->run_triggers($rule, 'after', 'update', $request, $object_old,$object_new,$obj);
                    if(isset($rule['properties']['fields'])){
                        $get_html = false;
                        foreach($rule['properties']['fields'] as $field=>$v){
                            if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) $get_html = true;
                        }
                        if($get_html){
                            foreach($rule['properties']['fields'] as $field=>$v){
                                if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])){
                                    $object_new[$field] = $this->pdoTools->getChunk("@INLINE ".$v['tpl'],$object_new);
                                }
                            }
                        }
                    }
                    $resp['data']['object'] = $object_new;
                    if(!$resp['success']) return $resp;
                    $data = $resp['data'];

                    return $this->success('update',$data);
                }
            }
        }
        
        return $this->error('update_error',['action'=>$action,'rule'=>$rule,'request'=>$request]);
    }
    
    public function read($rule,$request,$action, $where = [], $internal_action = ''){
        $resp = $this->run_triggers($rule, 'before', 'read', $request);
        if(!$resp['success']) return $resp;
        $resp = $this->getJSON($rule,$request);
        if(!$resp['success']){
            $resp['data'] = [
                'rows'=>[],
                'total'=>0,
            ];
            return $resp;
        }

        $rows0 = $resp['data']['json'];
        if(!empty($rule['properties']['json_path']['key'])){
            $key_path = $this->replaceKeyPlaceholders($rule['properties']['json_path']['key'], $request);
            $keys = explode('.', $key_path);
            foreach($keys as $key){
                if(isset($rows0[$key]) and !empty($rows0[$key])){
                    $rows0 = $rows0[$key];
                }else{
                    $rows0 = [];
                }
            }
        }
        
        if(isset($rule['properties']['fields'])){
            $get_html = false;
            foreach($rule['properties']['fields'] as $field=>$v){
                if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])) $get_html = true;
            }
            if($get_html){
                foreach($rows0 as $k=>$row){
                    foreach($rule['properties']['fields'] as $field=>$v){
                        if(isset($v['type']) and $v['type'] == 'html' and isset($v['tpl'])){
                            $rows0[$k][$field] = $this->pdoTools->getChunk("@INLINE ".$v['tpl'],$row);
                        }
                    }
                }
            }
        }
        $where = $this->aplyFilters($rule,$request['filters']);
        if($request['multiSortMeta']){
            foreach($request['multiSortMeta'] as $sort){
                // $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ?'ASC':'DESC';
                usort($rows0, function($a, $b) use ($sort) {
                    if($sort['order'] == 1){
                        return $a[$sort['field']] <= $b[$sort['field']];
                    }else{
                        return $a[$sort['field']] > $b[$sort['field']];
                    }
                });
            }
        }
        $k = 1;
        $total = 0;
        if(isset($request['limit']) and isset($request['offset'])){
            $rows = [];
            foreach($rows0 as $row){
                if(!$this->php_filter_array_like_modx($row,$where)) continue;
                if($k > $request['offset'] and $k <= $request['offset'] + $request['limit']) $rows[] = $row;
                $k++;
                $total++;
            }
        }else{
            $rows = [];
            foreach($rows0 as $row){
                if(!$this->php_filter_array_like_modx($row,$where)) continue;
                $rows[] = $row;
                $total++;
            }
        }
        
        $out = [
            'rows'=>$rows,
            'total'=>$total,
            // 'where'=>$where,
        ];

        $out['autocomplete'] = $this->autocompletes($rule['properties']['fields'],$rows,0);
        
        
        $resp = $this->run_triggers($rule, 'after', 'read', $request, $out);
        
        if(!$resp['success']) return $resp;
        
        if(!empty($resp['data']['out'])) $out = $resp['data']['out'];
        
        return $this->success('',$out);
    }
    public function getJSONWhere($rule,$request){
        if(empty($rule['properties']['json_path'])) return $this->error('not set json_path');
        $json_path = $rule['properties']['json_path'];
        if(empty($json_path['where']) and !is_array($json_path['where'])) return $this->error('not set json_path where');
        if(empty($json_path['field']) and !is_array($json_path['field'])) return $this->error('not set json_path field'); //field
        if(empty($request['filters'])) return $this->error('empty filters');
        $where = [];
        foreach($json_path['where'] as $k=>$v){
            if(!isset($request['filters'][$v])) continue;
            if(isset($request['filters'][$v]['constraints'])){
                $where[$k] = $request['filters'][$v]['constraints'][0]['value'];
            }else if(isset($request['filters'][$v]['value'])){
                $where[$k] = $request['filters'][$v]['value'];
            }
        }
        if(empty($where)) return $this->error('empty where');
        return $this->success('',$where);
    }
    public function getJSON($rule,$request){
        $resp = $this->getJSONWhere($rule,$request);
        // $this->modx->log(modX::LOG_LEVEL_ERROR, '[jsonTableAPIController::getJSON] Response error: ' . print_r($resp, true));
        if(!$resp['success']) return $resp;
        
        if(!$obj = $this->modx->getObject($rule['class'],$resp['data'])){
            // return $this->error('not found object',$resp['data']);
            $obj = $this->modx->newObject($rule['class'],$resp['data']);
        }
        $json = $obj->get($rule['properties']['json_path']['field']);
        // $this->modx->log(modX::LOG_LEVEL_ERROR, '[jsonTableAPIController::getJSON] $json ' . print_r($json, true));
        if(empty($json)){
            return $this->success('',['obj'=>$obj,'json'=>[]]);
        }else{
            if(!is_array($json)) $json = json_decode($json,1);
            if(!is_array($json)) return $this->success('',['obj'=>$obj,'json'=>[],'where'=>$resp['data']]);
            return $this->success('',['obj'=>$obj,'json'=>$json,'where'=>$resp['data']]);
        }
        return $this->error('not found object');
    }
    /**
     * Заменяет плейсхолдеры в формате {field_name} на значения из фильтров
     * @param string $key_path Путь с плейсхолдерами, например 'reiki.{shina}'
     * @param array $request Запрос с фильтрами
     * @return string Путь с замененными значениями
     */
    protected function replaceKeyPlaceholders($key_path, $request){
        if(empty($request['filters'])) return $key_path;
        
        // Находим все плейсхолдеры в формате {field_name}
        preg_match_all('/\{([^}]+)\}/', $key_path, $matches);
        
        if(!empty($matches[1])){
            foreach($matches[1] as $placeholder){
                // Получаем значение из фильтров
                $value = null;
                if(isset($request['filters'][$placeholder])){
                    if(isset($request['filters'][$placeholder]['constraints'])){
                        $value = $request['filters'][$placeholder]['constraints'][0]['value'];
                    }else if(isset($request['filters'][$placeholder]['value'])){
                        $value = $request['filters'][$placeholder]['value'];
                    }
                }
                
                // Заменяем плейсхолдер на значение
                if($value !== null){
                    $key_path = str_replace('{'.$placeholder.'}', $value, $key_path);
                }
            }
        }
        
        return $key_path;
    }
    
    public function php_filter_array_like_modx($row,$where){
        // $check = true;
        foreach($row as $fieldr=>$vr){
            foreach($where as $k=>$v){
                $k = str_replace('`','',$k);
                $arr = explode('.',$k);
                if(count($arr) == 1){
                    $field = $arr[0];
                }else{
                    $field = $arr[1];
                }
                $arr = explode(':',$field);
                if(count($arr) == 1){
                    if($fieldr == $field and $vr != $v) return false;
                }else{
                    $field = $arr[0]; $op = $arr[1];
                    switch($op){
                        case '!=':
                            if($fieldr == $field and $vr == $v) return false;
                        break;
                        case 'LIKE':
                            // $v = str_replace('%','',$v);
                            if($fieldr == $field){
                                if(preg_match(sprintf('/^%s$/i', preg_replace('/(^%)|(%$)/', '.*', $v)), $vr) !== 1) return false;
                            }
                        break;
                        case 'NOT LIKE':
                            // $v = str_replace('%','',$v);
                            if($fieldr == $field){
                                if(preg_match(sprintf('/^%s$/i', preg_replace('/(^%)|(%$)/', '.*', $v)), $vr) === 1) return false;
                            }
                        break;
                        case 'IN':
                            if($fieldr == $field and !in_array($vr,$v)) return false;
                        break;
                        case '<':
                            if($fieldr == $field and $vr >= $v) return false;
                        break;
                        case '<=':
                            if($fieldr == $field and $vr > $v) return false;
                        break;
                        case '>':
                            if($fieldr == $field and $vr <= $v) return false;
                        break;
                        case '>=':
                            if($fieldr == $field and $vr < $v) return false;
                        break;
                    }
                }
            }
        }
        return true;
    }
}

<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var gtsAPI $gtsAPI */
$gtsAPI = $modx->getService('gtsAPI', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/', $scriptProperties);
if (!$gtsAPI) {
    return 'Could not load gtsAPI class!';
}
$gtsAPI->initialize();
// if($modx->user->isMember('Administrator')){
//     $where = [];
// }else{
//     $where = ['user_id'=>$modx->user->id];
// }
// $gtsAPI->pdo->setConfig([
//     'class'=>'gtsAPIOtdel',
//     'where'=>$where,
//     'return'=>'data',
//     'limit'=>0
// ]);
// $otdels = $gtsAPI->pdo->run();
// if(!is_array($otdels) or count($otdels) == 0) return 'Нет доступа';
// $otdel_ids = [];
// $department_ids = [];
// foreach($otdels as $o){
//     $otdel_ids[] = $o['id'];
//     $department_ids[$o['department_id']] = $o['department_id'];
// }
// $gtsAPI->pdo->setConfig([
//     'class'=>'gtsAPIOtdelTypeZP',
//     'where'=>[
//         'otdel_id:IN'=>$otdel_ids
//     ],
//     'return'=>'data',
//     'limit'=>0
// ]);
// $gtsAPIOtdelTypeZPs = $gtsAPI->pdo->run();
// //echo '<pre>'.$gtsAPI->pdo->getTime().'</pre>';
// if(!is_array($gtsAPIOtdelTypeZPs) or count($gtsAPIOtdelTypeZPs) == 0) return 'Нет доступа';
// $type_zp_ids = [];
// foreach($gtsAPIOtdelTypeZPs as $v){
//     $type_zp_ids[$v['type_zp_id']] = $v['type_zp_id'];
// }
// $modx->setPlaceholders([
//     'otdel_ids'=>implode(',',$otdel_ids),
//     'department_ids'=>implode(',',$department_ids),
//     'type_zp_ids'=>implode(',',$type_zp_ids),
//     //'name_setting'=>$otdels[0]['name_setting'],
// ],'gtsapi_');
return;
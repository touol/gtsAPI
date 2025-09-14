<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;
$success = false;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $packageName = $options['namespace'] ?? 'unknown';
        $resourceGroupsFile = MODX_CORE_PATH . 'components/' . $packageName . '/resource_groups.json';
        
        if (file_exists($resourceGroupsFile)) {
            $resourceGroupsData = json_decode(file_get_contents($resourceGroupsFile), true);
            if (is_array($resourceGroupsData) && !empty($resourceGroupsData)) {
                foreach ($resourceGroupsData as $group_key => $group_data) {
                    // Проверяем, существует ли группа ресурсов
                    $existing_group = $modx->getObject('modResourceGroup', array('name' => $group_data['name']));
                    
                    if (!$existing_group) {
                        // Создаем новую группу ресурсов
                        $resource_group = $modx->newObject('modResourceGroup');
                        $resource_group->fromArray($group_data);
                        
                        if ($resource_group->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, 'Создана группа ресурсов: ' . $group_data['name']);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка создания группы ресурсов: ' . $group_data['name']);
                        }
                    } else {
                        // Обновляем существующую группу ресурсов
                        $existing_group->fromArray($group_data);
                        
                        if ($existing_group->save()) {
                            $modx->log(modX::LOG_LEVEL_INFO, 'Обновлена группа ресурсов: ' . $group_data['name']);
                        } else {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка обновления группы ресурсов: ' . $group_data['name']);
                        }
                    }
                }
            }
        }
        
        $success = true;
        break;
        
    case xPDOTransport::ACTION_UNINSTALL:
        $packageName = $options['namespace'] ?? 'unknown';
        $resourceGroupsFile = MODX_CORE_PATH . 'components/' . $packageName . '/resource_groups.json';
        
        if (file_exists($resourceGroupsFile)) {
            $resourceGroupsData = json_decode(file_get_contents($resourceGroupsFile), true);
            if (is_array($resourceGroupsData) && !empty($resourceGroupsData)) {
                foreach ($resourceGroupsData as $group_key => $group_data) {
                    $existing_group = $modx->getObject('modResourceGroup', array('name' => $group_data['name']));
                    
                    if ($existing_group) {
                        // Проверяем, есть ли ресурсы в этой группе
                        $resources_in_group = $modx->getCount('modResourceGroupResource', array('document_group' => $existing_group->get('id')));
                        
                        if ($resources_in_group == 0) {
                            // Удаляем группу только если в ней нет ресурсов
                            if ($existing_group->remove()) {
                                $modx->log(modX::LOG_LEVEL_INFO, 'Удалена группа ресурсов: ' . $group_data['name']);
                            } else {
                                $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка удаления группы ресурсов: ' . $group_data['name']);
                            }
                        } else {
                            $modx->log(modX::LOG_LEVEL_INFO, 'Группа ресурсов не удалена (содержит ресурсы): ' . $group_data['name']);
                        }
                    }
                }
            }
        }
        
        $success = true;
        break;
}

return $success;

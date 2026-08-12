<?php
/**
 * Резолвер групп пользователей и прав на группы ресурсов.
 *
 * Когда применять: пакету нужна СВОЯ группа пользователей (например, для закрытого
 * раздела, куда админ просто добавляет людей). Если задача — ограничить доступ к
 * данным/действиям, обычно правильнее шаблон разрешений MODX: см.
 * permissions.resolver.php — он не плодит групп и ложится на те, что уже есть на сайте.
 *
 * Формат user_groups.json (из _build/configs/user_groups.js):
 * {
 *   "MyGroup": {
 *      "description": "Доступ к разделу",
 *      "resource_groups": ["my_group_pages"],  // группы ресурсов, к которым даём доступ
 *      "policy": "Resource",                   // имя политики доступа (по умолчанию Resource)
 *      "context": "web",                       // контекст (по умолчанию web)
 *      "authority": 9999                       // уровень (по умолчанию 9999)
 *   }
 * }
 *
 * Идемпотентно: повторная установка не плодит ни групп, ни ACL.
 *
 * @var xPDOTransport $transport
 * @var array $options
 * @var modX $modx
 */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;
$success = false;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $packageName = $options['namespace'] ?? 'unknown';
        $file = MODX_CORE_PATH . 'components/' . $packageName . '/user_groups.json';
        if (!file_exists($file)) {
            return true;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data) || empty($data)) {
            return true;
        }

        foreach ($data as $groupName => $cfg) {
            if (!is_array($cfg)) {
                $cfg = [];
            }

            // 1. Группа пользователей
            $group = $modx->getObject('modUserGroup', ['name' => $groupName]);
            if (!$group) {
                $group = $modx->newObject('modUserGroup');
                $group->fromArray([
                    'name' => $groupName,
                    'description' => $cfg['description'] ?? '',
                ], '', true, true);
                if (!$group->save()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, "[{$packageName}] не создалась группа пользователей {$groupName}");
                    continue;
                }
                $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] создана группа пользователей {$groupName}");
            }

            // 2. Политика доступа
            $policyName = $cfg['policy'] ?? 'Resource';
            $policy = $modx->getObject('modAccessPolicy', ['name' => $policyName]);
            if (!$policy) {
                $modx->log(modX::LOG_LEVEL_WARN,
                    "[{$packageName}] политика '{$policyName}' не найдена, права не выставлены для {$groupName}");
                continue;
            }

            // 3. Доступ группы к группам ресурсов пакета
            $context = $cfg['context'] ?? 'web';
            $authority = isset($cfg['authority']) ? (int)$cfg['authority'] : 9999;
            foreach ((array)($cfg['resource_groups'] ?? []) as $rgName) {
                $rg = $modx->getObject('modResourceGroup', ['name' => $rgName]);
                if (!$rg) {
                    $modx->log(modX::LOG_LEVEL_WARN,
                        "[{$packageName}] группа ресурсов '{$rgName}' не найдена (создаётся резолвером resource_groups)");
                    continue;
                }

                $criteria = [
                    'target' => $rg->get('id'),
                    'principal_class' => 'modUserGroup',
                    'principal' => $group->get('id'),
                    'context_key' => $context,
                ];
                if ($modx->getObject('modAccessResourceGroup', $criteria)) {
                    continue; // уже есть — не плодим
                }

                $acl = $modx->newObject('modAccessResourceGroup');
                $acl->fromArray(array_merge($criteria, [
                    'authority' => $authority,
                    'policy' => $policy->get('id'),
                ]), '', true, true);
                if ($acl->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO,
                        "[{$packageName}] доступ: {$groupName} → группа ресурсов {$rgName} ({$policyName})");
                } else {
                    $modx->log(modX::LOG_LEVEL_ERROR,
                        "[{$packageName}] не выставился доступ {$groupName} → {$rgName}");
                }
            }
        }

        $modx->cacheManager->refresh();
        $success = true;
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        // Группы и права не удаляем: в них уже могут быть добавлены люди,
        // а снос прав при обновлении/переустановке — худшее, что может сделать пакет.
        $success = true;
        break;
}

return $success;

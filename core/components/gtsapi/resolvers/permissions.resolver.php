<?php
/**
 * Резолвер разрешений MODX: шаблон политик + сами разрешения + (опционально) политика.
 *
 * Зачем: пакету нужно ограничить доступ, но плодить свои группы пользователей нельзя —
 * админ чужого сайта им не обрадуется, а группы у всех свои. Правильный путь MODX —
 * привезти ШАБЛОН РАЗРЕШЕНИЙ со своими permissions. Дальше админ сам вешает политику
 * на ту группу, которая у него уже есть.
 *
 * Проверяются такие разрешения штатно: gtsAPI умеет `permissions` в конфиге таблицы
 * (modX::hasPermission), рядом с `groups`.
 *
 * Формат permissions.json (из _build/configs/permissions.js):
 * {
 *   "TotalLogTemplate": {
 *     "description": "Права компонента TotalLog",
 *     "template_group": "Admin",                       // имя или id группы шаблонов (по умолчанию Admin)
 *     "permissions": {
 *        "totallog_view":  "Просмотр журнала запросов",
 *        "totallog_admin": "Полный доступ к журналу"
 *     },
 *     "policies": {                                     // необязательно: готовые политики
 *        "TotalLog":       { "description": "...", "permissions": ["totallog_view"] },
 *        "TotalLog Admin": { "description": "...", "permissions": ["totallog_view","totallog_admin"] }
 *     }
 *   }
 * }
 *
 * Идемпотентно. При удалении пакета ничего не сносится — политики уже могут висеть
 * на группах пользователей.
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
        $file = MODX_CORE_PATH . 'components/' . $packageName . '/permissions.json';
        if (!file_exists($file)) {
            return true;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data) || empty($data)) {
            return true;
        }

        foreach ($data as $templateName => $cfg) {
            if (!is_array($cfg)) {
                continue;
            }

            // --- Группа шаблонов ---
            $groupId = 1; // Admin
            if (!empty($cfg['template_group'])) {
                if (is_numeric($cfg['template_group'])) {
                    $groupId = (int)$cfg['template_group'];
                } elseif ($tg = $modx->getObject('modAccessPolicyTemplateGroup', ['name' => $cfg['template_group']])) {
                    $groupId = (int)$tg->get('id');
                }
            }

            // --- Шаблон политик ---
            $tpl = $modx->getObject('modAccessPolicyTemplate', ['name' => $templateName]);
            if (!$tpl) {
                $tpl = $modx->newObject('modAccessPolicyTemplate');
                $tpl->fromArray([
                    'name' => $templateName,
                    'description' => $cfg['description'] ?? '',
                    'template_group' => $groupId,
                ], '', true, true);
                if (!$tpl->save()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, "[{$packageName}] не создался шаблон разрешений {$templateName}");
                    continue;
                }
                $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] создан шаблон разрешений {$templateName}");
            }

            // --- Разрешения внутри шаблона ---
            foreach ((array)($cfg['permissions'] ?? []) as $permName => $permDesc) {
                $criteria = ['template' => $tpl->get('id'), 'name' => $permName];
                if ($modx->getObject('modAccessPermission', $criteria)) {
                    continue;
                }
                $perm = $modx->newObject('modAccessPermission');
                $perm->fromArray(array_merge($criteria, [
                    'description' => $permDesc,
                    'value' => 1,
                ]), '', true, true);
                if ($perm->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] разрешение {$permName} → {$templateName}");
                }
            }

            // --- Готовые политики на основе шаблона ---
            foreach ((array)($cfg['policies'] ?? []) as $policyName => $policyCfg) {
                if ($modx->getObject('modAccessPolicy', ['name' => $policyName])) {
                    continue; // не перетираем — админ мог её донастроить
                }
                $permData = [];
                foreach (array_keys((array)($cfg['permissions'] ?? [])) as $permName) {
                    $permData[$permName] = in_array($permName, (array)($policyCfg['permissions'] ?? []), true);
                }
                $policy = $modx->newObject('modAccessPolicy');
                $policy->fromArray([
                    'name' => $policyName,
                    'description' => $policyCfg['description'] ?? '',
                    'parent' => 0,
                    'class' => '',
                    'data' => json_encode($permData),
                    'template' => $tpl->get('id'),
                ], '', true, true);
                if ($policy->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] создана политика доступа {$policyName}");
                }
            }
        }

        $modx->cacheManager->refresh();
        $success = true;
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        // Ничего не удаляем: политики уже могут быть назначены группам пользователей.
        $success = true;
        break;
}

return $success;

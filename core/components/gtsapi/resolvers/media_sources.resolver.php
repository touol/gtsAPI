<?php
/**
 * Резолвер медиаисточников пакета: файлы пакета (фото, сканы) лежат в своём источнике, а не в общем gtsAPIFile.
 *
 * Формат media_sources.json (из _build/configs/media_sources.js) — блоки по сайтам, как data.js / resources.js
 * (ключ — метка gtsapi_site_key, несколько через запятую, '*' или 'default' — для остальных сайтов):
 * {
 *   "vk24": {
 *     "vk24snab_schet": {                         // имя источника: по нему ищем существующий
 *        "description": "Фото счетов снабжения",
 *        "class_key": "sources.modFileMediaSource", // по умолчанию
 *        "properties": {                          // только при создании (или при "update": true)
 *            "basePath": "assets/uploads/gtssnab/schet/",
 *            "baseUrl":  "assets/uploads/gtssnab/schet/",
 *            "imageExtensions": "jpg,jpeg,png,gif,webp,pdf",
 *            "distributeByDate": true
 *        },
 *        "update": false,                          // true — свойства источника на сайте приводить к конфигу
 *        "config_name": "gtssnab_media_source",    // системная настройка: id источника на этом сайте
 *        "groups": { "Administrator": "Media Source Admin", "snab": "Media Source User" }
 *     }
 *   }
 * }
 * Блок без меток сайтов ({имя: {properties…}}) — для всех сайтов.
 *
 * Идемпотентно. Существующий источник (по имени) не пересоздаётся и не меняется, если не "update": true:
 * его id и файлы на сайте сохраняются. Права групп только добавляются, не снимаются.
 *
 * @var xPDOTransport $transport
 * @var array $options
 * @var modX $modx
 */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;

if (!function_exists('_gtsapiMediaSiteBlock')) {
    /** Блок своего сайта: метка gtsapi_site_key, иначе '*' / 'default'; конфиг без меток — как есть. */
    function _gtsapiMediaSiteBlock(modX $modx, array $data, $namespace)
    {
        foreach ($data as $v) {
            if (is_array($v) && (isset($v['properties']) || isset($v['config_name']) || isset($v['groups']) || isset($v['class_key']))) {
                return $data; // без блоков по сайтам
            }
            break;
        }
        $siteKey = strtolower(trim((string)$modx->getOption('gtsapi_site_key', null, '')));
        $fallback = [];
        foreach ($data as $key => $block) {
            foreach (array_map('trim', explode(',', (string)$key)) as $token) {
                if ($token === '*' || strtolower($token) === 'default') {
                    $fallback = (array)$block;
                } elseif ($siteKey !== '' && strtolower($token) === $siteKey) {
                    return (array)$block;
                }
            }
        }
        if (!$fallback) {
            $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] медиаисточники: для сайта '{$siteKey}' блока нет");
        }
        return $fallback;
    }

    /** Свойства в формате MODX: имя => [name, desc, type, options, value, lexicon]. */
    function _gtsapiMediaProperties(array $props)
    {
        $out = [];
        foreach ($props as $name => $value) {
            if (is_array($value) && isset($value['value'])) { // уже в формате MODX
                $out[$name] = $value + ['name' => $name, 'desc' => '', 'type' => 'textfield', 'options' => [], 'lexicon' => 'core:source'];
                continue;
            }
            $type = is_bool($value) ? 'combo-boolean' : (is_int($value) ? 'numberfield' : 'textfield');
            $out[$name] = ['name' => $name, 'desc' => '', 'type' => $type, 'options' => [], 'value' => $value, 'lexicon' => 'core:source'];
        }
        return $out;
    }
}

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $namespace = $options['namespace'] ?? 'unknown';
        $file = MODX_CORE_PATH . 'components/' . $namespace . '/media_sources.json';
        if (!file_exists($file)) {
            return true;
        }
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data) || !$data) {
            return true;
        }

        foreach (_gtsapiMediaSiteBlock($modx, $data, $namespace) as $name => $cfg) {
            $cfg = is_array($cfg) ? $cfg : [];
            $classKey = $cfg['class_key'] ?? 'sources.modFileMediaSource';

            // 1. Источник: ищем по имени, нет — создаём
            $source = $modx->getObject('sources.modMediaSource', ['name' => $name]);
            if (!$source) {
                $source = $modx->newObject($classKey);
                $source->fromArray([
                    'name' => $name,
                    'description' => $cfg['description'] ?? '',
                    'class_key' => $classKey,
                ], '', true, true);
                $source->setProperties(_gtsapiMediaProperties((array)($cfg['properties'] ?? [])));
                if (!$source->save()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, "[{$namespace}] не создался медиаисточник {$name}");
                    continue;
                }
                $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] создан медиаисточник {$name} (id {$source->get('id')})");
            } elseif (!empty($cfg['update']) && !empty($cfg['properties'])) {
                $source->setProperties(_gtsapiMediaProperties((array)$cfg['properties']), true);
                $source->save();
                $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] медиаисточник {$name}: свойства обновлены");
            }
            $sourceId = (int)$source->get('id');

            // 2. Настройка с id источника на этом сайте
            if (!empty($cfg['config_name'])) {
                $setting = $modx->getObject('modSystemSetting', $cfg['config_name']);
                if (!$setting) {
                    $setting = $modx->newObject('modSystemSetting');
                    $setting->fromArray([
                        'key' => $cfg['config_name'],
                        'xtype' => 'numberfield',
                        'namespace' => $namespace,
                        'area' => $namespace . '_media',
                    ], '', true, true);
                }
                if ((string)$setting->get('value') !== (string)$sourceId) {
                    $setting->set('value', $sourceId);
                    $setting->save();
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] {$cfg['config_name']} = {$sourceId}");
                }
            }

            // 3. Права групп на источник (только добавляем)
            foreach ((array)($cfg['groups'] ?? []) as $groupName => $policyName) {
                $group = $modx->getObject('modUserGroup', ['name' => $groupName]);
                $policy = $modx->getObject('modAccessPolicy', ['name' => $policyName ?: 'Media Source User']);
                if (!$group || !$policy) {
                    $modx->log(modX::LOG_LEVEL_WARN, "[{$namespace}] медиаисточник {$name}: нет группы '{$groupName}' или политики '{$policyName}'");
                    continue;
                }
                $criteria = [
                    'target' => $sourceId,
                    'principal_class' => 'modUserGroup',
                    'principal' => $group->get('id'),
                    'context_key' => '',
                ];
                if ($modx->getObject('sources.modAccessMediaSource', $criteria)) {
                    continue;
                }
                $acl = $modx->newObject('sources.modAccessMediaSource');
                $acl->fromArray(array_merge($criteria, ['authority' => 9999, 'policy' => $policy->get('id')]), '', true, true);
                if ($acl->save()) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] доступ: {$groupName} → медиаисточник {$name} ({$policyName})");
                }
            }
        }
        $modx->cacheManager->refresh();
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        // Источники не удаляем: в них файлы пользователей
        break;
}

return true;

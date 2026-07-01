<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */

// ВАЖНО: функции объявляем ПОД guard'ом function_exists() и ДО основной логики/return.
// При установке нескольких пакетов в одном PHP-процессе (pull всех пакетов) ресолвер
// каждого пакета инклудится отдельно — без guard'а было бы фатальное
// "Cannot redeclare _addResource()". Объявление до return — потому что conditional-функции
// НЕ хойстятся (в отличие от безусловных), а до низа файла после return мы бы не дошли.

if (!function_exists('_upsertResSetting')) {
    /**
     * Настройка config_name всегда должна указывать на актуальный ресурс: создаём если нет,
     * чиним если указывает не туда. $setting — уже загруженный объект (или null).
     */
    function _upsertResSetting($modx, $setting, $config_name, $package, $resource_id)
    {
        if (!$setting) {
            $setting = $modx->newObject('modSystemSetting');
            $setting->fromArray([
                'key' => $config_name,
                'namespace' => $package,
                'xtype' => 'textfield',
                'value' => $resource_id,
                'area' => $package . '_pages',
            ], '', true, true);
            $setting->save();
        } else if ((int)$setting->get('value') !== (int)$resource_id) {
            $setting->set('value', $resource_id);
            $setting->save();
        }
    }
}

if (!function_exists('_addResource')) {
    /**
     * @param modX $modx
     * @param array $data
     * @param string $uri
     * @param int $parent
     * @param string $package
     *
     * @return void
     */
    function _addResource($modx, array $data, $uri, $parent = 0, $package = 'unknown')
    {
        $file = $data['context_key'] . '/' . $uri;

        // Используем config_name из данных, если задан, иначе генерируем автоматически
        $config_name = isset($data['config_name']) ? $data['config_name'] : $package . '_p_' . str_replace('/', '_', $uri);
        // Настройку читаем НАПРЯМУЮ из БД (getObject), а НЕ через getOption: при install
        // кэш системных настроек бывает холодным → getOption вернёт 0 → резолвер плодит дубли.
        $setting = $modx->getObject('modSystemSetting', ['key' => $config_name]);
        $id = $setting ? (int)$setting->get('value') : 0;
        $new = false;

        $resource = $id ? $modx->getObject('modResource', $id) : null;
        // Fallback: настройка пуста/битая → найти существующий ресурс по parent+alias
        // и переиспользовать (не создавать новый). Делает дубли невозможными.
        if (!$resource && !empty($data['alias'])) {
            $resource = $modx->getObject('modResource', ['parent' => (int)$parent, 'alias' => $data['alias']]);
        }

        // Если ресурс существует и update = false — только дети (но настройку всё равно чиним)
        if ($resource && isset($data['update']) && $data['update'] === false) {
            _upsertResSetting($modx, $setting, $config_name, $package, $resource->id);
            if (!empty($data['resources'])) {
                $menuindex = 0;
                foreach ($data['resources'] as $alias => $item) {
                    $item['alias'] = $alias;
                    $item['context_key'] = $data['context_key'];
                    $item['menuindex'] = $menuindex++;
                    _addResource($modx, $item, $uri . '/' . $alias, $resource->id, $package);
                }
            }
            return;
        }

        if (!$resource) {
            $resource = $modx->newObject('modResource');
            $new = true;
        }

        $template_id = 1;
        if (isset($data['properties']['templatename']) && $template = $modx->getObject('modTemplate', array('templatename' => $data['properties']['templatename']))) {
            $template_id = $template->id;
            unset($data['properties']['templatename']);
        }

        if ($new
            || file_exists(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.tpl')
            || file_exists(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.md')
        ) {
            $content = '';
            if (file_exists(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.tpl')) {
                $content = _getContent(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.tpl');
            } else if (file_exists(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.md')) {
                $content = _getContent(MODX_CORE_PATH . 'components/' . $package . '/elements/resources/' . $file . '.md');
            }
        }
        $resource->fromArray(array_merge([
            'parent' => $parent,
            'published' => true,
            'deleted' => false,
            'hidemenu' => false,
            'createdon' => time(),
            'template' => $template_id,
            'isfolder' => !empty($data['isfolder']) || !empty($data['resources']),
            'uri' => $uri,
            'uri_override' => false,
            'richtext' => false,
            'searchable' => true,
            'content' => $content,
        ], $data), '', true, true);

        $resource->save();

        // Настройка всегда указывает на актуальный ресурс (создаём или чиним) — не даём ей устареть.
        _upsertResSetting($modx, $setting, $config_name, $package, $resource->id);

        if (!empty($data['groups'])) {
            if (is_string($data['groups'])) {
                $data['groups'] = explode(',', $data['groups']);
            }
            foreach ($data['groups'] as $group) {
                $resource->joinGroup($group);
            }
        }
        if (!empty($data['resources'])) {
            $menuindex = 0;
            foreach ($data['resources'] as $alias => $item) {
                $item['alias'] = $alias;
                $item['context_key'] = $data['context_key'];
                $item['menuindex'] = $menuindex++;
                _addResource($modx, $item, $uri . '/' . $alias, $resource->id, $package);
            }
        }
    }
}

if (!function_exists('_getContent')) {
    /**
     * @param string $filename
     *
     * @return string
     */
    function _getContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));

            return preg_match('#\<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }

        return '';
    }
}

if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx =& $transport->xpdo;
$success = false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $packageName = $options['namespace'] ?? 'unknown';
        $resourcesFile = MODX_CORE_PATH . 'components/' . $packageName . '/resources.json';

        if (file_exists($resourcesFile)) {
            $resourcesData = json_decode(file_get_contents($resourcesFile), true);
            if (is_array($resourcesData) && !empty($resourcesData)) {
                foreach ($resourcesData as $context => $items) {
                    $menuindex = 0;
                    foreach ($items as $alias => $item) {
                        $item['alias'] = $alias;
                        $item['context_key'] = $context;
                        $item['menuindex'] = $menuindex++;
                        _addResource($modx, $item, $alias, 0, $packageName);
                    }
                }
            }
        }

        $success = true;
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        break;
}

return $success;

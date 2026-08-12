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

if (!function_exists('_normHostRes')) {
    /**
     * Нормализация хоста: регистр, порт, www. — чтобы 'modx28.loc:8080' и 'www.MODX28.loc'
     * считались одним сайтом.
     */
    function _normHostRes($host)
    {
        $host = strtolower(trim((string)$host));
        $host = preg_replace('~^https?://~', '', $host);
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];
        $host = preg_replace('~^www\.~', '', $host);

        return rtrim($host, '.');
    }
}

if (!function_exists('_pickSiteResources')) {
    /**
     * Выбор блока конфигурации под текущий сайт.
     *
     * Поддерживаются два формата resources.json:
     *   1) { "<context>": { "<alias>": {...} } }                        — как раньше
     *   2) { "<сайты>": { "<context>": { "<alias>": {...} } } }         — конфиг под сайты
     *
     * Во втором формате родителя и шаблон можно писать прямо числом/именем — они уже
     * привязаны к конкретному сайту, и системные настройки для этого не нужны.
     *
     * Сайт в ключе (через запятую) опознаётся тремя способами:
     *   - метка сайта   — значение системной настройки `gtsapi_site_key` ('prod', 'office'…).
     *                     РЕКОМЕНДУЕТСЯ для публичных пакетов: не раскрывает адреса;
     *   - хост          — 'site.ru' (регистр, порт и www. не важны);
     *   - хеш хоста     — 'sha1:9f2c…' если метку заводить не хочется, а адрес светить нельзя.
     * Ключ '*' (или 'default') — блок для всех остальных сайтов.
     */
    function _pickSiteResources($modx, array $data, $packageName)
    {
        if (empty($data)) {
            return [];
        }

        // Legacy: ключ верхнего уровня — существующий контекст
        $firstKey = (string)array_keys($data)[0];
        if ($modx->getObject('modContext', ['key' => $firstKey])) {
            return $data;
        }

        $host = _normHostRes($modx->getOption('http_host', null, defined('MODX_HTTP_HOST') ? MODX_HTTP_HOST : ''));
        $hostHash = $host !== '' ? sha1($host) : '';
        $siteKey = strtolower(trim((string)$modx->getOption('gtsapi_site_key', null, '')));
        $fallback = null;

        foreach ($data as $key => $block) {
            foreach (array_map('trim', explode(',', (string)$key)) as $token) {
                if ($token === '' ) {
                    continue;
                }
                if ($token === '*' || strtolower($token) === 'default') {
                    $fallback = $block;
                    continue;
                }
                // 1. Метка сайта (настройка gtsapi_site_key) — адреса не раскрываются
                if ($siteKey !== '' && strtolower($token) === $siteKey) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] страницы: сайт '{$siteKey}' (метка)");

                    return $block;
                }
                // 2. Хеш хоста — 'sha1:…'
                if (stripos($token, 'sha1:') === 0) {
                    if ($hostHash !== '' && strtolower(substr($token, 5)) === substr($hostHash, 0, strlen($token) - 5)) {
                        $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] страницы: сайт опознан по хешу хоста");

                        return $block;
                    }
                    continue;
                }
                // 3. Обычный хост
                if ($host !== '' && _normHostRes($token) === $host) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] страницы: конфигурация сайта '{$token}'");

                    return $block;
                }
            }
        }

        if ($fallback !== null) {
            $modx->log(modX::LOG_LEVEL_INFO, "[{$packageName}] страницы: конфигурация по умолчанию (сайт '{$host}')");

            return $fallback;
        }

        $modx->log(modX::LOG_LEVEL_WARN,
            "[{$packageName}] в resources.json нет блока для этого сайта "
            . "(метка gtsapi_site_key='{$siteKey}', хеш хоста sha1:" . substr($hostHash, 0, 12) . ") — страницы не создаются");

        return [];
    }
}

if (!function_exists('_resolveParentRes')) {
    /**
     * Родитель страницы верхнего уровня. Жёстко зашивать id нельзя: на каждом сайте
     * (modx28, modx.pl, чужая установка) структура своя.
     *
     *   parent_setting — имя системной настройки с id родителя (приоритет)
     *   parent_alias   — искать родителя по алиасу в этом контексте
     *   иначе          — корень (0)
     *
     * Настройку читаем getObject'ом, а не getOption: при install кэш настроек холодный.
     */
    function _resolveParentRes($modx, array $data, $contextKey)
    {
        // Явный id родителя — когда конфиг уже привязан к конкретному сайту
        if (isset($data['parent']) && (int)$data['parent'] > 0) {
            return (int)$data['parent'];
        }
        if (!empty($data['parent_setting'])) {
            $s = $modx->getObject('modSystemSetting', ['key' => $data['parent_setting']]);
            $pid = $s ? (int)$s->get('value') : 0;
            if ($pid > 0 && $modx->getObject('modResource', $pid)) {
                return $pid;
            }
        }
        if (!empty($data['parent_alias'])) {
            $p = $modx->getObject('modResource', [
                'alias' => $data['parent_alias'],
                'context_key' => $contextKey,
            ]);
            if ($p) {
                return (int)$p->get('id');
            }
        }

        return 0;
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
            $found = $modx->getObject('modResource', ['parent' => (int)$parent, 'alias' => $data['alias']]);
            if ($found) {
                if ($setting) {
                    // Настройка пакета есть → ресурс наш, просто id в ней устарел.
                    $resource = $found;
                } else {
                    // Настройки нет — значит пакет ставится сюда впервые, а страница
                    // с таким алиасом уже чья-то. Не трогаем чужое: fromArray затёр бы
                    // заголовок, шаблон и контент. Создаём свою с префиксом пакета.
                    $newAlias = $package . '-' . $data['alias'];
                    $parts = explode('/', $uri);
                    array_pop($parts);
                    $parts[] = $newAlias;
                    $uri = implode('/', $parts);
                    $data['alias'] = $newAlias;
                    $modx->log(modX::LOG_LEVEL_WARN,
                        "[{$package}] alias '{$found->get('alias')}' занят ресурсом #{$found->get('id')}, "
                        . "создаю '{$newAlias}'");
                }
            }
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

        // Шаблон: template_setting (системная настройка с ИМЕНЕМ шаблона) → properties.templatename → 1.
        // Имя шаблона на разных сайтах разное, поэтому его тоже нельзя зашивать в пакет.
        $template_id = 1;
        $templateName = null;
        if (!empty($data['template_setting'])) {
            $ts = $modx->getObject('modSystemSetting', ['key' => $data['template_setting']]);
            if ($ts && trim((string)$ts->get('value')) !== '') {
                $templateName = trim((string)$ts->get('value'));
            }
        }
        if ($templateName === null && isset($data['properties']['templatename'])) {
            $templateName = $data['properties']['templatename'];
        }
        if ($templateName !== null) {
            if ($template = $modx->getObject('modTemplate', ['templatename' => $templateName])) {
                $template_id = $template->id;
            } else {
                $modx->log(modX::LOG_LEVEL_WARN,
                    "[{$package}] шаблон '{$templateName}' не найден, ставлю шаблон по умолчанию");
            }
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

        // URI считаем от ФАКТИЧЕСКОГО родителя: parent_setting/parent_alias могут увести
        // страницу вглубь дерева, а в $uri лежит только путь внутри пакета (по нему же
        // ищется файл контента, поэтому его не трогаем).
        if ((int)$parent > 0) {
            $path = $resource->getAliasPath($resource->get('alias'));
            if ($path && $path !== $resource->get('uri')) {
                $resource->set('uri', $path);
                $resource->save();
            }
        }

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
                // Конфиг может быть разбит по сайтам: { "host1,host2": { "<context>": ... } }
                $resourcesData = _pickSiteResources($modx, $resourcesData, $packageName);
                foreach ($resourcesData as $context => $items) {
                    $menuindex = 0;
                    foreach ($items as $alias => $item) {
                        $item['alias'] = $alias;
                        $item['context_key'] = $context;
                        $item['menuindex'] = $menuindex++;
                        // Родитель верхнего уровня: parent_setting → parent_alias → корень
                        $parent = _resolveParentRes($modx, $item, $context);
                        _addResource($modx, $item, $alias, $parent, $packageName);
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

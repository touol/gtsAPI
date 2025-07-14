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
        $packageName = $options['package_name'] ?? 'unknown';
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
    $config_name = $package . '_p_' . str_replace('/', '_', $uri);
    $id = $modx->getOption($config_name, null, 0);
    $new = false;
    
    if (!$resource = $modx->getObject('modResource', $id)) {
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

        if (!empty($data['groups'])) {
            if (is_string($data['groups'])) {
                $data['groups'] = explode(',', $data['groups']);
            }
            foreach ($data['groups'] as $group) {
                $resource->joinGroup($group);
            }
        }
        
        $resource->save();
        
        if (empty($id)) {
            $setting = $modx->newObject('modSystemSetting');
            $setting->fromArray([
                'key' => $config_name,
                'namespace' => $package,
                'xtype' => 'textfield',
                'value' => $resource->id,
                'area' => $package . '_pages',
            ], '', true, true);
            $setting->save();
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

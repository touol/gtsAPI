<?php
/**
 * Зависимости пакета: доустановка недостающих компонентов ПЕРЕД установкой самого пакета.
 *
 * Список берётся из `core/components/<пакет>/dependencies.json` (в исходниках —
 * `_build/configs/dependencies.js`):
 *
 *   {
 *     "gtsAPI":   { "version": "1.0.15-beta", "required": true },
 *     "pdoTools": { "version": "2.10.0-pl", "service_url": "modstore.pro" }
 *   }
 *
 * Поля записи:
 *   version      — минимальная версия. Уже стоит такая или новее — ничего не делаем.
 *   service_url  — у какого провайдера искать ('modstore.pro', 'modx.com'). По умолчанию
 *                  берётся первый настроенный на сайте.
 *   url          — прямая ссылка на transport.zip. Нужна для пакетов, которых нет
 *                  у провайдеров (например, релиз на GitHub).
 *   required     — по умолчанию true. Если такой пакет поставить не удалось,
 *                  установка ПРЕРЫВАЕТСЯ с понятным сообщением: лучше честно
 *                  отказаться, чем поставить компонент, который не заработает.
 *
 * Резолвер подключается ПЕРВЫМ — до создания таблиц и записи данных.
 *
 * @var xPDOTransport $transport
 * @var array $options
 * @var modX $modx
 */
if (!$transport->xpdo) {
    return true;
}
$modx =& $transport->xpdo;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        break;
    default:
        return true;
}

$namespace = $options['namespace'];
$file = MODX_CORE_PATH . 'components/' . $namespace . '/dependencies.json';
if (!is_file($file)) {
    return true;
}
$deps = json_decode(file_get_contents($file), true);
if (!is_array($deps) || !$deps) {
    return true;
}

if (!function_exists('gts_dep_download')) {
    function gts_dep_download($src, $dst)
    {
        $data = false;
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $src);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            if (!@ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            $data = curl_exec($ch);
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen')) {
            $data = @file_get_contents($src);
        }
        if ($data === false || $data === '') {
            return false;
        }
        file_put_contents($dst, $data);

        return file_exists($dst) && filesize($dst) > 0;
    }
}

if (!function_exists('gts_dep_register')) {
    /** Регистрация скачанного transport.zip как пакета и установка */
    function gts_dep_register($modx, $name, $signature, $providerId = 0)
    {
        $sig = explode('-', $signature);
        $ver = isset($sig[1]) ? explode('.', $sig[1]) : [0, 0, 0];

        if (!$package = $modx->getObject('transport.modTransportPackage', ['signature' => $signature])) {
            $package = $modx->newObject('transport.modTransportPackage');
            $package->set('signature', $signature);
        }
        $package->fromArray([
            'created' => date('Y-m-d H:i:s'),
            'updated' => null,
            'state' => 1,
            'workspace' => 1,
            'provider' => $providerId,
            'source' => $signature . '.transport.zip',
            'package_name' => $name,
            'version_major' => isset($ver[0]) ? $ver[0] : 0,
            'version_minor' => isset($ver[1]) ? $ver[1] : 0,
            'version_patch' => isset($ver[2]) ? $ver[2] : 0,
        ]);
        if (!empty($sig[2])) {
            $r = preg_split('/([0-9]+)/', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
            $package->set('release', is_array($r) && !empty($r) ? $r[0] : $sig[2]);
            if (is_array($r) && isset($r[1])) $package->set('release_index', $r[1]);
        }

        return $package->save() && $package->install();
    }
}

if (!function_exists('gts_dep_install')) {
    /** Поиск у провайдера → скачивание → установка */
    function gts_dep_install($modx, $name, array $opt)
    {
        // 1. Прямая ссылка — пакета может не быть ни у одного провайдера
        if (!empty($opt['url'])) {
            $signature = !empty($opt['signature'])
                ? $opt['signature']
                : preg_replace('~\.transport\.zip$~', '', basename(parse_url($opt['url'], PHP_URL_PATH)));
            $dst = MODX_CORE_PATH . 'packages/' . $signature . '.transport.zip';
            if (!gts_dep_download($opt['url'], $dst)) {
                return [0, "не удалось скачать по ссылке {$opt['url']}"];
            }

            return gts_dep_register($modx, $name, $signature)
                ? [1, "установлен из {$opt['url']}"]
                : [0, 'скачан, но не установился'];
        }

        // 2. Провайдер (modstore.pro, modx.com…)
        $provider = null;
        if (!empty($opt['service_url'])) {
            $provider = $modx->getObject('transport.modTransportProvider', [
                'service_url:LIKE' => '%' . $opt['service_url'] . '%',
            ]);
        }
        if (!$provider) {
            $provider = $modx->getObject('transport.modTransportProvider', 1);
        }
        if (!$provider) {
            return [0, 'на сайте не настроен ни один провайдер пакетов'];
        }

        $modx->getVersionData();
        $productVersion = $modx->version['code_name'] . '-' . $modx->version['full_version'];
        $response = $provider->request('package', 'GET', ['supports' => $productVersion, 'query' => $name]);
        if (empty($response)) {
            return [0, 'провайдер не ответил'];
        }

        $found = @simplexml_load_string($response->response);
        if (!$found) {
            return [0, 'не найден у провайдера'];
        }
        foreach ($found as $p) {
            if ((string)$p->name !== $name) {
                continue;
            }
            $signature = (string)$p->signature;
            $dst = MODX_CORE_PATH . 'packages/' . $signature . '.transport.zip';
            if (!gts_dep_download((string)$p->location, $dst)) {
                return [0, 'не удалось скачать'];
            }

            return gts_dep_register($modx, $name, $signature, $provider->get('id'))
                ? [1, "установлен {$signature}"]
                : [0, 'скачан, но не установился'];
        }

        return [0, 'не найден у провайдера'];
    }
}

$fail = [];
foreach ($deps as $name => $opt) {
    if (!is_array($opt)) {
        $opt = ['version' => $opt];
    }
    $need = isset($opt['version']) ? $opt['version'] : '0';
    $required = !isset($opt['required']) || $opt['required'];

    // Уже стоит нужная версия или новее?
    $ok = false;
    foreach ($modx->getIterator('transport.modTransportPackage', ['package_name' => $name]) as $installed) {
        if ($installed->compareVersion($need, '<=')) {
            $ok = true;
            break;
        }
    }
    if ($ok) {
        $modx->log(modX::LOG_LEVEL_INFO, "[DPLOG] [{$namespace}] зависимость {$name} {$need}: уже установлена");
        continue;
    }

    $modx->log(modX::LOG_LEVEL_INFO, "[DPLOG] [{$namespace}] зависимость {$name} {$need}: ставим…");
    list($success, $message) = gts_dep_install($modx, $name, $opt);
    $modx->log($success ? modX::LOG_LEVEL_INFO : modX::LOG_LEVEL_ERROR,
        "[DPLOG] [{$namespace}] зависимость {$name}: {$message}");

    if (!$success && $required) {
        $fail[] = "{$name} {$need} ({$message})";
    }
}

if ($fail) {
    // Прерываем установку: пакет без своих зависимостей всё равно не заработает,
    // и молча оставить его установленным — худший из вариантов.
    $modx->log(modX::LOG_LEVEL_ERROR,
        "[DPLOG] [{$namespace}] установка прервана: не хватает зависимостей — " . implode('; ', $fail)
        . '. Поставьте их вручную и повторите установку.');

    return false;
}

return true;

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
 *   auto         — true: зависимость ставит только этот резолвер, в requires манифеста
 *                  она не попадает. MODX проверяет requires ДО резолверов и сам
 *                  недостающий пакет не ставит, поэтому иначе до резолвера дело не дойдёт.
 *
 * Порядок записей это порядок установки. Провайдер, с которого скачан сам пакет,
 * спрашивается раньше остальных (кроме явно указанного в service_url).
 *
 * Скрипт идёт ПЕРВЫМ транспортом пакета (xPDOScriptVehicle с abort_install_on_vehicle_fail):
 * только так false прерывает установку, false обычного резолвера MODX лишь пишет в журнал.
 * Файлов пакета на этот момент ещё нет, поэтому список лежит в самом транспорте ($object['deps']).
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

if (is_array($object) && isset($object['deps'])) {
    $namespace = $object['namespace'];
    $deps = json_decode($object['deps'], true);
} else {
    // Как резолвер в пакетах, собранных до 1.2.3.
    $namespace = $options['namespace'];
    $file = MODX_CORE_PATH . 'components/' . $namespace . '/dependencies.json';
    if (!is_file($file)) {
        return true;
    }
    $deps = json_decode(file_get_contents($file), true);
}
if (!is_array($deps) || !$deps) {
    return true;
}

if (!function_exists('gts_dep_download')) {
    function gts_dep_get($src)
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

        return $data;
    }

    function gts_dep_download($src, $dst)
    {
        $data = gts_dep_get($src);
        // Транспортный пакет это zip. Страница ошибки или адрес вместо файла не должны лечь в core/packages.
        if (!is_string($data) || substr($data, 0, 2) !== 'PK') {
            return false;
        }
        file_put_contents($dst, $data);

        return file_exists($dst) && filesize($dst) > 0;
    }

    /**
     * Провайдер MODX отдаёт файл в два шага, как modTransportProvider::transfer(): по getUrl=true адрес файла,
     * затем сам файл. Прямой запрос у modx.com отвечает редиректом, а при open_basedir curl по нему не пойдёт.
     */
    function gts_dep_file_url($location)
    {
        $answer = gts_dep_get($location . (strpos($location, '?') === false ? '?' : '&') . 'getUrl=true');
        $answer = is_string($answer) ? trim($answer) : '';

        return preg_match('~^https?://\S+$~i', $answer) ? $answer : $location;
    }
}

if (!function_exists('gts_dep_body')) {
    /**
     * Тело ответа провайдера. В MODX 2 это modRestClient со свойством ->response,
     * в MODX 3 — PSR-7 ResponseInterface. Без этой развилки на MODX 3 любой поиск
     * пакета молча возвращал пустоту и всё выглядело как «пакета нет у провайдера».
     */
    function gts_dep_body($response)
    {
        if (!is_object($response)) {
            return '';
        }
        if (method_exists($response, 'getBody')) {
            return (string)$response->getBody();
        }

        return isset($response->response) ? (string)$response->response : '';
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

        // 2. Провайдеры (modstore.pro, modx.com…). Сначала указанный в конфиге,
        // потом остальные — пакет может лежать не там, где ждали.
        $providers = [];
        $wanted = !empty($opt['service_url']) ? $opt['service_url'] : '';
        if ($wanted !== '') {
            if ($p = $modx->getObject('transport.modTransportProvider', ['service_url:LIKE' => '%' . $wanted . '%'])) {
                $providers[$p->get('id')] = $p;
            }
        }
        if (!empty($opt['own_provider'])) {
            if ($p = $modx->getObject('transport.modTransportProvider', (int)$opt['own_provider'])) {
                $providers[$p->get('id')] = $p;
            }
        }
        foreach ($modx->getIterator('transport.modTransportProvider') as $p) {
            $providers[$p->get('id')] = $p;
        }
        if (!$providers) {
            return [0, 'на сайте не подключён ни один провайдер пакетов'];
        }
        // Указан провайдер, которого на сайте нет — самая частая причина промаха,
        // и по общему «не найден» её не опознать. Говорим прямо.
        $wantedMissing = $wanted !== ''
            && !$modx->getObject('transport.modTransportProvider', ['service_url:LIKE' => '%' . $wanted . '%']);

        $modx->getVersionData();
        $productVersion = $modx->version['code_name'] . '-' . $modx->version['full_version'];
        $tried = [];

        foreach ($providers as $provider) {
            $tried[] = $provider->get('name') ?: $provider->get('service_url');
            $response = $provider->request('package', 'GET', ['supports' => $productVersion, 'query' => $name]);
            $body = gts_dep_body($response);
            if ($body === '') {
                continue;
            }
            $found = @simplexml_load_string($body);
            if (!$found) {
                continue;
            }
            foreach ($found as $p) {
                // Регистр имени у провайдеров разный: pdoTools и pdotools это один пакет.
                if (strcasecmp((string)$p->name, $name) !== 0) {
                    continue;
                }
                $signature = (string)$p->signature;
                $parts = explode('-', $signature, 2);
                if (!empty($opt['version']) && version_compare(isset($parts[1]) ? $parts[1] : '0', $opt['version'], '<')) {
                    continue;
                }
                $dst = MODX_CORE_PATH . 'packages/' . $signature . '.transport.zip';
                if (!gts_dep_download(gts_dep_file_url((string)$p->location), $dst)) {
                    return [0, "найден у провайдера {$provider->get('name')}, но не скачался"
                        . ($provider->get('username') === '' ? ' (у провайдера не заданы логин и ключ)' : '')];
                }

                return gts_dep_register($modx, $name, $signature, $provider->get('id'))
                    ? [1, "установлен {$signature} с {$provider->get('name')}"]
                    : [0, 'скачан, но не установился'];
            }
        }

        $where = implode(', ', $tried);
        if ($wantedMissing) {
            return [0, "провайдер {$wanted} на этом сайте не подключён (искали у: {$where}). "
                . "Добавьте его в «Управление пакетами - Провайдеры» или поставьте пакет вручную"];
        }

        return [0, "не найден ни у одного провайдера ({$where})"];
    }
}

$self = $modx->getObject('transport.modTransportPackage', ['signature' => $transport->signature]);
$ownProvider = $self ? (int)$self->get('provider') : 0;

$fail = [];
foreach ($deps as $name => $opt) {
    if (!is_array($opt)) {
        $opt = ['version' => $opt];
    }
    $opt['own_provider'] = $ownProvider;
    $need = isset($opt['version']) ? $opt['version'] : '0';
    $required = !isset($opt['required']) || $opt['required'];

    // Уже стоит нужная версия или новее?
    $ok = false;
    // Только установленные: скачанный, но не поставленный пакет зависимость не закрывает.
    foreach ($modx->getIterator('transport.modTransportPackage', ['package_name' => $name, 'installed:IS NOT' => null]) as $installed) {
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

// Следующие транспорты пакета могут быть объектами поставленного только что пакета
// (например gtsAPIRule), а его модель в этом запросе ещё не загружена.
foreach ($deps as $name => $opt) {
    $model = MODX_CORE_PATH . 'components/' . strtolower($name) . '/model/';
    if (is_dir($model . strtolower($name))) {
        $modx->addPackage(strtolower($name), $model);
    }
}

return true;

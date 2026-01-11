<?php

// Подключаем все trait'ы
require_once __DIR__ . '/traits/TableCrudTrait.php';
require_once __DIR__ . '/traits/TableFieldsTrait.php';
require_once __DIR__ . '/traits/TableAutocompleteTrait.php';
require_once __DIR__ . '/traits/TableFilterTrait.php';
require_once __DIR__ . '/traits/TableExportTrait.php';
require_once __DIR__ . '/traits/TableTriggerTrait.php';
require_once __DIR__ . '/traits/TableTreeTrait.php';
require_once __DIR__ . '/traits/TableUtilsTrait.php';

/**
 * Основной контроллер API для работы с таблицами
 * 
 * Использует trait'ы для разделения функционала:
 * - TableCrudTrait - CRUD операции
 * - TableFieldsTrait - Управление полями
 * - TableAutocompleteTrait - Автокомплит
 * - TableFilterTrait - Фильтрация
 * - TableExportTrait - Экспорт и печать
 * - TableTriggerTrait - Триггеры и события
 * - TableTreeTrait - Древовидные структуры
 * - TableUtilsTrait - Утилиты
 */
class tableAPIController
{
    // Подключаем trait'ы
    use TableCrudTrait;
    use TableFieldsTrait;
    use TableAutocompleteTrait;
    use TableFilterTrait;
    use TableExportTrait;
    use TableTriggerTrait;
    use TableTreeTrait;
    use TableUtilsTrait;

    public $config = [];
    public $modx;
    public $pdo;
    public $pdoTools;
    public $models = [];
    public $triggers = [];

    /**
     * Конструктор
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            
        ], $config);

        if ($this->pdo = $this->modx->getService('myPdo', 'myPdo', $corePath . 'classes/', [])) {
            $this->pdo->setConfig($this->config);
        }
        $this->pdoTools = $this->modx->getService('pdoFetch');
    }

    /**
     * Маршрутизация запросов
     */
    public function route($gtsAPITable, $uri, $method, $request)
    {
        $req = json_decode(file_get_contents('php://input'), true);
        if (isset($req['filters']) and isset($request['filters'])) $req['filters'] = array_merge($req['filters'], $request['filters']);
        if (isset($request['is_virtual'])) $req['is_virtual'] = $request['is_virtual'];
        if (is_array($req)) $request = array_merge($request, $req);
          
        switch ($method) {
            case 'GET':
                if (empty($request['api_action'])) $request['api_action'] = 'read';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'PUT':
                $request['api_action'] = 'create';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'PATCH':
                $request['api_action'] = 'update';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'DELETE':
                $request['api_action'] = 'delete';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
            case 'OPTIONS':
                $request['api_action'] = 'options';
                return $this->route_post($gtsAPITable, $uri, $method, $request);
            break;
        }
        return $this->route_post($gtsAPITable, $uri, $method, $request);
    }

    /**
     * Обработка POST запросов
     */
    public function route_post($gtsAPITable, $uri, $method, $request)
    {
        if (empty($request['api_action'])) $request['api_action'] = 'create';
        
        // Декодируем filters если это JSON строка
        if (isset($request['filters']) && is_string($request['filters'])) {
            $decodedFilters = json_decode($request['filters'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request['filters'] = $decodedFilters;
            }
        }
        $rule = $gtsAPITable->toArray();
        if (empty($rule['class'])) $rule['class'] = $rule['table'];

        $resp = $this->checkPermissions($rule);

        if (!$resp['success']) {
            return $resp;
        }

        $properties = false;
        if ($rule['properties']) {
            $properties = json_decode($rule['properties'], 1);
        }
        if ($properties and is_array($properties)) {
            $rule['properties'] = $properties;
        } else {
            $rule['properties'] = [];
        }
        $this->addPackages($rule['package_id']);
        
        if (isset($rule['properties']['loadModels'])) {
            $loadModels = explode(',', $rule['properties']['loadModels']);
            foreach ($loadModels as $package) {
                $resp = $this->getService($package);
                if (!$resp['success']) {
                    return $resp;
                }
            }
        }
        
        $rule = $this->addFields($rule);

        // Вызов события для плагинов - позволяет изменить $rule
        $gtsAPIRunTriggersRule = $this->modx->invokeEvent('gtsAPIRunTriggers', [
            'class' => $rule['class'],
            'rule' => &$rule,
            'request' => $request,
            'trigger' => 'gtsapi_rule',
        ]);
        if (is_array($gtsAPIRunTriggersRule)) {
            $canSave = '';
            foreach ($gtsAPIRunTriggersRule as $msg) {
                if (!empty($msg)) {
                    $canSave .= $msg . "\n";
                }
            }
        } else {
            $canSave = $gtsAPIRunTriggersRule;
        }
        if (!empty($canSave)) return $this->error($canSave);
        
        // Проверяем, был ли изменен $rule через returnedValues
        if (isset($this->modx->event->returnedValues['rule'])) {
            $rule = $this->modx->event->returnedValues['rule'];
        }
        
        // Внутренний механизм триггеров через сервисы
        try {
            $class = $rule['class'];
            $triggers = $this->triggers;
            
            if (isset($triggers[$class]['gtsapi_rule']) and isset($triggers[$class]['model'])) {
                $service = $this->models[$triggers[$class]['model']];
                if (method_exists($service, $triggers[$class]['gtsapi_rule'])) {
                    $params = [
                        'rule' => &$rule,
                        'class' => $class,
                        'request' => $request,
                        'trigger' => 'gtsapi_rule',
                    ];
                    $resp = $service->{$triggers[$class]['gtsapi_rule']}($params);
                    if (!$resp['success']) return $resp;
                }
            }
        } catch (Error $e) {
            $this->modx->log(1, 'gtsAPI Ошибка триггера gtsapi_rule ' . $e->getMessage());
            return $this->error('Ошибка триггера gtsapi_rule ' . $e->getMessage());
        }
        
        // Добавляем действие excel_export если оно не отключено
        if (!isset($rule['properties']['actions']['excel_export']) || $rule['properties']['actions']['excel_export'] !== false) {
            if (!isset($rule['properties']['actions']['excel_export'])) {
                $rule['properties']['actions']['excel_export'] = [
                    'head' => true,
                    'icon' => 'pi pi-file-excel',
                    'class' => 'p-button-rounded p-button-success',
                    'label' => 'Excel'
                ];
            }
        }
        
        // Добавляем действие print если оно не отключено
        if (!isset($rule['properties']['actions']['print']) || $rule['properties']['actions']['print'] !== false) {
            if (!isset($rule['properties']['actions']['print'])) {
                $rule['properties']['actions']['print'] = [
                    'head' => true,
                    'icon' => 'pi pi-print',
                    'class' => 'p-button-rounded p-button-info',
                    'label' => 'Печать'
                ];
            }
        }
        
        $action = explode('/', $request['api_action']);
        if (count($action) == 1 and !in_array($request['api_action'], ['options', 'autocomplete', 'save_fields_style', 'reset_fields_style']) and isset($rule['properties']['actions'])) {
            $api_action = $request['api_action'];
            if ($api_action == 'watch_form') $api_action = $request['watch_action'];

            if (!isset($rule['properties']['actions'][$api_action]) and !isset($rule['properties']['hide_actions'][$api_action])) {
                return $this->error("Not api action!");
            }

            if (isset($rule['properties']['actions'][$api_action])) {
                $resp = $this->checkPermissions($rule['properties']['actions'][$api_action]);
                if (!$resp['success']) {
                    return $resp;
                }
            }
            if (isset($rule['properties']['hide_actions'][$api_action])) {
                $resp = $this->checkPermissions($rule['properties']['hide_actions'][$api_action]);
                if (!$resp['success']) {
                    return $resp;
                }
            }
        }
        if (in_array($request['api_action'], ['autocomplete'])) {
            if (empty($rule['properties']['autocomplete'])) return $this->error("Not api autocomplete!");
        }
        

        if (!isset($rule['properties']['aсtions'][$request['api_action']]['skip_sanitize']))
            $request = $this->modx->sanitize($request, $this->modx->sanitizePatterns);
        
        switch ($request['api_action']) {
            case 'create':
                return $this->create($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'insert':
                return $this->create($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'insert_child':
                return $this->create($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'read':
                return $this->read($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'update':
                return $this->update($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'delete':
                return $this->delete($rule, $request, $rule['aсtions'][$request['api_action']]);
            break;
            case 'options':
                return $this->options($rule, $request, $rule['aсtions'][$request['api_action']]);
            case 'autocomplete':
                return $this->get_autocomplete($rule, $request);
            break;
            case 'watch_form':
                return $this->watch_form($rule, $request);
            break;
            case 'excel_export':
                return $this->excel_export($rule, $request);
            break;
            case 'print':
                return $this->print($rule, $request);
            break;
            case 'save_fields_style':
                return $this->save_fields_style($rule, $request);
            break;
            case 'reset_fields_style':
                return $this->reset_fields_style($rule, $request);
            break;
            default:
                $action = explode('/', $request['api_action']);
                if (count($action) == 2) {
                    $resp = $this->getService(strtolower($action[0]));
                    if (!$resp['success']) {
                        return $resp;
                    }
                    $service = $this->models[strtolower($action[0])];

                    if (method_exists($service, 'handleRequest')) {
                        return $service->handleRequest($action[1], $request);
                    }
                }
        }
        return $this->error("Не найдено действие!");
    }

    /**
     * Проверка прав доступа
     */
    public function checkPermissions($rule_action)
    {
        if (isset($rule_action['authenticated']) and $rule_action['authenticated'] == 1) {
            if (!$this->modx->user->id > 0) return $this->error("Not api authenticated!", ['user_id' => $this->modx->user->id]);
        }

        if (isset($rule_action['groups']) and !empty($rule_action['groups'])) {
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if (!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if (isset($rule_action['permissions']) and !empty($rule_action['permissions'])) {
            $permissions = array_map('trim', explode(',', $rule_action['permissions']));
            foreach ($permissions as $pm) {
                if (!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }

    /**
     * Успешный ответ
     */
    public function success($message = "", $data = [])
    {
        header("HTTP/1.1 200 OK");
        return ['success' => 1, 'message' => $message, 'data' => $data];
    }

    /**
     * Ответ с ошибкой
     */
    public function error($message = "", $data = [])
    {
        return ['success' => 0, 'message' => $message, 'data' => $data];
    }

    /**
     * Добавление пакетов
     */
    public function addPackages($package_id)
    {
        if ($gtsAPIPackage = $this->modx->getObject('gtsAPIPackage', $package_id)) {
            $this->getService($gtsAPIPackage->name);
        }
    }

    /**
     * Получение сервиса
     */
    public function getService($package)
    {
        $class = strtolower($package);
        if ($class == 'modx') return $this->success();

        $path = MODX_CORE_PATH . "/components/$class/model/";
        if (file_exists($path . "$class.class.php")) {
            if (!$this->models[$class] = $this->modx->getService($class, $class, $path, [])) {
                return $this->error("Компонент $package не найден!");
            }
        } else if (file_exists($path . "$class/" . "$class.class.php")) {
            if (!$this->models[$class] = $this->modx->getService($class, $class, $path . "$class/", [])) {
                return $this->error("Компонент $package не найден!");
            }
        } else {
            $this->modx->addPackage($class, MODX_CORE_PATH . "components/{$class}/model/");
            return $this->success("Компонент $package не имеет сервиса!");
        }
        $service = $this->models[$class];

        if (method_exists($service, 'regTriggers')) {
            $triggers = $service->regTriggers();
            foreach ($triggers as &$trigger) {
                $trigger['model'] = $class;
            }
            $this->triggers = array_merge($this->triggers, $triggers);
        }
        return $this->success();
    }
}

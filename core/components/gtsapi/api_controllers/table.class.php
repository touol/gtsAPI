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
require_once __DIR__ . '/traits/TableVersionTrait.php';
require_once __DIR__ . '/traits/TriggerRegistryTrait.php';
require_once __DIR__ . '/traits/TablePasteTrait.php';
require_once __DIR__ . '/traits/ServiceActionAuditTrait.php';

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
    use TableVersionTrait;
    use TriggerRegistryTrait;
    use TablePasteTrait;
    use ServiceActionAuditTrait;

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
        // В режиме ИИ тело запроса принадлежит шлюзу /gtsai — там лежит сообщение
        // пользователя, а не параметры этого вызова. Подмешивать его нельзя:
        // клиент протащил бы «api_action: delete» мимо инструмента, ведь при
        // array_merge ниже тело перебивает всё, что собрал инструмент.
        if (!empty($this->config['ai_mode'])) $req = null;
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

        // batch_id приходит с запросом и просто складывается в лог: по нему
        // откатывается весь жест, а не одна строка.
        $this->pickBatchId($request);

        // Запрос пришёл из шлюза ИИ (/gtsai) — тот же путь и те же права, но
        // дополнительно сужаем. Права пользователя остаются потолком, этот
        // режим может только отнять.
        if (!empty($this->config['ai_mode'])) {
            $resp = $this->checkAIAccess($rule, $request);
            if (!$resp['success']) return $resp;
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

            // Правил на класс может быть несколько (разные компоненты правят свой
            // кусок конфига) — выполняем все по порядку загрузки.
            foreach ($this->triggerHandlers($class, 'gtsapi_rule') as $handler) {
                $params = [
                    'rule' => &$rule,
                    'class' => $class,
                    'request' => $request,
                    'trigger' => 'gtsapi_rule',
                ];
                $resp = $handler['service']->{$handler['method']}($params);
                if (!$resp['success']) return $resp;
            }
        } catch (Error $e) {
            $this->modx->log(1, 'gtsAPI Ошибка триггера gtsapi_rule ' . $e->getMessage());
            return $this->error('Ошибка триггера gtsapi_rule ' . $e->getMessage());
        }
        
        // Добавляем действие excel_export если оно не отключено
        if (!isset($rule['properties']['actions']['excel_export']) || $rule['properties']['actions']['excel_export'] !== false) {
            if (!isset($rule['properties']['actions']['excel_export'])) {
                // Нейтральная, а не 'success': выгрузка в Excel — вспомогательное
                // действие, а зелёная заливка спорит за внимание с тем, ради
                // чего на страницу пришли. Раньше страницы перекрашивали её у
                // себя (в расчёте она была белой) — каждая по-своему.
                $rule['properties']['actions']['excel_export'] = [
                    'head' => true,
                    'icon' => 'pi pi-file-excel',
                    'class' => 'p-button-secondary',
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
                    'class' => 'p-button-secondary',
                    'label' => 'Печать'
                ];
            }
        }
        
        $action = explode('/', $request['api_action']);
        if (count($action) == 1 and !in_array($request['api_action'], ['options', 'autocomplete', 'save_fields_style', 'reset_fields_style', 'sortable_reorder', 'sortable_insert_above', 'versions', 'restore_version', 'paste', 'paste_bulk'])) {
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
                return $this->create($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'insert':
                return $this->create($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'insert_child':
                return $this->create($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'read':
                return $this->read($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'update':
                return $this->update($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'delete':
                return $this->delete($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
            break;
            case 'copy':
                try {
                    $action = [];
                    if (isset($rule['properties']['actions']['copy'])) {
                        $action = $rule['properties']['actions']['copy'];
                        // Для UniTree получаем конфигурацию для конкретной таблицы
                        if (isset($action['tables']) && isset($request['table'])) {
                            if (isset($action['tables'][$request['table']])) {
                                $action = $action['tables'][$request['table']];
                            }
                        }
                    }
                    return $this->copy($rule, $request, $action);
                } catch (Exception $e) {
                    return $this->error('Ошибка копирования: ' . $e->getMessage());
                }
            break;
            case 'options':
                return $this->options($rule, $request, ($rule['aсtions'][$request['api_action']] ?? null));
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
            case 'sortable_reorder':
                return $this->sortableReorder($rule, $request);
            break;
            case 'sortable_insert_above':
                return $this->sortableInsertAbove($rule, $request);
            break;
            case 'versions':
                return $this->versions($rule, $request);
            break;
            case 'restore_version':
                return $this->restore_version($rule, $request);
            break;
            case 'paste':
                // Права проверяет сам paste: по действиям create/update таблицы
                return $this->paste($rule, $request, $method);
            break;
            case 'paste_bulk':
                // Откат/повтор вставки одним запросом, права проверяет сам
                return $this->paste_bulk($rule, $request, $method);
            break;
            default:
                $action = explode('/', $request['api_action']);
                if (count($action) == 2) {
                    // Пока только учёт: вызов метода без объявления в actions таблицы
                    // (см. ServiceActionAuditTrait). Блокировка — после разбора сводки.
                    $this->auditServiceAction($rule, $request['api_action']);
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
     * Ограничения режима ИИ. Вызывается только когда запрос пришёл из шлюза
     * /gtsai (config.ai_mode). Права пользователя проверяются как обычно, этот
     * метод их НЕ расширяет — он только отнимает.
     *
     * Что можно — решают ДВА разрешения, и нужны оба:
     *   1. таблица помечена `ai = 1` — этим она открывается ИИ на чтение
     *      (read/options/autocomplete/versions) и больше ни на что;
     *   2. конкретное действие помечено в конфиге таблицы:
     *      actions: { update: {'ai': 1} } — вот это ИИ уже может.
     *      Права самого действия (groups/permissions) проверяются как обычно,
     *      поэтому ИИ физически не может больше, чем этот пользователь руками.
     *
     * config.ai_mode — рубильники уровня чата, они только отнимают:
     *   ['write' => bool]     разрешены ли чату пишущие действия вообще
     *   ['rollback' => bool]  restore_version (откат делает сам шлюз по кнопке
     *                         пользователя, модели это действие не достаётся)
     *
     * ⚠️ Удаление откату НЕ подлежит: restore_version делает update по
     * object_id, а удалённую строку обновлять нечем. Ставить delete: {'ai': 1}
     * стоит только там, где это осознанно не страшно.
     */
    public function checkAIAccess($rule, $request)
    {
        $mode   = (array)$this->config['ai_mode'];
        $action = isset($request['api_action']) ? $request['api_action'] : '';

        if (empty($rule['ai'])) {
            return $this->error("ИИ не допущен к таблице {$rule['table']}.");
        }

        // Чтение таблицы и её конфига даёт сам флаг ai — это и есть «по
        // умолчанию только читать».
        $read = ['read', 'options', 'autocomplete', 'versions'];
        if (in_array($action, $read, true)) return $this->success();

        // Откат — действие шлюза по кнопке пользователя, а не модели.
        if ($action === 'restore_version') {
            if (empty($mode['rollback'])) {
                return $this->error('Откат доступен только по кнопке пользователя.');
            }
            return $this->success();
        }

        // Удаление в режиме отката — это отмена строки, которую ИИ сам же и
        // создал: вернуть её «как было» нечем, restore_version правит
        // существующую. Модели это недоступно: режим отката включает шлюз, и
        // только для строк из своего пакета правок.
        if ($action === 'delete' && !empty($mode['rollback'])) {
            return $this->success();
        }

        // Всё остальное — только если действие помечено в конфиге таблицы:
        // actions: { update: {'ai': 1} }. Нет пометки — нет действия.
        if (!$this->actionAllowsAI($rule, $action)) {
            return $this->error("Действие {$action} не открыто ИИ в таблице {$rule['table']}.");
        }

        // ⚠️ Правку ИИ пускаем только туда, откуда её можно откатить.
        // На этом уже наступили: запись в строки расчёта прошла, а откат
        // ответил «версионирование не включено» — и правка осталась навсегда.
        if ($action === 'update' && empty($rule['properties']['save_version_row'])) {
            return $this->error(
                "Правка таблицы {$rule['table']} закрыта для ИИ: не включено версионирование "
                . "(properties.save_version_row), откат был бы невозможен."
            );
        }
        if (empty($mode['write'])) {
            return $this->error('Этому чату ИИ разрешено только чтение.');
        }
        if (strpos($action, '/') === false) return $this->success();

        // Вызов «пакет/метод». Главная причина, по которой этот метод вообще
        // существует: такой вызов проверяет права ТОЛЬКО той таблицы, через
        // которую пришёл (см. ServiceActionAuditTrait), то есть через любую
        // доступную таблицу можно дёрнуть метод любого компонента. Людям это
        // пока только логируется, а ИИ перебрал бы методы — поэтому здесь
        // блокировка жёсткая: метод должен быть объявлен в actions этой таблицы
        // и пройти права по объявлению.
        // Вызов «пакет/метод» вдобавок обязан быть объявлен в actions этой
        // таблицы и пройти права по объявлению: иначе через любую доступную
        // таблицу дёргается метод любого компонента (см. ServiceActionAuditTrait).
        if ($this->serviceActionStatus($rule, $action) !== 'ok') {
            return $this->error("Действие {$action} не объявлено для таблицы {$rule['table']} — ИИ его не вызывает.");
        }
        return $this->success();
    }

    /**
     * Помечено ли действие как доступное ИИ в конфиге таблицы.
     * Ищем и в actions, и в hide_actions, и по ключу, и по полю action
     * (действие может быть объявлено как ['action' => 'пакет/метод']).
     */
    protected function actionAllowsAI($rule, $action)
    {
        foreach (['actions', 'hide_actions'] as $key) {
            if (empty($rule['properties'][$key]) || !is_array($rule['properties'][$key])) continue;
            foreach ($rule['properties'][$key] as $name => $cfg) {
                if (!is_array($cfg)) continue;
                $match = ($name === $action)
                    || (isset($cfg['action']) && $cfg['action'] === $action);
                if ($match && !empty($cfg['ai'])) return true;
            }
        }
        return false;
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
            // Каталога модели может не быть вовсе (например loadModels: 'modx' —
            // классы ядра). Молча пропускаем: xPDO иначе пишет в лог
            // «Path specified for package ... is not a valid directory».
            $modelPath = MODX_CORE_PATH . "components/{$class}/model/";
            if (is_dir($modelPath)) {
                $this->modx->addPackage($class, $modelPath);
            }

            return $this->success("Компонент $package не имеет сервиса!");
        }
        $service = $this->models[$class];

        $this->addServiceTriggers($service, $class);
        return $this->success();
    }
}

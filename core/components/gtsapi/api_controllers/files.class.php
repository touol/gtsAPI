<?php

class filesAPIController{
    public $config = [];
    public $modx;
    public $pdo;
    /** @var modMediaSource $source */
    public $source;

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsapi/';

        $this->config = array_merge([
            
        ], $config);

        // Загружаем лексиконы для медиа-источников
        $this->modx->lexicon->load('core:source');
        $this->modx->lexicon->load('core:file');

        if ($this->pdo = $this->modx->getService('pdoFetch')) {
            $this->pdo->setConfig($this->config);
        }
    }
    public function checkPermissions($rule_action){
        // $this->modx->log(1,"checkPermissions ".print_r($rule_action,1));
        if(isset($rule_action['authenticated']) and $rule_action['authenticated'] == 1){
            if(!$this->modx->user->id > 0) return $this->error("Not api authenticated!",['user_id'=>$this->modx->user->id]);
        }

        if(isset($rule_action['groups']) and !empty($rule_action['groups'])){
            // $this->modx->log(1,"checkPermissions groups".print_r($rule_action['groups'],1));
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if(isset($rule_action['permissions'])and !empty($rule_action['permissions'])){
            $permissions = array_map('trim', explode(',', $rule_action['permissions']));
            foreach($permissions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }

    /**
     * Проверка прав доступа к файловым операциям
     */
    public function checkFilePermissions($action = 'read')
    {
        // Доступ к файлам = ДВЕ независимые проверки, обе должны пройти:
        //
        // 1) Доступ к САМОМУ источнику — ACL источника (modAccessMediaSource) через
        //    $source->checkPolicy. Это scoping: какие источники вообще видит группа.
        //    Без него любое контекстное файловое право открывало бы ВСЕ источники.
        //    ('load' в checkPolicy спец-кейснут в true — берём 'list'/'view'/… по действию.)
        //
        // 2) Право на конкретную ФАЙЛОВУЮ операцию — контекстные права группы через
        //    $modx->hasPermission. Именно так их проверяет и сам modFileMediaSource
        //    внутри getContainerList/getObjectsInContainer (directory_list/file_list),
        //    поэтому гейт контроллера завязан на ту же модель — без рассинхрона.
        //    Права выдаёт политика gtsAPIFileAccess (шаблон gtsAPIFileTemplate, группа
        //    Object) на нужный контекст нужной группе.
        //
        // Анонима до сюда не пускает гейт в route(). sudo (админ) проходит обе.
        $srcMap = [
            'list'     => 'list',
            'read'     => 'view',   'download' => 'view',   'view' => 'view',
            'upload'   => 'create', 'create'   => 'create', 'update' => 'save',
            'delete'   => 'remove', 'remove'   => 'remove',
            'edit'     => 'save',   'save'     => 'save',
            'directory_create' => 'create',
            'directory_remove' => 'remove',
            'directory_update' => 'save',
        ];
        $fileMap = [
            'list'     => 'file_list',
            'read'     => 'file_view',  'download' => 'file_view', 'view' => 'file_view',
            'upload'   => 'file_upload', 'create'   => 'file_create', 'update' => 'file_update',
            'delete'   => 'file_remove', 'remove'   => 'file_remove',
            'edit'     => 'file_update', 'save'     => 'file_update',
            'directory_create' => 'directory_create',
            'directory_remove' => 'directory_remove',
            'directory_update' => 'directory_update',
        ];
        if (!isset($fileMap[$action])) {
            return false;
        }
        $srcPerm = isset($srcMap[$action]) ? $srcMap[$action] : 'list';
        // 1) доступ к источнику   2) право на файловую операцию
        return $this->source->checkPolicy($srcPerm) && $this->modx->hasPermission($fileMap[$action]);
    }

    /**
     * Confinement: путь обязан оставаться ВНУТРИ папки источника.
     *
     * Даже с аутентификацией и правами нельзя выпускать файловые операции за пределы
     * basePath источника. Отдельная проверка нужна потому, что дефолтный «Filesystem»
     * настроен с пустым basePath = корень сайта: без этой границы `/core/config/config.inc.php`
     * лежит «внутри источника» и читается штатно.
     *
     * @param string $path относительный путь запроса (уже после sanitizePath)
     * @return true|string true — путь безопасен; строка — текст ошибки
     */
    protected function guardPath($path)
    {
        $base = method_exists($this->source, 'getBasePath') ? $this->source->getBasePath('') : '';
        $realBase = $base ? realpath($base) : false;

        // Источник обязан иметь собственную папку. Корень сайта и всё внутри core/ —
        // запрещены как база: иначе «внутри источника» = весь сайт.
        $siteRoot = realpath(MODX_BASE_PATH);
        $coreRoot = realpath(MODX_CORE_PATH);
        if (!$realBase || $realBase === $siteRoot || ($coreRoot && strpos($realBase, $coreRoot) === 0)) {
            return 'Источник файлов настроен небезопасно: базовый путь не должен быть корнем сайта';
        }

        // Целевой абсолютный путь. Для создания файла его ещё нет — проверяем родителя.
        $rel = ltrim($path, '/');
        $target = realpath($realBase . '/' . $rel);
        if ($target === false) {
            $target = realpath($realBase . '/' . ltrim(dirname($rel), '/'));
        }
        $realBaseSlash = rtrim($realBase, '/\\') . DIRECTORY_SEPARATOR;
        if ($target === false || ($target !== rtrim($realBase, '/\\') && strpos($target, $realBaseSlash) !== 0)) {
            return 'Путь вне папки источника';
        }
        return true;
    }
    /**
     * Инициализация MediaSource
     * 
     * @param int $sourceId ID источника медиа
     * @return bool Результат инициализации
     */
    public function initializeSource($sourceId = 1) {
        $this->modx->loadClass('sources.modMediaSource');
        $this->source = modMediaSource::getDefaultSource($this->modx, $sourceId);
        if (!$this->source) {
            return false;
        }
        $this->source->initialize();
        return true;
    }
    
    /**
     * Маршрутизация запросов API
     * 
     * @param array $rule Правило маршрутизации
     * @param string $uri URI запроса
     * @param string $method Метод запроса (GET, POST и т.д.)
     * @param array $request Данные запроса
     * @param string $id Идентификатор (если есть)
     * @return array Результат обработки запроса
     */
    public function route($rule, $uri, $method, $request, $id){
        $req = json_decode(file_get_contents('php://input'), true);
        if(is_array($req)) $request = array_merge($request,$req);

        // ⚠ Файловый API — ТОЛЬКО для аутентифицированных. Правило `files` исторически
        // публичное (authenticated=0), а сам route() раньше отдавал управление действию
        // без единой проверки прав: любой неаутентифицированный мог прочитать
        // core/config/config.inc.php (доступы к БД) через ?action=content. Гейт здесь —
        // единственная точка, через которую проходят ВСЕ действия контроллера.
        if (!($this->modx->user && $this->modx->user->id > 0)) {
            return $this->error('Not api authenticated!', ['user_id' => 0]);
        }

        // Получаем ID источника медиа
        $sourceId = isset($request['source']) ? (int)$request['source'] : 1;
        
        // Инициализируем MediaSource
        if (!$this->initializeSource($sourceId)) {
            return $this->error('Не удалось инициализировать источник медиа');
        }
        
        // Обработка GET запросов
        if ($method === 'GET') {
            // Скачивание файла
            if (isset($request['action']) && $request['action'] === 'download') {
                return $this->downloadFile($request);
            }
            
            // Получение содержимого файла
            if (isset($request['action']) && $request['action'] === 'content') {
                return $this->getFileContent($request);
            }
            // Получение источников файлов
            if (isset($request['action']) && $request['action'] === 'source_list') {
                return $this->getSourceList($request);
            }
            // Получение списка файлов
            return $this->getFiles($request);
        }
        
        // Обработка POST запросов
        if ($method === 'POST') {
            $action = isset($request['action']) ? $request['action'] : '';
            
            switch ($action) {
                case 'directory':
                    return $this->createDirectory($request);
                case 'upload':
                    return $this->uploadFile($request);
                case 'rename':
                    return $this->renameFileOrDirectory($request);
                case 'remove':
                    return $this->removeFileOrDirectory($request);
                case 'update_content':
                    return $this->updateFileContent($request);
                case 'create_file':
                    return $this->createFile($request);
                default:
                    return $this->error('Неизвестное действие');
            }
        }
        
        return $this->error('Метод не поддерживается');
    }
    
    /**
     * Получение списка доступных медиа-источников
     * 
     * @param array $request Данные запроса
     * @return array Результат операции со списком доступных медиа-источников
     */
    public function getSourceList($request) {
        // Получение всех медиа-источников
        $mediaSources = $this->modx->getCollection('sources.modMediaSource');

        // Перебор и формирование списка медиа-источников
        $sources = [];
        foreach ($mediaSources as $source) {
            // Проверяем права доступа
            if (!$source->checkPolicy('list')) continue;
            
            $sources[] = [
                'id' => $source->get('id'),
                'name' => $source->get('name'),
            ];
        }
        return $this->success('', ['sources' => $sources]);
    }
    /**
     * Получение списка файлов в директории
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function getFiles($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('list')) {
            return $this->error('Нет прав доступа для просмотра списка файлов');
        }
        
        // Получаем путь к директории
        $path = isset($request['path']) ? $request['path'] : '/';
        $path = $this->sanitizePath($path);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        
        // Получаем список директорий
        $this->source->setRequestProperties(['dir' => $path]);
        $containerList = $this->source->getContainerList($path);

        // Проверяем наличие ошибок
        if ($containerList === false) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }

        // Получаем список файлов
        $objectList = $this->source->getObjectsInContainer($path);

        // Проверяем наличие ошибок
        if ($objectList === false) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }

        // Формируем ответ
        $files = [];
        $directories = [];

        // baseUrl источника (modMediaSource возвращает в 'url' только pathRelative)
        $sourceProps = $this->source->getPropertyList();
        $baseUrl = isset($sourceProps['baseUrl']) ? (string)$sourceProps['baseUrl'] : '';
        $baseUrlRelative = isset($sourceProps['baseUrlRelative'])
            ? !in_array(strtolower((string)$sourceProps['baseUrlRelative']), ['false', '0', 'no', ''])
            : true;
        if ($baseUrlRelative && $baseUrl !== '' && $baseUrl[0] !== '/') {
            $baseUrl = '/' . ltrim($baseUrl, '/');
        }
        $buildFullUrl = function ($urlOrPath) use ($baseUrl) {
            if (!$urlOrPath) return $urlOrPath;
            if (preg_match('#^(https?:)?//#i', $urlOrPath)) return $urlOrPath;
            if ($baseUrl === '' || $baseUrl === '/' || strpos($urlOrPath, $baseUrl) === 0) {
                return $urlOrPath[0] === '/' ? $urlOrPath : '/' . $urlOrPath;
            }
            return rtrim($baseUrl, '/') . '/' . ltrim($urlOrPath, '/');
        };

        // Обрабатываем директории
        foreach ($containerList as $item) {
            if ($item['type'] === 'dir') {
                $directories[] = [
                    'name' => $item['text'],
                    'path' => $item['pathRelative'],
                    'lastmod' => isset($item['lastmod']) ? $item['lastmod'] : null,
                    'is_dir' => true,
                    'is_readable' => isset($item['perms']['read']) ? $item['perms']['read'] : true,
                    'is_writable' => isset($item['perms']['write']) ? $item['perms']['write'] : true
                ];
            }
        }

        // Обрабатываем файлы
        foreach ($objectList as $item) {
            $fullUrl = $buildFullUrl($item['url']);
            $files[] = [
                'name' => $item['name'],
                'url' => $fullUrl,
                'size' => isset($item['size']) ? $item['size'] : 0,
                'lastmod' => isset($item['lastmod']) ? $item['lastmod'] : null,
                'type' => isset($item['mime']) ? $item['mime'] : 'application/octet-stream',
                'is_dir' => false,
                'is_readable' => isset($item['perms']['read']) ? $item['perms']['read'] : true,
                'is_writable' => isset($item['perms']['write']) ? $item['perms']['write'] : true,
                'image' => $fullUrl,
                'image_width' => isset($item['image_width']) ? $item['image_width'] : 0,
                'image_height' => isset($item['image_height']) ? $item['image_height'] : 0,
                'thumb' => $fullUrl,
                'thumb_width' => isset($item['thumb_width']) ? $item['thumb_width'] : 0,
                'thumb_height' => isset($item['thumb_height']) ? $item['thumb_height'] : 0,
                'ext'=>$item['ext'],
                'pathRelative'=>$item['pathRelative'],
                'path'=>$item['pathRelative'],
                'editable'=>!empty($item['page'])? true : false
            ];
        }
        
        // baseUrl источника — фронту нужен, чтобы из полного url файла получить
        // путь относительно источника (для навигации в подпапку год/месяц).
        return $this->success('', [
            'files' => $files,
            'directories' => $directories,
            'baseUrl' => ($baseUrl !== '' ? rtrim($baseUrl, '/') . '/' : ''),
        ]);
    }
    
    /**
     * Создание директории
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function createDirectory($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('create')) {
            return $this->error('Нет прав доступа для создания директорий');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['name'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $parent = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($parent); if ($__g !== true) return $this->error($__g);
        $name = $this->sanitizeName($request['name']);
        
        // Создаем директорию
        $success = $this->source->createContainer($name, $parent);
        
        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем ответ
        $path = rtrim($parent, '/') . '/' . $name;
        return $this->success('Директория успешно создана', [
            'path' => $path,
            'name' => $name,
            'is_dir' => true
        ]);
    }
    
    /**
     * Загрузка файла
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function uploadFile($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('upload')) {
            return $this->error('Нет прав доступа для загрузки файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || empty($_FILES)) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);

        // Распределение по году/месяцу, если включено в настройках источника
        // (свойство источника distributeByDate = Да). Разбивает большую плоскую
        // папку на подпапки ГОД/МЕСЯЦ, чтобы файловый браузер не тупил от объёма.
        $props = $this->source->getPropertyList();
        $byDate = isset($props['distributeByDate'])
            && !in_array(strtolower(trim((string)$props['distributeByDate'])), ['', '0', 'false', 'no', 'off'], true);
        if ($byDate) {
            $subDir = ltrim(rtrim($path, '/') . '/' . date('Y') . '/' . date('m') . '/', '/');
            // createContainer создаёт вложенные папки от корня источника
            $this->source->errors = array();
            $this->source->createContainer($subDir, '/');
            $path = $subDir;
        }

        // Загружаем файлы
        $this->source->errors = array();
        $success = $this->source->uploadObjectsToContainer($path, $_FILES);

        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }

        // Получаем информацию о загруженном файле (url — финальный путь, вкл. год/месяц)
        $fileInfo = [];
        foreach ($_FILES as $file) {
            $name = basename($file['name']);
            $relPath = ltrim(rtrim($path, '/') . '/' . $name, '/');
            // url в том же формате, что и листинг getFiles (ведущий '/'),
            // чтобы сохранённый в записи путь не отличался от прежних.
            $url = $this->source->getObjectUrl($relPath);
            if ($url && !preg_match('#^(https?:)?//#i', $url) && $url[0] !== '/') {
                $url = '/' . ltrim($url, '/');
            }
            $fileInfo[] = [
                'name' => $name,
                'path' => rtrim($path, '/') . '/' . $name,
                'url'  => $url,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }

        return $this->success('Файл успешно загружен', count($fileInfo) === 1 ? $fileInfo[0] : $fileInfo);
    }
    
    /**
     * Переименование файла или директории
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function renameFileOrDirectory($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('save')) {
            return $this->error('Нет прав доступа для переименования файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['newName'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        $newName = $this->sanitizeName($request['newName']);
        
        // Определяем, является ли объект директорией
        $isDir = substr($path, -1) === '/';
        
        // Переименовываем объект
        $success = false;
        if ($isDir) {
            $success = $this->source->renameContainer($path, $newName);
        } else {
            $success = $this->source->renameObject($path, $newName);
        }
        
        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем новый путь
        $pathParts = explode('/', rtrim($path, '/'));
        array_pop($pathParts);
        $newPath = implode('/', $pathParts) . '/' . $newName;
        
        // Формируем ответ
        return $this->success('Объект успешно переименован', [
            'path' => $newPath,
            'name' => $newName
        ]);
    }
    
    /**
     * Удаление файла или директории
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function removeFileOrDirectory($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('remove')) {
            return $this->error('Нет прав доступа для удаления файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к объекту');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        
        // Определяем, является ли объект директорией
        $isDir = substr($path, -1) === '/';
        
        // Удаляем объект
        $success = false;
        if ($isDir) {
            $success = $this->source->removeContainer($path);
        } else {
            $success = $this->source->removeObject($path);
        }
        
        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем ответ
        return $this->success('Объект успешно удален');
    }
    
    /**
     * Скачивание файла
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function downloadFile($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('download')) {
            return $this->error('Нет прав доступа для скачивания файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к файлу');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        
        // Получаем содержимое файла
        $fileArray = $this->source->getObjectContents($path);
        
        // Проверяем наличие ошибок
        if (empty($fileArray)) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Устанавливаем заголовки для скачивания
        $fileName = basename($path);
        $fileSize = strlen($fileArray['content']);
        $mimeType = isset($fileArray['mime']) ? $fileArray['mime'] : 'application/octet-stream';
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $fileSize);
        
        // Выводим содержимое файла
        echo $fileArray['content'];
        exit;
    }
    
    /**
     * Санитизация пути
     * 
     * @param string $path Путь для санитизации
     * @return string Санитизированный путь
     */
    protected function sanitizePath($path) {
        return preg_replace('/[\.]{2,}/', '', htmlspecialchars($path));
    }
    
    /**
     * Санитизация имени файла или директории
     * 
     * @param string $name Имя для санитизации
     * @return string Санитизированное имя
     */
    protected function sanitizeName($name) {
        return ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($name))), '/');
    }

   
    public function success($message = "",$data = []){
        //return array('success'=>1,'message'=>$message,'data'=>$data);
        header("HTTP/1.1 200 OK");
        return ['success'=>1,'message'=>$message,'data'=>$data];
    }
    public function error($message = "",$data = []){
        return ['success'=>0,'message'=>$message,'data'=>$data];
    }
    
    /**
     * Получение содержимого файла
     * 
     * @param array $request Данные запроса
     * @return array Результат операции с содержимым файла
     */
    public function getFileContent($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('read')) {
            return $this->error('Нет прав доступа для чтения содержимого файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к файлу');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        
        // Получаем содержимое файла
        $fileArray = $this->source->getObjectContents($path);
        
        // Проверяем наличие ошибок
        if (empty($fileArray)) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем ответ
        return $this->success('', [
            'content' => $fileArray['content'],
            'name' => basename($path),
            'path' => $path,
            'size' => strlen($fileArray['content']),
            'mime' => isset($fileArray['mime']) ? $fileArray['mime'] : 'application/octet-stream'
        ]);
    }
    
    /**
     * Обновление содержимого файла
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function updateFileContent($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('save')) {
            return $this->error('Нет прав доступа для редактирования содержимого файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['content'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($path); if ($__g !== true) return $this->error($__g);
        $content = $request['content'];
        
        // Обновляем содержимое файла
        $success = $this->source->updateObject($path, $content);
        
        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем ответ
        return $this->success('Файл успешно обновлен', [
            'path' => $path,
            'name' => basename($path),
            'size' => strlen($content)
        ]);
    }
    
    /**
     * Создание файла
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function createFile($request) {
        // Проверка прав доступа
        if (!$this->checkFilePermissions('create')) {
            return $this->error('Нет прав доступа для создания файлов');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['name'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $directory = $this->sanitizePath($request['path']);
        $__g = $this->guardPath($directory); if ($__g !== true) return $this->error($__g);
        $name = $this->sanitizeName($request['name']);
        $content = isset($request['content']) ? $request['content'] : '';
        
        // Создаем файл
        $path = $this->source->createObject($directory, $name, $content);
        
        // Проверяем наличие ошибок
        if (empty($path)) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Формируем ответ
        return $this->success('Файл успешно создан', [
            'path' => $path,
            'name' => $name,
            'size' => strlen($content)
        ]);
    }
}

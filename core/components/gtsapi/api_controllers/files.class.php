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
        if($rule_action['authenticated']){
            if(!$this->modx->user->isAuthenticated()) return $this->error("Not api authenticated!");
        }
        if($rule_action['groups']){
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if($rule_action['permitions']){
            $permitions = array_map('trim', explode(',', $rule_action['permitions']));
            foreach($permitions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
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
        if (!$this->source->checkPolicy('list')) {
            return $this->error('Доступ запрещен');
        }
        
        // Получаем путь к директории
        $path = isset($request['path']) ? $request['path'] : '/';
        $path = $this->sanitizePath($path);
        
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
            $files[] = [
                'name' => $item['name'],
                'url' => $item['url'],
                'size' => isset($item['size']) ? $item['size'] : 0,
                'lastmod' => isset($item['lastmod']) ? $item['lastmod'] : null,
                'type' => isset($item['mime']) ? $item['mime'] : 'application/octet-stream',
                'is_dir' => false,
                'is_readable' => isset($item['perms']['read']) ? $item['perms']['read'] : true,
                'is_writable' => isset($item['perms']['write']) ? $item['perms']['write'] : true,
                'image' => isset($item['image']) ? $item['image'] : $item['url'],
                'image_width' => isset($item['image_width']) ? $item['image_width'] : 0,
                'image_height' => isset($item['image_height']) ? $item['image_height'] : 0,
                'thumb' => isset($item['thumb']) ? $item['thumb'] : $item['url'],
                'thumb_width' => isset($item['thumb_width']) ? $item['thumb_width'] : 0,
                'thumb_height' => isset($item['thumb_height']) ? $item['thumb_height'] : 0,
                'ext'=>$item['ext'],
                'pathRelative'=>$item['pathRelative'],
                'path'=>$item['pathRelative'],
                'editable'=>!empty($item['page'])? true : false
            ];
        }
        
        return $this->success('', ['files' => $files, 'directories' => $directories]);
    }
    
    /**
     * Создание директории
     * 
     * @param array $request Данные запроса
     * @return array Результат операции
     */
    public function createDirectory($request) {
        // Проверка прав доступа
        if (!$this->source->checkPolicy('create')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['name'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $parent = $this->sanitizePath($request['path']);
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
        if (!$this->source->checkPolicy('create')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || empty($_FILES)) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        
        // Загружаем файлы
        $success = $this->source->uploadObjectsToContainer($path, $_FILES);
        
        // Проверяем наличие ошибок
        if (!$success) {
            $errors = $this->source->getErrors();
            return $this->error(implode("\n", $errors));
        }
        
        // Получаем информацию о загруженном файле
        $fileInfo = [];
        foreach ($_FILES as $file) {
            $fileInfo[] = [
                'name' => basename($file['name']),
                'path' => rtrim($path, '/') . '/' . basename($file['name']),
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
        if (!$this->source->checkPolicy('save')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['newName'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $path = $this->sanitizePath($request['path']);
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
        if (!$this->source->checkPolicy('remove')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к объекту');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        
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
        if (!$this->source->checkPolicy('list')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к файлу');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        
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
        if (!$this->source->checkPolicy('list')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path'])) {
            return $this->error('Не указан путь к файлу');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
        
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
        if (!$this->source->checkPolicy('save')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['content'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем путь
        $path = $this->sanitizePath($request['path']);
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
        if (!$this->source->checkPolicy('create')) {
            return $this->error('Доступ запрещен');
        }
        
        // Проверяем наличие необходимых параметров
        if (!isset($request['path']) || !isset($request['name'])) {
            return $this->error('Не указаны обязательные параметры');
        }
        
        // Получаем и санитизируем параметры
        $directory = $this->sanitizePath($request['path']);
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

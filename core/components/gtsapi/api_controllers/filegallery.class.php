<?php

class fileGalleryAPIController{
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
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'],
            'max_file_size' => 10485760, // 10MB
            'upload_path' => 'gallery/',
        ], $config);

        // Загружаем лексиконы
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
        $sourceId = $this->source->get('id');
        
        // Проверяем, настроены ли ACL для источника медиа
        $acls = $this->modx->getCollection('sources.modAccessMediaSource', ['target' => $sourceId]);
        
        if (!empty($acls)) {
            switch ($action) {
            
            case 'list':
                return $this->source->checkPolicy('list');
            case 'download':
            case 'read':
            case 'view':
                return $this->source->checkPolicy('load');
            case 'upload':
            case 'create':
            case 'update':
                return $this->source->checkPolicy('create');
            case 'delete':
                return $this->source->checkPolicy('remove');
            case 'edit':
                return $this->source->checkPolicy('save');
            default:
                return false;
            }
        }
        
        // Если права не заданы, применяем дефолтные правила
        switch ($action) {
            case 'list':
                return false;
            case 'read':
            case 'download':
            case 'view':
                // Чтение доступно всем
                return true;
                
            case 'upload':
            case 'create':
            case 'update':
            case 'delete':
            case 'edit':
                // Редактирование только для группы Administrator
                if (!$this->modx->user->isMember('Administrator')) {
                    return false;
                }
                return true;
                
            default:
                return false;
        }
    }

    /**
     * Инициализация MediaSource
     */
    public function initializeSource($sourceId = null)
    {
        $this->modx->loadClass('sources.modMediaSource');
        
        // Сначала пробуем найти источник gtsAPIFile
        if (!$sourceId) {
            $gtsAPISource = $this->modx->getObject('sources.modMediaSource', ['name' => 'gtsAPIFile']);
            if ($gtsAPISource) {
                $sourceId = $gtsAPISource->get('id');
            } else {
                // Если не найден, используем системную настройку
                $sourceId = $this->modx->getOption('gtsapi_default_media_source', null, 1);
            }
        }
        
        $this->source = $this->modx->getObject('sources.modMediaSource', $sourceId);
        if (!$this->source) {
            return false;
        }
        
        $this->source->initialize();
        return true;
    }

    /**
     * Маршрутизация запросов API
     */
    public function route($rule, $uri, $method, $request, $id)
    {
        $req = json_decode(file_get_contents('php://input'), true);
        if (is_array($req)) $request = array_merge($request, $req);

        // Получаем ID источника медиа
        $sourceId = isset($request['source']) ? (int)$request['source'] : null;

        // Инициализируем MediaSource
        if (!$this->initializeSource($sourceId)) {
            return $this->error('Не удалось инициализировать источник медиа');
        }

        // Обработка GET запросов
        if ($method === 'GET') {
            $action = isset($request['action']) ? $request['action'] : '';

            switch ($action) {
                case 'list':
                    return $this->getFilesList($request);
                case 'get':
                    return $this->getFile($request, $id);
                case 'download':
                    return $this->downloadFile($request, $id);
                case 'thumbnail':
                    return $this->getThumbnail($request, $id);
                case 'content':
                    return $this->getFileContent($request, $id);
                default:
                    return $this->getFilesList($request);
            }
        }

        // Обработка POST запросов
        if ($method === 'POST') {
            $action = isset($request['action']) ? $request['action'] : '';

            switch ($action) {
                case 'upload':
                    return $this->uploadFile($request);
                case 'attach':
                    return $this->attachFile($request);
                case 'detach':
                    return $this->detachFile($request);
                case 'update':
                    return $this->updateFile($request, $id);
                case 'generate_thumbnails':
                    return $this->generateThumbnails($request, $id);
                default:
                    return $this->uploadFile($request);
            }
        }

        // Обработка DELETE запросов
        if ($method === 'DELETE') {
            return $this->deleteFile($request, $id);
        }

        return $this->error('Метод не поддерживается');
    }

    /**
     * Получение списка файлов
     */
    public function getFilesList($request)
    {
        // Проверка прав на чтение
        if (!$this->checkFilePermissions('list')) {
            return $this->error('Нет прав доступа для просмотра списка файлов');
        }

        $where = [];
        $limit = isset($request['limit']) ? (int)$request['limit'] : 20;
        $offset = isset($request['offset']) ? (int)$request['offset'] : 0;

        // Фильтрация по классу и родителю
        if (isset($request['class']) && !empty($request['class'])) {
            $where['class'] = $request['class'];
        }
        if (isset($request['parent']) && $request['parent'] !== '') {
            $where['parent'] = (int)$request['parent'];
        }
        if (isset($request['list']) && !empty($request['list'])) {
            $where['list'] = $request['list'];
        }

        // Фильтрация по типу файла
        if (isset($request['type']) && !empty($request['type'])) {
            $where['type'] = $request['type'];
        }

        if (isset($request['trumb'])) {
            $where['trumb'] = $request['trumb'];
        }

        // Фильтрация по MIME типу
        if (isset($request['mime']) && !empty($request['mime'])) {
            $where['mime:LIKE'] = '%' . $request['mime'] . '%';
        }

        // Поиск по имени
        if (isset($request['search']) && !empty($request['search'])) {
            $where['name:LIKE'] = '%' . $request['search'] . '%';
        }

        // Только активные файлы
        $where['active'] = 1;

        $query = $this->modx->newQuery('gtsAPIFile');
        $query->where($where);
        $query->sortby('rank', 'ASC');
        $query->sortby('createdon', 'DESC');
        $query->limit($limit, $offset);

        $files = $this->modx->getCollection('gtsAPIFile', $query);
        $total = $this->modx->getCount('gtsAPIFile', $where);

        $result = [];
        foreach ($files as $file) {
            $result[] = $file->toArray();
        }

        return $this->success('', [
            'files' => $result,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Получение конкретного файла
     */
    public function getFile($request, $id)
    {
        // Проверка прав на чтение
        if (!$this->checkFilePermissions('read')) {
            return $this->error('Нет прав доступа для просмотра файла');
        }

        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        return $this->success('', $file->toArray());
    }

    /**
     * Загрузка файла
     */
    public function uploadFile($request)
    {
        // Проверка прав на загрузку
        if (!$this->checkFilePermissions('upload')) {
            return $this->error('Нет прав доступа для загрузки файлов');
        }

        if (empty($_FILES)) {
            return $this->error('Файлы не переданы');
        }

        $uploadedFiles = [];
        $errors = [];

        foreach ($_FILES as $fileKey => $fileData) {
            if (is_array($fileData['name'])) {
                // Множественная загрузка
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    $singleFile = [
                        'name' => $fileData['name'][$i],
                        'type' => $fileData['type'][$i],
                        'tmp_name' => $fileData['tmp_name'][$i],
                        'error' => $fileData['error'][$i],
                        'size' => $fileData['size'][$i]
                    ];
                    $result = $this->processSingleFile($singleFile, $request);
                    if ($result['success']) {
                        $uploadedFiles[] = $result['data'];
                    } else {
                        $errors[] = $result['message'];
                    }
                }
            } else {
                // Одиночная загрузка
                $result = $this->processSingleFile($fileData, $request);
                if ($result['success']) {
                    $uploadedFiles[] = $result['data'];
                } else {
                    $errors[] = $result['message'];
                }
            }
        }

        if (empty($uploadedFiles) && !empty($errors)) {
            return $this->error('Ошибки загрузки: ' . implode(', ', $errors));
        }

        return $this->success('Файлы успешно загружены', [
            'files' => $uploadedFiles,
            'errors' => $errors
        ]);
    }

    /**
     * Обработка одного файла
     */
    private function processSingleFile($fileData, $request)
    {
        // Проверка на ошибки загрузки
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            return $this->error('Ошибка загрузки файла: ' . $this->getUploadErrorMessage($fileData['error']));
        }

        // Проверка размера файла
        if ($fileData['size'] > $this->config['max_file_size']) {
            return $this->error('Файл слишком большой. Максимальный размер: ' . $this->formatFileSize($this->config['max_file_size']));
        }

        // Получение расширения файла
        $pathInfo = pathinfo($fileData['name']);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
        
        if (empty($extension)) {
            return $this->error('Не удалось определить расширение файла');
        }

        // Проверка расширения
        if (!in_array($extension, $this->config['allowed_extensions'])) {
            return $this->error('Недопустимое расширение файла: ' . $extension);
        }

        // Генерация уникального имени файла
        $originalName = isset($pathInfo['filename']) ? $pathInfo['filename'] : 'file';
        $fileName = $this->generateFileName($originalName, $extension);
        
        // Определяем путь загрузки в зависимости от класса объекта
        $uploadPath = $this->getUploadPath($request);

        // Получаем свойства источника медиа
        $sourceProperties = $this->source->getPropertyList();
        $basePath = isset($sourceProperties['basePath']) ? $sourceProperties['basePath'] : '';
        $baseUrl = isset($sourceProperties['baseUrl']) ? $sourceProperties['baseUrl'] : '';

        // Создание полного пути к директории
        $fullDirectoryPath = rtrim($basePath, '/') . '/' . $uploadPath;

        // Создание директории если не существует
        if (!is_dir($fullDirectoryPath)) {
            if (!mkdir($fullDirectoryPath, 0755, true)) {
                return $this->error('Не удалось создать директорию: ' . $fullDirectoryPath);
            }
        }

        // Создание полного пути к файлу
        $fullFilePath = $fullDirectoryPath . $fileName;

        // Загрузка файла
        $fileContent = file_get_contents($fileData['tmp_name']);
        if ($fileContent === false) {
            return $this->error('Не удалось прочитать временный файл');
        }

        // Сохранение файла
        if (file_put_contents($fullFilePath, $fileContent) === false) {
            return $this->error('Ошибка сохранения файла: ' . $fullFilePath);
        }

        // Получаем URL файла
        $fileUrl = rtrim($baseUrl, '/') . '/' . $uploadPath . $fileName;

        // Создание записи в базе данных
        /** @var gtsAPIFile $file */
        $file = $this->modx->newObject('gtsAPIFile');
        $dbData = [
            'parent' => isset($request['parent']) ? (int)$request['parent'] : 0,
            'class' => isset($request['class']) ? $request['class'] : 'modResource',
            'list' => isset($request['list']) ? $request['list'] : 'default',
            'name' => $originalName,
            'description' => isset($request['description']) ? $request['description'] : '',
            'path' => $uploadPath,
            'file' => $fileName,
            'mime' => $fileData['type'],
            'type' => $extension,
            'url' => $fileUrl,
            'hash' => sha1($fileContent),
            'session' => session_id(),
            'size' => $fileData['size'],
            'createdby' => $this->modx->user->get('id'),
            'source' => $this->source->get('id'),
            'context' => $this->modx->context->key,
            'active' => 1,
            'createdon' => date('Y-m-d H:i:s'),
            'properties' => json_encode($request)
        ];

        $file->fromArray($dbData);

        if (!$file->save()) {
            return $this->error('Ошибка сохранения информации о файле в БД');
        }

        // Генерация миниатюр для изображений
        if ($file->isImage()) {
            $file->generateThumbnails();
        }

        return $this->success('Файл успешно загружен', $file->toArray());
    }

    /**
     * Привязка файла к объекту
     */
    public function attachFile($request)
    {
        if (!isset($request['file_id']) || !isset($request['parent']) || !isset($request['class'])) {
            return $this->error('Не указаны обязательные параметры');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $request['file_id']);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        $file->set('parent', (int)$request['parent']);
        $file->set('class', $request['class']);
        if (isset($request['list'])) {
            $file->set('list', $request['list']);
        }

        if ($file->save()) {
            return $this->success('Файл успешно привязан', $file->toArray());
        } else {
            return $this->error('Ошибка привязки файла');
        }
    }

    /**
     * Отвязка файла от объекта
     */
    public function detachFile($request)
    {
        if (!isset($request['file_id'])) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $request['file_id']);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        $file->set('parent', 0);
        $file->set('class', 'modResource');
        $file->set('list', 'default');

        if ($file->save()) {
            return $this->success('Файл успешно отвязан', $file->toArray());
        } else {
            return $this->error('Ошибка отвязки файла');
        }
    }

    /**
     * Обновление информации о файле
     */
    public function updateFile($request, $id)
    {
        // Проверка прав на редактирование
        if (!$this->checkFilePermissions('update')) {
            return $this->error('Нет прав доступа для редактирования файлов');
        }

        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        // Обновляемые поля
        $allowedFields = ['name', 'description', 'active', 'rank', 'class', 'parent', 'list'];
        foreach ($allowedFields as $field) {
            if (isset($request[$field])) {
                $file->set($field, $request[$field]);
            }
        }

        if ($file->save()) {
            return $this->success('Информация о файле обновлена', $file->toArray());
        } else {
            return $this->error('Ошибка обновления файла');
        }
    }

    /**
     * Удаление файла
     */
    public function deleteFile($request, $id)
    {
        // Проверка прав на удаление
        if (!$this->checkFilePermissions('delete')) {
            return $this->error('Нет прав доступа для удаления файлов');
        }

        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        if ($file->remove()) {
            return $this->success('Файл успешно удален');
        } else {
            return $this->error('Ошибка удаления файла');
        }
    }

    /**
     * Скачивание файла
     */
    public function downloadFile($request, $id)
    {
        // Проверка прав на скачивание
        if (!$this->checkFilePermissions('download')) {
            return $this->error('Нет прав доступа для скачивания файлов');
        }

        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        if (!$file->initialized()) {
            return $this->error('Ошибка инициализации источника медиа');
        }

        $filePath = $file->get('path') . $file->get('file');
        $fileArray = $file->mediaSource->getObjectContents($filePath);

        if (empty($fileArray)) {
            $errors = $file->mediaSource->getErrors();
            return $this->error('Ошибка получения файла: ' . implode(', ', $errors));
        }

        // Устанавливаем заголовки для скачивания
        $fileName = $file->get('name') . '.' . $file->get('type');
        $fileSize = $file->get('size');
        $mimeType = $file->get('mime');

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $fileSize);

        echo $fileArray['content'];
        exit;
    }

    /**
     * Получение содержимого файла
     */
    public function getFileContent($request, $id)
    {
        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        if (!$file->initialized()) {
            return $this->error('Ошибка инициализации источника медиа');
        }

        $filePath = $file->get('path') . $file->get('file');
        $fileArray = $file->mediaSource->getObjectContents($filePath);

        if (empty($fileArray)) {
            $errors = $file->mediaSource->getErrors();
            return $this->error('Ошибка получения файла: ' . implode(', ', $errors));
        }

        return $this->success('', [
            'content' => $fileArray['content'],
            'name' => $file->get('name'),
            'path' => $filePath,
            'size' => strlen($fileArray['content']),
            'mime' => $file->get('mime')
        ]);
    }

    /**
     * Получение миниатюры
     */
    public function getThumbnail($request, $id)
    {
        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        return $this->success('', [
            'thumbnail_url' => $file->get('thumbnail_url'),
            'full_url' => $file->get('full_url'),
            'is_image' => $file->isImage()
        ]);
    }

    /**
     * Генерация миниатюр
     */
    public function generateThumbnails($request, $id)
    {
        if (!$id) {
            return $this->error('ID файла не указан');
        }

        /** @var gtsAPIFile $file */
        $file = $this->modx->getObject('gtsAPIFile', $id);
        if (!$file) {
            return $this->error('Файл не найден');
        }

        if ($file->generateThumbnails()) {
            return $this->success('Миниатюры успешно созданы');
        } else {
            return $this->error('Ошибка создания миниатюр');
        }
    }

    /**
     * Определение пути загрузки в зависимости от класса объекта
     */
    private function getUploadPath($request)
    {
        $class = isset($request['class']) ? $request['class'] : 'modResource';
        $parentId = isset($request['parent']) ? (int)$request['parent'] : 0;
        $list = isset($request['list']) ? $request['list'] : 'default';
        
        $basePath = '';
        
        switch ($class) {
            case 'modResource':
                // Для ресурсов создаем папку documents/ID_ресурса/
                $basePath = 'documents/' . $parentId . '/';
                break;
                
            case 'modUser':
                // Для пользователей создаем папку users/ID_пользователя/
                $basePath = 'users/' . $parentId . '/';
                break;
                
            default:
                // Для других классов создаем папку по имени класса
                $className = strtolower(str_replace('mod', '', $class));
                $basePath = $className . '/' . $parentId . '/';
                break;
        }
        
        // Добавляем подпапку по списку, если не default
        if ($list !== 'default') {
            $basePath .= $list . '/';
        }
        
        return $basePath;
    }

    /**
     * Генерация уникального имени файла
     */
    private function generateFileName($originalName, $extension)
    {
        $name = $this->transliterate($originalName);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        $name = substr($name, 0, 50);
        
        if (empty($name)) {
            $name = 'file';
        }
        
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        
        return $name . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Транслитерация
     */
    private function transliterate($string)
    {
        $transliteration = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        $string = mb_strtolower($string, 'UTF-8');
        return strtr($string, $transliteration);
    }

    /**
     * Форматирование размера файла
     */
    private function formatFileSize($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Получение сообщения об ошибке загрузки
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Файл превышает максимальный размер, указанный в php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Файл превышает максимальный размер, указанный в форме';
            case UPLOAD_ERR_PARTIAL:
                return 'Файл был загружен частично';
            case UPLOAD_ERR_NO_FILE:
                return 'Файл не был загружен';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Отсутствует временная папка';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Не удалось записать файл на диск';
            case UPLOAD_ERR_EXTENSION:
                return 'Загрузка файла остановлена расширением';
            default:
                return 'Неизвестная ошибка загрузки';
        }
    }

    public function success($message = "", $data = [])
    {
        header("HTTP/1.1 200 OK");
        return ['success' => 1, 'message' => $message, 'data' => $data];
    }

    public function error($message = "", $data = [])
    {
        return ['success' => 0, 'message' => $message, 'data' => $data];
    }
}

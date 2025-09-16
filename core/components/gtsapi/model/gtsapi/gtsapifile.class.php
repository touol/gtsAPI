<?php

class gtsAPIFile extends xPDOSimpleObject
{
    /** @var modX $modx */
    public $modx;
    /** @var gtsAPI $gtsAPI */
    public $gtsAPI;

    /* @var modMediaSource $mediaSource */
    public $mediaSource;
    /* @var array $mediaSourceProperties */
    public $mediaSourceProperties;
    /** @var array $initialized */
    public $initialized = array();

    /** @var array $imageThumbnail */
    public $imageDefaultThumbnail = array(
        'w'  => 120,
        'h'  => 90,
        'q'  => 90,
        'bg' => 'fff',
        'f'  => 'jpg'
    );

    /**
     * gtsAPIFile constructor.
     *
     * @param xPDO $xpdo
     */
    public function __construct(xPDO & $xpdo)
    {
        parent::__construct($xpdo);

        $this->modx = $xpdo;
        $corePath = $this->modx->getOption('gtsapi_core_path', null,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/gtsapi/');
        
        /** @var gtsAPI $gtsAPI */
        $this->gtsAPI = $this->modx->getService(
            'gtsAPI',
            'gtsAPI',
            $corePath . 'model/gtsapi/',
            array(
                'core_path' => $corePath
            )
        );
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = array())
    {
        if (!$this->initialized()) {
            return false;
        }

        $filename = $this->get('path') . $this->get('file');
        if (!@$this->mediaSource->removeObject($filename)) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,
                '[gtsAPIFile] Error remove the attachment file at: ' . $filename);
        }

        // Удаляем связанные миниатюры
        $thumbnails = $this->getMany('Children');
        foreach ($thumbnails as $thumbnail) {
            $thumbnail->remove();
        }

        return parent::remove($ancestors);
    }

    /**
     * @return bool|string
     */
    public function initialized()
    {
        $source = $this->get('source');
        if (!empty($this->initialized[$source])) {
            return true;
        }
        /** @var modMediaSource $mediaSource */
        if ($mediaSource = $this->modx->getObject('sources.modMediaSource', $source)) {
            $mediaSource->set('ctx', $this->get('context'));
            if ($mediaSource->initialize()) {
                $this->mediaSource = $mediaSource;
                $this->mediaSourceProperties = $mediaSource->getPropertyList();
                $this->initialized[$source] = true;

                return true;
            }
        }

        return 'Could not initialize media source with id = ' . $source;
    }

    /**
     * @param array|string $k
     * @param null         $format
     * @param null         $formatTemplate
     *
     * @return mixed|string
     */
    public function get($k, $format = null, $formatTemplate = null)
    {
        switch ($k) {
            case 'format_size':
                $value = $this->formatFileSize($this->get('size'));
                break;
            case 'format_createdon':
                $value = $this->formatFileCreatedon($this->get('createdon'));
                break;
            case 'thumbnail_url':
                $value = $this->getThumbnailUrl();
                break;
            case 'full_url':
                $value = $this->getFullUrl();
                break;
            default:
                $value = parent::get($k, $format, $formatTemplate);
                break;
        }

        return $value;
    }

    /**
     * Получение URL миниатюры
     * @return string
     */
    public function getThumbnailUrl()
    {
        if ($this->get('trumb')) {
            return $this->get('trumb');
        }
        
        // Если это изображение, возвращаем основной URL
        if ($this->isImage()) {
            return $this->get('url');
        }
        
        // Для других типов файлов возвращаем иконку по умолчанию
        return $this->getDefaultIcon();
    }

    /**
     * Получение полного URL файла
     * @return string
     */
    public function getFullUrl()
    {
        $url = $this->get('url');
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        $siteUrl = $this->modx->getOption('site_url');
        return rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Проверка, является ли файл изображением
     * @return bool
     */
    public function isImage()
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(strtolower($this->get('type')), $imageTypes);
    }

    /**
     * Получение иконки по умолчанию для типа файла
     * @return string
     */
    public function getDefaultIcon()
    {
        $type = strtolower($this->get('type'));
        $iconMap = [
            'pdf' => '/assets/components/gtsapi/icons/pdf.svg',
            'doc' => '/assets/components/gtsapi/icons/doc.svg',
            'docx' => '/assets/components/gtsapi/icons/doc.svg',
            'xls' => '/assets/components/gtsapi/icons/excel.svg',
            'xlsx' => '/assets/components/gtsapi/icons/excel.svg',
            'txt' => '/assets/components/gtsapi/icons/text.svg',
            'zip' => '/assets/components/gtsapi/icons/archive.svg',
            'rar' => '/assets/components/gtsapi/icons/archive.svg',
        ];
        
        return isset($iconMap[$type]) ? $iconMap[$type] : '/assets/components/gtsapi/icons/file.svg';
    }

    /**
     * @param     $bytes
     * @param int $precision
     *
     * @return string
     */
    public function formatFileSize($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * @param        $time
     * @param string $format
     *
     * @return string
     */
    public function formatFileCreatedon($time, $format = '%d.%m.%Y %H:%M')
    {
        return strftime($format, strtotime($time));
    }

    /**
     * Генерация миниатюр для изображений
     * @return bool
     */
    public function generateThumbnails()
    {
        if (!$this->initialized()) {
            return false;
        }

        if (!$this->isImage()) {
            return false;
        }

        $imageThumbnails = $this->getImageThumbnails();
        if (empty($imageThumbnails)) {
            return false;
        }

        // Удаляем существующие миниатюры перед генерацией новых
        $this->removeExistingThumbnails();

        $thumbnailType = $this->getThumbnailType();
        foreach ($imageThumbnails as $k => $imageThumbnail) {
            $imageThumbnails[$k] = array_merge(
                $this->imageDefaultThumbnail,
                array('f' => $thumbnailType),
                $imageThumbnail
            );
        }

        foreach ($imageThumbnails as $sizeName => $imageThumbnail) {
            if ($thumbnail = $this->makeThumbnail($imageThumbnail)) {
                $this->saveThumbnail($thumbnail, $imageThumbnail, $sizeName);
            }
        }

        return true;
    }

    /**
     * Удаление существующих миниатюр
     * @return bool
     */
    public function removeExistingThumbnails()
    {
        if (!$this->initialized()) {
            return false;
        }

        // Находим все существующие миниатюры этого файла
        $thumbnails = $this->xpdo->getCollection('gtsAPIFile', array(
            'parent' => $this->get('id'),
            'parent0'=> $this->get('parent0'),
            'class'=> $this->get('class'),
            'trumb:!=' => ''
        ));

        foreach ($thumbnails as $thumbnail) {
            // Удаляем физический файл
            $filename = $thumbnail->get('path') . $thumbnail->get('file');
            if (!@$this->mediaSource->removeObject($filename)) {
                $this->modx->log(xPDO::LOG_LEVEL_WARN,
                    '[gtsAPIFile] Could not remove thumbnail file: ' . $filename);
            }

            // Удаляем запись из базы данных
            if (!$thumbnail->remove()) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,
                    '[gtsAPIFile] Could not remove thumbnail record with ID: ' . $thumbnail->get('id'));
            }
        }

        return true;
    }

    /**
     * @return array|bool|mixed
     */
    public function getImageThumbnails()
    {
        if (!$this->initialized()) {
            return false;
        }
        if (isset($this->mediaSourceProperties['imageThumbnails'])) {
            return $this->xpdo->fromJSON($this->mediaSourceProperties['imageThumbnails']);
        }

        return array();
    }

    /**
     * @return bool|string
     */
    public function getThumbnailType()
    {
        if (!$this->initialized()) {
            return false;
        }
        if (isset($this->mediaSourceProperties['thumbnailType']) AND !empty($this->mediaSourceProperties['thumbnailType'])) {
            return $this->mediaSourceProperties['thumbnailType'];
        }

        return 'jpg';
    }

    /**
     * Создание миниатюры
     * @param array $options
     * @param null $contents
     * @return bool|string
     */
    public function makeThumbnail($options = array(), $contents = null)
    {
        $this->mediaSource->errors = array();
        $filename = $this->get('path') . $this->get('file');
        $contents = $contents ?: $this->mediaSource->getObjectContents($filename);

        if (!is_array($contents)) {
            return "[gtsAPIFile] Could not retrieve contents of file {$filename} from media source.";
        } elseif (!empty($this->mediaSource->errors['file'])) {
            return "[gtsAPIFile] Could not retrieve file {$filename} from media source: " . $this->mediaSource->errors['file'];
        }

        if (!class_exists('modPhpThumb')) {
            require MODX_CORE_PATH . 'model/phpthumb/modphpthumb.class.php';
        }
        
        $phpThumb = new modPhpThumb($this->xpdo);
        $phpThumb->initialize();

        $cacheDir = $this->xpdo->getOption('gtsapi_phpThumb_config_cache_directory', null,
            MODX_CORE_PATH . 'cache/phpthumb/');
        
        if (!is_writable($cacheDir)) {
            if (!$this->xpdo->cacheManager->writeTree($cacheDir)) {
                $this->xpdo->log(modX::LOG_LEVEL_ERROR, '[phpThumbOf] Cache dir not writable: ' . $cacheDir);
                return false;
            }
        }

        $phpThumb->setParameter('config_cache_directory', $cacheDir);
        $phpThumb->setParameter('config_cache_disable_warning', true);
        $phpThumb->setParameter('config_allow_src_above_phpthumb', true);
        $phpThumb->setParameter('config_allow_src_above_docroot', true);
        $phpThumb->setParameter('allow_local_http_src', true);
        $phpThumb->setParameter('config_document_root', $this->xpdo->getOption('base_path', null, MODX_BASE_PATH));
        $phpThumb->setParameter('config_temp_directory', $cacheDir);
        $phpThumb->setParameter('config_max_source_pixels',
            $this->xpdo->getOption('gtsapi_phpThumb_config_max_source_pixels', null, '26843546'));

        $phpThumb->setCacheDirectory();
        $phpThumb->setSourceData($contents['content']);
        
        foreach ($options as $k => $v) {
            $phpThumb->setParameter($k, $v);
        }

        if ($phpThumb->GenerateThumbnail()) {
            ImageInterlace($phpThumb->gdimg_output, true);
            if ($phpThumb->RenderOutput()) {
                return $phpThumb->outputImageData;
            }
        }
        
        $this->xpdo->log(modX::LOG_LEVEL_ERROR,
            '[gtsAPIFile] Could not generate thumbnail for "' . $this->get('url') . '". ' . print_r($phpThumb->debugmessages, 1));

        return false;
    }

    /**
     * Сохранение миниатюры
     * @param $thumbnail
     * @param array $options
     * @param string $sizeName
     * @return bool
     */
    public function saveThumbnail($thumbnail, $options = array(), $sizeName = '')
    {
        // Генерируем случайный суффикс
        $randomSuffix = strtolower(strtr(base64_encode(openssl_random_pseudo_bytes(2)), '+/=', 'zzz'));
        
        // Получаем имя файла без расширения
        $baseName = rtrim(str_replace('.' . $this->get('type'), '', $this->get('file')), '.');
        
        // Формируем новое имя файла: logo_1757790816_7684.nguz.medium.jpg
        $filename = $baseName . '.' . $randomSuffix . '.' . $sizeName . '.' . $options['f'];

        /** @var gtsAPIFile $thumbnailFile */
        $thumbnailFile = $this->xpdo->newObject('gtsAPIFile', array_merge(
            $this->toArray('', true),
            array(
                'class'   => $this->get('class'),        // Сохраняем оригинальный класс
                'parent'  => $this->get('id'),           // parent = ID оригинального файла
                'parent0' => $this->get('parent'),       // parent0 = оригинальный parent
                'file'    => $filename,
                'trumb'   => $sizeName,                  // Записываем размер миниатюры
                'hash'    => sha1($thumbnail)
            )
        ));

        $this->mediaSource->createContainer($thumbnailFile->get('path'), '/');
        $file = $this->mediaSource->createObject(
            $thumbnailFile->get('path'),
            $thumbnailFile->get('file'),
            $thumbnail
        );

        if ($file) {
            $size = strlen($thumbnail);
            $mime = $this->image_file_type_from_binary($thumbnail);

            $thumbnailFile->set('size', $size);
            $thumbnailFile->set('mime', $mime);
            $thumbnailFile->set('type', $options['f']);
            $thumbnailFile->set('properties', $this->modx->toJSON($options));
            $thumbnailFile->set('url',
                $this->mediaSource->getObjectUrl($thumbnailFile->get('path') . $thumbnailFile->get('file')));

            return $thumbnailFile->save();
        } else {
            return false;
        }
    }

    /**
     * @return bool|string
     */
    public function getThumbnailName()
    {
        if (!$this->initialized()) {
            return false;
        }
        if (isset($this->mediaSourceProperties['thumbnailName']) AND !empty($this->mediaSourceProperties['thumbnailName'])) {
            return $this->mediaSourceProperties['thumbnailName'];
        }

        return '{name}.{rand}.{w}.{h}.{ext}';
    }

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        if ($this->isNew()) {
            if (!$this->get('list')) {
                $this->set('list', 'default');
            }
            if (!$this->get('session')) {
                $this->set('session', session_id());
            }
            if (!$this->get('class')) {
                $this->set('class', 'modResource');
            }
            if (!$this->get('createdon')) {
                $this->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
            }
            if (!$this->get('createdby')) {
                if (!empty($this->modx->user) AND $this->modx->user instanceof modUser) {
                    $this->set('createdby', $this->modx->user->get('id'));
                }
            }
            
            // Устанавливаем parent0 равным parent, если он не задан
            if (!$this->get('parent0')) {
                $this->set('parent0', $this->get('parent'));
            }

            $q = $this->xpdo->newQuery('gtsAPIFile');
            $q->where(array(
                'parent'  => $this->get('parent'),
                'class'   => $this->get('class'),
                'source'  => $this->get('source'),
                'context' => $this->get('context')
            ));
            $this->set('rank', $this->xpdo->getCount('gtsAPIFile', $q));
        }

        $saved = parent::save($cacheFlag);

        return $saved;
    }

    /**
     * Определение типа изображения по бинарным данным
     * @param $binary
     * @return string
     */
    protected function image_file_type_from_binary($binary) {
        if (
        !preg_match(
            '/\A(?:(\xff\xd8\xff)|(GIF8[79]a)|(\x89PNG\x0d\x0a)|(BM)|(\x49\x49(?:\x2a\x00|\x00\x4a))|(FORM.{4}ILBM))/',
            $binary, $hits
        )
        ) {
            return 'application/octet-stream';
        }
        static $type = array (
            1 => 'image/jpeg',
            2 => 'image/gif',
            3 => 'image/png',
            4 => 'image/x-windows-bmp',
            5 => 'image/tiff',
            6 => 'image/x-ilbm',
        );
        return $type[count($hits) - 1];
    }

    /**
     * Получение связанного ресурса
     * @return modResource|null
     */
    public function getResource()
    {
        if ($this->get('class') === 'modResource' && $this->get('parent')) {
            return $this->modx->getObject('modResource', $this->get('parent'));
        }
        return null;
    }

    /**
     * Получение связанного пользователя
     * @return modUser|null
     */
    public function getUser()
    {
        if ($this->get('class') === 'modUser' && $this->get('parent')) {
            return $this->modx->getObject('modUser', $this->get('parent'));
        }
        return null;
    }

    /**
     * Получение массива данных для API
     * @return array
     */
    public function toArray($keyPrefix = '', $rawValues = false, $excludeLazy = false, $includeRelated = false)
    {
        $array = parent::toArray($keyPrefix, $rawValues, $excludeLazy, $includeRelated);
        
        // Добавляем вычисляемые поля
        $array['format_size'] = $this->get('format_size');
        $array['format_createdon'] = $this->get('format_createdon');
        $array['thumbnail_url'] = $this->get('thumbnail_url');
        $array['full_url'] = $this->get('full_url');
        $array['is_image'] = $this->isImage();
        
        // Добавляем информацию о связанных объектах
        if ($resource = $this->getResource()) {
            $array['resource_pagetitle'] = $resource->get('pagetitle');
        }
        
        if ($user = $this->getUser()) {
            $array['user_username'] = $user->get('username');
        }
        
        return $array;
    }
}

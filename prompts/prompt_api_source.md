# API для файлов MODX с использованием MediaSource

Данный API основан на анализе процессоров MODX для работы с файлами и директориями. API использует MediaSource MODX для доступа к файловой системе.

## Основные концепции

### MediaSource

MediaSource - это абстракция для работы с файлами в MODX. Он предоставляет единый интерфейс для работы с различными источниками файлов (локальная файловая система, S3, FTP и т.д.).

```php
// Получение MediaSource
$this->modx->loadClass('sources.modMediaSource');
$source = modMediaSource::getDefaultSource($this->modx, $sourceId);
$source->setRequestProperties($properties);
$source->initialize();
```

### Проверка прав доступа

Перед выполнением операций с файлами необходимо проверить права доступа:

```php
// Проверка прав на чтение
if (!$source->checkPolicy('list')) {
    return $this->failure($this->modx->lexicon('permission_denied'));
}

// Проверка прав на создание
if (!$source->checkPolicy('create')) {
    return $this->failure($this->modx->lexicon('permission_denied'));
}

// Проверка прав на удаление
if (!$source->checkPolicy('remove')) {
    return $this->failure($this->modx->lexicon('permission_denied'));
}

// Проверка прав на сохранение/обновление
if (!$source->checkPolicy('save')) {
    return $this->failure($this->modx->lexicon('permission_denied'));
}
```

## API для работы с директориями

### Получение списка файлов и директорий

```php
/**
 * Получение списка файлов и директорий
 * 
 * @param string $dir Путь к директории
 * @return array Список файлов и директорий
 */
public function getContainerList($dir) {
    $source->setRequestProperties(['dir' => $dir]);
    return $source->getContainerList($dir);
}
```

### Создание директории

```php
/**
 * Создание директории
 * 
 * @param string $name Имя новой директории
 * @param string $parent Родительская директория
 * @return boolean Результат операции
 */
public function createContainer($name, $parent = '') {
    $name = ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($name))),'/');
    $parent = ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($parent))),'/');
    return $source->createContainer($name, $parent);
}
```

### Удаление директории

```php
/**
 * Удаление директории
 * 
 * @param string $dir Путь к директории
 * @return boolean Результат операции
 */
public function removeContainer($dir) {
    $dir = preg_replace('/[\.]{2,}/', '', htmlspecialchars($dir));
    return $source->removeContainer($dir);
}
```

### Переименование директории

```php
/**
 * Переименование директории
 * 
 * @param string $path Путь к директории
 * @param string $name Новое имя директории
 * @return boolean Результат операции
 */
public function renameContainer($path, $name) {
    $path = preg_replace('/[\.]{2,}/', '', htmlspecialchars($path));
    $name = preg_replace('/[\.]{2,}/', '', htmlspecialchars($name));
    return $source->renameContainer($path, $name);
}
```

## API для работы с файлами

### Загрузка файлов

```php
/**
 * Загрузка файлов
 * 
 * @param string $path Путь для загрузки
 * @param array $files Массив файлов ($_FILES)
 * @return boolean Результат операции
 */
public function uploadFiles($path, $files) {
    $path = preg_replace('/[\.]{2,}/', '', htmlspecialchars($path));
    return $source->uploadObjectsToContainer($path, $files);
}
```

### Получение содержимого файла

```php
/**
 * Получение содержимого файла
 * 
 * @param string $file Путь к файлу
 * @return array Массив с содержимым и информацией о файле
 */
public function getFileContents($file) {
    $file = preg_replace('/[\.]{2,}/', '', htmlspecialchars($file));
    return $source->getObjectContents($file);
}
```

### Обновление содержимого файла

```php
/**
 * Обновление содержимого файла
 * 
 * @param string $file Путь к файлу
 * @param string $content Новое содержимое файла
 * @return string|boolean Путь к файлу или false в случае ошибки
 */
public function updateFile($file, $content) {
    $file = preg_replace('/[\.]{2,}/', '', htmlspecialchars($file));
    return $source->updateObject($file, $content);
}
```

### Удаление файла

```php
/**
 * Удаление файла
 * 
 * @param string $file Путь к файлу
 * @return boolean Результат операции
 */
public function removeFile($file) {
    $file = preg_replace('/[\.]{2,}/', '', $file);
    return $source->removeObject($file);
}
```

### Переименование файла

```php
/**
 * Переименование файла
 * 
 * @param string $oldFile Путь к файлу
 * @param string $name Новое имя файла
 * @return boolean Результат операции
 */
public function renameFile($oldFile, $name) {
    $oldFile = preg_replace('/[\.]{2,}/', '', htmlspecialchars($oldFile));
    $name = preg_replace('/[\.]{2,}/', '', htmlspecialchars($name));
    return $source->renameObject($oldFile, $name);
}
```

### Создание файла

```php
/**
 * Создание файла
 * 
 * @param string $directory Директория для создания файла
 * @param string $name Имя файла
 * @param string $content Содержимое файла
 * @return string|boolean Путь к файлу или false в случае ошибки
 */
public function createFile($directory, $name, $content = '') {
    $directory = ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($directory))),'/');
    $name = ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($name))),'/');
    return $source->createObject($directory, $name, $content);
}
```

## Обработка ошибок

После выполнения операций с файлами необходимо проверить наличие ошибок:

```php
if (empty($success)) {
    $errors = $source->getErrors();
    $msg = implode("\n", $errors);
    return $this->failure($msg);
}
```

## Пример использования API

```php
// Получение MediaSource
$this->modx->loadClass('sources.modMediaSource');
$source = modMediaSource::getDefaultSource($this->modx, 1); // 1 - ID источника по умолчанию
$source->setRequestProperties([]);
$source->initialize();

// Проверка прав доступа
if (!$source->checkPolicy('list')) {
    return $this->failure('Доступ запрещен');
}

// Получение списка файлов
$files = $source->getContainerList('/');

// Создание директории
if ($source->checkPolicy('create')) {
    $success = $source->createContainer('new_folder', '/');
    if (!$success) {
        $errors = $source->getErrors();
        // Обработка ошибок
    }
}

// Создание файла
if ($source->checkPolicy('create')) {
    $path = $source->createObject('/', 'new_file.txt', 'Содержимое файла');
    if (empty($path)) {
        $errors = $source->getErrors();
        // Обработка ошибок
    }
}

// Получение содержимого файла
if ($source->checkPolicy('list')) {
    $fileArray = $source->getObjectContents('/new_file.txt');
    if (empty($fileArray)) {
        $errors = $source->getErrors();
        // Обработка ошибок
    } else {
        $content = $fileArray['content'];
    }
}
```

## Безопасность

При работе с путями файлов необходимо всегда выполнять санитизацию входных данных:

```php
// Санитизация пути
$path = preg_replace('/[\.]{2,}/', '', htmlspecialchars($path));

// Санитизация имени файла/директории
$name = ltrim(strip_tags(preg_replace('/[\.]{2,}/', '', htmlspecialchars($name))),'/');
```

Это предотвращает атаки типа path traversal и другие уязвимости, связанные с манипуляцией путями файлов.

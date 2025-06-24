# Формат API для работы с файлами и директориями в MODX

## Общая информация

Вместо стандартного API MODX рекомендуется использовать gtsAPI для работы с файлами и директориями. gtsAPI предоставляет более простой и удобный интерфейс для выполнения операций с файловой системой.

## Базовый URL

```
/api/files
```

## Заголовки запросов

```
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

## Аутентификация

Аутентификация осуществляется через куки сессии MODX. Необходимо включить опцию `withCredentials: true` при настройке HTTP-клиента.

## Методы API

### Получение списка файлов в директории

**Запрос:**
```
GET /api/files?path=/path/to/directory&source=1
```

**Параметры:**
- `path` - путь к директории (относительно корня источника медиа)
- `source` - ID источника медиа (по умолчанию 1)

**Ответ:**
```json
{
  "success": true,
  "data": {
    "files": [
      {
        "name": "file1.jpg",
        "path": "/path/to/directory/file1.jpg",
        "size": 12345,
        "lastmod": "2023-06-21T10:30:00",
        "type": "image/jpeg",
        "is_dir": false,
        "is_readable": true,
        "is_writable": true
      },
      // ...
    ],
    "directories": [
      {
        "name": "subdirectory",
        "path": "/path/to/directory/subdirectory",
        "lastmod": "2023-06-20T15:45:00",
        "is_dir": true,
        "is_readable": true,
        "is_writable": true
      },
      // ...
    ]
  }
}
```

### Создание директории

**Запрос:**
```
POST /api/files
```

**Тело запроса:**
```json
{
  "action": "directory",
  "path": "/path/to/parent",
  "name": "new_directory",
  "source": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Директория успешно создана",
  "data": {
    "path": "/path/to/parent/new_directory",
    "name": "new_directory",
    "is_dir": true
  }
}
```

### Загрузка файла

**Запрос:**
```
POST /api/files
```

**Тело запроса (FormData):**
- `action` - значение "upload"
- `file` - файл для загрузки
- `path` - путь к директории для загрузки
- `source` - ID источника медиа

**Ответ:**
```json
{
  "success": true,
  "message": "Файл успешно загружен",
  "data": {
    "name": "uploaded_file.jpg",
    "path": "/path/to/directory/uploaded_file.jpg",
    "size": 54321,
    "type": "image/jpeg"
  }
}
```

### Переименование файла или директории

**Запрос:**
```
POST /api/files
```

**Тело запроса:**
```json
{
  "action": "rename",
  "path": "/path/to/file.jpg",
  "newName": "new_name.jpg",
  "source": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Файл успешно переименован",
  "data": {
    "path": "/path/to/new_name.jpg",
    "name": "new_name.jpg"
  }
}
```

### Удаление файла или директории

**Запрос:**
```
POST /api/files
```

**Тело запроса:**
```json
{
  "action": "remove",
  "path": "/path/to/file_or_directory",
  "source": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Файл успешно удален"
}
```

### Получение содержимого файла

**Запрос:**
```
GET /api/files?action=content&path=/path/to/file.txt&source=1
```

**Параметры:**
- `path` - путь к файлу
- `source` - ID источника медиа

**Ответ:**
```json
{
  "success": true,
  "data": {
    "content": "Содержимое файла...",
    "name": "file.txt",
    "path": "/path/to/file.txt",
    "size": 1234,
    "mime": "text/plain"
  }
}
```

### Обновление содержимого файла

**Запрос:**
```
POST /api/files
```

**Тело запроса:**
```json
{
  "action": "update_content",
  "path": "/path/to/file.txt",
  "content": "Новое содержимое файла",
  "source": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Файл успешно обновлен",
  "data": {
    "path": "/path/to/file.txt",
    "name": "file.txt",
    "size": 1234
  }
}
```

### Создание файла

**Запрос:**
```
POST /api/files
```

**Тело запроса:**
```json
{
  "action": "create_file",
  "path": "/path/to/directory",
  "name": "newfile.txt",
  "content": "Содержимое нового файла",
  "source": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Файл успешно создан",
  "data": {
    "path": "/path/to/directory/newfile.txt",
    "name": "newfile.txt",
    "size": 1234
  }
}
```

### Скачивание файла

**Запрос:**
```
GET /api/files?action=download&path=/path/to/file.jpg&source=1
```

**Параметры:**
- `path` - путь к файлу
- `source` - ID источника медиа

**Ответ:**
Содержимое файла с соответствующими заголовками для скачивания.

### Получение списка доступных медиа-источников

**Запрос:**
```
GET /api/files?action=source_list
```

**Параметры:**
- Нет обязательных параметров

**Ответ:**
```json
{
  "success": true,
  "data": {
    "sources": [
      {
        "id": 1,
        "name": "Название источника 1"
      },
      {
        "id": 2,
        "name": "Название источника 2"
      },
      // ...
    ]
  }
}
```

Этот метод возвращает список всех доступных медиа-источников, к которым у текущего пользователя есть права доступа (проверяется право "list"). Метод полезен для создания интерфейсов выбора медиа-источника перед выполнением операций с файлами.

## Обработка ошибок

В случае ошибки API возвращает ответ со статусом `success: false` и сообщением об ошибке:

```json
{
  "success": false,
  "message": "Описание ошибки",
  "code": 403
}
```

## Примеры использования с axios

```javascript
import axios from 'axios';

// Создание экземпляра axios с базовыми настройками
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true
});

// Получение списка файлов
async function getFiles(path, source = 1) {
  try {
    const response = await api.get('/files', {
      params: { path, source }
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при получении списка файлов:', error);
    throw error;
  }
}

// Создание директории
async function createDirectory(path, name, source = 1) {
  try {
    const response = await api.post('/files', {
      action: 'directory',
      path,
      name,
      source
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при создании директории:', error);
    throw error;
  }
}

// Загрузка файла
async function uploadFile(file, path, source = 1) {
  try {
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('file', file);
    formData.append('path', path);
    formData.append('source', source);
    
    const response = await api.post('/files', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при загрузке файла:', error);
    throw error;
  }
}

// Получение содержимого файла
async function getFileContent(path, source = 1) {
  try {
    const response = await api.get('/files', {
      params: { action: 'content', path, source }
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при получении содержимого файла:', error);
    throw error;
  }
}

// Обновление содержимого файла
async function updateFileContent(path, content, source = 1) {
  try {
    const response = await api.post('/files', {
      action: 'update_content',
      path,
      content,
      source
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при обновлении содержимого файла:', error);
    throw error;
  }
}

// Создание файла
async function createFile(path, name, content = '', source = 1) {
  try {
    const response = await api.post('/files', {
      action: 'create_file',
      path,
      name,
      content,
      source
    });
    return response.data;
  } catch (error) {
    console.error('Ошибка при создании файла:', error);
    throw error;
  }
}

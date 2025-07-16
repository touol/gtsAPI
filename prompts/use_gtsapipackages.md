# Использование gtsAPIPackages - Общее руководство

## Описание

Данный файл содержит общее описание использования конфигурации gtsAPIPackages для создания API таблиц в системе gtsAPI.

## Структура конфигурации

```javascript
export default {
    packagename: {
        name: 'packagename', // имя пакета MODX
        gtsAPITables: {
            tablename: {
                table: 'tablename', // Название таблицы
                class: 'ClassName', // Класс MODX таблицы базы данных (если отличается от table)
                autocomplete_field: 'field_name', // Поле для автокомплита
                version: 1, // Версия конфигурации
                type: 1, // Тип таблицы: 1 - PVTables, 2 - JSON, 3 - UniTree
                authenticated: true, // Требуется аутентификация
                groups: 'Administrator,manager', // Группы пользователей с доступом
                permissions: '', // Разрешения MODX
                active: true, // Активность таблицы
                properties: {
                    // Конфигурация свойств таблицы
                }
            }
        }
    }
}
```

## Основные параметры таблицы

### Обязательные параметры
- `table` - название таблицы в базе данных
- `version` - версия конфигурации (увеличивается при изменениях)
- `type` - тип таблицы (1, 2 или 3)
- `active` - активность таблицы

### Опциональные параметры
- `class` - класс MODX (если отличается от названия таблицы)
- `autocomplete_field` - поле для автокомплита
- `authenticated` - требование аутентификации
- `groups` - группы пользователей с доступом
- `permissions` - разрешения MODX

## Типы таблиц

1. **type: 1** - Обычные таблицы PVTables
2. **type: 2** - JSON таблицы
3. **type: 3** - Деревья UniTree

## Конфигурация properties

### Hide ID
```javascript
properties: {
    hide_id: 1, // Скрывает поле ID в интерфейсе таблицы
    // остальные настройки...
}
```

Параметр `hide_id` используется для скрытия поля ID в интерфейсе таблицы. Полезно когда:
- ID не имеет смысла для пользователя
- При отображении агрегированных данных
- При группировке записей по полям
- Когда ID записи не относится к отображаемой строке целиком

### Actions (Действия)
```javascript
actions: {
    read: {}, // Чтение
    create: { groups: 'Administrator' }, // Создание
    update: { groups: 'Administrator' }, // Обновление
    delete: { groups: 'Administrator' }, // Удаление
    
    // Кастомные действия
    raschet_row: {
        action: 'gtsshop/raschet_row', // Путь к методу в сервисном файле
        row: true, // Действие применяется к строке
        icon: "pi pi-calculator", // Иконка для кнопки
        groups: 'Administrator' // Группы доступа (опционально)
    }
}
```

#### Кастомные действия

Кастомные действия позволяют добавлять специальные операции, которые обрабатываются в сервисном файле пакета (например, `gtsshop.class.php`).

**Структура кастомного действия:**
- `action` - путь к методу в формате `package/method_name`
- `row` - если `true`, действие применяется к выбранной строке
- `icon` - CSS класс иконки для кнопки
- `groups` - группы пользователей с доступом к действию

**Пример реализации в сервисном файле:**
```php
public function raschet_row($data = array())
{
    // Получение ID строки из $data['id']
    if(!$gsRaschetProduct = $this->modx->getObject("gsRaschetProduct", (int)$data['id'])) {
        return $this->error('Не найдена строка расчета!');
    }
    
    // Выполнение расчетов
    // ... логика обработки ...
    
    return $this->success('Расчет выполнен!', ['refresh_row' => 1]);
}

public function handleRequest($action, $data = array())
{
    switch($action) {
        case 'raschet_row':
            return $this->raschet_row($data);
        break;
        // другие действия...
    }
}
```

**Методы ответа:**
- `$this->success($message, $data)` - успешный ответ
- `$this->error($message, $data)` - ответ с ошибкой

**Специальные параметры ответа:**
- `refresh_row` - обновить строку в таблице
- `refresh_table` - обновить всю таблицу
- `reload_with_id` - перезагрузить с новым ID

### Query (Запросы)
```javascript
query: {
    leftJoin: {
        TableName: {
            class: 'ClassName',
            on: 'TableName.id = MainTable.foreign_id'
        }
    },
    where: {
        'field': 'value'
    },
    select: {
        MainTable: '*',
        TableName: 'TableName.field1, TableName.field2'
    },
    sortby: {
        'field': 'ASC'
    }
}
```

### Autocomplete
```javascript
autocomplete: {
    tpl: '{$name}', // Шаблон отображения
    where: {
        "name:LIKE": "%query%"
    },
    limit: 0 // Лимит записей
}
```

### Fields (Поля)
```javascript
fields: {
    "field_name": {
        "label": "Подпись поля",
        "type": "text", // Тип поля
        "readonly": true, // Только для чтения
        "modal_only": true, // Только в модальном окне
        "table_only": true // Только в таблице
    }
}
```

## Типы полей

- `text` - текстовое поле
- `textarea` - многострочное текстовое поле
- `number` - числовое поле
- `decimal` - десятичное число
- `date` - дата
- `boolean` - логическое поле
- `autocomplete` - автокомплит
- `view` - только просмотр
- `hidden` - скрытое поле
- `html` - HTML контент

## Пример простой конфигурации

```javascript
export default {
    mypackage: {
        name: 'mypackage',
        gtsAPITables: {
            users: {
                table: 'users',
                version: 1,
                type: 1,
                authenticated: true,
                groups: 'Administrator',
                active: true,
                properties: {
                    autocomplete: {
                        tpl: '{$name}',
                        where: {
                            "name:LIKE": "%query%"
                        },
                        limit: 0
                    },
                    actions: {
                        read: {},
                        create: { groups: 'Administrator' },
                        update: { groups: 'Administrator' },
                        delete: { groups: 'Administrator' }
                    },
                    fields: {
                        "id": {
                            "type": "view"
                        },
                        "name": {
                            "label": "Имя",
                            "type": "text"
                        },
                        "email": {
                            "label": "Email",
                            "type": "text"
                        },
                        "active": {
                            "label": "Активен",
                            "type": "boolean"
                        }
                    }
                }
            }
        }
    }
}
```

## Специальные возможности

### Для группированных данных
Если вам нужны таблицы с группировкой данных (GROUP BY), обратитесь к файлу `use_group_gtsapipackages.md` для получения подробной информации о параметре `data_fields`.

### Для деревьев UniTree
При использовании `type: 3` доступны дополнительные настройки для работы с иерархическими структурами.

## Рекомендации

1. **Всегда увеличивайте версию** при изменении конфигурации
2. **Используйте осмысленные названия** для таблиц и полей
3. **Настраивайте права доступа** через groups и permissions
4. **Тестируйте конфигурацию** после каждого изменения
5. **Документируйте изменения** в комментариях

## Совместимость

Конфигурация gtsAPIPackages полностью совместима с системой MODX и поддерживает все стандартные типы полей и операции.

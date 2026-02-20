# gtsAPI — REST API фреймворк для MODX

## Роль

Ядро gtsERP. Предоставляет REST API для всех компонентов системы. Перехватывает запросы к `/api/*` и маршрутизирует к нужному контроллеру.

## Архитектура

### Маршрутизация

```
/api/{tableName}/{id} → Plugin (OnHandleRequest) → gtsAPI::route() → Controller
```

1. Plugin `elements/plugins/gtsapi.php` перехватывает `/api/*`
2. `gtsAPI::route()` ищет `gtsAPITable` или `gtsAPIRule` по имени
3. Выбирает контроллер по типу таблицы

### Контроллеры (`core/components/gtsapi/api_controllers/`)

| Контроллер | Тип | Назначение |
|-----------|-----|-----------|
| `tableAPIController` | 1 | Стандартные таблицы (CRUD) |
| `jsonTableAPIController` | 2 | JSON-таблицы (данные в JSON-поле) |
| `treeAPIController` | 3 | Иерархические структуры |
| `defaultAPIController` | — | Кастомные endpoints (gtsAPIRule) |
| `packageAPIController` | — | Управление пакетами (деплой) |

### Трейты (`tableAPIController`)

- `TableCrudTrait` — CREATE, READ, UPDATE, DELETE
- `TableFieldsTrait` — управление полями
- `TableAutocompleteTrait` — автодополнение
- `TableFilterTrait` — фильтрация и поиск
- `TableExportTrait` — экспорт, печать
- `TableTriggerTrait` — триггеры событий
- `TableTreeTrait` — древовидные структуры
- `TableUtilsTrait` — утилиты

## Ключевые файлы

- `core/components/gtsapi/model/gtsapi.class.php` — главный класс
- `core/components/gtsapi/api_controllers/` — контроллеры
- `elements/plugins/gtsapi.php` — MODX plugin
- `assets/components/gtsapi/action.php` — фронтенд action handler
- `assets/components/gtsapi/js/web/pvtables/pvtables.js` — собранный PVTables

## Модели xPDO

- `gtsAPITable` — определения таблиц API
- `gtsAPIRule` — кастомные API endpoints
- `gtsAPIAction` — действия для rules
- `gtsAPIPackage` — пакеты компонентов
- `gtsAPIField` — определения полей
- `gtsAPIToken` — JWT токены
- `gtsAPIFile` — файлы
- `gtsAPISelect` — варианты выбора

## Аутентификация

- JWT через `Authorization: Bearer {token}`
- `gtsAPI::auth_from_token()` — валидация
- Настройка доступа: `authenticated`, `groups`, `permissions` в gtsAPITable

## REST API

```
GET    /api/{table}        → read (список)
GET    /api/{table}/{id}   → get (одна запись)
PUT    /api/{table}        → create
POST   /api/{table}/{id}   → update
DELETE /api/{table}        → delete
```

## Документация

- `core/components/gtsapi/docs/gtsapi_rule_trigger.md` — триггеры
- `core/components/gtsapi/docs/vue_ssr_guide.md` — SSR
- `core/components/gtsapi/docs/gtsAPIGallery_guide.md` — галерея
- `prompts/use_gtsapipackages.md` — гайд по gtsapipackages
- `prompts/use_group_gtsapipackages.md` — группировка данных

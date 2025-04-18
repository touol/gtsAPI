# Описание компонента gtsAPI

## Общее описание

gtsAPI - это компонент для MODX, предназначенный для создания унифицированного API для работы с данными из различных компонентов. Он обеспечивает единый интерфейс для доступа к данным, управления правами доступа и обработки запросов от фронтенда.

## Основные функции

1. **Унифицированный доступ к данным** - Предоставляет единый интерфейс для доступа к данным из различных компонентов.
2. **Управление правами доступа** - Обеспечивает гибкую систему прав доступа для различных пользователей и групп.
3. **Обработка запросов от фронтенда** - Обрабатывает запросы от фронтенда и перенаправляет их соответствующим компонентам.
4. **Интеграция с MODX** - Интегрируется с MODX для аутентификации и авторизации.
5. **Триггеры для обработки событий** - Предоставляет систему триггеров для обработки событий.

## Роль в ERP системе

gtsAPI является ключевым компонентом ERP системы, обеспечивая взаимодействие между различными компонентами и фронтендом. Он используется для:

1. **Доступа к данным** - Обеспечивает доступ к данным из различных компонентов через единый интерфейс.
2. **Управления правами доступа** - Обеспечивает гибкую систему прав доступа для различных пользователей и групп.
3. **Обработки запросов** - Обрабатывает запросы от фронтенда и перенаправляет их соответствующим компонентам.
4. **Интеграции с другими системами** - Обеспечивает интеграцию с другими системами через API.

## Технический стек

- **PHP** - Основной язык программирования
- **MODX** - CMS, на которой построен компонент
- **PDO** - Интерфейс для работы с базой данных
- **JSON** - Формат данных для обмена информацией

## Взаимодействие с другими компонентами

gtsAPI взаимодействует с другими компонентами ERP системы:

1. **OrgStructure** - Предоставляет API для работы с организационной структурой.
2. **PVTables** - Обеспечивает данные для отображения в табличном виде. Подробнее об интеграции с PVTables можно прочитать в [pvtables-integration.md](pvtables-integration.md).
3. **gtsShop** - Предоставляет API для работы с магазином и вариантами товаров.
4. **gtsSync** - Обеспечивает синхронизацию данных между различными системами.
5. **getTables** - Предоставляет API для работы с таблицами базы данных.

## Приоритеты разработки

1. **Производительность** - Оптимизация производительности при работе с большими объемами данных.
2. **Безопасность** - Обеспечение безопасности данных и защиты от несанкционированного доступа.
3. **Расширяемость** - Обеспечение возможности расширения функциональности компонента.
4. **Документирование API** - Создание подробной документации для облегчения интеграции с другими компонентами.
5. **Тестирование и отладка** - Тестирование и отладка компонента для выявления и исправления возможных ошибок.

# Тесты gtsAPI

| Файл | Что проверяет | Запуск |
|---|---|---|
| `php/audit_handle_request.php` | статический аудит: какие `case` в `handleRequest` компонентов `Extras/*` не проверяют права (правило — `Extras/CLAUDE.md`), с числом вызовов из сводки `gtsapi_undeclared_actions.json`. Без MODX, только чтение файлов | из `Extras/`: `php gtsAPI/tests/php/audit_handle_request.php [Extras-dir] [undeclared.json] > out.md` |
| `e2e/default_limit.mjs` | Playwright: размер страницы таблиц по умолчанию | см. шапку файла |

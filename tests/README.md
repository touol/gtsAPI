# Тесты gtsAPI

| Файл | Что проверяет | Запуск |
|---|---|---|
| `php/audit_handle_request.php` | статический аудит: какие `case` в `handleRequest` компонентов `Extras/*` не проверяют права (правило — `Extras/CLAUDE.md`), с числом вызовов из сводки `gtsapi_undeclared_actions.json`. Без MODX, только чтение файлов | из `Extras/`: `php gtsAPI/tests/php/audit_handle_request.php [Extras-dir] [undeclared.json] > out.md` |
| `php/admin_only_actions.php` | действия настройки gtsAPI (export_/save_*, gen_*, clean_fields) — только админу; не-админ получает отказ, конфиг не меняется | `php tests/php/admin_only_actions.php [логин]`, `MODX_ROOT` — корень сайта (по умолчанию дев vk-24) |
| `php/package_calls.php` | 'пакет/метод' через таблицу: свой пакет — ok, чужой — `foreign`, пара в `gtsAPIPackageCall` — ok в обе стороны; резолвер `api_call_packages`; сквозь контроллер — журнал `gtsapi_foreign_calls.json` | `php tests/php/package_calls.php`, `MODX_ROOT` |
| `e2e/default_limit.mjs` | Playwright: размер страницы таблиц по умолчанию | см. шапку файла |

<?php
/**
 * Аудит: какие методы компонентов, вызываемые через api_action 'пакет/метод', не проверяют права.
 *
 * Правило (Extras/CLAUDE.md): каждый case в handleRequest начинается с проверки прав, потому что
 * метод доступен через любую таблицу, на которую у пользователя есть доступ.
 *
 * Статический разбор, без MODX: для каждого *.class.php с handleRequest берёт case'ы switch,
 * ищет проверку в теле case, в начале handleRequest (до switch) и в первых строках вызываемого метода.
 * Результат — эвристика, каждую находку перед правкой смотреть глазами.
 *
 * Запуск:
 *   php gtsAPI/tests/php/audit_handle_request.php [Extras-dir] [undeclared.json] > out.md
 *   по умолчанию Extras — родитель gtsAPI, json — Extras/планы/gtsapi_undeclared_actions.json
 * Формат вывода — markdown-таблица по пакетам.
 */

$extras = $argv[1] ?? realpath(__DIR__ . '/../../..');
$jsonFile = $argv[2] ?? $extras . '/планы/gtsapi_undeclared_actions.json';

// Что считаем проверкой прав. isAuthenticated / user->id — только «залогинен», не права.
$GUARD = '/->is(?:Member|Admin|Boss|Runner|Manager|Allowed)\w*\s*\(|->hasPermission\s*\(|checkPermissions\s*\(|->sudo\b|'
       . '\$this->\w*(?:[Aa]ccess|[Pp]erm|[Rr]ight|[Aa]llow|[Gg]uard|[Cc]an)\w*\s*\(/';
$LOGIN = '/isAuthenticated\s*\(|->user->id\s*(?:==|<|>|!=)|empty\(\s*\$this->modx->user->id/';

$used = [];
if (is_file($jsonFile)) {
    foreach ((array)json_decode(file_get_contents($jsonFile), true) as $row) {
        [$pkg, $m] = array_pad(explode('/', $row['action'], 2), 2, '');
        $k = strtolower($pkg) . '/' . $m;
        $used[$k] = ($used[$k] ?? 0) + (int)$row['count'];
    }
}

/** Тело блока {...}, начиная с позиции открывающей скобки. Строки и комментарии грубо пропускаем. */
function block($src, $open) {
    $d = 0; $n = strlen($src);
    for ($i = $open; $i < $n; $i++) {
        $c = $src[$i];
        if ($c === '/' && ($src[$i + 1] ?? '') === '/') { $i = strpos($src, "\n", $i) ?: $n; continue; }
        if ($c === '/' && ($src[$i + 1] ?? '') === '*') { $i = (strpos($src, '*/', $i) ?: $n) + 1; continue; }
        if ($c === '"' || $c === "'") {
            for ($i++; $i < $n && $src[$i] !== $c; $i++) if ($src[$i] === '\\') $i++;
            continue;
        }
        if ($c === '{') $d++;
        if ($c === '}' && --$d === 0) return substr($src, $open + 1, $i - $open - 1);
    }
    return substr($src, $open + 1);
}

function methodBody($src, $name) {
    if (!preg_match('/function\s+' . preg_quote($name, '/') . '\s*\([^)]*\)[^{]*\{/', $src, $m, PREG_OFFSET_CAPTURE)) return null;
    return block($src, $m[0][1] + strlen($m[0][0]) - 1);
}

$files = array_merge(
    glob("$extras/*/core/components/*/model/*.class.php"),
    glob("$extras/*/core/components/*/model/*/*.class.php")
);
$out = [];
foreach ($files as $f) {
    $src = file_get_contents($f);
    $hr = methodBody($src, 'handleRequest');
    if ($hr === null) continue;
    $rel = str_replace('\\', '/', substr($f, strlen($extras) + 1));
    $pkgDir = explode('/', $rel)[0];
    $svc = strtolower(basename(dirname($f)) === 'model' ? basename($f, '.class.php') : basename($f, '.class.php'));

    $pre = ($p = strpos($hr, 'switch')) !== false ? substr($hr, 0, $p) : $hr;
    $topGuard = preg_match($GUARD, $pre) ? 'общая до switch' : '';

    if (!preg_match_all('/case\s+[\'"]([^\'"]+)[\'"]\s*:/', $hr, $cm, PREG_OFFSET_CAPTURE)) {
        $dyn = preg_match('/method_exists\s*\(\s*\$this\s*,\s*\$action/', $hr) ? 'динамический вызов $this->$action' : 'нет case';
        $out[$pkgDir][] = [$rel, '*', $topGuard ?: '—', '', $dyn];
        continue;
    }
    // тело case k: от конца «case 'x':» до начала следующего case / default
    $bodies = [];
    foreach ($cm[0] as $k => [$full, $start]) {
        $from = $start + strlen($full);
        $to = $cm[0][$k + 1][1] ?? (preg_match('/default\s*:/', $hr, $dm, PREG_OFFSET_CAPTURE, $from) ? $dm[0][1] : strlen($hr));
        $bodies[$k] = substr($hr, $from, $to - $from);
    }
    foreach ($cm[1] as $i => [$label, $pos]) {
        // сквозные case 'a': case 'b': тело — у первого непустого ниже
        for ($j = $i; trim($bodies[$j]) === '' && isset($bodies[$j + 1]); $j++);
        $body = $bodies[$j];
        $method = preg_replace('#^[^/]+/#', '', $label);
        $body = str_replace(['$this->$action(', '$this->{$action}('], '$this->' . $method . '(', $body);
        $guard = $topGuard;
        $note = '';
        if (!$guard && preg_match($GUARD, $body)) $guard = 'в case';
        if (!$guard && !preg_match('/\$\w+->(?!success\b|error\b)/', $body)) {
            $guard = 'не нужна (пустой case)';
        }
        // $this->method( или делегат $naryads->method( / $this->obj->method(
        if (!$guard && preg_match('/\$\w+->(?:\w+(?:\(\))?->)?(?!success\b|error\b|modx\b|get\b|getOption\b|getService\b|getObject\b|getTableName\b|getCollection\b|newQuery\b)(\w+)\s*\(/', $body, $mm)) {
            $mb = methodBody($src, $mm[1]);
            if ($mb === null) {
                // метод в трейте / подключаемом классе пакета
                $pkgCore = dirname($f, preg_match('#/model/[^/]+/[^/]+$#', str_replace('\\', '/', $f)) ? 3 : 2);
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pkgCore, FilesystemIterator::SKIP_DOTS));
                foreach ($it as $pf) {
                    if ($pf->getExtension() !== 'php' || $pf->getPathname() === $f) continue;
                    if (($mb = methodBody(file_get_contents($pf->getPathname()), $mm[1])) !== null) break;
                }
            }
            if ($mb !== null) {
                $head = implode("\n", array_slice(explode("\n", $mb), 0, 40));
                if (preg_match($GUARD, $head)) $guard = 'в ' . $mm[1] . '()';
            } else {
                $note = $mm[1] . '() не найден в классе (трейт/родитель?)';
            }
        }
        if (!$guard && (preg_match($LOGIN, $body) || preg_match($LOGIN, $pre))) $note = trim($note . ' только «залогинен»');
        $out[$pkgDir][] = [$rel, $method, $guard ?: '**нет**', $used["$svc/$method"] ?? '', $note, kind($method)];
    }
}

/** Грубо по имени: меняет данные или только читает. Для очерёдности, не для решения. */
function kind($m) {
    if (preg_match('/password|token|sso|login|secret/i', $m)) return 'секрет';
    if (preg_match('/^(save|set|create|update|delete|remove|toggle|move|reset|sync|recalc|clean|import|run|close|open|reopen|restore|attach|confirm|mark|add|bump|cancel|copy|undo|fix|unblock|block)/i', $m)) return 'запись';
    if (preg_match('/^(get|show|list|load|find|search|fetch_info|info|print|export|preview|features|filials)|^generate_print|_(list|table|info|count|map|data|sums|summary|live|board|positions|orders|diff|cell|matrix|pending|virtual|html|people|source)$|^(lusya|smens|smena)_(matrix|cell|pending|virtual|bottleneck\w*|staff_board|summary|list|mats|zagotovki)$/i', $m)) return 'чтение';
    return 'запись';
}

ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
$total = $open = 0;
foreach ($out as $rows) foreach ($rows as $r) { $total++; if ($r[2] === '**нет**') $open++; }
echo "Методов в handleRequest: $total, без найденной проверки: $open. Вызовов — из " . basename($jsonFile) . "\n\n";
foreach ($out as $pkg => $rows) {
    $n = count(array_filter($rows, function ($r) { return $r[2] === '**нет**'; }));
    echo "### $pkg — без проверки $n из " . count($rows) . "\n`{$rows[0][0]}`\n\n"
       . "| Метод | Тип | Проверка | Вызовов на office | Статус | Заметка |\n|---|---|---|---|---|---|\n";
    foreach ($rows as $r) {
        $status = $r[2] === '**нет**' ? 'открыт' : '';
        echo "| `{$r[1]}` | {$r[5]} | {$r[2]} | {$r[3]} | $status | {$r[4]} |\n";
    }
    echo "\n";
}

<?php
/**
 * gtsTheme — переключатель схемы и темы.
 *
 * Ставит на <html> два признака, которые читает L0 (PVTables/src/theme/gts-tokens.css):
 *     data-gts-scheme  light | dark      — солнце / луна
 *     data-gts-theme   имя темы          — выпадающее меню
 *
 * Темы — данные, а не код. Сниппет НЕ знает ни одной темы по имени: он берёт
 * их из CSS-файлов. Чтобы добавить свою, достаточно положить файл в папку тем
 * или передать его в &themeFiles. Пересобирать PVTables не нужно.
 *
 * Выбор хранится в localStorage у каждого пользователя. Схема всегда
 * проставляется явным значением, даже когда пользователь ничего не выбирал:
 * системную настройку разрешает ранний скрипт. Это нужно, чтобы совпадал
 * darkModeSelector PrimeVue — он ждёт признак в DOM, а не media-запрос.
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * Параметры:
 *   &themes=`1`
 *       показывать выбор темы. По умолчанию только схема светлая/тёмная
 *
 *   &themesPath=`components/gtsapi/themes/`
 *       папки с темами, через запятую, относительно assets. Каждый *.css
 *       в них — тема, имя = имя файла. Пусто — папка не сканируется
 *
 *   &themeFiles=`components/mypkg/css/ocean.css, sand:/assets/x/s.css`
 *       явные файлы тем, через запятую. Имя берётся из имени файла либо
 *       задаётся до двоеточия. Перекрывают найденное в папках при совпадении
 *       имени — так сторонний пакет подменяет тему, ничего не удаляя
 *
 *   &themeList=`teal,forest`
 *       какие темы показать и в каком порядке. Пусто — все найденные
 *
 *   &defaultTheme=`corporate`     пусто — из настройки gtsapi_theme_default
 *   &defaultScheme=`light`        auto | light | dark, пусто — gtsapi_scheme_default
 *   &loadCss=`1`                  подключать CSS тем (0 — если это делает шаблон)
 *   &class=`gts-ts--ghost`        доп. классы на обёртку. gts-ts--ghost —
 *                                 прозрачные кнопки под цвет панели, на которой
 *                                 стоит виджет (тёмный navbar, цветная шапка)
 *   &storageKey=`gtsTheme`
 *   &toPlaceholder=`...`
 */

$assetsPath = $modx->getOption('assets_path');
$assetsUrl  = $modx->getOption('assets_url');

$showThemes = !empty($themes) && !in_array($themes, ['0', 'false', 'no'], true);
$loadCss    = !isset($loadCss) || !in_array($loadCss, [0, '0', 'false', false, 'no'], true);
$storageKey = !empty($storageKey) ? (string)$storageKey : 'gtsTheme';

$defaultScheme = !empty($defaultScheme)
    ? (string)$defaultScheme
    : $modx->getOption('gtsapi_scheme_default', null, 'light');
if (!in_array($defaultScheme, ['auto', 'light', 'dark'], true)) {
    $defaultScheme = 'auto';
}

$defaultTheme = !empty($defaultTheme)
    ? (string)$defaultTheme
    : $modx->getOption('gtsapi_theme_default', null, 'corporate');

/**
 * Разбирает список, разделённый запятыми, в массив непустых значений.
 */
$splitList = function ($raw) {
    if (empty($raw)) {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', (string)$raw)), 'strlen'));
};

/* ---------------------------------------------------------------------------
   1. Собираем реестр тем: имя => ['path' => абсолютный путь, 'url' => адрес].
      Сначала папки, затем явные файлы — поэтому &themeFiles перекрывает
      одноимённую тему из папки, а не дублирует её.
   ------------------------------------------------------------------------ */
$registry = [];

$dirs = $splitList(isset($themesPath) ? $themesPath : 'components/gtsapi/themes/');
foreach ($dirs as $dir) {
    $dir = trim($dir, '/') . '/';
    $abs = $assetsPath . $dir;
    if (!is_dir($abs)) {
        $modx->log(modX::LOG_LEVEL_WARN, '[gtsTheme] папка тем не найдена: ' . $abs);
        continue;
    }
    foreach ((array)glob($abs . '*.css') as $file) {
        $name = basename($file, '.css');
        $registry[$name] = ['path' => $file, 'url' => $assetsUrl . $dir . basename($file)];
    }
}

foreach ($splitList(isset($themeFiles) ? $themeFiles : '') as $entry) {
    // Форма "имя:файл" или просто "файл" — тогда имя берётся из имени файла.
    // Двоеточие в схеме (http://) не должно приниматься за разделитель имени.
    $name = '';
    $file = $entry;
    if (preg_match('~^([A-Za-z0-9_-]+):(?!//)(.+)$~', $entry, $m)) {
        $name = $m[1];
        $file = trim($m[2]);
    }

    $isExternal = (bool)preg_match('~^(https?:)?//~', $file);
    if ($isExternal) {
        $url  = $file;
        $path = '';
    } elseif (strpos($file, '/') === 0) {
        // от корня сайта
        $url  = $file;
        $path = rtrim($modx->getOption('base_path'), '/') . $file;
    } else {
        $url  = $assetsUrl . ltrim($file, '/');
        $path = $assetsPath . ltrim($file, '/');
    }

    if ($name === '') {
        $name = basename(parse_url($url, PHP_URL_PATH), '.css');
    }

    if ($path !== '' && !is_file($path)) {
        $modx->log(modX::LOG_LEVEL_WARN, '[gtsTheme] файл темы не найден: ' . $path);
        continue;
    }

    $registry[$name] = ['path' => $path, 'url' => $url];
}

/* ---------------------------------------------------------------------------
   2. Какие темы показываем и в каком порядке.
   ------------------------------------------------------------------------ */
$wanted = $splitList(isset($themeList) ? $themeList : '');
if (empty($wanted)) {
    $wanted = array_keys($registry);
    sort($wanted);
}

$list = [];
foreach ($wanted as $name) {
    if (isset($registry[$name])) {
        $list[] = $name;
    } else {
        // Молча это дало бы тему без CSS: атрибут проставится, блока под него
        // нет, и пользователь увидит нейтральную вместо выбранной.
        $modx->log(modX::LOG_LEVEL_WARN, '[gtsTheme] тема "' . $name . '" не найдена — пропущена');
    }
}

if (empty($list) && !empty($registry)) {
    $list = array_keys($registry);
    sort($list);
}

if (!in_array($defaultTheme, $list, true)) {
    if (!empty($list)) {
        $modx->log(modX::LOG_LEVEL_WARN,
            '[gtsTheme] тема по умолчанию "' . $defaultTheme . '" недоступна, взята "' . $list[0] . '"');
        $defaultTheme = $list[0];
    } else {
        // Тем нет вообще — работает нейтральная из gts-tokens.css.
        // Не ошибка: PVTables и в одиночку выглядит прилично.
        $defaultTheme = '';
    }
}

/* ---------------------------------------------------------------------------
   3. Подключаем CSS.

      Сначала контракт токенов (§1–§3 из PVTables/src/theme/gts-tokens.css,
      копия кладётся сборкой). Он и так есть внутри pvtables.css, но тот
      подключается только на страницах с Vue-приложением. На обычных страницах
      сайта PVTables нет, а переключатель есть — и без контракта файлы тем
      бесполезны: они объявляют приватные пары --_x-l/--_x-d, а собрать из них
      публичные --gts-* некому.

      Повторное объявление на Vue-страницах безвредно: значения те же, а файл
      один и тот же, браузер возьмёт его из кеша.

      Затем все предлагаемые темы, а не только текущая: переключение происходит
      на клиенте без перезагрузки, и файл нужной темы должен уже лежать в
      странице. Когда меню скрыто — только тема по умолчанию.
   ------------------------------------------------------------------------ */
if ($loadCss) {
    $contract = $assetsPath . 'components/gtsapi/css/web/gts-tokens.css';
    if (is_file($contract)) {
        $modx->regClientCSS($assetsUrl . 'components/gtsapi/css/web/gts-tokens.css?v=' . filemtime($contract));
    } else {
        $modx->log(modX::LOG_LEVEL_WARN,
            '[gtsTheme] не найден контракт токенов ' . $contract
            . ' — темы не соберутся на страницах без PVTables');
    }
}

if ($loadCss) {
    $toLoad = $showThemes ? $list : array_filter([$defaultTheme], 'strlen');
    foreach ($toLoad as $name) {
        $url = $registry[$name]['url'];
        $p   = $registry[$name]['path'];
        if ($p !== '' && is_file($p)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . filemtime($p);
        }
        $modx->regClientCSS($url);
    }
}

/* ---------------------------------------------------------------------------
   4. Подписи тем — из лексикона: gtsapi_theme_<имя>.
      Сторонний пакет добавляет свою строку в свой лексикон и не трогает наш.
      Нет строки — показываем имя файла, это лучше пустоты.
   ------------------------------------------------------------------------ */
$modx->lexicon->load('gtsapi:default');
$titles = [];
foreach ($list as $name) {
    $key   = 'gtsapi_theme_' . $name;
    $title = $modx->lexicon($key);
    $titles[$name] = ($title === $key || $title === '') ? $name : $title;
}

$cfg = json_encode([
    'key'    => $storageKey,
    'theme'  => $defaultTheme,
    'scheme' => $defaultScheme,
    'themes' => $list,
], JSON_UNESCAPED_UNICODE);

/* ---------------------------------------------------------------------------
   5. Ранний скрипт. Идёт в <head> и выполняется до первой отрисовки: иначе
      страница успевает мигнуть светлой, прежде чем применится тёмная.
      Поэтому он синхронный, без DOMContentLoaded и без внешнего файла.
   ------------------------------------------------------------------------ */
$modx->regClientStartupHTMLBlock(
'<script>
(function(){
  var c = ' . $cfg . ';
  var d = document.documentElement;
  var saved = {};
  try { saved = JSON.parse(localStorage.getItem(c.key) || "{}") || {}; } catch (e) {}

  var theme = saved.theme && c.themes.indexOf(saved.theme) > -1 ? saved.theme : c.theme;
  var pref  = saved.scheme || c.scheme;
  if (pref !== "light" && pref !== "dark") pref = "auto";

  function resolve(p) {
    if (p !== "auto") return p;
    return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  }

  if (theme) d.dataset.gtsTheme = theme;
  d.dataset.gtsScheme = resolve(pref);

  // Состояние наружу: виджет ниже и Vue-приложения читают отсюда,
  // чтобы не разбирать localStorage повторно.
  window.gtsTheme = {
    key: c.key,
    themes: c.themes,
    theme: theme,
    scheme: pref,                  // что выбрано: auto | light | dark
    resolved: d.dataset.gtsScheme, // что применилось: light | dark
    // Значения из настроек сайта — к ним возвращает «Сбросить».
    // Держим отдельно от текущих: иначе после первого же переключения
    // сбрасывать было бы не к чему.
    defaults: { theme: c.theme, scheme: c.scheme }
  };
})();
</script>'
);

/* ---------------------------------------------------------------------------
   6. Стили виджета.
   ------------------------------------------------------------------------ */
$widgetCss = $assetsPath . 'components/gtsapi/css/web/gtstheme.css';
$modx->regClientCSS($assetsUrl . 'components/gtsapi/css/web/gtstheme.css'
    . (is_file($widgetCss) ? '?v=' . filemtime($widgetCss) : ''));

/* ---------------------------------------------------------------------------
   7. Поведение. Обычный JS без Vue — виджет должен работать и на страницах,
      где Vue-приложения нет вовсе.
   ------------------------------------------------------------------------ */
$modx->regClientHTMLBlock(
'<script>
(function(){
  var st = window.gtsTheme;
  if (!st) return;
  var d = document.documentElement;
  var root = document.querySelector("[data-gts-switch]");
  if (!root) return;

  var mq = window.matchMedia ? window.matchMedia("(prefers-color-scheme: dark)") : null;

  function resolve(p) {
    if (p !== "auto") return p;
    return mq && mq.matches ? "dark" : "light";
  }

  function save() {
    try {
      localStorage.setItem(st.key, JSON.stringify({ theme: st.theme, scheme: st.scheme }));
    } catch (e) {}   // приватный режим: переключение работает, просто не запомнится
  }

  function apply() {
    st.resolved = resolve(st.scheme);
    if (st.theme) d.dataset.gtsTheme = st.theme;
    d.dataset.gtsScheme = st.resolved;
    paint();
    // Vue-приложения могут пересчитать то, что не на CSS: графики, canvas, карты.
    window.dispatchEvent(new CustomEvent("gts:theme", {
      detail: { theme: st.theme, scheme: st.scheme, resolved: st.resolved }
    }));
  }

  function paint() {
    var dark = st.resolved === "dark";
    var btn = root.querySelector("[data-gts-scheme-toggle]");
    if (btn) {
      btn.setAttribute("aria-pressed", dark ? "true" : "false");
      btn.setAttribute("title", dark ? "Светлая схема" : "Тёмная схема");
      btn.setAttribute("aria-label", dark ? "Включить светлую схему" : "Включить тёмную схему");
    }
    var items = root.querySelectorAll("[data-gts-theme-item]");
    for (var i = 0; i < items.length; i++) {
      var on = items[i].getAttribute("data-gts-theme-item") === st.theme;
      items[i].setAttribute("aria-checked", on ? "true" : "false");
    }

    // Сбрасывать нечего, пока в localStorage пусто: гасим пункт, а не прячем,
    // чтобы меню не прыгало по высоте при каждом переключении.
    var reset = root.querySelector("[data-gts-reset]");
    if (reset) reset.disabled = !stored();
  }

  function stored() {
    try { return !!localStorage.getItem(st.key); } catch (e) { return false; }
  }

  // --- схема: солнце / луна
  var toggle = root.querySelector("[data-gts-scheme-toggle]");
  if (toggle) {
    toggle.addEventListener("click", function () {
      // Из "auto" уходим в противоположное тому, что сейчас на экране,
      // иначе первое нажатие выглядело бы как отсутствие реакции.
      st.scheme = st.resolved === "dark" ? "light" : "dark";
      apply();
      save();
    });
  }

  // Пока выбрано "auto" — следуем за системой, если её переключили при
  // открытой странице. После явного выбора не вмешиваемся.
  if (mq) {
    var onSystem = function () { if (st.scheme === "auto") apply(); };
    if (mq.addEventListener) mq.addEventListener("change", onSystem);
    else if (mq.addListener) mq.addListener(onSystem);
  }

  // --- тема: выпадающее меню
  var menuBtn = root.querySelector("[data-gts-theme-toggle]");
  var menu    = root.querySelector("[data-gts-theme-menu]");

  function openMenu(open) {
    if (!menu || !menuBtn) return;
    menu.hidden = !open;
    menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
    if (open) {
      var cur = menu.querySelector("[aria-checked=\'true\']") || menu.querySelector("[data-gts-theme-item]");
      if (cur) cur.focus();
    }
  }

  if (menuBtn && menu) {
    menuBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      openMenu(menu.hidden);
    });

    menu.addEventListener("click", function (e) {
      if (e.target.closest("[data-gts-reset]")) {
        // Забываем личный выбор и возвращаемся к настройкам сайта.
        // save() здесь звать нельзя — он тут же запишет всё обратно.
        try { localStorage.removeItem(st.key); } catch (err) {}
        st.theme  = st.defaults.theme;
        st.scheme = st.defaults.scheme;
        apply();
        openMenu(false);
        menuBtn.focus();
        return;
      }

      var item = e.target.closest("[data-gts-theme-item]");
      if (!item) return;
      st.theme = item.getAttribute("data-gts-theme-item");
      apply();
      save();
      openMenu(false);
      menuBtn.focus();
    });

    menu.addEventListener("keydown", function (e) {
      // Все пункты, включая сброс: стрелками нужно доходить и до него.
      var items = Array.prototype.slice.call(
        menu.querySelectorAll("[data-gts-theme-item], [data-gts-reset]:not([disabled])")
      );
      var i = items.indexOf(document.activeElement);
      if (e.key === "ArrowDown" || e.key === "ArrowUp") {
        e.preventDefault();
        var next = e.key === "ArrowDown" ? i + 1 : i - 1;
        if (next < 0) next = items.length - 1;
        if (next >= items.length) next = 0;
        items[next].focus();
      } else if (e.key === "Escape") {
        openMenu(false);
        menuBtn.focus();
      }
    });

    document.addEventListener("click", function (e) {
      if (!menu.hidden && !root.contains(e.target)) openMenu(false);
    });
  }

  paint();
})();
</script>'
);

/* ---------------------------------------------------------------------------
   8. Разметка. Иконки — встроенный SVG, а не primeicons: виджет должен
      рисоваться и там, где иконочный шрифт не подключён.
   ------------------------------------------------------------------------ */
$sun = '<svg class="gts-ts-i gts-ts-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">'
     . '<circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.2 5.2l1.4 1.4M17.4 17.4l1.4 1.4M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4"/></svg>';

$moon = '<svg class="gts-ts-i gts-ts-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
      . '<path d="M20 14.4A8.5 8.5 0 1 1 9.6 4a6.8 6.8 0 0 0 10.4 10.4z"/></svg>';

$palette = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . '<path d="M12 3a9 9 0 1 0 0 18c1 0 1.7-.8 1.7-1.7 0-.5-.2-.9-.5-1.2-.3-.3-.5-.7-.5-1.1 0-.9.8-1.7 1.7-1.7H16a5 5 0 0 0 5-5c0-4-4-7.3-9-7.3z"/>'
         . '<circle cx="7.5" cy="11.5" r="1.2" fill="currentColor" stroke="none"/>'
         . '<circle cx="11" cy="7.5" r="1.2" fill="currentColor" stroke="none"/>'
         . '<circle cx="15.5" cy="9" r="1.2" fill="currentColor" stroke="none"/></svg>';

$chevron = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9.5l6 6 6-6"/></svg>';

$cls = 'gts-ts';
if (!empty($class)) {
    $cls .= ' ' . htmlspecialchars(trim((string)$class), ENT_QUOTES, 'UTF-8');
}

$out = '<div class="' . $cls . '" data-gts-switch>';

$out .= '<button type="button" class="gts-ts-btn gts-ts-scheme" data-gts-scheme-toggle'
      . ' aria-pressed="false" aria-label="Переключить схему" title="Переключить схему">'
      . $sun . $moon . '</button>';

// Меню рисуем при любом числе тем: даже с одной в нём есть «Сбросить»,
// который возвращает и схему тоже — а её нынешнее значение может быть auto,
// чего кнопкой солнце/луна не выразить.
if ($showThemes) {
    $out .= '<div class="gts-ts-menu-wrap">'
          . '<button type="button" class="gts-ts-btn gts-ts-theme" data-gts-theme-toggle'
          . ' aria-haspopup="true" aria-expanded="false" aria-label="Выбрать тему" title="Тема">'
          . $palette . $chevron . '</button>'
          . '<div class="gts-ts-menu" data-gts-theme-menu role="menu" hidden>';

    foreach ($list as $name) {
        $out .= '<button type="button" class="gts-ts-item" role="menuitemradio" aria-checked="false"'
              . ' data-gts-theme-item="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
              . '<span class="gts-ts-dot gts-ts-dot--' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>'
              . '<span class="gts-ts-name">' . htmlspecialchars($titles[$name], ENT_QUOTES, 'UTF-8') . '</span>'
              . '</button>';
    }

    $reset = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"'
           . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
           . '<path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"/><path d="M3 4.5V9h4.5"/></svg>';

    // Подпись говорит, к чему именно вернёт, — иначе «Сбросить» это кнопка
    // с неизвестным результатом.
    $back = [];
    if ($defaultTheme !== '' && isset($titles[$defaultTheme])) {
        $back[] = mb_strtolower($titles[$defaultTheme], 'UTF-8');
    }
    $back[] = [
        'light' => 'светлая',
        'dark'  => 'тёмная',
        'auto'  => 'как в системе',
    ][$defaultScheme];

    $out .= '<div class="gts-ts-sep" role="separator"></div>'
          . '<button type="button" class="gts-ts-item gts-ts-reset" role="menuitem" data-gts-reset'
          . ' title="Забыть личный выбор и вернуться к настройкам сайта">'
          . '<span class="gts-ts-dot gts-ts-dot--reset" aria-hidden="true">' . $reset . '</span>'
          . '<span class="gts-ts-name">Сбросить'
          . '<small class="gts-ts-hint">' . htmlspecialchars(implode(', ', $back), ENT_QUOTES, 'UTF-8') . '</small>'
          . '</span></button>';

    $out .= '</div></div>';
}

$out .= '</div>';

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $out);
    return '';
}

return $out;

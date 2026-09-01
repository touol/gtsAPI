{*
    gtsTopbar — верхняя строка приложения.

    Бренд, меню ресурсов MODX с выпадающими списками до третьего уровня,
    справа выпадашка пользователя и переключатель схемы/темы.

    Всё на токенах L0 (gts-tokens.css), без Bootstrap. Поведение —
    js/web/topbar.js: раскрытие по клику вдобавок к наведению, третий уровень,
    сворачивание в значок на узком экране.

    Вызов:
        {'gtsTopbar' | chunk}
        {'gtsTopbar' | chunk : [
            'brand'   => 'gtsShopAdmin',
            'parents' => 'gtsshop_p_admin' | option,
            'level'   => 3
        ]}

    Параметры (все необязательные):
        brand    надпись слева. По умолчанию — название сайта
        href     куда ведёт бренд. По умолчанию — корень сайта
        parents  родитель для pdoMenu. По умолчанию настройка gtsshop_p_admin
        level    глубина меню, по умолчанию 3
        themes   показывать выбор темы, по умолчанию 1

    CSS и JS подключаются прямо отсюда: чанк не может звать regClientCSS,
    а разносить один блок по двум местам в каждом шаблоне — верный способ
    однажды забыть половину. Чанк на страницу один, дублей не будет.
*}
{var $tbBrand   = $brand   ?: $_modx->config.site_name}
{var $tbHref    = $href    ?: $_modx->config.site_url}
{var $tbParents = $parents ?: ('gtsshop_p_admin' | option)}
{var $tbLevel   = $level   ?: 3}
{var $tbThemes  = $themes  !== null ? $themes : 1}

<link rel="stylesheet" href="{'assets_url' | config}components/gtsapi/css/web/topbar.css">
<script defer src="{'assets_url' | config}components/gtsapi/js/web/topbar.js"></script>

<nav class="gts-nav" data-gts-nav>
    <a class="gts-nav__brand" href="{$tbHref}">{$tbBrand}</a>

    {* Значок меню. Виден только на узком экране (см. topbar.css), но в
       разметке есть всегда: так не нужен JS при загрузке, чтобы решить,
       показывать его или нет. *}
    <button class="gts-nav__burger" type="button" data-gts-nav-toggle
            aria-expanded="false" aria-controls="gts-nav-menu" aria-label="Меню">
        <svg class="gts-nav__bars" viewBox="0 0 24 24" width="18" height="18" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16"/></svg>
        <svg class="gts-nav__x" viewBox="0 0 24 24" width="18" height="18" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <ul class="gts-nav__menu" id="gts-nav-menu">
        {* aria-expanded ставится только веткам: у листьев его быть не должно,
           иначе скринридер обещает раскрытие, которого нет. *}
        {$_modx->runSnippet('!pdoMenu', [
            'parents' => $tbParents,
            'level' => $tbLevel,
            'checkPermissions' => 'list',
            'outerTpl' => '@INLINE:{$wrapper}',
            'rowTpl' => '@INLINE:{$level == 1
                ? "<li class=\"gts-nav__item {$classnames}\">
                       <a class=\"gts-nav__link\" href=\"{$link}\"" ~
                       ($wrapper ? " aria-expanded=\"false\"" : "") ~ ">{$menutitle}" ~
                       ($wrapper ? "<span class=\"gts-nav__caret\" aria-hidden=\"true\">▾</span>" : "") ~
                   "</a>" ~
                       ($wrapper ? "<ul class=\"gts-nav__sub\">{$wrapper}</ul>" : "") ~
                   "</li>"
                : "<li class=\"gts-nav__subitem {$classnames}\">
                       <a class=\"gts-nav__sublink\" href=\"{$link}\"" ~
                       ($wrapper ? " aria-expanded=\"false\"" : "") ~ ">{$menutitle}" ~
                       ($wrapper ? "<span class=\"gts-nav__caret\" aria-hidden=\"true\">▾</span>" : "") ~
                   "</a>" ~
                       ($wrapper ? "<ul class=\"gts-nav__sub\">{$wrapper}</ul>" : "") ~
                   "</li>"
            }'
        ])}
    </ul>

    <div class="gts-nav__right">
        {if $_modx->user.id}
            {* Выпадашка пользователя. Классы те же, что у разделов меню, —
               значит раскрывается тем же topbar.js, без отдельного кода. *}
            <div class="gts-nav__item gts-nav__usermenu">
                <a class="gts-nav__link" href="#" aria-expanded="false">
                    <svg class="gts-nav__usericon" viewBox="0 0 24 24" width="16" height="16"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.6"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                    <span class="gts-nav__user">{$_modx->user.fullname ?: $_modx->user.username}</span>
                    <span class="gts-nav__caret" aria-hidden="true">▾</span>
                </a>
                <ul class="gts-nav__sub gts-nav__sub--right">
                    <li class="gts-nav__subitem">
                        <a class="gts-nav__sublink" href="{$_modx->config.site_url}">Главная</a>
                    </li>
                    <li class="gts-nav__subitem">
                        <a class="gts-nav__sublink" href="{$_modx->resource.id | url}?service=logout">Выход</a>
                    </li>
                </ul>
            </div>
        {/if}
        {$_modx->runSnippet('!gtsTheme', ['themes' => $tbThemes, 'class' => 'gts-ts--ghost'])}
    </div>
</nav>

/* ============================================================================
   topbar.js — поведение верхней строки (см. css/topbar.css).

   Три вещи, которых нельзя добиться одним CSS:

   1. Пункт с детьми не должен переходить по ссылке. Клик по нему раскрывает
      подменю и фиксирует его открытым — в отличие от наведения, которое
      закрывается, как только мышь ушла. Наведение при этом никуда не делось,
      оно живёт в CSS и работает параллельно.
   2. Третий уровень с клавиатуры: :focus-within открывает, но переключать
      и закрывать по Esc нужно здесь.
   3. Узкий экран. Меню сворачивается в значок, по клику раскрывается
      вертикально, вложенные уровни — гармошкой, а не всплывашкой сбоку.
      Наведения там нет вовсе.

   Раскрытие держится на атрибуте data-open у <li>; CSS только рисует.
   ========================================================================= */
(function () {
    'use strict';

    var MOBILE = 860;   // тот же порог, что в topbar.css

    var nav = document.querySelector('[data-gts-nav]');
    if (!nav) return;

    var burger = nav.querySelector('[data-gts-nav-toggle]');

    function isMobile() {
        return window.matchMedia('(max-width: ' + MOBILE + 'px)').matches;
    }

    /** Ветка ли это: у пункта есть вложенный список. */
    function hasSub(li) {
        return !!li.querySelector(':scope > .gts-nav__sub');
    }

    function setOpen(li, open) {
        if (open) {
            li.setAttribute('data-open', '');
        } else {
            li.removeAttribute('data-open');
            // Закрываем и всё, что раскрыто внутри: иначе при повторном
            // открытии раздел показывался бы с уже развёрнутой веткой.
            var inner = li.querySelectorAll('[data-open]');
            for (var i = 0; i < inner.length; i++) {
                inner[i].removeAttribute('data-open');
            }
        }
        var link = li.querySelector(':scope > .gts-nav__link, :scope > .gts-nav__sublink');
        if (link) link.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function closeSiblings(li) {
        var parent = li.parentNode;
        for (var i = 0; i < parent.children.length; i++) {
            var sib = parent.children[i];
            if (sib !== li && sib.hasAttribute && sib.hasAttribute('data-open')) {
                setOpen(sib, false);
            }
        }
    }

    function closeAll() {
        var open = nav.querySelectorAll('[data-open]');
        for (var i = 0; i < open.length; i++) {
            setOpen(open[i], false);
        }
    }

    function setMenuOpen(open) {
        if (open) {
            nav.setAttribute('data-menu-open', '');
        } else {
            nav.removeAttribute('data-menu-open');
            closeAll();
        }
        if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    /* --- клики по пунктам ----------------------------------------------
       Слушаем весь nav, а не только список разделов: выпадашка пользователя
       живёт в правой части, вне <ul>, но раскрывается тем же механизмом. */
    nav.addEventListener('click', function (e) {
        var link = e.target.closest('.gts-nav__link, .gts-nav__sublink');
        if (!link) return;

        var li = link.parentNode;
        if (!hasSub(li)) return;   // лист — уходим по ссылке как обычно

        e.preventDefault();

        var open = li.hasAttribute('data-open');
        if (!open) closeSiblings(li);
        setOpen(li, !open);
    });

    // Клавиатура: Esc закрывает текущий уровень и возвращает фокус наверх.
    nav.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var li = document.activeElement && document.activeElement.closest('[data-open]');
        if (!li) return;
        setOpen(li, false);
        var link = li.querySelector(':scope > .gts-nav__link, :scope > .gts-nav__sublink');
        if (link) link.focus();
    });

    /* --- значок меню на узком экране ------------------------------------ */
    if (burger) {
        burger.addEventListener('click', function (e) {
            e.stopPropagation();
            setMenuOpen(!nav.hasAttribute('data-menu-open'));
        });
    }

    /* --- клик мимо меню -------------------------------------------------- */
    document.addEventListener('click', function (e) {
        if (nav.contains(e.target)) return;
        closeAll();
        if (isMobile()) setMenuOpen(false);
    });

    /* --- смена ширины ----------------------------------------------------
       Раскрытая гармошка при переходе на широкий экран превратилась бы в
       набор одновременно открытых всплывашек. Схлопываем всё. */
    var wasMobile = isMobile();
    window.addEventListener('resize', function () {
        var now = isMobile();
        if (now === wasMobile) return;
        wasMobile = now;
        closeAll();
        setMenuOpen(false);
    });
})();

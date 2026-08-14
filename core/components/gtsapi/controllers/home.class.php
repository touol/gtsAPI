<?php

/**
 * Страница админки gtsAPI.
 *
 * Раньше здесь была заготовка ExtJS-панели из шаблона modExtra, а сами страницы
 * рисовал контроллер getTables. Теперь контроллер сам разбирает параметр меню
 * &config и вызывает нужные сниппеты gtsAPI.
 *
 * Сама страница приложение НЕ рисует: она отдаёт iframe на
 * assets/components/gtsapi/admin.php, а тот уже вызывает сниппеты. Причина —
 * в слоях CSS: PrimeVue и Tailwind внутри PVTables собраны в @layer, а стили
 * темы менеджера идут без слоя и потому сильнее любого правила в слое. В одном
 * документе они портят друг друга в обе стороны, и селекторами это не лечится.
 *
 * Формат config — JSON вида {"<Сниппет>": {<его параметры>}}:
 *
 *   &config={"PVTabs":{"tabs":{"DocType":{"title":"Типы документов","table":"DocType"}}}}
 *   &config={"PVTable":{"table":"gtsAPITable"}}
 *   &config={"mixVue":{"app":"pvSklad","config":{"module":"BarcodeDocs"}}}
 *
 * Ключ — имя сниппета, значение — его свойства. Сниппетов можно перечислить
 * несколько, они выводятся подряд. Вместо JSON можно указать имя системной
 * настройки, в которой этот JSON лежит: &config=gtsapi_admin_pv.
 *
 * Строковые значения верхнего уровня сниппетами не считаются: "title" задаёт
 * заголовок страницы.
 */
class gtsAPIHomeManagerController extends modExtraManagerController
{
    /** @var gtsAPI $gtsAPI */
    public $gtsAPI;

    /** @var array Разобранный конфиг: имя сниппета => его свойства */
    protected $blocks = [];

    /** @var string Заголовок страницы из конфига */
    protected $title = '';

    /** @var string Накопленные ошибки — показываем их на странице, а не молчим */
    protected $error = '';

    /** @var string Параметр &config как есть — его передаём в iframe */
    protected $rawConfig = '';

    public function initialize()
    {
        $this->gtsAPI = $this->modx->getService('gtsAPI', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');

        $raw = isset($_REQUEST['config']) ? trim((string)$_REQUEST['config']) : '';
        $this->rawConfig = $raw;
        if ($raw === '') {
            $this->error = 'В пункте меню не задан параметр &config';
        } else {
            $config = $this->parseConfig($raw);
            if (!is_array($config)) {
                $this->error = 'Параметр &config не разобран: ' . $this->error;
            } else {
                foreach ($config as $key => $value) {
                    if (is_array($value)) {
                        $this->blocks[$key] = $value;
                    } elseif ($key === 'title' || $key === 'pagetitle') {
                        $this->title = (string)$value;
                    }
                }
                if (!$this->blocks) {
                    $this->error = 'В конфиге нет ни одного блока вида "Сниппет": { … }';
                }
            }
        }

        if ($this->error) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[gtsAPI admin] ' . $this->error);
        }

        parent::initialize();
    }

    /**
     * JSON прямо в параметре меню или имя системной настройки, где он лежит.
     *
     * @param string $raw
     * @return array|null
     */
    protected function parseConfig($raw)
    {
        if ($raw[0] !== '{' && $raw[0] !== '[') {
            $setting = $this->modx->getOption($raw, null, '');
            if ($setting === '') {
                $this->error = "системная настройка «{$raw}» пуста или не существует";

                return null;
            }
            $raw = trim($setting);
        }

        $config = json_decode($raw, true);
        if (!is_array($config)) {
            $this->error = json_last_error_msg();

            return null;
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['gtsapi:manager', 'gtsapi:default'];
    }

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }

    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->title !== '' ? $this->title : $this->modx->lexicon('gtsapi');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        if ($this->error) {
            $this->content .= '<div class="gtsapi-admin-error" style="padding:15px;color:#a00;">'
                . $this->error . '</div>';

            return '';
        }

        // Приложение живёт в iframe, а не в этой странице.
        //
        // Иначе стили дерутся насмерть в обе стороны: тема менеджера ломает
        // таблицы и формы PVTables, а Tailwind из PVTables ломает дерево
        // ресурсов и панели менеджера. Причина не в селекторах, а в слоях CSS:
        // PrimeVue и Tailwind собраны в @layer, а правила темы менеджера — без
        // слоя, и потому сильнее ЛЮБОГО правила в слое, как его ни уточняй.
        // Развести это можно только границей документа.
        $url = MODX_ASSETS_URL . 'components/gtsapi/admin.php?config='
            . rawurlencode($this->rawConfig);

        // Высота рамки — ровно то, что осталось от окна под шапкой менеджера.
        // Фиксированный calc() тут не годится: высота шапки зависит от темы,
        // от строки версии MODX и от того, свёрнуто ли меню. Отсюда и вторая
        // полоса прокрутки — рамка оказывалась выше свободного места.
        // Считаем от реального положения рамки и пересчитываем при ресайзе.
        $this->content .= '
        <style>
        #gtsapi-admin-frame { display:block; width:100%; border:0; }
        </style>
        <iframe id="gtsapi-admin-frame" scrolling="no"
                src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></iframe>
        <script>
        (function () {
            var frame = document.getElementById("gtsapi-admin-frame");
            if (!frame) return;
            var fit = function () {
                var top = frame.getBoundingClientRect().top;
                // 8px — чтобы рамка не упиралась в самый низ окна
                var h = Math.max(300, window.innerHeight - top - 8);
                frame.style.height = h + "px";
            };
            fit();
            window.addEventListener("resize", fit);
            // Шапка менеджера досчитывается после загрузки скриптов ExtJS,
            // поэтому меряем ещё раз, когда всё встало на места.
            window.addEventListener("load", fit);
            setTimeout(fit, 300);
        })();
        </script>';

        return '';
    }
}

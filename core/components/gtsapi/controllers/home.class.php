<?php

/**
 * The home manager controller for gtsAPI.
 *
 */
class gtsAPIHomeManagerController extends modExtraManagerController
{
    /** @var gtsAPI $gtsAPI */
    public $gtsAPI;


    /**
     *
     */
    public function initialize()
    {
        $this->gtsAPI = $this->modx->getService('gtsAPI', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');
        parent::initialize();
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
        return $this->modx->lexicon('gtsapi');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->gtsAPI->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/gtsapi.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/misc/default.window.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/widgets/items/grid.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/widgets/items/windows.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->gtsAPI->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');

        $this->gtsAPI->config['date_format'] = $this->modx->getOption('gtsapi_date_format', null, '%d.%m.%y <span class="gray">%H:%M</span>');
        $this->gtsAPI->config['help_buttons'] = ($buttons = $this->getButtons()) ? $buttons : '';

        $this->addHtml('<script type="text/javascript">
        gtsAPI.config = ' . json_encode($this->gtsAPI->config) . ';
        gtsAPI.config.connector_url = "' . $this->gtsAPI->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "gtsapi-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .=  '<div id="gtsapi-panel-home-div"></div>';
        return '';
    }

    /**
     * @return string
     */
    public function getButtons()
    {
        $buttons = null;
        $name = 'gtsAPI';
        $path = "Extras/{$name}/_build/build.php";
        if (file_exists(MODX_BASE_PATH . $path)) {
            $site_url = $this->modx->getOption('site_url').$path;
            $buttons[] = [
                'url' => $site_url,
                'text' => $this->modx->lexicon('gtsapi_button_install'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1&encryption_disabled=1',
                'text' => $this->modx->lexicon('gtsapi_button_download'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1',
                'text' => $this->modx->lexicon('gtsapi_button_download_encryption'),
            ];
        }
        return $buttons;
    }
}
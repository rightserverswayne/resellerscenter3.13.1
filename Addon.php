<?php

namespace MGModule\ResellersCenter;

use MGModule\ResellersCenter as main;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of Addon
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
define("ADDON_DIR", __DIR__);

class Addon extends main\mgLibs\process\AbstractMainDriver
{

    /**
     * Load Addon WHMCS Configuration
     * 
     * 
     */
    function loadAddonConfiguration()
    {
        $result = mgLibs\MySQL\Query::select(
                        array(
                    'setting'
                    , 'value'
                        )
                        , 'tbladdonmodules'
                        , array(
                    'module' => $this->configuration()->systemName
                        )
        );

        while ($row = $result->fetch())
        {
            if($row['setting'] != 'version')
            {
                $this->configuration()->{$row['setting']} = $row['value'];
            }
        }
    }

    /**
     * Return Tempalates Path
     * 
     * @param boolean $relative
     * @return string
     */
    static function getModuleTemplatesDir($relative = false)
    {

        $dir = ($relative) ? '' : (__DIR__ . DS);

        $dir .= 'templates' . DS;

        if (self::I()->isAdmin())
        {
            return $dir . 'admin';
        }
        else
        {
            $template = $GLOBALS['CONFIG']['Template'];

            if (file_exists(__DIR__ . DS . 'templates' . DS . 'clientarea' . DS . $template))
            {
                return $dir . 'clientarea' . DS . $template;
            }
            else
            {
                return $dir . 'clientarea' . DS . 'default';
            }
        }
    }

    public function getAssetsURL()
    {
        if ($this->isAdmin())
        {
            return '../modules/addons/' . $this->configuration()->systemName . '/templates/admin/assets';
        }
        else
        {
            return 'modules/addons/' . $this->configuration()->systemName . '/' . self::getModuleTemplatesDir(true) . '/assets';
        }
    }

    public function getType()
    {
        return 'addon';
    }

    public static function getMainDIR()
    {
        return __DIR__;
    }

    public static function getWHMCSDIR()
    {
        return substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'modules' . DS . 'addons' . DS . "ResellersCenter"));
    }

    static function getUrl($page = null, $action = null, $params = array())
    {
        if (self::I()->isAdmin())
        {
            $url = 'addonmodules.php?module=' . self::I()->configuration()->systemName;
        }
        else
        {
            $url = 'index.php?m=' . self::I()->configuration()->systemName;
        }

        if ($page)
        {
            $url .= '&mg-page=' . $page;
            if ($action)
            {
                $url .= '&mg-action=' . $action;
            }

            if ($params)
            {
                $url .= '&' . http_build_query($params);
            }
        }

        return $url;
    }

    static function genCustomPageUrl($page = null, $action = null, $params = array())
    {
        if (self::I()->isAdmin())
        {
            $url = 'addonmodules.php?module=' . self::I()->configuration()->systemName . '&customPage=1';
        }
        else
        {
            $url = 'index.php?m=' . self::I()->configuration()->systemName . '&customPage=1';
        }

        if ($page)
        {
            $url .= '&mg-page=' . $page;
        }

        if ($action)
        {
            $url .= '&mg-action=' . $action;
        }

        if ($params)
        {
            $url .= '&' . http_build_query($params);
        }

        return $url;
    }

    static function genJSONUrl($page)
    {
        if (self::I()->isAdmin())
        {
            return 'addonmodules.php?module=' . self::I()->configuration()->systemName . '&json=1&mg-page=' . $page;
        }
        else
        {
            return 'index.php?m=' . self::I()->configuration()->systemName . '&json=1&mg-page=' . $page;
        }
    }

    static function config()
    {
        return array
            (
            'name'        => self::I()->configuration()->name
            , 'description' => self::I()->configuration()->description
            , 'version'     => self::I()->configuration()->version
            , 'author'      => self::I()->configuration()->author
            , 'fields'      => self::I()->configuration()->getAddonWHMCSConfig()
        );
    }

    static function activate()
    {
        try
        {
            self::I()->configuration()->activate();

            return array(
                'status' => 'success'
            );
        }
        catch (\Exception $ex)
        {
            return array(
                'status'      => 'error'
                , 'description' => $ex->getMessage()
            );
        }
    }

    static function deactivate()
    {
        self::I()->configuration()->deactivate();
    }

    static function upgrade($vars)
    {
        try
        {
            self::I()->configuration()->upgrade($vars);
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            models\whmcs\errors\Register::register($ex);
            return array("error" => $ex->getMessage());
        }
    }

    static function getHTMLAdminCustomPage($input)
    {
        try
        {
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();

            $page   = empty($input['mg-page']) ? 'home' : $input['mg-page'];
            $page   = ucfirst($page);
            $action = empty($input['mg-action']) ? 'index' : $input['mg-action'];

            list($content) = self::I()->runControler($page, $action, $input, 'CustomHTML');
            return $content;
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            mgLibs\Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir());

            $message = $ex->getMessage();
            if (method_exists($ex, 'getToken'))
            {
                $message .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
            }

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
            return mgLibs\Smarty::I()->view('fatal', array(
                        'message' => $message
            ));
        }
    }

    static function getHTMLAdminPage($input)
    {
        try
        {
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();

            if (self::I()->isDebug())
            {
                self::I()->configuration()->activate();
            }

            $menu = array();
            foreach (self::I()->configuration()->getAddonMenu() as $catName => $category)
            {

                // display the page or not
                if (strpos($catName, "documentation") === false)
                {
                    $className  = self::I()->getMainNamespace() . "\\controllers\\" . self::I()->getType() . "\\" . 'admin' . "\\" . ucfirst($catName);
                    $controller = new $className ();
                    if (method_exists($controller, "isActive") && !$controller->{"isActive"}())
                        continue;
                }                                

                if (isset($category['submenu']))
                {
                    foreach ($category['submenu'] as $subName => &$subPage)
                    {
                        if (empty($subPage['url']))
                        {
                            $subPage['url'] = self::getUrl($catName, $subName);
                        }
                    }
                }

                $category['url'] = self::getUrl($catName);

                $menu[$catName] = $category;
            }


            if (empty($input['mg-page']))
            {
                global $CONFIG;

                if ($CONFIG["RC_Skip_Dashboard"])
                {
                    $input['mg-page'] = array_keys($menu)[1];
                }
                else
                {
                    $input['mg-page'] = key($menu);
                }
            }

            if ($input['mg-page'])
            {
                $breadcrumb[0] = array(
                    'name'        => $input['mg-page'],
                    'url'         => $menu[$input['mg-page']]['url'],
                    'icon'        => $menu[$input['mg-page']]['icon'],
                    'disableLink' => $menu[$input['mg-page']]['disableLink']
                );
                if ($input['mg-action'])
                {
                    $breadcrumb[1] = array(
                        'name' => $input['mg-action']
                        , 'url'  => $menu[$input['mg-page']]['submenu'][$input['mg-action']]['url']
                        , 'icon' => $menu[$input['mg-page']]['submenu'][$input['mg-action']]['icon']
                    );
                }
            }


            $page   = $input['mg-page'];
            $action = empty($input['mg-action']) ? 'index' : $input['mg-action'];
            $page   = ucfirst($page);
            $vars   = array(
                'assetsURL'       => self::I()->getAssetsURL()
                , 'mainURL'         => self::I()->getUrl()
                , 'mainName'        => self::I()->configuration()->name
                , 'menu'            => $menu
                , 'breadcrumbs'     => $breadcrumb
                , 'JSONCurrentUrl'  => self::I()->genJSONUrl($page)
                , 'currentPageName' => $page
                , 'Addon'           => self::I()
                , 'isWHMCS6'        => version_compare($GLOBALS['CONFIG']['Version'], '6.0.0', '>=')
                , 'isWHMCS72'       => version_compare($GLOBALS['CONFIG']['Version'], '7.2.0', '>=')
                , 'isWHMCS78'       => version_compare($GLOBALS['CONFIG']['Version'], '7.8.0', '>=')
            );

            try
            {

                list($content, $success, $error) = self::I()->runControler($page, $action, $input, 'HTML');
                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error']   = $error;

                $path = __DIR__ . DS . "core" . DS . "hooks" . DS . "InvoiceCreationPreEmail.php";
                if (file_exists($path))
                {
                    //$vars['error'] = "Error: The module was unable to delete file {$path} during update. Please delete it manually.";
                }
            }
            catch (\Exception $ex)
            {
                self::dump($ex);
                main\mgLibs\error\Register::register($ex);
                $vars['error'] = $ex->getMessage();
                if (method_exists($ex, 'getToken'))
                {
                    $vars['error'] .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
                }

                $logger = new core\Logger();
                $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
            }

            mgLibs\Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir());

            $html = mgLibs\Smarty::I()->view('main', $vars);

            if (self::I()->isDebug())
            {
                $tmp = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!</strong></div>';

                if ($langs = mgLibs\Lang::getMissingLangs())
                {
                    $tmp .= '<pre>';
                    foreach ($langs as $lk => $lang)
                    {
                        $tmp .= $lk . " = '" . $lang . "';\n";
                    }
                    $tmp .= '</pre>';
                }
                $html = $tmp . $html;
            }

            return $html;
        }
        catch (\Exception $ex)
        {
            self::dump($ex);

            main\mgLibs\error\Register::register($ex);
            mgLibs\Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir());

            $message = $ex->getMessage();
            if (method_exists($ex, 'getToken'))
            {
                $message .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
            }

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
            return mgLibs\Smarty::I()->view('fatal', array(
                        'message'   => $message
                        , 'assetsURL' => self::I()->getAssetsURL()
            ));
        }
    }

    public static function getHTMLClientAreaPage($input)
    {
        //Reseller that is masquerading as a client does not have access to RC panel
        if (isset($_SESSION["loggedAsClient"]) && $input['mg-action'] != 'returnToRc')
        {
            redir();
        }

        //Check if logged client is a reseller
        $clientid = isset($_SESSION["loggedAsClient"]) ? $_SESSION["loggedAsClient"] : $_SESSION["uid"];
        if (!core\helpers\Reseller::isReseller($clientid))
        {
            redir();
        }                        

        $menu = array();
        foreach (self::I()->configuration()->getClienMenu() as $catName => $category)
        {
            // display the page or not
            if (strpos($catName, "documentation") === false)
            {
                $className  = self::I()->getMainNamespace() . "\\controllers\\" . self::I()->getType() . "\\" . 'clientarea' . "\\" . ucfirst($catName);
                $controller = new $className ();
                if (method_exists($controller, "isActive") && !$controller->{"isActive"}())
                    continue;
            }
            if (isset($category['submenu']))
            {
                foreach ($category['submenu'] as $subName => &$subPage)
                {
                    if (empty($subPage['url']))
                    {
                        $subPage['url'] = self::getUrl($catName, $subName);
                    }
                }
            }

            $category['url'] = self::getUrl($catName);

            $menu[$catName] = $category;
        }

        //block promotions page if reseller does not have an access
        $reseller = core\helpers\Reseller::getLogged();
        if(!$reseller->settings->admin->promotions)
        {
            unset($menu["promotions"]);
            $input['mg-page'] = ($input['mg-page'] == 'promotions') ? 0 : $input['mg-page'];
        }
        
        if (empty($input['mg-page']))
        {
            if ($reseller->settings->private->skipResellerDashboard && $input['mg-page'] == 0)
            {
                next($menu);
            }
            
            if (!$reseller->settings->private->docsDoNotShowAgain && $reseller->settings->private->skipResellerDashboard)
            {
                end($menu);
            }            

            $input['mg-page'] = key($menu);
        }

        $output = array(
            'pagetitle'    => self::I()->configuration()->clientareaName,
            'templatefile' => self::I()->getModuleTemplatesDir(true) . '/main',
            'requirelogin' => isset($_SESSION['uid']) ? false : true,
        );

        $breadcrumb = array(self::I()->getUrl() => self::I()->configuration()->clientareaName);

        try
        {            
            self::I()->setMainLangContext();

            $page = ucfirst($input['mg-page']);
            $pageTranslated = Lang::absoluteT('addonCA', 'pagesLabels','label', $input['mg-page']);

            if (!empty($input['mg-page']))
            {
                $url              = self::I()->getUrl($input['mg-page']);
                $breadcrumb[$url] = $pageTranslated;
            }
            $action = empty($input['mg-action']) ? 'index' : $input['mg-action'];
            $vars   = array(
                'assetsURL'       => self::I()->getAssetsURL(),
                'mainURL'         => self::I()->getUrl(),
                'mainName'        => self::I()->configuration()->clientareaName,
                'JSONCurrentUrl'  => self::I()->genJSONUrl($page),
                'currentPageName' => strtolower($page),
                'menu'            => $menu,
                'breadcrumbs'     => $breadcrumb,
                'isWHMCS6'        => version_compare($GLOBALS['CONFIG']['Version'], '6.0.0', '>='),
                'isLagom'         => self::isLagom()
            );

            try
            {
                $vars['MGLANG'] = mgLibs\Lang::getInstance();
                list($content, $success, $error) = self::I()->runControler($page, $action, $input, 'HTML');

                if (self::I()->isDebug())
                {
                    $html = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!</strong></div>';

                    if ($langs = mgLibs\Lang::getMissingLangs())
                    {
                        $html .= '<pre>';
                        foreach ($langs as $lk => $lang)
                        {
                            $html .= $lk . " = '" . $lang . "';\n";
                        }
                        $html .= '</pre>';
                    }

                    $content = $html . $content;
                }


                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error']   = $error;
            }
            catch (\Exception $ex)
            {
                self::dump($ex);
                main\mgLibs\error\Register::register($ex);
                $vars['error'] = mgLibs\Lang::absoluteT($ex->getMessage());
//                if (method_exists($ex, 'getToken'))
//                {
//                    $vars['error'] .= mgLibs\Lang::absoluteT($ex->getMessage());
//                }

                $logger = new core\Logger();
                $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
            }
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            main\mgLibs\error\Register::register($ex);
            $vars['error'] = mgLibs\Lang::absoluteT('generalError');
            if (method_exists($ex, 'getToken'))
            {
                $vars['error'] .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
            }

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
        }


        $output['breadcrumb'] = $breadcrumb;
        $output['vars']       = $vars;

        return $output;
    }

    public static function isLagom()
    {
        $lagomKeys = [
            'lagom',
            'lagom2'
        ];

        $template = main\models\whmcs\Configuration::where('setting', 'Template')->first();
        return in_array($template->value, $lagomKeys);
    }

    static function getJSONAdminPage($input)
    {
        $page = 'home';
        if (!empty($input['mg-page']))
        {
            $page = $input['mg-page'];
        }
        $page   = ucfirst($page);
        $action = empty($input['mg-action']) ? 'index' : $input['mg-action'];
        try
        {
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();

            list($result, $success, $error) = self::I()->runControler($page, $action, $input, 'JSON');

            $content = self::parseContent($result, $success, $error);
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            main\mgLibs\error\Register::register($ex);
            $content['result'] = 'error';
            $content['error']  = $ex->getMessage();
            if (method_exists($ex, 'getToken'))
            {
                $content['error'] .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
            }

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
        }

        if (Request::get("datatable") !== null)
        {
            return json_encode($content["data"]);
        }
        else
        {
            return '<JSONRESPONSE#' . json_encode($content) . '#ENDJSONRESPONSE>';
        }
    }

    public static function getJSONClientAreaPage($input)
    {
        if (isset($_SESSION["loggedAsClient"]) && $input['mg-action'] != 'returnToRc')
        {
            return;
        }

        $clientid = isset($_SESSION["loggedAsClient"]) ? $_SESSION["loggedAsClient"] : $_SESSION["uid"];
        if (!core\helpers\Reseller::isReseller($clientid))
        {
            return;
        }

        $page = 'home';
        if (!empty($input['mg-page']))
        {
            $page = $input['mg-page'];
        }
        $page   = ucfirst($page);
        $action = empty($input['mg-action']) ? 'index' : $input['mg-action'];

        try
        {
            self::I()->setMainLangContext();

            list($result, $success, $error) = self::I()->runControler($page, $action, $input, 'JSON');

            $content = self::parseContent($result, $success, $error);
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            $content['result'] = 'error';
            main\mgLibs\error\Register::register($ex);
            $content['error']  = mgLibs\Lang::absoluteT('generalError');
            if (method_exists($ex, 'getToken'))
            {
                $content['error'] .= mgLibs\Lang::absoluteT('token') . $ex->getToken();
            }

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
        }

        if (Request::get("datatable") !== null)
        {
            return json_encode($content["data"]);
        }
        else
        {
            return '<JSONRESPONSE#' . json_encode($content) . '#ENDJSONRESPONSE>';
        }
    }

    static function parseContent($result, $success, $error)
    {
        if ($error)
        {
            $content['error']  = $error;
            $content['result'] = 'error';
        }
        elseif ($success)
        {
            $content['success'] = $success;
            $content['result']  = 'success';
        }

        if ($langs = mgLibs\Lang::getMissingLangs())
        {
            $html = '<pre>';
            foreach ($langs as $lk => $lang)
            {
                $html .= $lk . " = '" . $lang . "';\n";
            }
            $html .= '</pre>';

            $content['error']  = $html;
            $content['result'] = 'error';
        }

        $content['data'] = $result;

        return $content;
    }

    static function cron($input)
    {
        try
        {
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();

            self::I()->runControler('Cron', 'index', $input, 'CRON');
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            main\mgLibs\error\Register::register($ex);
        }
    }

    static function localAPI($action, $arguments)
    {
        $output = array(
            'action' => $action
        );

        try
        {
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();

            list($result, $success, $error) = self::I()->runControler('localAPI', $action, $arguments, 'API');
            $output['success'] = $result;
        }
        catch (\Exception $ex)
        {
            self::dump($ex);
            main\mgLibs\error\Register::register($ex);
            $output['error'] = array(
                'message' => $ex->getMessage()
                , 'code'    => $ex->getCode()
            );

            $logger = new core\Logger();
            $logger->addNewLog(repository\Logs::ERROR, $ex->getMessage());
        }

        return $output;
    }
}

<?php 

namespace MGModule\ResellersCenter;

use MGModule\ResellersCenter as main;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Module Configuration
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Configuration extends main\mgLibs\process\AbstractConfiguration
{
    public $debug = false;
    
    public $systemName = 'ResellersCenter';
    
    public $name = 'Resellers Center';
    
    public $description = 'This addon allows your clients to act as resellers, offering your chosen products to their own contacts. Additionally, resellers are able to brand their contact client area, emails and invoices, and define custom products prices within limits defined by admin.<br>For more info visit our <a href="http://www.docs.modulesgarden.com/Resellers_Center_For_WHMCS" style="color: #4169E1;" target="_blank">Wiki</a>.';
    
    public $clientareaName = 'Resellers Center';

    public $encryptHash = 'uUc1Y8cWxDOAzlq11lBwelqzo6PGMTA0dbHaKQ109psefoJgIFMOgmReKCZbpCYpDSnrtfjmCIUyplaBJaUh40auDALprOHtj1g92ZRBS6S94IbZWaeZRYkG1f81h6qLMYEOr016RurCnmodFCWdMkTqrlVBvH249gzXPduKQVXpN9hooComaRPY5jZD6s8GdfR5E_BNP3v8Ui8RrdqMPST_8quMW48LhHY88xCvSWwDNjkC2tCAaK67Id2NjzIdoNTHUMISRg81nHX8ZGcbP74mxixo_ASd8YoWnDCAs8yiT4t0PwKRO_y3C1kDo69Nxz1YYt4tY1VzOD_DFBulAA5NCJLfogroo';
        
    public $version = '3.13.0';
    
    public $author = '<a href="http://www.modulesgarden.com" targer="_blank">ModulesGarden</a>';

    public $tablePrefix = 'ResellersCenter_';
    
    function __construct() 
    {
        $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'moduleVersion.php';
        if(file_exists($file))
        {
            include $file;
            $this->version = $moduleVersion;
        }

        global $CONFIG;
        $language = Lang::getLang() ?: $CONFIG['Language'];

        if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . "langs" . DIRECTORY_SEPARATOR . "{$language}.php"))
        {
            include __DIR__ . DIRECTORY_SEPARATOR . "langs" . DIRECTORY_SEPARATOR . "{$language}.php";
        }
        else
        {
            include __DIR__ . DIRECTORY_SEPARATOR . "langs" . DIRECTORY_SEPARATOR . "english.php";
        }

        if(!empty($_LANG['ClientAreaModuleName']))
        {
            $this->clientareaName = $_LANG["ClientAreaModuleName"];
        }
    }
            
    function getAddonMenu(){
        return array(
            'dashboard'  =>  array
            (
                'icon'          =>  'fa fa-dashboard',
                'doNotDisplay'  =>  '1'
            ),
            'resellers'  =>  array
            (
                'icon'          =>  'fa fa-suitcase'
            ),
            'groups'  =>  array
            (
                'icon'          =>  'fa fa-group'
            ),
            'payouts'  =>  array
            (
                'icon'          =>  'fa fa-bank'
            ),
            'statistics'  =>  array
            (
                'icon'          =>  'fa fa-bar-chart-o '
            ),
            'configuration'  =>  array
            (
                'icon'          =>  'fa fa-cogs'
            ),
            'utilities' => array( 
                'disableLink'   => true,
                'icon' => 'glyphicon glyphicon-cog',
                'submenu'=> array
                (
                    'logs' => array
                    (
                        'icon' => 'glyphicon glyphicon-hourglass'
                    ),
                    'integrationCode' => array
                    (
                        'icon' => 'fa fa-code'
                    ),
                    'creditLinesLogs' => array
                    (
                        'icon' => 'fa fa-list'
                    ),
                    'dataExport'    => array(
                        'icon'  => 'glyphicon glyphicon-save'
                    ),
                    'resellerDocumentation' => array
                    (
                        'icon' => 'fa fa-book'
                    ),
                    'Documentation' => array (
                        'icon' => 'glyphicon glyphicon-book',
                        'externalUrl' => "https://www.docs.modulesgarden.com/Resellers_Center_For_WHMCS"
                    )
                )
            ),
        );
    }
    
    function getClienMenu(){
        return array(
            'dashboard' => array(
                'icon'          =>  'fa fa-dashboard',
                'doNotDisplay'  =>  '1'
            ),
            'search'        => array(
                'icon'          =>  'fa fa-search'
            ),
            'clients'        => array(
                'icon'          =>  'fa fa-users'
            ),
            'invoices'  =>  array
            (
                'icon'          =>  'fa fa-file-o'
            ),   
            'orders'  =>  array
            (
                'icon'          =>  'fa fa-shopping-cart '
            ), 
            'tickets'  =>  array
            (
                'icon'          =>  'fa fa-ticket'
            ),
            'pricing'  =>  array
            (
                'icon'          =>  'fa fa-dollar'
            ),
            'promotions'  =>  array
            (
                'icon'          =>  'fa fa-money'
            ),
            'configuration'  =>  array
            (
                'icon'          =>  'fa fa-cogs'
            ),
            'statistics'  =>  array
            (
                'icon'          =>  'fa fa-bar-chart-o'
            ),
            'documentation' => array(
                'icon'          =>  'fa fa-book'
            )
        );
    }
        
    public function getAddonWHMCSConfig(){
        return array(
            'hooksEnabled' => array(
                    "FriendlyName"  => "Hooks Enabled",
                    "Type"        => "yesno",
                    "Size"        => "25",
                    "Description" => "Hooks in the module will be enabled.",
                    "Default"     => "true",
            ),
            'mainWhmcsEnabled' => array(
                    "FriendlyName"  => mgLibs\Lang::T("mainWhmcsEnabled", "title"),
                    "Type"        => "yesno",
                    "Size"        => "25",
                    "Description" => mgLibs\Lang::T("mainWhmcsEnabled", "description"),
                    "Default"     => "true",
            ),
            'adminStoreServiceFilter' => array(
                "FriendlyName"  => mgLibs\Lang::T("adminStoreServiceFilter", "title"),
                "Type"        => "yesno",
                "Size"        => "25",
                "Description" => mgLibs\Lang::T("adminStoreServiceFilter", "description"),
                "Default"     => "true",
            ),
        );
    }
    
    /**
     * Run When Module Install
     * 
     * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
     */
    function activate()
    {
        $errors1 = $this->performQueryFromFile("/core/database/3.0.0/schema.sql");
  
        //Remove tables that has not been used before
        $this->clearUnusedTables();
        $errors2 = $this->performQueryFromFile("/core/database/3.1.0/upgrade.sql");
        $errors = $this->performQueryFromFile("/core/database/3.12.0/upgrade.sql");
        $errors2 = array_merge($errors2, $errors);
        $this->addDefaultDocumentation();
        
        //Check if storage dir is writable
        if(!$this->isStorageWriteable())
        {
            $path = __DIR__ .DS. 'storage';
            $errors2[] = "Storage directory or at least one of subdirectories are not writeable. Resellers will not be able to upload their company logo. Please set write permissions for dir and all subdirectories in {$path}";        
        }

        //Copy configuration file
        $path = __DIR__.DS.'config';
        if(!file_exists($path.DS.'configuration.php'))
        {
            $handle = fopen($path.DS.'configuration.php', 'w');
            if(!$handle)
            {
                $errors2[] = "Configuation directory is not writeable. The module cannot create default configuration. Please set write permissions for {$path}";
            }
            else
            {
                fwrite($handle, file_get_contents($path . DS . 'configuration.php_new'));
            }
        }

        $errors = array_merge($errors1, $errors2);
        if(!empty($errors)) {
            throw new \Exception('Error: ' . implode(';',$errors));
        }
    }
    
    function deactivate(){
        return array(
            'status'=>'success'
        );
    }
    
    /**
     * Migration from 2.x to 3.0 and upgrades
     * 
     * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
     */
    function upgrade($vars)
    {
        $version = empty($vars['version']) ? $vars['ResellersCenter']['version'] : $vars['version'];
        
        if(version_compare($version, "3.0.0") < 0)
        {
            $errors = $this->performQueryFromFile("/core/database/3.0.0/schema.sql");
            if(!empty($errors)) {
                throw new \Exception('Error: ' . implode(';',$errors));
            }
            
            $migrator = new resources\Migrator();
            $migrator->performMigration();
        }
        
        if(version_compare($version, "3.0.1") < 0)
        {
            $this->performQueryFromFile("/core/database/3.0.1/upgrade-rc.sql");
            $this->addDefaultDocumentation();
        }
        
        if(version_compare($version, "3.1.0") < 0)
        {
            //Remove old hooks
            unlink(__DIR__ . DS . "core".DS."hooks".DS."InvoiceCreationPreEmail.php");
            
            $this->clearUnusedTables();
            $errors = $this->performQueryFromFile("/core/database/3.1.0/upgrade.sql");
            if(!empty($errors)) {
                throw new \Exception('Error: ' . implode(';',$errors));
            }
        }
        
        if(version_compare($version, "3.1.2") < 0)
        {
            //Remove old hooks
            unlink(__DIR__ . DS . "core".DS."hooks".DS."InvoiceCreationPreEmail.php");
        }

        //Enable AdminStoreServiceFilter
        if(version_compare($version, "3.5.0") < 0)
        {
            $module = new main\Core\Whmcs\AddonModules\AddonModule();
            $module->adminStoreServiceFilter = "on";
            $module->save();
        }

        //Enable AdminStoreServiceFilter
        if(version_compare($version, "3.5.2") < 0)
        {
            $errors = $this->performQueryFromFile("/core/database/3.5.2/upgrade.sql");
            if(!empty($errors))
            {
                throw new \Exception('Error: ' . implode(';',$errors));
            }
        }

        if (version_compare($version, "3.13.0") < 0) {

            $errors = $this->performQueryFromFile("/core/database/3.12.0/upgrade.sql");
            if (!empty($errors)) {
                throw new \Exception('Error: ' . implode(';',$errors));
            }
        }
    }
    
    /**
     * Helper to perform raw queries for module
     *
     * @param type $file
     * @return array
     */
    function performQueryFromFile($file = '')
    {
        $collation = $this->getWHMCSTablesCollation();
        $charset = $this->getWHMCSTablesCharset();

        $query = file_get_contents(__DIR__ . $file);
        $query = str_replace("#collation#", $collation, $query);
        $query = str_replace("#charset#", $charset, $query);
        
        $queries = explode(';', $query);
        $results = [];

        foreach($queries as $i => $query)
        {
            try
            {
                $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();

                if(!empty($query))
                {
                    $statement = $pdo->prepare($query);
                    $statement->execute();    
                }
            }
            catch(\PDOException $ex)
            {
                $results[] = sprintf('Query (%s/%s) in file `%s`, error: %s', ($i+1), count($queries)-1, $file, $ex->getMessage());
            }
        }

        return $results;
    }

    protected function getWHMCSTablesCharset()
    {
        require_once ROOTDIR . DS . 'configuration.php';
        global $db_name;

        $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();

        $query = $pdo->prepare("SELECT CCSA.character_set_name as Charset FROM information_schema.`TABLES` T,
            information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
            WHERE CCSA.collation_name = T.table_collation
            AND T.table_schema = :db_name
            AND T.table_name = 'tblclients';");

        $query->execute(['db_name' => $db_name]);
        $result = $query->fetchObject();

        return $result->Charset;
    }

    function getWHMCSTablesCollation()
    {
        $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
        $statement = $pdo->prepare("SHOW TABLE STATUS WHERE name = 'tblclients'");
        $statement->execute();
        $result = $statement->fetchObject();
        
        return $result->Collation;
    }
    
    function addDefaultDocumentation()
    {
        //Check if documentation table is empty
        $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
        $statement = $pdo->prepare("SELECT count(id) as count FROM ResellersCenter_Documentations");
        $statement->execute();
        $result = $statement->fetchObject();

        if($result->count == 0)
        {
            $query   = file_get_contents(__DIR__ . "/core/database/3.0.1/upgrade.sql");
            $content = file_get_contents(__DIR__."/resources/reseller_documentation/ManualForReseller.tpl");

            $query = str_replace("#name#", "ModulesGarden Manual", $query);
            $query = str_replace("#content#", htmlentities($content), $query);
            $query = str_replace("#pdfpath#", "modules/addons/ResellersCenter/storage/documentations/ManualForReseller.pdf", $query);

            $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
            $statement = $pdo->prepare($query);
            $statement->execute();   
        }
    }
    
    function clearUnusedTables()
    {
        $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
        $statement = $pdo->prepare("SELECT count(id) as count FROM ResellersCenter_Invoices");
        $statement->execute();
        $result = $statement->fetchObject();
        
        if($result->count == 0)
        {
            $queries = array(
                "DROP TABLE IF EXISTS `ResellersCenter_Transactions`;",
                "DROP TABLE IF EXISTS `ResellersCenter_Invoices`;",
                "DROP TABLE IF EXISTS `ResellersCenter_InvoiceItems`;"
            );
            
            foreach($queries as $query) 
            {
                $pdo = \Illuminate\Database\Capsule\Manager::connection()->getPdo();
                $statement = $pdo->prepare($query);
                $statement->execute();    
            }
        }
    }
    
    function isStorageWriteable()
    {
        //Check Storage prermission
        $path = __DIR__ .DS. 'storage';
        $iswirtable = is_writeable($path);
        
        if(!$iswirtable) {
            return false;
        }
        
        //Check subfolders
        $subfolders = array("cookies", "documentations", "logo");
        foreach($subfolders as $sub)
        {
            $path = __DIR__ .DS. 'storage'. DS. $sub;
            $iswirtable = is_writeable($path);
            
            if(!$iswirtable)
            {
                return false;
            }
        }
        
        return true;
    }
}

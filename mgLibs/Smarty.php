<?php

namespace MGModule\ResellersCenter\mgLibs;
use MGModule\ResellersCenter as main;
use MGModule\ResellersCenter\mgLibs\Lang;


/**
 * Smarty Wrapper
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */
class Smarty {
    static private $_instance;
    
    private $_smarty;
    private $_templateDIR;
    
    final private function __construct(){}
    final protected function __clone(){}
    
    /**
     * Get Single-top Instance
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return smarty
     */
    static function I(){
        if(empty(self::$_instance))
        {            
            self::$_instance = new self();
            
            if(!class_exists('Smarty'))
            {
                if(file_exists(ROOTDIR.DS.'includes'.DS.'smarty'.DS.'Smarty.class.php'))
                {
                    require_once(ROOTDIR.DS.'includes'.DS.'smarty'.DS.'Smarty.class.php');
                }
                else
                {
                    die('Smarty does not exists!');
                }
            }
            
            self::$_instance->_smarty = new \Smarty();
        }
        
        return self::$_instance;
    }
    
    /**
     * Set Tempalte Dir
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $dir
     */
    function setTemplateDir($dir)
    {
        if(is_array($dir))
        {
            throw new Exceptions\System('Wrong Template Path');
        }
        
        self::I()->_templateDIR = $dir;
    }
    
    /**
     * Get current template dir
     * 
     * @return type
     */
    public function getCurrentTemplateDir()
    {
        return self::I()->_templateDIR;
    }
    
    /**
     * Parse Template File
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @global string $templates_compiledir
     * @param string $template
     * @param array $vars
     * @param string $customDir
     * @return string
     * @throws exceptions\System
     */
    function view($template,$vars = array(), $customDir = false){
        if(is_array($customDir))
        {
            throw new Exceptions\System('Wrong Template Path');
        }
        
        global $templates_compiledir;
        if($customDir)
        {
            self::I()->_smarty->template_dir = $customDir;  
        }
        else
        {
            self::I()->_smarty->template_dir = self::I()->_templateDIR;
        }
        
        self::I()->_smarty->compile_dir = $templates_compiledir;
        self::I()->_smarty->force_compile = 1;
        self::I()->_smarty->caching = 0;

        $this->clear();
        
        self::I()->_smarty->assign('MGLANG', Lang::getInstance());

        if(!isset(self::I()->_smarty->registered_plugins['function']['lang']))
        {
            self::I()->_smarty->registerPlugin("function", "lang", ["\\WHMCS\\Smarty", "langFunction"]);
        }

        if(is_array($vars))
        {
            foreach($vars as $key => $val)
            {
                self::I()->_smarty->assign($key, $val);
            }
        }
        
        if(is_array(self::I()->_smarty->template_dir))
        {
            $file = self::I()->_smarty->template_dir[0].DS.$template.'.tpl';
        }
        else
        {
            $file = self::I()->_smarty->template_dir.DS.$template.'.tpl';
        }

        if(!file_exists($file))
        {
            throw new exceptions\System('Unable to find Template:'.$file);
        }
        
        try 
        {
            $result = self::I()->_smarty->fetch($template.'.tpl', uniqid()); 
            
            if($customDir) {
                self::I()->_smarty->template_dir = self::I()->_templateDIR;
            }
            
            return $result;
        }
        catch (\Exception $e)
        {
            logActivity("Resellers Center - Smarty Error: ".$e->getMessage());
            throw new exceptions\System('Smarty Error:'.$e->getMessage());
        }
    }
    
    protected function clear()
    {
        self::I()->_smarty->clearAllAssign();
    }
    
    public function fetchString($string, $params)
    {
        global $templates_compiledir;

        //Parse message
        $smarty = new \Smarty();
        $smarty->setCompileDir($templates_compiledir);

        //Inject params
        foreach ($params as $key => $value)
        {
            $smarty->assign($key, $value);
        }

        return $smarty->fetch('string:' . $string);
    }
}

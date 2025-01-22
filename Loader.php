<?php

namespace MGModule\ResellersCenter;
use MGModule\ResellersCenter as main;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

/**
 * Module Class Loader
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */

if(!class_exists(__NAMESPACE__.'\Loader')){
    class Loader {
        static $whmcsDir;
        static $myName;
        static $avaiableDirs = array();

        /**
         * Set Paths
         * 
         * @param string $dir
         */
        function __construct($dir = null){
            if(empty($dir))
            {
                $checkDirs = array(
                        'modules'.DS.'addons'.DS
                        ,'modules'.DS.'servers'.DS
                ); 

                self::$myName = substr(__NAMESPACE__, 9);

                foreach($checkDirs as $dir)
                {
                    if($pos = strpos(__DIR__,$dir.self::$myName))
                    {
                        self::$whmcsDir = substr(__DIR__,0,$pos);
                        break;
                    }
                }       

                if(self::$whmcsDir)
                {
                    foreach($checkDirs as $dir)
                    {
                        $tmp = self::$whmcsDir.$dir.self::$myName;
                        if(file_exists($tmp))
                        {
                            self::$avaiableDirs[] = $tmp.DS;
                        }
                    }
                }
            }
            else
            {
                self::$mainDir = $dir;
            }

            spl_autoload_register(array($this,'loader'));
            spl_autoload_register(array($this,'vendorLoader'));
        }

        /**
         * Load Class File
         * 
         * @author Michal Czech <michael@modulesgarden.com>
         * @param string $className
         * @return bool
         * @throws main\mgLibs\exceptions\base
         * @throws \Exception
         */
        static function loader($className){ 
            if(strpos($className, __NAMESPACE__) === false)
            {
                return;
            }
            
            $className = substr($className,strlen(__NAMESPACE__));

            $originClassName = $className;
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';
            if ($lastNsPos = strrpos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DS, $namespace).DS;
            }
            
            $fileName .= $className.'.php';

            $foundFile = false;
            $error = array();

            foreach(self::$avaiableDirs as $dir)
            {
                $tmp = self::isExistDir($dir . $fileName);
                if($tmp)
                {
                    if($foundFile)
                    {
                        //todo THROW ERROR FOR DEVELOPER
                    }
                    else
                    {
                        $foundFile = $tmp;
                    }
                }
            }

            if($foundFile)
            {
                require_once $foundFile;            
                
                if(!class_exists(__NAMESPACE__.$originClassName) && !interface_exists(__NAMESPACE__.$originClassName) && !trait_exists(__NAMESPACE__.$originClassName))
                {
                    $error['message'] = 'Unable to find class:'.$originClassName.' in file:'.$foundFile;
                    $error['code']    = main\mgLibs\exceptions\Codes::MISING_FILE_CLASS;
                }
            } 
            
            if($error)
            { 
                if(class_exists(__NAMESPACE__.'\mgLibs\exceptions\Base',false))
                {
                    throw new main\mgLibs\exceptions\Base($error['message'], $error['code']);
                }
                else
                {
                    throw new \Exception($error['message'], $error['code']);
                }
            }
            return true;
        }
        
        static function isExistDir($dir)
        {
            $arrayDir    = explode(DS, $dir);
            $lastElement = end($arrayDir);
            array_pop($arrayDir);
            $orgiDir     = '';
            $return      = true;
            $arrayDir = self::wrapToMainWhmcsDir($arrayDir);
            for ($index = 1; $index < count($arrayDir); $index++)
            {
                foreach (['', 'lcfirst', 'ucfirst'] as $function)
                {
                    $file = ($function == '') ? $arrayDir[$index] : $function($arrayDir[$index]);
                    if (is_dir($orgiDir . DS . $file))
                    {
                        if (count($arrayDir) == ($index + 1))
                        {
                            $path = $orgiDir . DS . $file . DS . $lastElement;
                            if (file_exists($path))
                            {
                                return $path;
                            }
                            $path = $orgiDir . DS . $file . DS . lcfirst($lastElement);
                            if (file_exists($path))
                            {
                                return $path;
                            }
                            $path = $orgiDir . DS . $file . DS . ucfirst($lastElement);
                            if (file_exists($path))
                            {
                                return $path;
                            }
                            return false;
                        }
                        else
                        {
                            $orgiDir .= DS . $file;
                        }
                        $return = true;
                        break;
                    }
                    else
                    {
                        $return = false;
                    }
                }
                if ($return == false)
                {
                    break;
                }
            }
            return false;
        }

        
        /**
         * Load Class from vendor directory
         * 
         * @param type $className
         * @return type
         */
        static function vendorLoader($className)
        {
            //Load only class that are not in RC namespace
            if(strpos($className, __NAMESPACE__) !== false) {
                return;
            }
            
            $originClassName = $className;
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';

            if($lastNsPos = strrpos($className, '\\'))
            {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DS, $namespace).DS;
            }

            $fileName .= str_replace('_', DS, $className) . '.php';

            $tmp = __DIR__.DS. "vendor" .DS. $fileName;
            if(! file_exists($tmp))
            {
                /**
                 * We can't do much about that. 
                 */
                return;
                
                //Only for debug!!
                //throw new \Exception("Class " . $className . " not found"); 
            }

            $error = array();
            require_once $tmp;            

            if(!class_exists($originClassName) && !interface_exists($originClassName))
            {
                $error['message'] = 'Unable to find class:'.$originClassName.' in file:'.$tmp;
                $error['code']    = main\mgLibs\exceptions\Codes::MISING_FILE_CLASS;
            }

            if($error)
            { 
                if(class_exists(__NAMESPACE__.'\mgLibs\exceptions\Base',false))
                {
                    throw new main\mgLibs\exceptions\Base($error['message'], $error['code']);
                }
                else
                {
                    throw new \Exception($error['message'], $error['code']);
                }
            }

            return true;
        }

        static function listClassesInNamespace($className){
            $originClassName = $className;
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';
            if ($lastNsPos = strrpos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }

            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);

            foreach(self::$avaiableDirs as $dir)
            {
                $tmp = $dir.$fileName;
                if(file_exists($tmp))
                {
                    $foundFile = $tmp;
                }
            }

            $files = array();

            if ($handle = opendir($foundFile)) {
                while (false !== ($entry = readdir($handle))) {
                    if (
                            $entry != "." 
                            && $entry != ".."
                            && strpos($entry,'.php') === (strlen($entry)-4)
                        ) {

                        $files[] = __NAMESPACE__.'\\'.$originClassName.'\\'.substr($entry, 0,strlen($entry)-4);
                    }
                }
                closedir($handle);
            }

            return $files;
        }
        
        static function wrapToMainWhmcsDir($arrayDir)
        {
            $newPath = [0 => $arrayDir[0], 1 => '']; 
            $mainDir = ROOTDIR; 
            $mainDirParts = explode(DS, $mainDir);

            for ($key = 1; $key < count($arrayDir); $key++)
            {                
                if($key === 1 && $arrayDir[$key] === '')
                {
                    $newPath[1] .= DS;
                    continue;                    
                }
                
                if ($arrayDir[$key] === $mainDirParts[$key])
                {
                    $newPath[1] .= (($newPath[1] === '' || $newPath[1] === DS) ? '' : DS) . $arrayDir[$key];

                    continue;
                }

                array_push($newPath, $arrayDir[$key]);
            }

            return $newPath;
        }        

        
    }
    
}

require_once __DIR__.DS.'vendor/omnipay-2.3/vendor/autoload.php';
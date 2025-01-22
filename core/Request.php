<?php
namespace MGModule\ResellersCenter\core;

/**
 * Description of Request
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Request
{
    /**
     * Get variable from request
     * This function uses 'dot' notation to access values from array
     * 
     * @param type $name
     * @param type $default
     * @return mixed
     */
    public static function get($name = null, $default = null)
    {
        if(empty($name))
        {
            return $_REQUEST;
        }
        
        if(strpos($name, ".") !== false)
        {
            $array = $_REQUEST;
            $path = explode(".", $name);
            foreach($path as $element)
            {
                if(!is_array($array) || !isset($array[$element])) {
                    return $default;
                }
                
                $array = $array[$element];
            }
            
            return $array;
        }
        
        return $_REQUEST[$name];
    }
    
    public static function set($name, $value)
    {
        $_REQUEST[$name] = $value;
    }

    public static function merge(array $params)
    {
        foreach ($params as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function clear($name)
    {
        unset($_REQUEST[$name]);
    }

    public static function exists($name)
    {
        return isset($_REQUEST[$name]);
    }
    
    /**
     * Get and parse datatable request
     * This function uses 'dot' notation to access values from array
     * 
     * @return type
     */
    public static function getDatatableRequest()
    {
        $result = new \stdClass();
        $result->filter     = self::get("sSearch");
        $result->limit      = self::get("iDisplayLength");
        $result->offset     = self::get("iDisplayStart");
        $result->orderBy    = self::get("iSortCol_0");
        $result->orderDir   = self::get("sSortDir_0");
        $result->filters    = self::get("filters");
        
        $result->columns = array();
        
        //Get columns from datatable request
        $exist = true;
        $counter = 0;
        while($exist)
        {
            $col = self::get("mDataProp_{$counter}");
            $counter++;
            
            if(!empty($col)){
                $result->columns[] = $col;
            }
            else{
                $exist = false;
            }
        }

        return $result;
    }
}

<?php
namespace MGModule\ResellersCenter\core\datatable;

/**
 * Description of DatatableAccordions
 * @TODO: Rewrite this to use smarty (.tpl) elemets!!!
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DatatableAccordions 
{
    private $format;

    public function parseData($data, $format)
    {
        $this->format = $format;
        $grouped = $this->groupBy($data);
        
        $result = array();
        foreach($grouped as $contents)
        {
            $result[] = $this->getMainRow($contents);
            foreach($contents as $content)
            {
                foreach(array_keys($content) as $key)
                {
                    if(!in_array($key, $this->format["contentCols"])){
                        $content[$key] = '';
                    }
                }
                
                $content["DT_RowClass"] = $content[$this->format["groupBy"]];
                $result[] = $content;
            }
        }
        
        return $result;
    }
        
    private function groupBy($data)
    {
        $result = array();
        foreach($data as $row)
        {
            $row = (array)$row;
            
            $groupBykey = $row[$this->format["groupBy"]];
            $result[$groupBykey][] = $row;
        }
        
        return $result;
    }
    
    /**
     * Get main row based on content and rows
     * 
     * @param type $data
     * @return type
     */
    private function getMainRow($data)
    {
        $result = array();
        //Init
        foreach($this->format["mainCols"] as $keys)
        {
            $result[$keys] = null;
        }
        
        //Fill
        foreach($data[0] as $key => $value)
        {
            if(in_array($key, $this->format["mainCols"])) {
                $result[$key] = $value;
            }
            else {
                $result[$key] = "-";
            }
        }
        
        //Get contetns IDs
        $ids = array();
        foreach($data as $values)
        {
            $ids[] = $values["id"];
        }
        
        $result["id"] = implode(",", $ids);
        $result["mainRow"] = true;
        return $result;
    }
}

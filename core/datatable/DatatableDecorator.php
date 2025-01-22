<?php
namespace MGModule\ResellersCenter\core\datatable;

use MGModule\ResellersCenter\core\datatable\DatatableAccordions;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\whmcs\Hostings;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\mgLibs\Smarty;

/**
 * Description of DatatableDecorator
 * @TODO: Rewrite this to use smarty (.tpl) elemets!!!
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DatatableDecorator 
{
    /**
     *  Parsed data
     * 
     * @var array
     */
    protected $result;
    
    /**
     *  Conatins contents format of columns
     *  array (
     *      "col_name" => 
     *          "link"  => array(
     *              <relid_key>  //class will search for all necesery link by itself.
     *              <type>       // product, client, hosting, etc
     *          )
     *          "lang"  => <value> or <array> //absolute ($_LANG['datatable][<value>]) or normal lang if array provided
     *          "class" => string of classes for <td> or array(array(<ifvalue>, <class_name>))
     *          "custom" => <value> // if custom then swap orginal value
     *          "default" => <value> //use <value> if field is empty
     *  
     *      "DT_RowId" => array(text => <value>, col => <col_name>) //add id to DT rows like <value><col_name_value>
     *      )
     * 
     * @var array 
     */
    protected $format;
    
    
    /**
     *  Conatins buttons for each row in action column
     * 
     * @var array 
     */
    protected $buttons;
    
    /**
     * Information reagarding accordion in databales
     * array(
     *    "groupBy" => array(<col_name>, <col_name>, ...)   //This is also what values should be displayed in main row
     *    "content" => array(<col_name>, <col_name>, ...);  //Columns that should be displayed in child row (empty = all)
     * )
     * 
     * @var array 
     */
    protected $accordion;
    
    /**
     *  @param $format
     */
    public function __construct($format = [], $buttons = [], $accordion = []) 
    {
        Smarty::I()->setTemplateDir(__DIR__.DS."elements");
        
        $this->format = $format;
        $this->buttons = $buttons;
        $this->accordion = $accordion;
    }
    
    /**
     * 
     * @param array $data
     * @param int $displayCount
     * @param int $totalCount
     */
    public function parseData($data, $displayCount = null, $totalCount = null)
    {
        $result = array();
        
        if(! empty($this->accordion)){
            $da = new DatatableAccordions();
            $data = $da->parseData($data, $this->accordion) ?: [];
        }

        $result["aaData"] = [];
        foreach($data as $row)
        {        
            $row = (array)$row;
            $parsed = $this->parseRow((array)$row);

            if(! empty($this->accordion) && ! $row["mainRow"]){
                $parsed["DT_RowClass"] = "hidden childRow {$row["DT_RowClass"]}";
            }
            else
            {
                $parsed["DT_RowClass"] = $row["DT_RowClass"];
            }
            
            if($this->format["DT_RowId"]) {
                $text = $this->format["DT_RowId"]["text"];
                $value = $row[$this->format["DT_RowId"]["col"]];
                $parsed["DT_RowId"] = $text.$value;
            }
            
            $result["aaData"][] = $parsed;
        }
        
        $result['iTotalDisplayRecords'] = $displayCount;
        $result['iTotalRecords']        = $totalCount;
        $result['sEcho']                = Request::get("sEcho");

        $this->result = $result;
    }
    
    public function getResult()
    {
        return $this->result;
    }

    public function parseRow($row)
    {
        $result = array();
        foreach($row as $colname => $value)
        {
            if($this->format[$colname]["custom"]) {
                $result[$colname] = $this->addTd($this->format[$colname]["custom"]);
                continue;
            }
            $originalValue = $value;
            
            //use default if extist and value is empty
            if($this->format[$colname]["default"] && $value == '')
            {
                $value = $this->format[$colname]["default"];
            }
           
            //Add language
            if(!empty($this->format[$colname]["lang"]))
            {
                $lang = $this->getLang($value, $this->format[$colname]["lang"], $this->format[$colname]["absolute"]);
                $value = Smarty::I()->view("lang", array("lang" => $lang, "value" => $value));
            }
            
            //Prefix & Suffix
            if(!empty($this->format[$colname]["prefix"]) || !empty($this->format[$colname]["suffix"]))
            {
                $value = $this->format[$colname]["prefix"] . $value . $this->format[$colname]["suffix"];
            }
            
            //Add Link
            $link = $this->generateLink($row, $this->format[$colname]["link"]);
            $value = Smarty::I()->view("link", array("link" => $link, "value" => $value));

            //Override
            if(!empty($this->format[$colname]["override"]))
            {
                if(is_callable($this->format[$colname]["override"]))
                {
                    $value = call_user_func($this->format[$colname]["override"], $row);
                }
                else
                {
                    $value = $row;
                }
            }

            //Add Column tags with class - the last part of parsing
            $value = Smarty::I()->view("column", array("class" => $this->format[$colname]["class"], "value" => $value, "originalValue" => $originalValue));
            
            $result[$colname] = $value;
        }

        if(! empty($this->buttons)) {
            $result["actions"] = $this->addButtons($row);
        }

        return $result;
    }
    
    public function orderBy($data, $dtRequest)
    {
        usort($data, function($obA, $obB) use($dtRequest) 
        {
            $orderBy = $dtRequest->columns[$dtRequest->orderBy];
            
            if($dtRequest->orderDir == 'asc'){
                return strnatcmp($obA[$orderBy], $obB[$orderBy]);
            }
            else {
                return strnatcmp($obB[$orderBy], $obA[$orderBy]);
            }
        });
        
        return $data;
    }

    /**
     * Add link to column 
     * 
     * @param $row array 
     * @param $link array( "0" => <relid>
     *                     "1" => <type> (product, client, hosting, etc))
     */
    protected function generateLink($row, $data)
    {
        $relid = $row[$data[0]];
        $invoiceType = $data[2] ? $row[$data[2]] : null;

        if (empty($relid)) {
            return "";
        }

        $type = $data[1];

        if ($type == 'invoice' && is_string($invoiceType) && strtolower($invoiceType) == 'reseller') {
            $type = 'resellerinvoice';
        }

        switch($type)
        {
            case 'product':
                $link = "configproducts.php?action=edit&id={$relid}";
                break;
            case 'hosting':
                $repo = new Hostings();
                $hosting = $repo->find($relid);
                
                $link = "clientsservices.php?userid={$hosting["userid"]}&id={$relid}";
                break;
            case 'client':
                $link = "clientssummary.php?userid={$relid}";
                break;
            case 'reseller':
                $link = 'addonmodules.php?module=ResellersCenter&mg-page=resellers&mg-action=details&customHTML=1&rid='.$relid;
                break;
            case 'addon':
                $link = "configaddons.php?action=manage&id={$relid}";
                break;
            case 'invoice':
                $link = "invoices.php?action=edit&id={$relid}";
                break;
            case 'resellerinvoice':
                $link = "";
                break;
            case 'quote':
                $link = "quotes.php?action=manage&id={$relid}";
                break;
            case 'transaction':
                $link = "transactions.php?action=edit&id={$relid}";
                break;
            case 'ticket':
                $link = "supporttickets.php?action=view&id={$relid}";
                break;
            case 'ticketdepartment':
                $link = "configticketdepartments.php?action=edit&id={$relid}";
                break;
            case 'domain':
                $link = "//{$relid}";
                break;
            case 'domainedit':
                $repo = new Domains();
                $domain = $repo->find($relid);
                
                $link = "clientsdomains.php?userid={$domain->userid}&id={$domain->id}";
                break;
            case 'domainpricing':
                $link = "configdomains.php?action=editpricing&id={$relid}";
                break;
            case 'hostingaddon':
                $repo = new HostingAddons();
                $hostingAddon = $repo->find($relid);
                
                $link = "clientsservices.php?userid={$hostingAddon->userid}&id={$hostingAddon->hostingid}&aid={$hostingAddon->addonid}";
                break;
            case 'knowledgebase':
                $link = "supportkb.php?action=edit&id={$relid}";
                break;
            case 'download':
                $link = "supportdownloads.php?action=edit&id={$relid}";
                break;
            case 'dlcategory':
                $link = "supportdownloads.php?catid={$relid}";
                break;
            case 'announcement':
                $link = "supportannouncements.php?action=manage&id={$relid}";
                break;
            case 'promotion_rc':
                $link = "index.php?m=ResellersCenter&mg-page=promotions&mg-action=details&id={$relid}";
                break;
            case 'servicefix':
                $link = 'addonmodules.php?module=ResellersCenter&mg-page=debug&mg-action=fixservice&';
                break;
            default:
                $link = '';
        }

        return $link;
    }
    
    protected function getLang($text, $path, $absolute = false)
    {
        if(empty($text) && $text != 0)
        {
            return;
        }

        if(is_array($path))
        {
            array_push($path, $text);
            $method = $absolute ? "\MGModule\ResellersCenter\mgLibs\Lang::absoluteT" : "\MGModule\ResellersCenter\mgLibs\Lang::T";
            $lang   = call_user_func_array($method, $path);
        }
        else
        {
            $lang = Lang::absoluteT(array("datatable", $text));
        }

        return $lang;
    }
    
    protected function addButtons($row)
    {
        $result = null;
        if(empty($this->accordion))
        {
            $result = $this->parseButtons($row);
        }
        elseif(key_exists("actions", $row))
        {
            $result = $this->parseButtons($row);
        }
        else
        {
            $result = '';
        }
        
        return $result;
    }
    
    protected function parseButtons($row)
    {
        $buttons = '';
        foreach($this->buttons as $button)
        {
            if($this->skipButton($button["if"], $row)){
                continue;
            }
            
            if($button["href"]) {
                $button["link"] = $this->generateLink($row, $button["href"]);
            }

            $data  = '';
            foreach($button["data"] as $name => $valueKey)
            {
                $data .= "data-{$name}='{$row[$valueKey]}'";
            }

            //Use <a> tag instead of <button>
            $button = array_merge($button, array("data" => $data));
            $buttons .= $button["href"] ? Smarty::I()->view("href", $button) : Smarty::I()->view("button", $button);
        }
        
        return "<td class='datatableActions'>{$buttons}</td>";
    }
    
    protected function skipButton($statement, $data)
    {
        if(is_array($statement[0])) 
        {
            $result = true;
            foreach($statement as $if)
            {
                switch($if[1])
                {
                    case "!=":
                        $result = ($data[$if[0]] != $if[2] ? false : true);
                        break;
                    case "==":
                        $result = ($data[$if[0]] == $if[2] ? false : true);
                        break;
                }
            }
            
            return $result;
        }
        else
        {
            if($data[$statement[0]] != $statement[1])
            {
                return true;
            }
        }
        
        return false;
    }
   
    
    protected function addTd($value)
    {
        return "<td>{$value}</td>";
    }
}

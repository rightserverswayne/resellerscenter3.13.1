<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Contents;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\ResellersPricing;

/**
 * Description of AbstractContents
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractContents extends AbstractContentsIterator
{
    /**
     * Reseller object
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     */
    protected $reseller;
    
    /**
     * Currency model
     *
     * @var \MGModule\ResellersCenter\models\whmcs\Currency
     */
    protected $currency;
    
    /**
     * Array with loaded objects
     *
     * @var mixed
     */
    protected $contents;
    
    /**
     * Init content list
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     */
    public function __construct(\MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {
        $this->reseller = $reseller;
    }
    
    /**
     * Add currency to filter objects
     * 
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        
        //Reset contents - we have to reload it
        $this->contents = []; 
        $this->ids = [];
    }

    /**
     * Get Object by id
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function __get($id)
    {
        //Load contents if not loaded
        $this->contents ?: $this->load();
        $selected = $this->contents[$id];
        
        if(!$selected->exists)
        {
            throw new \Exception("Unable to find {$this->getSelfType()} with id {$id}");
        }
        
        return $selected;
    }

    /**
     * Check if object is assigned to reseller
     *
     * @param $id
     * @return bool
     */
    public function __isset($id)
    {
        //Load contents if not loaded
        $this->contents ?: $this->load();

        return !empty($this->contents[$id]);
    }
    
    /**
     * Return true if reseller does not has any configured objects
     * 
     * @return type
     */
    public function isEmpty()
    {
        $this->contents ?: $this->load();
        return empty($this->contents);
    }
    
    public function search($search, $columns)
    {
        //Load if not loaded
        $this->contents ?: $this->load();
        
        if(empty($search))
        {
            return $this->contents;
        }
        
        //filter
        $result = [];
        foreach($this->contents as $content)
        {
            foreach($columns as $col)
            {
                //If any col match search term
                if(strpos(strtolower($content->{$col}), strtolower($search)) !== false)
                {
                    //add content to result
                    $result[] = $content;
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Load content objects
     */
    protected function load()
    {
        $repo = new ResellersPricing();
        $contents = $repo->getConfiguredByType($this->reseller->id, $this->getSelfType(), $this->currency->id);
        
        foreach($contents as $content)
        {
            $this->ids[] = $content->relid;
            $this->contents[$content->relid] = $this->getContentObject($content->relid);
        }
    }
    
    /**
     * Get Content object
     * 
     * @param int $id
     * @return \MGModule\ResellersCenter\Core\Whmcs\Products\{$type}
     */
    protected function getContentObject($id)
    {
        $typeS = strtolower((new \ReflectionClass($this))->getShortName());
        $type = ucfirst(trim($typeS, "s"));
        
        $namespace = "\\MGModule\\ResellersCenter\\Core\\Whmcs\\Products";
        $classname = "{$namespace}\\{$typeS}\\{$type}";
        
        return new $classname($id, $this->reseller);
    }

    /**
     * Get type of current object
     * 
     * @return type
     */
    private function getSelfType()
    {
        $type = (new \ReflectionClass($this))->getShortName();
        return strtolower(trim($type, "s"));
    }
}
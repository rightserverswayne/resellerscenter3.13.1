<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain as DomainService;

/**
 * Description of Domains
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domains extends AbstractContents
{
    protected function getModelClass()
    {
        return "\MGModule\ResellersCenter\Models\Whmcs\Domain";
    }
    
    public function getServiceObject($idOrTld, $type)
    {
        return new DomainService($idOrTld, $this->reseller, $type);
    }

    /**
     * Get all domains
     *
     * @return mixed
     */
    public function getAll()
    {
        if($this->contents == null)
        {
            $this->load();
        }

        return $this->contents;
    }
    
    /**
     * Get first domain from list
     */
    public function getFirst()
    {
        if($this->contents == null)
        {
            $this->load();
        }
        
        return array_shift(array_values($this->contents));
    }

    /**
     * Load content objects
     */
    protected function load()
    {
        $repo = new ResellersPricing();
        $contents = $repo->getConfiguredDomains($this->reseller->id, $this->currency->id);
        
        foreach($contents as $content)
        {
            $this->ids[] = $content->relid;
            $this->contents[$content->relid] = $this->getContentObject($content->relid);
        }
    }
}

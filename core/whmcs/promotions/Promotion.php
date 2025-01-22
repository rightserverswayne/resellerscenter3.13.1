<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;
use MGModule\ResellersCenter\repository\whmcs\Promotions;
use MGModule\ResellersCenter\models\whmcs\Promotion as PromotionModel;

use MGModule\ResellersCenter\Core\Whmcs\Products\products\Product;
use MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Products\addons\Addon;

/**
 * Description of Promotion
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotion extends WhmcsObject
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Get model class
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\Models\Whmcs\Promotion::class;
    }

    /**
     * Init promotion object
     *
     * @param $code
     */
    public function __construct($idOrModal, $code = null)
    {
        if(empty($idOrModal))
        {
            $repo = new Promotions();
            $idOrModal = $repo->getByCode($code);
        }

        parent::__construct($idOrModal);
    }

    /**
     * Override get to retrieve appliesto and requires variables in correct format
     *
     * @param $name
     * @return array|mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        switch ($name)
        {
            case "upgradeconfig":
                $result = unserialize($value);
                break;
            case "appliesto":
            case "requires":
                $result = explode(",", $value);
                break;
            case "type":
                $result = $this->getTypeObject();
                break;
            default:
                $result = $value;
        }

        return $result;
    }

    /**
     * Set correct format for appliesto and requires
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $value = (($name == "appliesto" || $name == "requires") && is_array($value)) ? implode(",", $value) : $value;
        $value = is_array($value) ? serialize($value) : $value;
        parent::__set($name, (string)$value);
    }

    /**
     * Get promotion validator
     *
     * @param $promotion
     * @return Validator
     */
    public function getValidator()
    {
        $result = $this->validator ?: new Validator($this);
        return $result;
    }

    /**
     * Get contents from reseller store that this promotion applies to
     *
     * @return type
     */
    public function getAppliesTo()
    {
        $result = [];
        foreach($this->appliesto as $element)
        {
            if(!empty($element))
            {
                $result[$element] = $this->getProduct($element);
            }
        }

        return $result;
    }

    /**
     * Get contents from reseller store that are required by this promotion
     *
     * @return type
     */
    public function getRequires()
    {
        $result = [];
        foreach($this->requires as $element)
        {
            if(!empty($element))
            {
                $result[$element] = $this->getProduct($element);
            }
        }

        return $result;
    }

    /**
     * Get Discount amount
     * THIS METHOD IS NOT VALIDATING PROMOTION
     * 
     * @param Product|Domain $product
     * @param Currency $currency
     * @return int
     */
    public function getDiscountAmount($product, $billincycle, Currency $currency)
    {
        $discount = 0;
        $price = $product->getPricing($currency)->getBrandedPrice($billincycle);
        switch($this->type)
        {
            case Promotions::TYPE_FIXED:
                $discount = $price - $this->value;
                break;
            case Promotions::TYPE_OVERRIDE:
                $discount = $price - $this->value;
                break;
            case Promotions::TYPE_PERCENTAGE:
                $discount = $price * ($this->value / 100);
                break;
            case Promotions::TYPE_FREE_SETUP:
                $discount = $product->getPricing($currency)->getBrandedPrice(\MGModule\ResellersCenter\repository\whmcs\Pricing::SETUP_FEES[$billincycle]);
                break;
        }
        
        return ($discount > 0) ? $discount : 0;
    }
    
    /**
     * Update or create record in database
     * 
     * @param type $data
     */
    public function updateOrCreate($data)
    {
        $this->model = new PromotionModel();
        empty($data["id"]) ? : $this->load();

        //Fill checkbox values with 0s if not set
        $data["recurring"]      = $data["recurring"] ? : 0;
        $data["applyonce"]      = $data["applyonce"] ? : 0;
        $data["newsignups"]     = $data["newsignups"] ? : 0;
        $data["lifetimepromo"]  = $data["lifetimepromo"] ? : 0;
        $data["onceperclient"]  = $data["onceperclient"] ? : 0;
        $data["existingclient"] = $data["existingclient"] ? : 0;
        
        //cycles as string
        $data["cycles"] = $data["cycles"] ? implode(",", $data["cycles"]) : "";
        
        //Clear config if necessery
        $data["upgrades"] = $data["upgrades"] ?: 0;
        $data["appliesto"] = $data["appliesto"] ?: "";
        $data["requires"] = $data["requires"] ?: "";

        //Assign vars
        foreach ($data as $name => $value)
        {
            $this->{$name} = $value;
        }

        //save in database and reload
        $this->save();
        $this->id = $this->model->id;

        $this->load();
    }

    /**
     * Get promotion type object
     *
     * @return Type
     */
    private function getTypeObject()
    {
        $map =
        [
            Promotions::TYPE_FIXED      => "Fixed",
            Promotions::TYPE_FREE_SETUP => "FreeSetupFee",
            Promotions::TYPE_OVERRIDE   => "Override",
            Promotions::TYPE_PERCENTAGE => "Percentage",
        ];

        $this->load();
        $namespace = "\\MGModule\\ResellersCenter\\Core\\Whmcs\\Promotions\\Types\\";
        $classname = $map[$this->model->type];

        $class = "{$namespace}{$classname}";
        $type = new $class($this);

        return $type;
    }

    /**
     * Get Content
     *
     * @param type $id
     * @return Domain|Addon|Product
     */
    private function getProduct($id)
    {
        if(is_numeric($id))
        {
            return new Product($id);
        }
        elseif(substr($id, 0, 1) == "A")
        {
            $aid = substr($id, 1);
            return new Addon($aid);
        }
        elseif(substr($id, 0, 1) == "D")
        {
            $extension = substr($id, 1);
            return new Domain($extension);
        }
    }

}
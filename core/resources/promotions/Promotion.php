<?php
namespace MGModule\ResellersCenter\Core\Resources\Promotions;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use \MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion as BasePromotion;
/**
 * Description of PromotionRC
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotion extends BasePromotion
{
    const PREFIX = "RC_#_";

    protected $reseller;

    /**
     * Get reseller promotion by promo code
     *
     * @param $code
     * @param Reseller $reseller
     * @return Promotion
     */
    public static function getByCode($code, Reseller $reseller)
    {
        return new Promotion(null, $code, $reseller);
    }

    /**
     * Promotion constructor.
     *
     * @param $id
     * @param $code
     * @param Reseller $reseller
     */
    public function __construct($id, $code, Reseller $reseller)
    {
        $this->reseller = $reseller;
        parent::__construct($id, $this->getPrefix() . $code);
    }

    /**
     * @param $name
     * @return array|mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        //hide prefix
        $value = ($name == "code") ? str_replace($this->getPrefix(), "", $value) : $value;
        return $value;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        //Add prefix do promotion code
        $value = ($name == "code") ? $this->getPrefix() . $value : $value;
        parent::__set($name, $value);
    }

    /**
     * Get promotion code with prefix
     *
     * @param null $id
     * @return mixed
     */
    public function getPrefix()
    {
        $prefix = str_replace("#", $this->reseller->id, self::PREFIX);
        return $prefix;
    }

    /**
     * Get promotion code with reseller prefix
     *
     * @return mixed
     */
    public function getFullCode()
    {
        return $this->model->code;
    }

}
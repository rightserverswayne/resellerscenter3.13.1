<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Types;

use MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion;

/**
 * Description of Type
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class Type
{
    /**
     * Promotion type system name
     *
     * @var String
     */
    protected $sysname;

    /**
     * @var Promotion
     */
    public $promotion;

    /**
     * Type constructor.
     *
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    public function __toString()
    {
        return $this->promotion->getAttributes()['type'];
    }

    /**
     * Get promotion value
     *
     * @return mixed
     */
    abstract function getPromoValue($recurring, $setupfee);
}
<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Types;

use MGModule\ResellersCenter\repository\whmcs\Promotions;

/**
 * Description of Percentage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Percentage extends Type
{
    /**
     * Promotion type system name
     *
     * @var String
     */
    protected $sysname = Promotions::TYPE_PERCENTAGE;

    /**
     * Get discount amount
     *
     * @param $recurring
     * @param $setupfee
     * @return array|mixed
     */
    public function getPromoValue($recurring, $setupfee)
    {
        $rate = $this->promotion->value   / 100;

        return
        [
            "recurring" => $recurring * $rate,
            "setupfee"  => $setupfee  * $rate,
        ];
    }
}
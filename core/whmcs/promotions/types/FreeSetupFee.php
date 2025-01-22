<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Types;

use MGModule\ResellersCenter\repository\whmcs\Promotions;

/**
 * Description of FreeSetupFee
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class FreeSetupFee extends Type
{
    /**
     * Promotion type system name
     *
     * @var String
     */
    protected $sysname = Promotions::TYPE_FREE_SETUP;

    /**
     * Get discount amount
     *
     * @param $prices
     * @return array|mixed
     */
    public function getPromoValue($recurring, $setupfee)
    {
        return
        [
            "recurring" => 0,
            "setupfee"  => $setupfee,
        ];
    }
}
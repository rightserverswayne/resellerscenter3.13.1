<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Types;

use MGModule\ResellersCenter\repository\whmcs\Promotions;

/**
 * Description of Override
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Override extends Type
{
    /**
     * Promotion type system name
     *
     * @var String
     */
    protected $sysname = Promotions::TYPE_OVERRIDE;

    /**
     * Get discount amount
     *
     * @param $prices
     * @return array|mixed
     */
    public function getPromoValue($recurring, $setupfee)
    {
        $discount = $recurring + $setupfee - $this->promotion->value;

        $res1   = $recurring - $discount;
        $res2   = 0;

        if($res1 < 0)
        {
            $res2 = abs($res1);
            $res1 = $recurring;
        }

        return
        [
            "recurring" => $res1,
            "setupfee"  => $res2,
        ];
    }
}
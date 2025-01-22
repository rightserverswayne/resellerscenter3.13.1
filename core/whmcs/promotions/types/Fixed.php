<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Types;

use MGModule\ResellersCenter\repository\whmcs\Promotions;

/**
 * Description of Fixed
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Fixed extends Type
{
    /**
     * Promotion type system name
     *
     * @var String
     */
    protected $sysname = Promotions::TYPE_FIXED;

    /**
     * Get discount amount
     *
     * @param $prices
     * @return array|mixed
     */
    public function getPromoValue($recurring, $setupfee)
    {
        $result["setupfee"]  = 0;
        $result["recurring"] = $this->promotion->value;

        //If promotion amount is bigger than recurring price apply what is left from discount to setupfee
        if($recurring < $this->promotion->value)
        {
            //Set max promotion to recurring amount
            $result["recurring"] = $recurring;
            $result["setupfee"]  = $setupfee - ($this->promotion->value - $recurring);
        }

        return
        [
            "recurring" => $result["recurring"]   > 0 ? $result["recurring"]    : 0,
            "setupfee"  => $result["setupfee"]    > 0 ? $result["setupfee"]     : 0,
        ];
    }
}
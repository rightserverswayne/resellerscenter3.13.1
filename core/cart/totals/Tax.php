<?php
namespace MGModule\ResellersCenter\Core\Cart\Totals;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;

/**
 * Description of Tax
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tax
{
    /**
     * Tax 1 rate
     *
     * @var float
     */
    protected $rate1;

    /**
     * Tax 2 rate
     *
     * @var float
     */
    protected $rate2;

    /**
     * @var boolean
     */
    protected $inclusive;

    /**
     * @var boolean
     */
    protected $compound;

    /**
     * Tax constructor.
     *
     * @param $rate1
     * @param $rate2
     */
    public function __construct($rate1, $rate2)
    {
        $this->rate1 = $rate1 / 100;
        $this->rate2 = $rate2 / 100;
    }

    /**
     * Get tax value for provided prices
     *
     * @param $prices
     * @param $subtotal
     * @return array
     */
    public function getAndApply(&$prices, &$subtotal)
    {
        $taxType  = Whmcs::getConfig("TaxType");
        $compound = Whmcs::getConfig("TaxL2Compound");

        if($taxType == "Inclusive")
        {
            if ($compound)
            {
                 $res1 = $this->getInclusiveCompoundTax($prices["today"]);
                 $res2 = $this->getInclusiveCompoundTax($prices["setupfee"]);

                 $subtotal -= $res1["tax1"] + $res2["tax1"] + $res1["tax2"] + $res2["tax2"];
            }
            else
            {
                $res1 = $this->getInclusiveTax($prices["today"]);
                $res2 = $this->getInclusiveTax($prices["setupfee"]);

                $subtotal -= $res1["tax1"] + $res2["tax1"] + $res1["tax2"] + $res2["tax2"];
            }
        }
        else
        {
            if ($compound)
            {
                $res1 = $this->getExclusiveCompoundTax($prices["today"]);
                $res2 = $this->getExclusiveCompoundTax($prices["setupfee"]);

                //Apply tax to recurring value
                $this->getExclusiveCompoundTax($prices["recurring"][key($prices["recurring"])], true);
            }
            else
            {
                $res1 = $this->getExclusiveTax($prices["today"]);
                $res2 = $this->getExclusiveTax($prices["setupfee"]);

                //Apply tax to recurring value
                $this->getExclusiveTax($prices["recurring"][key($prices["recurring"])], true);
            }
        }

        return
        [
            "tax1" => $res1["tax1"] + $res2["tax1"],
            "tax2" => $res1["tax2"] + $res2["tax2"]
        ];
    }

    /**
     * Get compound tax values
     *
     * @param $price
     * @return array
     */
    protected function getInclusiveCompoundTax(&$price)
    {
        //Tax2
        $tax2 = format_as_currency($price - $price / (1 + $this->rate2));
        $price -= $tax2;

        //Tax1
        $tax1 = format_as_currency($price - $price / (1 + $this->rate1));
        $price -= $tax1;

        return
        [
            "tax1" => $tax1,
            "tax2" => $tax2
        ];
    }

    /**
     * Get Inclusive tax but not compound
     *
     * @param $price
     * @return array
     */
    protected function getInclusiveTax(&$price)
    {
        $base = $price / (1 + ($this->rate1 + $this->rate2));

        $tax1 = format_as_currency($base * $this->rate1);
        $tax2 = format_as_currency($base * $this->rate2);

        $price = $price - $tax1 - $tax2;

        return
        [
            "tax1" => $tax1,
            "tax2" => $tax2
        ];
    }

    /**
     * Get Exclusive compound tax
     *
     * @param $price
     * @param $apply
     * @return array
     */
    protected function getExclusiveCompoundTax(&$price, $apply = false)
    {
        $tax1   = $price * $this->rate1;
        $tax2   = ($price + $price * $this->rate1) * $this->rate2;

        if($apply)
        {
            $price += $tax1 + $tax2;
        }

        return
        [
            "tax1" => $tax1,
            "tax2" => $tax2
        ];
    }

    /**
     * Get exclusive tax
     *
     * @param $price
     * @param $apply
     * @return array
     */
    protected function getExclusiveTax(&$price, $apply = false)
    {
        $tax1 = $price * $this->rate1;
        $tax2 = $price * $this->rate2;

        if($apply)
        {
            $price += $tax1 + $tax2;
        }

        return
        [
            "tax1" => $tax1,
            "tax2" => $tax2
        ];
    }
}
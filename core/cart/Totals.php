<?php
namespace MGModule\ResellersCenter\Core\Cart;

use MGModule\ResellersCenter\Core\Cart\Totals\Discount;
use MGModule\ResellersCenter\Core\Cart\Totals\Products;
use MGModule\ResellersCenter\Core\Cart\Totals\Tax;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

class Totals
{
    use HasProperties;

    /**
     * @var Products
     */
    protected $products;

    /**
     * @var Reseller
     */
    protected $reseller;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @var Tax
     */
    protected $tax;

    /**
     * Set Reseller Object
     *
     * @param Reseller $reseller
     * @return $this
     */
    public function setReseller(Reseller $reseller)
    {
        $this->reseller = $reseller;
        $this->products = new Products($reseller);

        return $this;
    }

    /**
     * Set Client object
     *
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client   = $client;
        $this->currency = $client->getCurrency();

        return $this;
    }

    /**
     * Set Currency Object
     *
     * @param Currency $currency
     * @return $this
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Set Promotion object
     *
     * @param string $promocode
     * @return $this
     */
    public function setPromotion($promocode)
    {
        if($promocode)
        {
            $promotion      = new Promotion(null, $promocode);
            $this->discount = new Discount($promotion, $this->client);
        }

        return $this;
    }

    /**
     * Set tax rates
     *
     * @param $tax1
     * @param $tax2
     * @return Totals
     */
    public function setTaxRates($tax1, $tax2)
    {
        $this->tax = new Tax($tax1, $tax2);

        return $this;
    }

    /**
     * Get cart totals
     */
    public function getCartTotal()
    {
        $result = $this->getEmptyTotalArray();
        foreach($this->products->getAll() as $item) {
            $item->qty = $item->qty ?: 1;

            //Get product prices
            $prices = $item->getPrices($this->currency);
            $result["subtotal"] += $item->qty * ($prices["today"] + $prices["setupfee"]);

            //Get discount for current item
            if (!empty($this->discount)) {
                //Returns array of 0s if does not apply to product
                $discount = $this->discount->getAndApply($item, $prices);
                $result["discountraw"] += $discount["recurring"] + $discount["setupfee"];
                $result["discount"] = $result["discountraw"];
            }

            //Apply tax
            if ($item->isTaxable() && !$this->client->taxexempt) {
                $tax = $this->tax->getAndApply($prices, $result["subtotal"]);
                $result["taxtotal"] += $tax["tax1"];
                $result["taxtotal2"] += $tax["tax2"];
                $result["total"] += $tax["tax1"] + $tax["tax2"];
            }

            //Summarize
            $result["total"] += $item->qty * ($prices["today"] + $prices["setupfee"]);
            $result["totalrecurring".key($prices["recurring"])] += $item->qty * current($prices["recurring"]);
        }

        return $this->getTotalBeautified($result);
    }

    /**
     * Get renewal total info
     *
     * @return array
     */
    public function getRenewalsTotal()
    {
        return
        [
            "renewals" => $this->products->getRenewalsTotal($this->currency)
        ];
    }

    /**
     * Get empty total array
     *
     * @return array
     */
    protected function getEmptyTotalArray()
    {
        $result =
        [
            "subtotal"      => 0,
            "total"         => 0,
            "taxtotal"      => 0,
            "taxtotal2"     => 0,
            "discount"      => 0,
            "discountraw"   => 0
        ];

        foreach(Pricing::BILLING_CYCLES as $billincycle)
        {
            $result["totalrecurring{$billincycle}"] = 0;
        }

        return $result;
    }

    /**
     * Get total array with prices in currency format
     *
     * @param $array
     * @return mixed
     */
    protected function getTotalBeautified($array)
    {
        foreach($array as $key => $element)
        {
            if(is_array($element))
            {
                $array[$key] = $this->getTotalBeautified($element);
            }
            else
            {
                if($array[$key] || in_array($key, ["subtotal", "total"]))
                {
                    $array[$key] = formatCurrency($element, $this->currency->id);
                }
                else
                {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    /**
     * Get tax rate in float number
     *
     * @param $rate
     * @return float|int
     */
    protected function getStandardizedTaxRate($rate)
    {
        return is_int($rate) ? $rate / 100 : $rate;
    }

    /**
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        return
        [
            "products" =>
            [
                "class"  => Products::class,
                "parent" => $this->reseller
            ],
        ];
    }
}



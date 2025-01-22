<?php
namespace MGModule\ResellersCenter\Core\Cart\Totals;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion;

/**
 * Description of Promotion
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Discount
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Promotion
     */
    protected $promotion;

    /**
     * @var boolean
     */
    protected $used;

    /**
     * Discount constructor.
     *
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion, Client $client)
    {
        $this->client    = $client;
        $this->promotion = $promotion;
    }

    /**
     * Get discount on provided product and apply it to its price
     *
     * @param $product
     * @param $prices
     * @return array
     */
    public function getAndApply($product, &$prices)
    {
        $discount = $this->getProductDiscount($product, $prices);
        $prices["today"]    -= $discount["recurring"];
        $prices["setupfee"] -= $discount["setupfee"];

        if ($this->promotion->recurring)
        {
            $key = key($prices["recurring"]);
            $prices["recurring"][$key] -= $discount["recurring"];
        }

        return $discount;
    }

    /**
     * Get discount for product
     *
     * @param $product
     * @param $prices
     * @return array
     */
    protected function getProductDiscount($product, $prices)
    {
        $discount =
        [
            "recurring" => 0,
            "setupfee" => 0,
        ];

        //Skip if promo was already used in cart and settings does not allow us to use it again
        if(!$this->promotion->applyonce || !$this->used)
        {
            //Check if promotion applies to product
            if ($this->promotion->getValidator()->check($product))
            {
                $this->used = true;
                $discount   = $this->promotion->type->getPromoValue(current($prices["recurring"]), $prices["setupfee"]);
            }
        }

        return $discount;
    }
}
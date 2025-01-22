<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation;

use MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion;

/**
 * Description of Rule
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class Rule
{
    /**
     * @var Promotion
     */
    protected $promotion;

    /**
     * Rule constructor.
     *
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Run rule check on a product
     *
     * @param $product
     * @return mixed
     */
    abstract public function run($product);
}
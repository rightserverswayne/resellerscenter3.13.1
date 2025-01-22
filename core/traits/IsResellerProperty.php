<?php

namespace MGModule\ResellersCenter\Core\Traits;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

/**
 * Description of IsResellerProperty
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait IsResellerProperty
{
    /**
     * Property object parent
     *
     * @var
     */
    protected $reseller;

    /**
     * IsResellerProperty constructor.
     *
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Method for object that need to initialize themselves
     */
    protected function initIsProperty(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }
}
<?php

namespace MGModule\ResellersCenter\Core\Traits;

/**
 * Description of IsResellerProperty
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait IsObjectProperty
{
    /**
     * Property object parent
     *
     * @var
     */
    protected $parent;

    /**
     * Method for object that need to initialize themselves
     */
    protected function initIsObjectProperty($parent)
    {
        $this->parent = $parent;
    }
}
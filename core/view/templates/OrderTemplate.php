<?php
namespace MGModule\ResellersCenter\Core\View\Templates;

/**
 * Description of OrderTemplate
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderTemplate extends Template
{
    public function hasCheckout()
    {
        $files = $this->getFiles();
        return in_array("checkout.tpl", $files);
    }
}

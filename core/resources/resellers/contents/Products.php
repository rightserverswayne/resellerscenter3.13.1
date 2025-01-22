<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Contents;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Products extends AbstractContents
{
    protected function getModelClass()
    {
        return "\MGModule\ResellersCenter\Models\Whmcs\Product";
    }
}
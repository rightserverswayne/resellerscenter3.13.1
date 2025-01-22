<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Contents;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addons extends AbstractContents
{
    protected function getModelClass()
    {
        return "\MGModule\ResellersCenter\Models\Whmcs\Addon";
    }
}

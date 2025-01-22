<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Contacts;

use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

/**
 * Description of Contact
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contact extends WhmcsObject
{
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Contact::class;
    }
}
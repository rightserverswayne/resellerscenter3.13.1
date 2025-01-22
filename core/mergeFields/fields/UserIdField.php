<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Users;

class UserIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        $reseller = new Reseller($args['resellerId']);
        $userRepo = new Users();
        $user = $userRepo->find($value);

        $result['reset_password_url'] = urldecode($this->getBrandedUrl($reseller, 'index.php', ['rp' => '/password/reset/redeem/'.$user->reset_token]));
        $result['userEmail'] = $user->email;
        $result['verification_url'] = urldecode($this->getBrandedUrl($reseller, 'index.php', ['rp' => '/user/verify/'.$user->email_verification_token]));

        return $result;
    }
}
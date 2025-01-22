<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\Invites;
use MGModule\ResellersCenter\repository\whmcs\Users;

class InvitationIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        $repo = new Invites();
        $invitation = $repo->find($value);

        if ($args['resellerId']) {
            return $result;
        }

        $reseller = new Reseller($args['resellerId']);
        $client = (new Clients())->find($invitation->client_id);
        $user = (new Users())->find($invitation->invited_by);

        $result['invite_sent_by_admin'] = $invitation->invited_by_admin == 1;
        $result['invite_sender_name'] = $client->firstname.' '.$client->lastname;
        $result['invite_account_name'] = $user->first_name.' '.$user->last_name;
        $result['invite_accept_url'] = urldecode($this->getBrandedUrl($reseller, 'index.php', ['rp' => '/invite/'.$invitation->token]));
        $result['invitationEmail'] = $invitation->email;

        return $result;
    }
}
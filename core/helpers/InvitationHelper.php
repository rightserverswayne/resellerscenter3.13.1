<?php

namespace MGModule\ResellersCenter\core\helpers;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\whmcs\Invites;

class InvitationHelper
{
    public function checkAndGetInvite()
    {
        /* Checking if site is branded and if user came from invitation page (hook is only used by WHMCS 8+) */
        if(Session::get('branded') !== true || !(Request::exists('rp') || Server::get('PATH_INFO')))
        {
            return;
        }

        /* Getting rp from Request and checking if is it invitation with token (PATH_INFO if URL Friendly Option Enabled) */
        $requestRp = ltrim(Request::get('rp') ?: Server::get('PATH_INFO'), '/');
        $requestRp = explode('/', $requestRp);
        if(count($requestRp) != 2 || $requestRp[0] != 'invite')
        {
            return;
        }

        /* Checking whether the invitation token exists in the database and whether it is active */
        $invite = (new Invites())
            ->where('token', $requestRp[1])
            ->where('accepted_at', null)
            ->first();

        return $invite;
    }

    public function acceptInvitation(\MGModule\ResellersCenter\models\whmcs\Invite $invite, $time)
    {
        $invite->accepted_at = $time;
        $invite->save();

        return $invite;
    }

    public function loginAsNewInvitedUser($userId)
    {
        $auth = new \WHMCS\Authentication\AuthManager;
        $user = \WHMCS\User\User::where('id', $userId)->first();
        if(!$user)
        {
            throw new \Exception('Invited User not found.');
        }

        $auth->login($user);
    }
}
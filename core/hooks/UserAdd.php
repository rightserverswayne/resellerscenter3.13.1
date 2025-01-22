<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\helpers\InvitationHelper;
use MGModule\ResellersCenter\Core\Redirect;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\repository\whmcs\UsersClients;

class UserAdd
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[0] = function($params)
        {
            $this->userBrandedInvitation($params);
        };
    }

    private function userBrandedInvitation($params)
    {
        $invitationHelper = new InvitationHelper();

        /* Data Validation and getting Invite when all good */
        $invite = $invitationHelper->checkAndGetInvite();
        if(!$invite)
        {
            return;
        }

        /* Simulating the same WHMCS process as in not branded site */
        //Accepting invitation
        $now = (new \DateTime('NOW'))->format('Y-m-d H:i:s');
        $invite = $invitationHelper->acceptInvitation($invite, $now);

        //Creating new record in tblusers_clients table
        $usersClients = new UsersClients();
        $userClientsRecord = [
            'auth_user_id' => $params['user_id'],
            'client_id' => $invite->client_id,
            'invite_id' => $invite->id,
            'owner' => 0,
            'permissions' => $invite->permissions,
            'last_login' => null,
            'created_at' => $now,
            'updated_at' => $now
        ];
        $usersClients->create($userClientsRecord);

        //Logging as new User
        $invitationHelper->loginAsNewInvitedUser($params['user_id']);

        //Redirecting to clientarea page with successful invite accepted parameter
        $domain = Server::get('HTTP_HOST') ?: Server::get('SERVER_NAME');
        $path = dirname(Server::get('SCRIPT_NAME'));
        Redirect::to($domain,$path . '/clientarea.php', ['inviteaccepted' => 1]);
    }
}
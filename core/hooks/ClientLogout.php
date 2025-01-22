<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\ClientLogoutHelper;
/**
 * Description of ClientLogout
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientLogout
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
        $this->functions[0] = function ($params)
        {
            return ClientLogoutHelper::returnToAdminStore($params);
        };
    }
}

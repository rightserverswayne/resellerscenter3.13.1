<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of PreModuleChangePackage
 *
 * @author Paweł Złamaniec
 */
class PreModuleChangePackage
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
        $this->functions[10] = function($params)
        {
            return $this->abortAutoUpgrade($params);
        };
    }

    /**
     * Remove service relation
     *
     * @param type $params
     * @return type
     */
    public function abortAutoUpgrade($params)
    {
        if(Session::get("RC_AbortAutoUpgrade"))
        {
            $params["abortcmd"] = true;
        }
    }
}

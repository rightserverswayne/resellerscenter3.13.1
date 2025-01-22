<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of AddonDelete
 *
 * @author Paweł Złamaniec
 */
class AfterCronJob
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
        $this->functions[10] = function($params) {
            return $this->clear($params);
        };
    }

    /**
     * Remove service relation
     *
     * @param type $params
     * @return type
     */
    public function clear($params)
    {
        Session::clear("ResellerInvoices");
    }
}

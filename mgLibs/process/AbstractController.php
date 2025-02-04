<?php

namespace MGModule\ResellersCenter\mgLibs\process;

use MGModule\ResellersCenter as main;

/**
 * Description of abstractController
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractController
{
    public $mgToken                   = null;
    private $registredValidationErros = array();

    function __construct($input = [])
    {
        if (isset($input['mg-token']))
        {
            $this->mgToken = $input['mg-token'];
        }
    }

    /**
     * Generate Token For Form
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @return string
     */
    function genToken()
    {
        return md5(time());
    }

    /**
     * Validate Token With previous checked
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $token
     * @return boolean
     */
    function checkToken($token = null)
    {
        if ($token === null)
        {
            $token = $this->mgToken;
        }

        if ($_SESSION['mg-token'] === $token)
        {
            return false;
        }

        $_SESSION['mg-token'] = $token;

        return true;
    }

    function dataTablesParseRow($template, $data)
    {
        $row = main\mgLibs\Smarty::I()->view($template, $data);

        $output = array();

        if (preg_match_all('/\<td\>(?P<col>.*?)\<\/td\>/s', $row, $result))
        {
            foreach ($result['col'] as $col)
            {
                $output[] = $col;
            }
        }

        return $output;
    }

    function registerErrors($errors)
    {
        $this->registredValidationErros = $errors;
    }

    function getFieldError($field, $langspace = 'validationErrors')
    {
        if (!isset($this->registredValidationErros[$field]))
        {
            return false;
        }

        $message = array();
        foreach ($this->registredValidationErros[$field] as $type)
        {
            $message[] = main\mgLibs\Lang::absoluteT($langspace, $type);
        }

        return implode(',', $message);
    }

    public function isActive()
    {
        return true;
    }

    public function getLoggedClient()
    {
        return $_SESSION["uid"];
    }
}

<?php
namespace MGModule\ResellersCenter\repository\source;

/**
 * Description of ModelException
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class RepositoryException extends \Exception
{
    public function __construct($message, $code, $previous) 
    {
        $translatedMsg = \MGModule\ResellersCenter\mgLibs\Lang::T('exception','repository',$message);
        parent::__construct($translatedMsg, $code, $previous);
    }   
}
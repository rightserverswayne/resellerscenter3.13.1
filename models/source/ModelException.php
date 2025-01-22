<?php
namespace MGModule\ResellersCenter\models\source;

/**
 * Description of ModelException
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ModelException extends \Exception
{
    public function __construct($message, $code = null, $previous = null) 
    {
        $translatedMsg = \MGModule\ResellersCenter\mgLibs\Lang::T('exception','model',$message);
        parent::__construct($translatedMsg, $code, $previous);
    }   
}

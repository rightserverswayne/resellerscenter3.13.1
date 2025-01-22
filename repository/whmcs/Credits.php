<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Credits
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Credits extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Credit';
    }
}

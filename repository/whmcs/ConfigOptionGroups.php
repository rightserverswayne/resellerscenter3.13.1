<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of ConfigOptionGroups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionGroups extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\ConfigOptionGroup';
    }
}

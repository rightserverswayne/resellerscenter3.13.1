<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\models\whmcs\Server;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Servers extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Server';
    }

    public function getAvailable($search = "")
    {
        $model = new Server();
        if($search)
        {
            $result = $model->where("name", "LIKE", "%{$search}%")
                            ->get();
        }
        else
        {
            $result = $model->get();
        }

        return $result;
    }
}

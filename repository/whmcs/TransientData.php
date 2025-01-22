<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

/**
 * Description of TransientData
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TransientData extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\TransientData';
    }
    
    public function getClientVeryficationLink($clientid)
    {
        $dataName = "{$clientid}:emailVerificationClientKey";

        $model = $this->getModel();
        $data = $model->where('name', '=', $dataName)->first();
        return $data->data;
    }
}

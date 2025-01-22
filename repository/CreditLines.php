<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

class CreditLines extends AbstractRepository
{
    function determinateModel()
    {
        return CreditLine::class;
    }

    public function updateOrCreate(array $data)
    {
        $model = $this->getModel();
        $model->updateOrCreate(['client_id' => $data['client_id']],$data);
    }

    public function getByClientId($clientId)
    {
        $model = $this->getModel();
        return $model->where('client_id',$clientId)->first();
    }
}
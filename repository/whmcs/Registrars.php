<?php
namespace MGModule\ResellersCenter\Repository\Whmcs;
use MGModule\ResellersCenter\Models\Whmcs\Registrar;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Registrars
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Registrars extends AbstractRepository
{
    public function determinateModel()
    {
        return Registrar::class;
    }

    public function getAvailable($search = "")
    {
        $model = new Registrar();
        if($search)
        {
            $data = $model->where("registrar", "LIKE", "%{$search}%")
                            ->get();
        }
        else
        {
            $data = $model->get();
        }

        //To assoc
        $result = [];
        foreach($data as $record)
        {
            $result[$record->registrar][$record->setting] = $record->value;
        }

        return $result;
    }
}

<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;

use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\Repository\Whmcs\Registrars;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Registrar as RegistrarResource;

/**
 * Description of Registrar
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Registrar extends AbstractFilter
{
    public function getData($search = "")
    {
        $repo       = new Registrars();
        $records    = $repo->getAvailable($search);

        $result = [];
        foreach($records as $name => $config)
        {
            $registrar = new RegistrarResource($name);
            $result[] =
            [
                "id"    => $registrar->name,
                "text"  => $registrar->FriendlyName ?: $registrar->name
            ];
        }

        return $result;
    }
}
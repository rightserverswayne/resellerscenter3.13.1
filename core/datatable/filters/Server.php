<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\repository\whmcs\Servers;

/**
 * Description of Server
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Server extends AbstractFilter
{
    public function getData($search = "")
    {
        $repo       = new Servers();
        $servers    = $repo->getAvailable($search);

        $result = [];
        foreach ($servers as $server)
        {
            $result[] =
            [
                "id"    => $server->id,
                "text"  => $server->name
            ];
        }

        return $result;
    }
}
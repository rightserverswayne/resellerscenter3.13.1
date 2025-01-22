<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables\Filters;

use MGModule\ResellersCenter\Core\Datatable\Filters\Server as ServerFilter;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;
use MGModule\ResellersCenter\repository\whmcs\Servers;

/**
 * Description of Server
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Server extends ServerFilter
{
    use IsResellerProperty;

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
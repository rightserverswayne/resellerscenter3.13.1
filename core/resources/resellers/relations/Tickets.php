<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\repository\ResellersTickets;

/**
 * Description of Tickets
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tickets extends AbstractRelations
{
    /**
     * Define type ambiguous type.
     * If true then relation can belong to many Resellers
     *
     * @var boolean
     */
    protected $ambiguous = false;

    /**
     * @return ResellersClients
     */
    protected function getRepo()
    {
        return new ResellersTickets();
    }
}
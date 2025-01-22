<?php

namespace MGModule\ResellersCenter\libs\GlobalSearch;

class SearchTypes
{
    const CLIENT_TYPE = 'Client';
    const SERVICE_TYPE = 'Service';
    const ADDON_TYPE = 'Addon';
    const DOMAIN_TYPE = 'Domain';
    const INVOICE_TYPE = 'Invoice';
    const ORDER_TYPE = 'Order';
    const TICKET_TYPE = 'Ticket';

    public static function getTypes()
    {
        return [self::CLIENT_TYPE,
            self::SERVICE_TYPE,
            self::ADDON_TYPE,
            self::DOMAIN_TYPE,
            self::INVOICE_TYPE,
            self::ORDER_TYPE,
            self::TICKET_TYPE];
    }
}
<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

interface ButtonInterface
{
    public function getButtons(Reseller $reseller):array;
}
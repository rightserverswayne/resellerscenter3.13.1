<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Interfaces;

use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

interface InvoiceModelInterface
{
    public function getRepository():AbstractRepository;
    public function recalculate();
    public function getType();
    public function getReseller();
    public function decrementNumbering();
    public function setCustomInvoiceNumber();
}
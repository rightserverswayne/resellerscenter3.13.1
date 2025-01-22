<?php

namespace MGModule\ResellersCenter\gateways\DeferredPayments;

use MGModule\ResellersCenter\core\form\fields\Switcher;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\fields\Textarea;
use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\InvoiceGateway;
use MGModule\ResellersCenter\Core\Resources\gateways\PaymentGateway;

class DeferredPayments extends PaymentGateway implements InvoiceGateway
{
    const SYS_NAME = 'deferredpayments';

    public $adminName = "Deferred Payments";
    public $type = "Invoices";

    protected static string $sysName = self::SYS_NAME;

    public function __construct()
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);

        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", "Deferred Payments");
        $displayName->addStyle("width", 9);

        $instructions = new Textarea("instructions", "Instructions", "instructions", "Bank Name:\nPayee Name:\nSort Code:\nAccount Number:");
        $instructions->addStyle("width", 9);
        $instructions->addStyle("custom", array("height" => "125px"));

        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
        $this->configuration->add($instructions);

        parent::__construct();
    }

    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        // TODO: Implement link() method.
    }

    public function callback($data)
    {
        // TODO: Implement callback() method.
    }

    public function refund($params)
    {
        // TODO: Implement refund() method.
    }
}
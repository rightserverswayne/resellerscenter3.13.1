<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Whmcs as WhmcsHelper;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services\ConsolidatedInvoiceService;
use MGModule\ResellersCenter\repository\Invoices;

/**
 * Description of DailyCronJob
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DailyCronJob
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[10] = function()
        {
            $this->sendInvoiceRemainders();
            $this->sendInvoiceOverdueNotice();
        };

        $this->functions[20] = function()
        {
            $this->activeConsolidatedInvoices();
        };
    }

    /**
     * Send invoice payment remainder to reseller's clients
     *
     * @param $params
     * @throws \Exception
     */
    public function sendInvoiceRemainders()
    {
        $remainderDays = WhmcsHelper::getConfig("SendInvoiceReminderDays");
        if(WhmcsHelper::getConfig("SendReminder") != "on" || $remainderDays == 0)
        {
            return;
        }

        $repo = new Invoices();
        $invoices = $repo->getDaysBeforeDue($remainderDays);
        foreach($invoices as $invoice)
        {
            $rcinvoice = new Invoice($invoice->id);
            if(!$rcinvoice->reseller->settings->admin->disableEndClientInvoices)
            {
                $rcinvoice->sendMessage("Invoice Payment Reminder");
            }
        }
    }

    public function sendInvoiceOverdueNotice()
    {
        $noticeDays = [
            "First"     => WhmcsHelper::getConfig("SendFirstOverdueInvoiceReminder"),
            "Second"    => WhmcsHelper::getConfig("SendSecondOverdueInvoiceReminder"),
            "Third"     => WhmcsHelper::getConfig("SendThirdOverdueInvoiceReminder"),
        ];

        foreach($noticeDays as $type => $days)
        {
            $repo = new Invoices();
            $invoices = $repo->getDaysAfterDue($days);

            foreach($invoices as $invoice)
            {
                $rcinvoice = new Invoice($invoice->id);
                if(!$rcinvoice->reseller->settings->admin->disableEndClientInvoices)
                {
                    $rcinvoice->sendMessage("{$type} Invoice Overdue Notice");
                }
            }
        }
    }

    public function activeConsolidatedInvoices()
    {
        $service = new ConsolidatedInvoiceService();
        $service->activeConsolidatedInvoices();
    }
}
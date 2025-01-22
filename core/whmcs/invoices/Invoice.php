<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Invoices;

use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\HostingAddons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

/**
 * Description of Invoice
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoice extends WhmcsObject
{
    /**
     * Get model class
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Invoice::class;
    }

    public function create($params)
    {
        $classname = $this->getModelClass();

        $this->model = new $classname();

        $this->userid = $params['userid'];
        $this->date = $params['date'];
        $this->duedate = $params['duedate'];
        $this->status = $params['status'];
        $this->paymentmethod = $params['paymentmethod'];
        $this->save();

        $this->id = $this->model->id;
        return $this;
    }
    
    public function refreshSnaphot()
    {
        if($this->model->snaphot)
        {
            $this->model->snaphot->delete(); 
            \WHMCS\Invoice::saveClientSnapshotData($this->id);
        }
    }

    public function addItem($relid, $type, $description, $amount, $taxed)
    {
        $params['invoiceid'] = $this->id;
        $params['userid'] = $this->userid;
        $params['type'] = $type;
        $params['relid'] = $relid;
        $params['description'] = $description;
        $params['amount'] = $amount;
        $params['taxed'] = $taxed;
        $params['duedate'] = $this->duedate;
        $params['paymentmethod'] = $this->paymentmethod;

        $item = new InvoiceItem();
        $item->create($params);
    }

    /**
     * Make credit payment for invoice using credits from related client's account
     * 
     * @param type $amount
     * @throws Exception
     */
    public function addCreditPayment($amount)
    {
        if($this->client->credit < $amount)
        {
            throw new \Exception("notEnoughCredits");
        }
        
        if($amount > 0)
        {
            WhmcsAPI::request("ApplyCredit", array(
                "invoiceid" => $this->id,
                "amount" => $amount,
            ));
        }
        
        //Reload model to get current status
        $this->load();
    }
    
    /**
     * Activates related order
     */
    public function activateRelatedOrder()
    {
        WhmcsAPI::request("AcceptOrder", array("orderid" => $this->order->id));

        //Activate addons - WHMCS is skipping addons for some reason...
        foreach($this->items as $item)
        {
            if($item->service instanceof \MGModule\ResellersCenter\models\whmcs\HostingAddon && 
               $item->service->status == HostingAddons::STATUS_PENDING)
            {
                WhmcsAPI::request("UpdateClientAddon", array(
                    "id" => $item->service->id, 
                    "status" => HostingAddons::STATUS_ACTIVE
                ));
            }
        }
    }

    public function getInvoiceForReseller()
    {
        $admin = [];
        $reseller = [];
        foreach($this->items as $item)
        {
            $item->service->resellerService->exists ? $reseller[] = $item : $admin[] = $item;
        }

        //No reseller items on invoice
        if(empty($reseller))
        {
            return null;
        }

        //All items on invoice belongs to reseller
        if(empty($admin))
        {
            return $this;
        }

        //Create new invoice for reseller
        $params['userid'] = $this->userid;
        $params['date'] = $this->date;
        $params['duedate'] = $this->duedate;
        $params['status'] = $this->status;
        $params['paymentmethod'] = $this->paymentmethod;

        $invoice = new Invoice();
        $invoice->create($params);

        //Assign reseller item
        foreach($reseller as $item)
        {
            $item->invoiceid = $invoice->id;
            $item->save();
        }

        //update invoices
        updateInvoiceTotal($this->id);
        updateInvoiceTotal($invoice->id);

        return $invoice;
    }

    /**
     * Find and return related reseller
     */
    public function getReseller()
    {
        //Check if this is reseller payment for end client order
        foreach($this->items as $item)
        {
            if($item->type == InvoiceItems::TYPE_RC_ORDER)
            {
                return null;
            }
        }

        //Get any service from invoice
        $service = null;
        foreach($this->items as $item)
        {
            if($item->service instanceof \MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting ||
               $item->service instanceof \MGModule\ResellersCenter\Core\Whmcs\Services\Addons\Addon ||
               $item->service instanceof \MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain
            )
            {
                $service = $item->service;
                break;
            }
            elseif($item->type == InvoiceItems::TYPE_UPGRADE)
            {
                $upgrade = new Upgrade($item->relid);
                $service = $upgrade->hosting;
                break;
            }
        }

        $reseller = null;
        if($service->resellerService->reseller->exists)
        {
            $reseller = new Reseller($service->resellerService->reseller->id);
        }
        
        return $reseller;
    }
    
    public function getProfits()
    {
        $profits = [];
        
        $reseller = $this->getReseller();
        foreach($this->items as $item)
        {
            $item = new InvoiceItem($item->id);
            $profits[] = [
                "itemid" => $item->id,
                "itemrelid" => $item->relid,
                "amount" => $item->getProfit($reseller)
            ];
        }
        
        return $profits;
    }

    public function isReadyToProcess()
    {
        if($this->resellerInvoice->exists)
        {
            return ($this->resellerInvoice->total == 0 && $this->total == $this->amountpaid);
        }
        else
        {
            return ($this->total == $this->amountpaid);
        }
    }
}

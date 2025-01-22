<?php

namespace MGModule\ResellersCenter\core;

use MGModule\ResellersCenter\core\mergeFields\FieldsFactory;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;

/**
 * Description of MergeFields
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class MergeFields
{
    public $fields;
    protected array $additionalParams = [];
    private $key;
    
    public function __construct($filename = 'mergefields.list') 
    {
        $dir = __DIR__. DS. ".." . DS . 'resources'. DS . $filename;
        $this->load($dir);
    }

    public function addAdditionalParam($key, $value):self
    {
        $this->additionalParams[$key] = $value;

        return $this;
    }
    
    public function load($dir)
    {
        $file = fopen($dir, "r");
        if($file) 
        {
            while(($line = fgets($file)) !== false) 
            {
                $this->parseLine($line);
            }

            fclose($file);
        }
        else 
        {
            throw new \Exception("Unable to find MergeFields list file.");
        }
    }
    
    public function getFieldsValues($resellerid = null, $clientid = null, $ticketid = null, $invoiceid = null, $hostingid = null, $domainid = null, $rcinvoiceid = null, $invitationId = null, $userId = null)
    {
        //It must be first in this function
        $functionVars = get_defined_vars();

        $result = $this->getOtherFields($resellerid);

        $args = array_merge($this->additionalParams, ['resellerId'=>$resellerid]);

        foreach ($functionVars as $functionVar=>$value) {
            if ($value !== null) {

                $field = FieldsFactory::create($functionVar);

                $result = array_merge($result, $field->getRelatedFields($value, $this->fields, $args));
            }
        }
        return $result;
    }

    public function getTicketRelatedFields($ticketid, $reply = false)
    {
        $field = FieldsFactory::create('ticketid');
        return $field->getRelatedFields($ticketid, $this->fields, ['reply'=>$reply]);
    }

    public function getOrderRelatedFields($clientid)
    {
        global $whmcs;

        $repo = new Clients();
        $client = $repo->find($clientid);
        $order = $client->orders->sortByDesc("id")->first();
        
        //Get order details
        $details = '';
        if ($order->hostings) {
            foreach ($order->hostings as $hosting) {
                $details .= "{$whmcs->get_lang("orderproduct")}: {$hosting->product->group->name} - {$hosting->product->name} <br />";
                if ( !empty($hosting->domain) && trim($hosting->domain) ) {
                    $details .= "{$whmcs->get_lang("orderdomain")}: {$hosting->domain} <br />";
                }
                $details .= "{$whmcs->get_lang("firstpaymentamount")}: {$client->currencyObj->prefix}{$hosting->firstpaymentamount}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("recurringamount")}: {$client->currencyObj->prefix}{$hosting->amount}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("orderbillingcycle")}: {$hosting->billingcycle} <br />";
            }
        }

        if ($order->domains) {
            foreach ($order->domains as $domain) {
                $details .= "{$whmcs->get_lang("domainregistration")}: {$domain->type} <br />";
                $details .= "{$whmcs->get_lang("orderdomain")}: {$domain->domain} <br />";
                $details .= "{$whmcs->get_lang("firstpaymentamount")}: {$client->currencyObj->prefix}{$domain->firstpaymentamount}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("recurringamount")}: {$client->currencyObj->prefix}{$domain->recurringamount}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("orderbillingcycle")}: {$domain->registrationperiod} <br />";
            }
        }

        if ($order->addons) {
            foreach ($order->addons as $hostingAddon) {
                $details .= "{$whmcs->get_lang("orderaddon")}: {$hostingAddon->addon->name} <br />";
                $details .= "{$whmcs->get_lang("firstpaymentamount")}: {$client->currencyObj->prefix}{$hostingAddon->setupfee}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("recurringamount")}: {$client->currencyObj->prefix}{$hostingAddon->recurring}{$client->currencyObj->suffix} <br />";
                $details .= "{$whmcs->get_lang("orderbillingcycle")}: {$hostingAddon->billingcycleFriendly} <br />";
            }
        }

        return array("order_number" => $order->ordernum, "order_details" => $details);
    }
    
    public function getOtherFields($resellerid)
    {
        global $CONFIG;
        $reseller = new Reseller($resellerid);
        
        if($reseller->settings->admin->branding)
        {
            $settings = $reseller->settings->private;

            $whmcsURL = parse_url($CONFIG["SystemURL"]);
            $pathToLogo = CAHelper::getLogoPath().$settings->logo;
            if($reseller->settings->admin->cname && $settings->domain)
            {
                $logoUrl = $whmcsURL['scheme'] . '://' . $settings->domain . rtrim($whmcsURL['path'], '/' ) .'/'. $pathToLogo;
            }
            else
            {
                $logoUrl = "{$CONFIG["SystemURL"]}/{$pathToLogo}";
            }

            $domain = $this->getBrandedDomain($reseller);
            $brandedWhmcsUrl = rtrim($domain,'/ '). ($reseller->settings->admin->cname ? '/' : '&rcredirect=');
            $result = [
                'company_name'     => $settings->companyName,
                'company_domain'   => $domain,
                'signature'        => nl2br($settings->signature),
                'company_logo_url' => $logoUrl,
                'whmcs_url'        => $brandedWhmcsUrl,
                'whmcs_link'       => "<a href='{$domain}'>{$domain}</a>",
            ];
        }
        else
        {
            $result = [
                'company_name'     => $CONFIG['CompanyName'],
                'company_domain'   => $CONFIG['Domain'],
                'signature'        => nl2br($CONFIG['Signature']),
                'company_logo_url' => $CONFIG['LogoURL'],
                'whmcs_url'        => rtrim($CONFIG['SystemURL'],'/').'/',
                'whmcs_link'       => "<a href='{$CONFIG["SystemURL"]}'>{$CONFIG["SystemURL"]}</a>",
            ];
        }

        $result = array_merge($result, array(
            'date' => date("Y-m-d"),
            'time' => date("H:i:s"),
        ));
            
        return $result;
    }
    
    private function parseLine($line)
    {
        if(trim($line) == '')
        {
            return;
        }
        
        if(strpos($line, "#") !== false)
        {
            $this->key = trim(substr($line, 1));
        }
        else
        {
            $delimiter = strpos($line, ":");
            $name = trim(substr($line, 0, $delimiter));
            $variable = trim(substr($line, $delimiter +1));
            
            $this->fields[$this->key][$name] = $variable;
        }
    }

    /**
     * returns branded domain if reseller is allowed to brand WHMCS URL
     * otherwise default SystemURL is returned
     * 
     * @global type $CONFIG
     * @param Reseller $reseller
     * @return type
     */
    protected function getBrandedDomain(Reseller $reseller)
    {
        global $CONFIG;
        
        $whmcsURL = parse_url($CONFIG["SystemURL"]);
        if($reseller->settings->admin->cname && $reseller->settings->private->domain)
        {
            $resellerDomain = rtrim($reseller->settings->private->domain,'/'); 
            if(isset($whmcsURL["path"]))
            {
                $resellerDir    =  trim($whmcsURL["path"], '/');
                $domain         = "{$whmcsURL["scheme"]}://{$resellerDomain}/{$resellerDir}";   
            }
            else
            {
                $domain         = "{$whmcsURL["scheme"]}://{$resellerDomain}";
            }
        }
        else
        {
            $domain = "{$CONFIG["SystemURL"]}/index.php?resid={$reseller->id}";
        }
        
        return $domain;
    }
}

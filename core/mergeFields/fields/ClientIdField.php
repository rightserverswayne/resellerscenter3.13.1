<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Helpers\Urls\FriendlyUrl;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\SessionStorage;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\TransientData;

class ClientIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        //Get all infomations from the table
        $repo = new Clients();
        $client = $repo->find($value);
        foreach ($fields["client_related"] as $key => $variable) {
            $name = substr(trim($variable, "}"), 2);
            $result[$name] = $client->{$key};
        }

        //Get Reseller shop URL
        $reseller = $client->resellerClient->reseller->exists ? new Reseller($client->resellerClient->reseller) : ResellerHelper::getCurrent();

        //Add addtional variables values
        //Custom Fields
        foreach ($client->customFields as $key => $field) {
            $result["client_custom_fields"][$key + 1] = $field->getValueByRelid($client->id);
        }

        //Total due invoice balance
        foreach ($client->invoices as $invoice) {
            $result["total_due_invoices_balance"] += $invoice->total;
        }

        $result["client_name"] = $client->firstname . ' ' . $client->lastname;
        $result["client_group_name"] = $client->group->name;

        $result["client_address1"] = $client->address1;
        $result["client_address2"] = $client->address2;

        //Get Client password
        $storage = new SessionStorage();
        $password = $storage->getStoredByKey("userid_{$client->id}");
        $result["client_password"] = $password->value;

        //Reset password
        $result["pw_reset_url"] = $this->getBrandedUrl($reseller, "pwreset.php", array("key" => $client->pwresetkey));
        if (Whmcs::isVersion("7.8")) {
            global $CONFIG;
            $whmcsUrl = parse_url($CONFIG["SystemURL"]);
            $friendlyUrl = FriendlyUrl::generate();

            $url    = "{$CONFIG["SystemURL"]}/{$friendlyUrl}password/reset/use/key/{$client->pwresetkey}";
            $url.= strpos($friendlyUrl, '?') !== false ? "&resid={$reseller->id}" : "?resid={$reseller->id}";

            if ($reseller->settings->admin->cname && $reseller->settings->private->domain) {
                $resellerDomain = rtrim($reseller->settings->private->domain,'/');
                $url = "{$whmcsUrl["scheme"]}://{$resellerDomain}/{$friendlyUrl}password/reset/use/key/{$client->pwresetkey}";
            }

            $result["pw_reset_url"] = $url;
        }

        //Verfication link
        $transient = new TransientData();
        $verificationURL = $this->getBrandedUrl($reseller, "clientarea.php", array("verificationId" => $transient->getClientVeryficationLink($client->id)));
        if (Whmcs::isVersion('8.0')) {
            $result['verification_url'] = $verificationURL;
        } else {
            $result['client_email_verification_link'] = "<a href='{$verificationURL}'>{$verificationURL}</a>";
        }

        return $result;
    }
}
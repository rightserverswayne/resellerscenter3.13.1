<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\whmcs\Hostings;

class HostingIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];
        $repo = new Hostings();
        $hosting = $repo->find($value);

        foreach ($fields["product_related"] as $key => $variable) {
            $name = substr(trim($variable, "}"), 2);
            $result[$name] = $hosting->{$key};
        }

        if (empty($result['service_suspension_reason'])) {
            $result['service_suspension_reason'] = \Lang::trans("suspendreasonoverdue");
        }

        $result["service_product_name"] = $hosting->product->name;
        $result["service_product_description"] = $hosting->product->description;
        $result["service_server_name"] = $hosting->serverObj->name;
        $result["service_server_hostname"] = $hosting->serverObj->hostname;
        $result["service_server_ip"] = $hosting->serverObj->ipaddress;
        $result["service_ns1"] = $result["service_ns1"] ?: $hosting->serverObj->nameserver1;
        $result["service_ns2"] = $result["service_ns2"] ?: $hosting->serverObj->nameserver2;
        $result["service_ns3"] = $hosting->serverObj->nameserver3;
        $result["service_ns4"] = $hosting->serverObj->nameserver4;
        $result["service_ns1_ip"] = $hosting->serverObj->nameserver1ip;
        $result["service_ns2_ip"] = $hosting->serverObj->nameserver2ip;
        $result["service_ns3_ip"] = $hosting->serverObj->nameserver3ip;
        $result["service_ns4_ip"] = $hosting->serverObj->nameserver4ip;
        $result["service_cancellation_type"] = $hosting->cancelation->type;
        $result["service_password"] = decrypt($hosting->password);

        //CustomFields
        $result["service_custom_fields"] = [];
        foreach ($hosting->customFields as $key => $field) {
            $repoFieldValue = $field->getValueModel($hosting->id);
            if ($repoFieldValue->exists) {
                $fName = explode('|', $field->fieldname)[0];
                $fValue = $field->fieldtype == 'password' ? decrypt($repoFieldValue->value) : $repoFieldValue->value;
                $result["service_custom_fields"][] = $fValue;
                $result["service_custom_field_".$fName] = $fValue;
                $result["service_custom_fields_by_name"][] = ['name' => $fName, 'value' =>$fValue];
            }
        }

        //Get SSL configuration link
        global $CONFIG;
        $parsed = parse_url($CONFIG["SystemURL"]);

        $cert = md5($hosting->sslorder->id);
        $reseller = new Reseller($hosting->resellerService->reseller);
        if ($reseller->settings->admin->cname) {
            $resellerDomain = rtrim($reseller->settings->private->domain,'/');
            $sslUrl = "{$parsed["scheme"]}://{$resellerDomain}/configuressl.php?cert={$cert}";
        } else {
            $sslUrl = "{$CONFIG["SystemURL"]}/configuressl.php?resid={$reseller->id}&cert={$cert}";
        }

        $sslConfigurationLink = "<a href='{$sslUrl}'>{$sslUrl}</a>";
        $result["ssl_configuration_link"] = $sslConfigurationLink;

        if (DateFormatHelper::changeDateFormatIsAllowed()) {
            $dateFormat = $this->getResellerDateFormat($args['resellerId']);
            $dateFormatter = new DateFormatter();
            $result["service_reg_date"] = $dateFormatter->format($result["service_reg_date"], $dateFormat);
            $result["service_next_due_date"] = $dateFormatter->format($result["service_next_due_date"], $dateFormat);
        }

        return $result;
    }
}
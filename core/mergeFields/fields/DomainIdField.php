<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\whmcs\Domains;

class DomainIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        $repo = new Domains();
        $domain = $repo->find($value);
        foreach ($fields["domain_related"] as $key => $variable) {
            $name = substr(trim($variable, "}"), 2);
            $result[$name] = $domain->{$key};
        }

        $helper = new DomainHelper($domain->domain);
        $result["domain_sld"] = $helper->getDomain();
        $result["domain_tld"] = $helper->getTLD();

        $secondsDiff = strtotime($domain->expirydate) - time();
        //Convert -0 to 0 so it looks g00d in mails
        $result["domain_days_until_expiry"] = (int)number_format(ceil($secondsDiff/3600/24));

        $secondsDiff = strtotime($domain->nextduedate) - time();
        $result["domain_days_until_nextdue"] = (int)number_format(ceil($secondsDiff/3600/24));

        $secondsDiff = time() - strtotime($domain->expirydate);
        $daysDiff = floor($secondsDiff/3600/24);
        $result["domain_days_after_expiry"] = $daysDiff >= 0 ? $daysDiff : 0;

        $secondsDiff = time() - strtotime($domain->nextduedate);
        $daysDiff = floor($secondsDiff/3600/24);
        $result["domain_days_after_nextdue"] = $daysDiff >= 0 ? $daysDiff : 0;

        $result["domain_renewal_url"] = urldecode($this->getBrandedUrl($domain->client->resellerClient->reseller, 'index.php', ['rp' => '/domain/'.$helper->getFullName().'/renew']));
        $result["domains_manage_url"] = urldecode($this->getBrandedUrl($domain->client->resellerClient->reseller, 'clientarea.php?action=domains'));

        if (DateFormatHelper::changeDateFormatIsAllowed()) {
            $dateFormat = $this->getResellerDateFormat($args['resellerId']);
            $dateFormatter = new DateFormatter();
            $result["domain_reg_date"] = $dateFormatter->format($result["domain_reg_date"], $dateFormat);
            $result["domain_next_due_date"] = $dateFormatter->format($result["domain_next_due_date"], $dateFormat);
            $result["domain_expiry_date"] = $dateFormatter->format($result["domain_expiry_date"], $dateFormat);
        }

        return $result;
    }
}
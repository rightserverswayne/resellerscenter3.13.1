<?php

namespace MGModule\ResellersCenter\core\helpers;

use MGModule\ResellersCenter\Helpers\ModuleConfiguration;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;
use MGModule\ResellersCenter\Loader;

/**
 * Description of Helper
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Helper 
{
    /**
     * Get existing invoices templates from resource
     * 
     * @since 3.0.0
     * @return type
     */
    public static function getAvailableInvoiceTemplates()
    {
        $templatePath = __DIR__ .DS."..".DS.'resources'. DS .'invoicetemplates';
        if(!is_dir($templatePath))
        {
            return;
        }

        $templates = scandir($templatePath);
        $templates = array_diff($templates, array('.', '..', 'index.php'));
        
        return $templates;
    }
    
    /**
     * Get custom gateways from the module
     * 
     * @since 3.0.0
     * @return type
     */
    static public function getCustomGateways($resellerid)
    {
        $files = scandir(__DIR__ .DS."..".DS."..".DS.'gateways');
        $files = array_diff($files, array('.', '..', 'callback'));

        $moduleConfig = ModuleConfiguration::getModuleConfiguration();
        $bannedGateways = (array)$moduleConfig['bannedGateways'];
        
        $gateways = [];
        foreach($files as $file) 
        {
            if(strpos($file, ".") === false)
            {
                $gateways[] = $file;
            }
        }

        $result = [];
        foreach($gateways as $index => $gateway)
        {
            $gatewayObj = PaymentGatewayFactory::get($resellerid, $gateway);

            if (in_array($gatewayObj->name, $bannedGateways)) {
                continue;
            }

            $order = $gatewayObj->order != '' ? $gatewayObj->order : $index;
            $order += array_key_exists($order, $result) ? 1 : 0;
            $result[$order] = $gatewayObj;
        }

        usort($result, function ($a, $b) {
            return $a->order > $b->order;
        });

        return $result;
    }

    static public function getEnabledCustomGateways($resellerId)
    {
        $allGateways = self::getCustomGateways($resellerId);
        return array_filter($allGateways, function ($gateway)
        {
            return $gateway->enabled;
        });
    }
    
    static function getAcceptedCCTypes()
    {
        global $CONFIG;
        
        $cardsString = $CONFIG["AcceptedCardTypes"];
        $cards = explode(",", $cardsString);
        
        return $cards;
    }
    
    /**
     * Format text message to html output
     * An old WHMCS function
     * 
     * @param string $message
     * @return string
     */
    public static function ticketMessageFormat($message)
    {
        require_once Loader::$whmcsDir."/includes/ticketfunctions.php";

        $message = strip_tags($message);
        $message = preg_replace("/\[div=\"(.*?)\"\]/", "<div class=\"$1\">", $message);
        if($replacetags)
        {
            foreach (array_keys($replacetags) as $key) {
                $message = str_replace("[" . $key . "]", "<" . $key . ">", $message);
                $message = str_replace("[/" . $key . "]", "</" . $key . ">", $message);
            }
        }

        $message = nl2br($message);
        $replacetags = array("b" => "strong", "i" => "em", "u" => "ul", "div" => "div");
        $message = ticketAutoHyperlinks($message);

        return $message;
    }

    /**
     * Get parameter from current URL
     * 
     * @param type $name
     * @param type $url
     * @return type
     */
    public static function getParamFromURL($name, $url)
    {
        $url = html_entity_decode($url);
        
        $parsed = parse_url($url);
        $query = explode("&", $parsed["query"]);
        foreach($query as $params)
        {
            $parts = explode("=", $params);
            if($parts[0] == $name)
            {
                return $parts[1];
            }
        }
    }
    
    /**
     * Get price value in target currency
     * 
     * @param type $price
     * @param type $sourceCurrency
     * @param type $targetCurrency
     */
    public static function calcCurrencyValue($price, $sourceCurrency, $targetCurrency)
    {
        $currencies = new Currencies();
        $source = $currencies->find($sourceCurrency);
        $target = $currencies->find($targetCurrency);
     
        return $target->rate * $price / $source->rate;
    }

    /**
     * @param $needle
     * @param $haystack
     * @return bool|int|string
     */
    public static function recursiveArraySearch($needle,$haystack) 
    {
        foreach($haystack as $key=>$value) 
        {
            $current_key = $key;
            if($needle === $value || (is_array($value) && self::recursiveArraySearch($needle, $value) !== false)) 
            {
                return $current_key;
            }
        }
        return false;
    }

    public static function includeTax($price, $tax1, $tax2)
    {
        $tax1 /= 100;
        $tax2 /= 100;

        $config = new \MGModule\ResellersCenter\repository\whmcs\Configuration();
        if($config->getSetting("TaxType") == "Inclusive")
        {
            if($config->getSetting("TaxL2Compound"))
            {
                $taxlvl2 = $price - $price / (1 + $tax2);
                $price -= $taxlvl2;
                $taxlvl1 = $price - $price / (1 + $tax1);
                $price -= $taxlvl1;
            }
            else
            {
                $price = $price / (1 + ($tax1 + $tax2));
            }
        }
        else
        {
            $price += $price * $tax1 + $price * $tax2;
        }

        return round($price, 2);
    }

}

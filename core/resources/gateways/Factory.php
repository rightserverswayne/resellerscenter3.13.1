<?php

namespace MGModule\ResellersCenter\Core\Resources\Gateways;

use MGModule\ResellersCenter\Repository\PaymentGateways;

/**
 * Description of Factory
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Factory 
{
    const GATEWAYS_NAMESPACE = 'MGModule\\ResellersCenter\\gateways\\';
    
    /**
     * Get Payment gateway object and set configuration
     * 
     * @param type $resellerid
     * @param type $name
     * @return PaymentGateway
     */
    static function get($resellerid, $name)
    {
        $classname = self::GATEWAYS_NAMESPACE . "{$name}\\{$name}";

        $obj = class_exists($classname) ? new $classname() : self::searchAsSysName($name);

        if (!$obj) {
            return null;
        }

        $repo = new PaymentGateways();
        $settings = $repo->getGatewaySettings($resellerid, $name);

        $obj->setConfiguration($settings);
        return $obj;
    }

    private static function searchAsSysName($name)
    {
        $files = scandir(__DIR__ . DS . ".." . DS . ".." . DS . ".." . DS . 'gateways');
        $files = array_diff($files, ['.', '..', 'callback']);
        foreach ($files as $file) {
            if (strpos($file, ".") != false) continue;

            $classname = self::GATEWAYS_NAMESPACE . "{$file}\\{$file}";

            if (!class_exists($classname)) continue;

            $gatewayObject = new $classname();

            if ($gatewayObject->getSysName() == $name) {
                return $gatewayObject;
            }
        }

        return null;
    }
}

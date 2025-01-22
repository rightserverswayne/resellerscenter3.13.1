<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Gateways;


use MGModule\ResellersCenter\Core\Traits\IsFlattened;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;

/**
 * Description of PaymentGateway.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGateway
{
    use IsFlattened;

    /**
     * Gateway system name
     *
     * @var string
     */
    protected $sysname;

    /**
     * PaymentGateway constructor.
     *
     * @param $sysname
     */
    public function __construct($sysname)
    {
        $this->sysname = $sysname;
    }

    /**
     * Get data from database
     *
     * @return array
     */
    protected function getData()
    {
        $repo = new PaymentGateways();
        $data = $repo->getGatewaySettings($this->sysname);

        return $data;
    }
}
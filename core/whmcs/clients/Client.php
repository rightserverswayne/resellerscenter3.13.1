<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Clients;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Traits\HasModel;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Traits\Profile;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Traits\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Traits\CreditCard;

/**
 * Description of Client
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Client extends WhmcsObject
{
    use HasModel;
    use Profile,
        Session,
        CreditCard;

    /**
     * Client custom fields
     *
     * @var CustomFields
     */
    public $customFields;

    /**
     * Client credits
     *
     * @var Credits
     */
    public $credits;

    /**
     * Load client and his custom fields
     *
     * @param $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->customFields = new CustomFields($this);
        $this->credits      = new Credits($this);
    }

    /**
     * Set model for object
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Client::class;
    }

    /**
     * Get Reseller object
     */
    public function getReseller()
    {
        return new Reseller($this->resellerClient->reseller);
    }

    /**
     * Get Currency object
     *
     * @return Currency
     */
    public function getCurrency()
    {
        if (empty($this->model)) {
            $this->load();
        }

        return new Currency($this->model->currency);
    }

    public function toArray()
    {
        $details = parent::toArray();

        $gateway = (array)json_decode($details['gatewayid']);
        if (!empty($gateway['method'])) {
            $details['gatewayid'] = $gateway['method'];
        }
        return $details;
    }
}

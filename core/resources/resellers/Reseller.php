<?php

namespace MGModule\ResellersCenter\Core\Resources\Resellers;

use MGModule\ResellersCenter\core\MailConfiguration;
use MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Tickets;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Addons;
use MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Clients;
use MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Domains;
use MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Hosting;
use MGModule\ResellersCenter\Core\Resources\ResourceObject;
use MGModule\ResellersCenter\Core\Traits\HasModel;
use MGModule\ResellersCenter\Core\Traits\HasProperties;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;


/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Reseller extends ResourceObject
{
    use HasModel, HasProperties
    {
        HasProperties::__get insteadof HasModel;
        HasModel::load as hasModelLoad;
    }

    /**
     * Reseller user id
     *
     * @var type
     */
    protected $userid;

    /**
     * Reseller model
     * 
     * @var type 
     */
    protected $model;
    
    /**
     * Reseller settings
     * 
     * @var Settings
     */
    protected $settings;
    
    /**
     *
     * @var \MGModule\ResellersCenter\Core\Whmcs\Clients\Client
     */
    protected $client;
    
    /**
     * Reseller's clients
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Clients
     */
    protected $clients;
    
    /**
     * Hosting related with the reseller
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Hosting
     */
    protected $hosting;
    
    /**
     * Domains related with the reseller
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Domains
     */
    protected $domains;
    
    /**
     * Addons related with the reseller
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Addons
     */
    protected $addons;

    /**
     * Tickets related with the reseller
     *
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Relations\Tickets
     */
    protected $tickets;
    
    /**
     * Products / Addons / Domains available in reseller shop
     * 
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Contents
     */
    protected $contents;

    /**
     * Promotions available in reseller store
     * 
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Promotions
     */
    protected $promotions;

    /**
     * Gateways object
     *
     * @var Gateways
     */
    protected $gateways;

    /**
     * View helper for the reseller
     *
     * @var View
     */
    protected $view;

    /**
     * Set Reseller model class
     *
     * @return string
     */
    public function getModelClass()
    {
        return \MGModule\ResellersCenter\models\Reseller::class;
    }

    /**
     * Reseller constructor.
     *
     * @param integer|\MGModule\ResellersCenter\models\Reseller|null $idOrModel
     * @param integer|null  $userid
     */
    public function __construct($idOrModel = null, $userid = null)
    {
        parent::__construct($idOrModel);
        if ($userid != null && is_numeric($userid)) {
            $this->userid = $userid;
        }
    }

    public function transferCreditToClient($amount, $userid, $description)
    {
        //Remove from reseller
        WhmcsAPI::request("AddCredit", array(
            "clientid" => $this->__get("client")->id,
            "amount" => $amount,
            "description" => "Credit transfered to client #{$userid} for: '{$description}'",
            "type" => "remove"
        ));

        //Transfer to client
        WhmcsAPI::request("AddCredit", array(
            "clientid" => $userid,
            "amount" => $amount,
            "description" => $description,
            "type" => "add"
        ));
    }

    /**
     * Load reseller modal
     *
     * @throws \Exception
     */
    protected function load()
    {
        if ($this->userid) {
            $repo = new Resellers();
            $this->model = $repo->getResellerByClientId($this->userid);
        } else {
            $this->hasModelLoad();
        }
    }

    public function hasCustomMailBox(): bool
    {
        return $this->settings->private->customMailSettings == 'on' || $this->settings->admin->customMailSettings;
    }

    public function getMailConfig():MailConfiguration
    {
        $config = new MailConfiguration();
        $config->setHostname($this->settings->private->mailHostName);
        $config->setUsername($this->settings->private->mailUserName);
        $config->setPassword($this->settings->private->mailPassword);
        $config->setPort($this->settings->private->mailPort);
        $config->setSecure($this->settings->private->smtpSslType);
        return $config;
    }

    public function setMailConfig($config)
    {
        $this->settings->private->mailHostName = $config['mailHostName'];
        $this->settings->private->mailUserName = $config['mailUserName'];
        $this->settings->private->mailPort = $config['mailPort'];
        $this->settings->private->mailPassword = $config['mailPassword'];
        $this->settings->private->smtpSslType = $config['smtpSslType'];
    }

    /**
     * Get non object properties name
     *
     * @return array
     */
    protected function getDisabledProperties()
    {
        return ["id", "userid"];
    }

    /**
     * Override properties classes
     *
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        return
        [
            "client" =>
            [
                "class" => Client::class,
                "model" => $this->client_id,
            ],
            "clients" =>
            [
                "class" => Clients::class,
            ],
            "hosting" =>
            [
                "class" => Hosting::class,
            ],
            "domains" =>
            [
                "class" => Domains::class,
            ],
            "addons" =>
            [
                "class" => Addons::class,
            ],
            "tickets" =>
            [
                "class" => Tickets::class,
            ],
            "contents" =>
            [
                "class" => Contents::class,
            ],
            "promotions" =>
            [
                "class" => Promotions::class,
            ],
            "view" =>
            [
                "class" => View::class,
            ],
            "settings" =>
            [
                "class" => Settings::class,
            ],
            "gateways" =>
            [
                "class" => Gateways::class,
            ],
        ];
    }
}

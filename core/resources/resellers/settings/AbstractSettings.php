<?php

namespace MGModule\ResellersCenter\Core\Resources\Resellers\Settings;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

/**
 * Description of AbstractSettings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AbstractSettings
{
    /**
     * Reseller object
     *
     * @var
     */
    protected $reseller;

    /**
     * Configuration array
     *
     * @var mxied
     */
    protected $data;

    /**
     * AbstractSettings constructor.
     *
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Get settings values from $data array
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $this->data = $this->data ?: $this->getData();

        /**
         * Check if value is serialized
         * using @ to suppress warning when variable is not serialized
         */

        if(!is_array($this->data[$name]))
        {
            $unserialized = @unserialize($this->data[$name]);
            if ($this->data[$name] !== 'b:0;' && $unserialized !== false) {
                return $unserialized;
            }
        }
        
        return $this->data[$name];
    }

    /**
     * Set configuration values in $data array
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Write changes in database
     *
     * @param null $data
     * @throws \Exception
     */
    public function save($data = null)
    {
        $this->data = $this->data ?: $this->getData();
        if ($data !== null) {
            $this->validate($data); //throw exception if not ok
            $this->data = $data;
        }
        
        $repo = new ResellersSettings();
        $repo->saveSettings($this->reseller->id, $this->data, $this->isPrivate());
    }

    /**
     * Validate domain
     *
     * @param $settings
     * @throws \Exception
     */
    public function validate($settings)
    {
        if($this->reseller->settings->admin->cname)
        {
            global $CONFIG;
            $whmcsSystemUrl = parse_url($CONFIG['SystemURL']);
            $whmcsDomain = $whmcsSystemUrl['host'] ?: trim($whmcsSystemUrl['path'], '/');

            $resellerDomain = Server::getDomainWithoutWwwPrefix($settings['domain']);

            if (strpos(trim($resellerDomain, '/'), '/') !== false) {
                throw new \Exception("DomainWhmcsWithPath");
            }

            if (empty($whmcsDomain)) {
                throw new \Exception("EmptyDomainWhmcs");
            }
            if (empty($resellerDomain)) {
                throw new \Exception("EmptyDomainReseller");
            }
            if ($whmcsDomain === $resellerDomain) {
                throw new \Exception("DomainInWhmcs");
            }

            $repo = new Resellers();
            $reseller = $repo->getResellerByDomainName($resellerDomain);

            if ($reseller->exists && $reseller->id != $this->reseller->id) {
                throw new \Exception("DomainInUse");
            }
        }
    }

    /**
     * Get configuration array from the database
     *
     * @return \MGModule\ResellersCenter\repository\type
     * @throws \ReflectionException
     */
    protected function getData()
    {
        $repo = new ResellersSettings();
        $result = $repo->getSettings($this->reseller->id, $this->isPrivate());

        return $result;
    }

    /**
     * Check configuration type
     *
     * @return bool
     * @throws \ReflectionException
     */
    protected function isPrivate()
    {       
        //determinate setting type
        $classname = (new \ReflectionClass($this))->getShortName();
        $isPrivate = strpos($classname, "Private") === false ? false : true;

        return $isPrivate;
    }
}

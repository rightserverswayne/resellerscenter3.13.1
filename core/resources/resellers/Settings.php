<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Resellers\Settings\AdminSettings;
use MGModule\ResellersCenter\Core\Resources\Resellers\Settings\PrivateSettings;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\repository\ResellersSettings;

/**
 * Description of Settings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Settings
{
    use HasProperties;

    /**
     * Reseller Object
     *
     * @var Reseller
     */
    protected $reseller;

    /**
     * Configuration set by reseller
     * 
     * @var Settings\ResellerSettings
     */
    protected $private;
    
    /**
     * Configuration set by admin
     *
     * @var Settings\AdminSettings 
     */
    protected $admin;

    /**
     * Settings constructor.
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Save settings
     *
     * @param $data
     */
    public function save($data)
    {
        $repo = new ResellersSettings();
        $repo->saveSettings($this->reseller->id, $data);
    }

    /**
     * Generate and get next invoice number
     *
     * @param bool $save
     * @return mixed
     */
    public function getNextInvoiceNumber($save = true)
    {
        $this->private = new Settings\PrivateSettings($this->reseller);
        $format = $this->private->invoicenumber;

        if (empty($format)) {
            return '';
        }

        $next   = $this->private->nextinvoicenumber;

        $format = str_replace("{YEAR}", date("Y"), $format);
        $format = str_replace("{MONTH}", date("m"), $format);
        $format = str_replace("{DAY}", date("d"), $format);
        $format = str_replace("{NUMBER}", $next, $format);

        //update next invoice number
        if ($save) {
            $rawNumber        = $this->private->nextinvoicenumber + 1;
            $resellerSettings = new ResellersSettings();
            $resellerSettings->saveSingleSetting($this->reseller->id, 'nextinvoicenumber', $rawNumber, true);
        }

        return $format;
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
            "private" =>
            [
                "class"     => PrivateSettings::class,
                "parent"    => $this->reseller,
            ],

            "admin" =>
            [
                "class"     => AdminSettings::class,
                "parent"    => $this->reseller,
            ]
        ];
    }
}
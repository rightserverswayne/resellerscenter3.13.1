<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\ResellerProperty;
use MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatable;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\Core\View\Templates\OrderTemplate;

/**
 * Description of View
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class View
{
    use HasProperties;

    /**
     * Reseller Object
     *
     * @var Reseller
     */
    protected $reseller;

    /**
     * Datatable view object
     *
     * @var Datatable
     */
    protected $datatable;

    /**
     * View constructor.
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * @return OrderTemplate
     */
    public function getOrderTemplate()
    {
        $name = Whmcs::getConfig("OrderFormTemplate");
        if ($this->reseller->settings->admin->branding)
        {
            $name = $this->reseller->settings->private->orderTemplate ?: $name;
        }

        $template = new OrderTemplate($name);
        return $template;
    }

    /**
     * Override values in WHMCS config array
     */
    public function overrideConfig()
    {
        global $CONFIG;

        $all = $this->reseller->contents->domains->getAll();
        if(empty($all))
        {
            $CONFIG["AllowRegister"] = false;
            $CONFIG["AllowTransfer"] = false;
        }
    }

    /**
     * Override properties classes
     *
     * @return array
     */
    public function getOverriddenPropertiesClasses()
    {
        return
        [
            "datatable" =>
            [
                "class"  => Datatable::class,
                "parent" => $this->reseller,
            ]
        ];
    }
}

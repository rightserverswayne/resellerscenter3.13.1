<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Products\AbstractProduct;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use \MGModule\ResellersCenter\repository\whmcs\ConfigOptions;
use \MGModule\ResellersCenter\repository\whmcs\ConfigOptionsSubs;

use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\Core\Whmcs\Products\products\Product;

use \MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of Upgrade
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Upgrade extends AbstractProduct
{
    /**
     * @var Hosting
     */
    protected $hosting;

    /**
     * @param $name
     * @return Hosting
     */
    public function __get($name)
    {
        if($name == "hosting")
        {
            $this->hosting = $this->hosting ?: new Hosting($this->model->hosting);
            return $this->hosting;
        }

        return parent::__get($name);
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Upgrade::class;
    }

    /**
     * @param Currency $currency
     * @return Pricing
     */
    public function getPricing(Currency $currency)
    {
        return new Pricing($this, $currency);
    }

    /**
     * @return float
     */
    public function getDaysUntilRenewal()
    {
        $result = 0;
        if($this->hosting->billingcycle != "onetime" && $this->hosting->billingcycle != "free")
        {
            $datediff = strtotime($this->__get("hosting")->nextduedate) - time();
            $result = ceil($datediff / (3600 * 24));
        }

        return $result;
    }

    public function getOldTotalDays($billingcycle = null)
    {
        $totaldays = 0;
        if($this->hosting->billingcycle != "onetime" && $this->hosting->billingcycle != "free")
        {
            $year   = substr($this->hosting->nextduedate, 0, 4);
            $month  = substr($this->hosting->nextduedate, 5, 2);
            $day    = substr($this->hosting->nextduedate, 8, 2);

            $oldCycleMonths = getBillingCycleMonths($billingcycle);
            $prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $oldCycleMonths, $day, $year));
            $totaldays = round((strtotime($this->hosting->nextduedate) - strtotime($prevduedate)) / 86400);
        }

        return $totaldays;
    }

    /**
     * @return float
     */
    public function getNewTotalDays()
    {
        $billingcycle = explode(",", $this->newvalue)[1];

        $year   = substr($this->hosting->nextduedate, 0, 4);
        $month  = substr($this->hosting->nextduedate, 5, 2);
        $day    = substr($this->hosting->nextduedate, 8, 2);

        $newCycleMonths = getBillingCycleMonths($billingcycle);
        $prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $newCycleMonths, $day, $year));
        $newTotalDays = round((strtotime($this->hosting->nextduedate) - strtotime($prevduedate)) / 86400);

        return $newTotalDays;
    }

    /**
     * @return Product
     * @throws \ReflectionException
     */
    public function getNewProduct()
    {
        $pid = explode(",", $this->newvalue)[0];
        $product = new Product($pid, $this->reseller);

        return $product;
    }

    /**
     * @return Product
     * @throws \ReflectionException
     */
    public function getOldProduct()
    {
        $product = new Product($this->originalvalue, $this->reseller);
        return $product;
    }

    /**
     * @return array
     */
    public function getOrderDetails()
    {
        $newvalue = explode(",", $this->newvalue);
        
        $result = [];
        $result["description"] = $this->getOrderDescription();
        $result["billingcycle"] = Lang::absoluteT("billingcycles", $newvalue[1]);
        
        return array_merge($result, $this->model->toArray());
    }

    /**
     * @return string
     */
    public function getOrderDescription()
    {
        $result = "";
        if($this->type == "package")
        {
            $months = ["onetime" => 0, "monthly" => 1, "quarterly" => 3, "semiannually" => 6, "annually" => 12, "biennially" => 24, "triennially" => 36];
            $nowDate = date("Y-m-d");
            $newDate = date("Y-m-d", strtotime($nowDate . " +{$months[$this->newBillingcycle]} Months"));

            $result =  Whmcs::lang("upgradedowngradepackage") . ": {$this->productFrom->name} - {$this->hosting->domain} | {$this->productFrom->name} => {$this->productNew->name} ({$nowDate} - {$newDate})";
        }
        elseif($this->type == "configoptions")
        {
            $original = explode("=>", $this->originalvalue);
            
            $configs = new ConfigOptions();
            $conifg = $configs->find($original[0]);
            
            $result = Lang::T("UpgradeDowngradeOptionsInfo") . ": {$this->hosting->product->name} - {$this->hosting->domain} <br /> ";
            switch($conifg->optiontype)
            {
                case 1:
                case 2:
                    $options = new ConfigOptionsSubs();
                    $old = $options->find($original[1]);
                    $new = $options->find($this->newvalue);
                    
                    $result .= "{$conifg->optionname}: {$old->optionname} => {$new->optionname}";
                    break;
                case 3:
                    $op1 = Lang::T("UpgradeDowngrade", "YesNo", $original[1]);
                    $op2 = Lang::T("UpgradeDowngrade", "YesNo", $this->newvalue);
                    $result .= "{$conifg->optionname}: {$op1} => {$op2}";
                    break;
                case 4:
                    $options = new ConfigOptionsSubs();
                    $option = $options->getByConfigId($original[0]);
                    $result .= "{$conifg->optionname}: {$original[1]} => {$this->newvalue} x {$option[0]->optionname}";
                    break;
            }
        }
        
        return $result;
    }

    public function getConfigurableOptionsUpgradeCost()
    {
        if (!function_exists("SumUpConfigOptionsOrder")) {
            require_once ROOTDIR . "/includes/upgradefunctions.php";
        }

        $upgradeCost = 0;

        if ($_REQUEST["type"] != 'configoptions') {
            return $upgradeCost;
        }

        if ($this->checkConfigOptionsUpgradeIsAvailable($this->hosting->packageid)) {
            $configoptions = $_REQUEST["configoptions"] ?: $_REQUEST["configoption"];
            $promocode = $_REQUEST["promocode"];

            if (!is_array($configoptions)) {
                $configoptions = [];
            }

            $originUserId = null;
            $upgradeUserId = Session::get("RC_UpgradeUid");

            if ($upgradeUserId) {
                $originUserId = Session::get("uid");
                Session::set("uid", $upgradeUserId);
            }

            $upgrades = SumUpConfigOptionsOrder($this->hosting->id, $configoptions, $promocode, $this->hosting->paymentmethod, true);

            if ($originUserId) {
                Session::set("uid", $originUserId);
            }

            foreach ($upgrades as $vals) {
                $upgradeCost += $vals['price']->toNumeric();
            }
        }

        return $upgradeCost;
    }

    private function checkConfigOptionsUpgradeIsAvailable($packageid):bool
    {
        $productInfo = \WHMCS\Database\Capsule::table("tblproducts")->find($packageid, ["tax", "name", "configoptionsupgrade"]);
        return (bool)$productInfo->configoptionsupgrade;
    }

}

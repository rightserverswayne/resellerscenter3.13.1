<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\cart\CartTotals;
use MGModule\ResellersCenter\Core\Cart\Totals;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\Core\Helpers\ClientAreaHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;

use MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain as DomainService;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of OrderDomainRenewalPriceOverride
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderDomainRenewalPriceOverride
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
}
$reseller = Reseller::getCurrent();
if($reseller->exists && Request::get("rp") == "/cart/domain/renew/calculate")
{
    if(!function_exists("calcCartTotals"))
    {
        require_once ROOTDIR.DS."includes".DS."orderfunctions.php";
        require_once ROOTDIR.DS."includes".DS."invoicefunctions.php";
    }

    $client = ClientAreaHelper::getLoggedClient();
    $currency = CartHelper::getCurrency();

    $tax1 = getTaxRate(1, $client->state, $client->country);
    $tax2 = getTaxRate(2, $client->state, $client->country);

    $cart = new Totals();
    $cart->setReseller($reseller)
            ->setCurrency($currency)
            ->setTaxRates($tax1["rate"], $tax2["rate"]);

    $totals = calcCartTotals();
    $renewals = Session::get("cart.renewals") ?: [];
    foreach($renewals as $domainid => $period)
    {
        $domain = new DomainService($domainid);
        $price = $domain->getRelatedProduct()->getPricing($currency, ResellersPricing::TYPE_DOMAINRENEW)->getBrandedPrice(Pricing::DOMAIN_PEROIDS[$period]);

        $totals["renewals"][$domainid]["price"] = formatCurrency($price);
        $totals["renewals"][$domainid]["priceBeforeTax"] = formatCurrency($price);

        $cart->products->addFromSource(ResellersPricing::TYPE_DOMAINRENEW, ["domainid" => $domainid, "period" => $period]);
    }

    //Recalculate whole cart
    $totals = array_merge($cart->getCartTotal(), $cart->getRenewalsTotal());
    $html   = $reseller->view->getOrderTemplate()->parseSingleTemplate("ordersummary",
    [
        "renewals"      => true,
        "carttotals"    => $totals,
    ]);

    $response = new \WHMCS\Http\JsonResponse();
    $response->setData(["body" => $html]);
    $response->send();
    \WHMCS\Terminus::getInstance()->doExit();
}

<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Cart\Order\ViewDecorator;
use MGModule\ResellersCenter\Core\Helpers\ClientAreaHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\CreditLineHistories;
use MGModule\ResellersCenter\Addon;

class ClientAreaHomepagePanels
{
    public $functions;

    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($params)
        {
            self::$params = $this->addCreditLinePanels($params);
        };
    }

    public function addCreditLinePanels($params)
    {
        $client = ClientAreaHelper::getLoggedClient();

        $creditLineService = new CreditLineService();
        $creditLine = $creditLineService->getEnableCreditLine($client->id);

        $reseller = Reseller::getByCurrentURL();

        if ($creditLine->limit > 0 && ($reseller->exists || Reseller::isReseller($client->id))) {
            $lang = Lang::getInstance();

            $decorator = new ViewDecorator();
            $creditLinesRepo = new CreditLineHistories();
            $orders = $creditLinesRepo->getAllOrdersActivatedByCreditLine($client->id);
            $ordersCount = count($orders);

            $creditLeft = $creditLine->limit - $creditLine->usage;
            $formattedPriceLimit = formatCurrency($creditLine->limit, $client->currency);
            $formattedPriceUsage = formatCurrency($creditLine->usage, $client->currency);
            $formattedPriceLeft = formatCurrency($creditLeft, $client->currency);

            $assetsStylesLink = \MGModule\ResellersCenter\Addon::I()->getAssetsURL(). '/css/mg-style.css';

            $creditLineOrdersPanel =$params->addChild('creditLineBalance', [
                    'label' => $lang->T('panels', 'creditLineBalance', 'label'),
                    'icon' => 'far fa-credit-card-blank',
                    'order' => '200',
                    'extras' => ['color' => 'blue'],
                    'bodyHtml' =>
                        "<link rel='stylesheet' type='text/css' href=$assetsStylesLink rel='stylesheet'>
                <div class='row mx-0'>
                    <div class='col-md-6 creditInfoItem'>
                        <div class='row mx-0 creditInfoItemContent creditInfoItemLimit'>
                            $formattedPriceLimit
                        </div>
                        <div class='row mx-0 creditInfoItemTitle'>{$lang->T('panels', 'creditLineBalance', 'limit')}</div>
                    </div>
                    <div class='col-md-6 creditInfoItem'>
                        <div class='row mx-0 creditInfoItemContent creditInfoItemUsage'>
                            $formattedPriceUsage
                        </div>
                        <div class='row mx-0 creditInfoItemTitle'>{$lang->T('panels', 'creditLineBalance', 'usage')}</div>
                    </div>
                </div>
                <div class='row mx-0'>
                    <div class='col-md-6 creditInfoItem'>
                        <div class='row mx-0 creditInfoItemContent creditInfoItemLeft'>
                            $formattedPriceLeft
                        </div>
                        <div class='row mx-0 creditInfoItemTitle'>{$lang->T('panels', 'creditLineBalance', 'left')}</div>
                    </div>
                    <div class='col-md-6 creditInfoItem'>
                        <div class='row mx-0 creditInfoItemContent creditInfoItemInvoices'>
                            $ordersCount
                        </div>
                        <div class='row mx-0 creditInfoItemTitle'>{$lang->T('panels', 'creditLineBalance', 'pendingInvoices')}</div>
                    </div>
                </div>"
                ]);

            if ($ordersCount) {
                $itemName = $lang->T('panels', 'creditLineOrders', 'label');
                $item = $decorator->getWhmcsKnpMenuItem(0, $itemName, '');
                $item->setClass('creditOrders');
                $creditLineOrdersPanel->addChild($item);

                foreach ($orders as $order) {
                    $formattedAmount = formatCurrency($order->amount , $client->currency);
                    $itemName = '#' . $order->id . " ";
                    $itemName .= " ". $lang->T('panels', 'creditLineOrders', 'amount'). ": " . $formattedAmount . " ";
                    $itemName .= " " . $lang->T('panels', 'creditLineOrders', 'date'). ": " . $order->date . " ";
                    $item = $decorator->getWhmcsKnpMenuItem($order->id, $itemName, $order->link);
                    $creditLineOrdersPanel->addChild($item);
                }
            }
        }

        return $params;
    }
}
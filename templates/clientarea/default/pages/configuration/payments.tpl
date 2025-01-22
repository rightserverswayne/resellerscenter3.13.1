<div class="box light">
    <div class="box-title tabbable-line">

        <ul class="nav nav-tabs nav-left nav-paymantconfiguration">
            
            <div class="nav-icon non" disabled="true">
                <i class="glyphicon glyphicon-move"></i>
            </div>

            {foreach from=$gateways key=index item=gateway name=gatewayTabsName}
                <li class="gateways-item {if $smarty.foreach.gatewayTabsName.index eq 0}active{/if}">
                    <a href="#RCPayments{$index}" data-toggle="tab" data-gateway="{$gateway->name}">
                        {$gateway->adminName}
                    </a>
                </li>
            {/foreach}
        </ul>
        
    </div>
    <div class="box-body" style='min-height: 320px;'>
                
        <div class="tab-content">
            {foreach from=$gateways key=index item=gateway name=gatewayTabsContent}
                <div id="RCPayments{$index}" class="tab-pane {if $smarty.foreach.gatewayTabsContent.index eq 0}active{/if}" >
                    <div class="scroller">
                        <form class="paymentGatewayForm">
                            {$gateway->config(['gateways', $gateway->name])}
                        </form>
                    </div>
                </div>
            {/foreach}
        </div>
        
    </div>
</div>
        
<script>
    $('li.gateways-item').on('click', function(){
        $('div.tab-pane').removeClass('active');
        var href = $(this).find('a').attr('href').substring(1);
        $('#' + href).addClass('active')

        $('.payments').addClass('active');
        $('.generalPaymentsItem').addClass('active');
    });

    document.addEventListener('DOMContentLoaded',function(){
        $('.generalPaymentsItem > a:nth-child(1)').on('click',function() {
            item = $('li.gateways-item:nth-child(2)');
            item.addClass("active");
        });

    });


</script>

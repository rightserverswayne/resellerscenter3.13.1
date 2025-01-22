{**********************************************************************
* ResellersCenter product developed. (2016-07-21)
*
*
*  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
*  CONTACT                        ->       contact@modulesgarden.com
*
*
* This software is furnished under a license and may be used and copied
* only  in  accordance  with  the  terms  of such  license and with the
* inclusion of the above copyright notice.  This software  or any other
* copies thereof may not be provided or otherwise made available to any
* other person.  No title to and  ownership of the  software is  hereby
* transferred.
*
*
**********************************************************************}

{**
* @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
*}
<div id="RCOrders">
    <div class="box light">
        <div class="box-title tabbable-line">

            <div class="caption">
                <i class="fa fa-shopping-cart font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>

            <ul class="nav nav-tabs">
                <li role="presentation" class="active">
                    <a href="#RCOrdersTab" aria-controls="RCOrdersTab" role="tab" data-toggle="tab">{$MGLANG->T('orders','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCServicesTab" aria-controls="RCServicesTab" role="tab" data-toggle="tab">{$MGLANG->T('hosting','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCAddonsTab" aria-controls="RCAddonsTab" role="tab" data-toggle="tab">{$MGLANG->T('addons','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCDomainsTab" aria-controls="RCDomainsTab" role="tab" data-toggle="tab">{$MGLANG->T('domains','title')}</a>
                </li>
            </ul>
        </div>

        <div class="box-body">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="RCOrdersTab">
                    {include file='orders/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCServicesTab">
                    {include file='services/hosting/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCAddonsTab">
                    {include file='services/addons/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCDomainsTab">
                    {include file='services/domains/base.tpl'}
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    $('.nav-tabs a').click(function(){
        $('.nav-tabs a').tab('show')
    })
</script>

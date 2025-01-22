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
<div id="RCPricing" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-dollar font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('title')}
            </span>
        </div>
           
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="pricingSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="ResellersCenter_Pricing.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" onclick="ResellersCenter_Pricing.addNewItem();" class="openPricingModal btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-plus"></i>
            </a>
        </div>
            
        <ul class="nav nav-tabs">
            {if $settings->admin->products}
                <li class="active">
                    <a href="#RCPricingProducts" data-toggle="tab" >
                        {$MGLANG->T('products', 'title')}
                    </a>
                </li>
                <li>
                    <a href="#RCPricingAddons" data-toggle="tab">
                        {$MGLANG->T('addons', 'title')}
                    </a>
                </li>
            {/if}

            {if $settings->admin->domains}
                <li {if !$settings->admin->products}class="active"{/if}>
                    <a href="#RCPricingDomains" data-toggle="tab">
                        {$MGLANG->T('domains', 'title')}
                    </a>
                </li>
            {/if}
        </ul>
                        
    </div>
    <div class="box-body">
            <div class="tab-content">
                {if $settings->admin->products}
                    {* PRODUCTS *}
                    <div class="tab-pane active" id="RCPricingProducts">
                        {include file='products/base.tpl'}
                    </div>

                    {* ADDONS *}
                    <div class="tab-pane" id="RCPricingAddons">
                        {include file='addons/base.tpl'}
                    </div>
                {/if}
                            
                {if $settings->admin->domains}
                    {* DOMAINS *}
                    <div class="tab-pane {if !$settings->admin->products}active{/if}" id="RCPricingDomains">
                        {include file='domains/base.tpl'}
                    </div>
                {/if}
            </div>
            
            {if !$settings->admin->products && !$settings->admin->domains}
                <center>
                    <div class="help-block">
                        {$MGLANG->T('noConfigurationAvailable')}
                    </div>
                </center>
            {/if}
    </div>
</div>

<script type="text/javascript">
    {include file='controller.js'}
</script>
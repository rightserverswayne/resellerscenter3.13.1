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
<div class='row'>
    <div class='col-md-6'>
        
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','paypal','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[paypalAutoTransfer]"
                            {if $settings->paypalAutoTransfer}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('billing','paypal','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','resellerinvoice','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[resellerInvoice]"
                            {if $settings->resellerInvoice}checked{/if}
                            {if $reseller->hasRelatedInvoices}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('billing','resellerinvoice','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','invoice','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[invoiceBranding]"
                            {if $settings->invoiceBranding}checked{/if}
                            {if $settings->resellerInvoice && $reseller->hasRelatedInvoices}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','invoice','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','disableEndClientInvoices','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[disableEndClientInvoices]"
                           {if $settings->disableEndClientInvoices}checked{/if}
                           {if !$settings->resellerInvoice}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','disableEndClientInvoices','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','allowCreditPayment','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[allowCreditPayment]"
                           {if $settings->allowCreditPayment}checked{/if}
                           {if !$settings->resellerInvoice}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','allowCreditPayment','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','configoptions','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[configoptions]"
                           {if $settings->configoptions}checked{/if}
                           {if $settings->resellerInvoice}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','configoptions','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','allowcreditline','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[allowcreditline]"
                           {if $settings->allowcreditline}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','allowcreditline','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','checkResellerAlso','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[checkResellerAlso]"
                           {if $settings->checkResellerAlso}checked{/if}
                            {if !$settings->resellerInvoice}disabled{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','checkResellerAlso','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','removeZeroInvoices','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[removeZeroInvoices]"
                           {if $settings->removeZeroInvoices}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','removeZeroInvoices','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','disableZeroConsolidated','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[disableZeroConsolidated]"
                           {if $settings->disableZeroConsolidated}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm1-6'>
                <div class='help-block'>{$MGLANG->T('billing','disableZeroConsolidated','help')}</div>
            </div>
        </div>
    </div>
            
    <div class='col-md-6'>
        
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','gateways','label')}</label>
            </div>
            <div class='col-md-12'>
                <select {if $settings->resellerInvoice}disabled{/if} multiple class="form-control select2" name="settings[gateways][]">
                    {foreach from=$gateways key=name item=gateway}
                        <option value="{$name}" {if $settings->gateways && $name|in_array:$settings->gateways}selected{/if}>
                            {$gateway.name}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','gateways','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','nextinvoicenumber','label')}</label>
            </div>
            <div class="col-md-12">
                <input class="form-control input-sm" 
                       name="settings[nextinvoicenumber]"
                       {if $privatesettings->nextinvoicenumber}disabled="disabled"{/if}
                       value="{if $privatesettings->nextinvoicenumber}{$privatesettings->nextinvoicenumber}{else}{$settings->nextinvoicenumber}{/if}" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="help-block">{$MGLANG->T('billing','nextinvoicenumber','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','invoicenumber','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       name="settings[invoicenumber]"
                       {if $privatesettings->invoicenumber}disabled="disabled"{/if}
                       value="{if $privatesettings->invoicenumber}{$privatesettings->invoicenumber}{else}{$settings->invoicenumber}{/if}"/>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','invoicenumber','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','creditlinelimit','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       type ="number"
                       min="0"
                       step="0.01"
                       name="creditLineSettings[creditlinelimit]"
                       value="{if $creditline->limit}{$creditline->limit}{else}0{/if}"
                       {if !$settings->resellerInvoice}disabled{/if}/>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','creditlinelimit','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','enableConsolidatedInvoices','label')}</label>
            </div>

            <div class='col-md-12'>
                <select class="form-control select" name="settings[enableConsolidatedInvoices]" {if !$settings->resellerInvoice}disabled{/if}>
                    <option value="">
                        {$MGLANG->T('billing','consolidatedSettingSelect','asGroup')}
                    </option>
                    {if not $resellerHasUnpaidInvoices || $settings->enableConsolidatedInvoices eq "on"}
                        <option value="on" {if $settings->enableConsolidatedInvoices eq "on"}selected{/if}>
                            {$MGLANG->T('billing','consolidatedSettingSelect','on')}
                        </option>
                    {/if}
                    <option value="off" {if $settings->enableConsolidatedInvoices eq "off"}selected{/if}>
                        {$MGLANG->T('billing','consolidatedSettingSelect','off')}
                    </option>
                </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','enableConsolidatedInvoices','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','consolidatedInvoicesDay','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       type ="number"
                       min="1"
                       max="31"
                       step="1"
                       name="settings[consolidatedInvoicesDay]"
                       value="{if $settings->consolidatedInvoicesDay}{$settings->consolidatedInvoicesDay}{else}1{/if}"/>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','consolidatedInvoicesDay','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','endClientConsolidatedInvoices','label')}</label>
            </div>
            <div class='col-md-12'>
                <select class="form-control select" name="settings[endClientConsolidatedInvoices]">
                    <option value="">
                        {$MGLANG->T('billing','consolidatedSettingSelect','asGroup')}
                    </option>
                    {if not $endClientHasUnpaidInvoices || $settings->endClientConsolidatedInvoices eq "on"}
                        <option value="on" {if $settings->endClientConsolidatedInvoices eq "on"}selected{/if}>
                            {$MGLANG->T('billing','consolidatedSettingSelect','on')}
                        </option>
                    {/if}
                    <option value="off" {if $settings->endClientConsolidatedInvoices eq "off"}selected{/if}>
                        {$MGLANG->T('billing','consolidatedSettingSelect','off')}
                    </option>
                </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','endClientConsolidatedInvoices','help')}</div>
            </div>
        </div>
    </div>
</div>       
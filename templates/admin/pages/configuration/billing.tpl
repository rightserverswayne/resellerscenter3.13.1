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
<div class="row">
    <div class='col-md-12'>
        <div class="help-block">{$MGLANG->T('billing', 'help')}</div>
    </div>
</div>
    
<div class='row'>
    <div class='col-md-6'>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label>{$MGLANG->T('billing','paypal','label')}</label>
            </div>
            <div class='col-md-2 col-sm-2'>
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
                            {if $settings.paypalAutoTransfer}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-7 col-sm-7'>
                <div class='help-block'>{$MGLANG->T('billing','paypal','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','invoice','label')}</label>
            </div>
            <div class='col-md-2 col-sm-2'>
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
                            {if $settings.invoiceBranding}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-7 col-sm-7'>
                <div class='help-block'>{$MGLANG->T('general','invoice','help')}</div>
            </div>
        </div>
                
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','resellerinvoice','label')}</label>
            </div>
            <div class='col-md-2 col-sm-2'>
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
                            {if $settings.resellerInvoice}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-7 col-sm-7'>
                <div class='help-block'>{$MGLANG->T('billing','resellerinvoice','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('billing','allowcreditline','label')}</label>
            </div>
            <div class='col-md-2 col-sm-2'>
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
                           {if $settings.allowcreditline}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-7 col-sm-7'>
                <div class='help-block'>{$MGLANG->T('billing','allowcreditline','help')}</div>
            </div>
        </div>
    </div>
            
    <div class='col-md-6'>

        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('billing','gateways','label')}</label>
            </div>
            <div class='col-md-12'>
                {if empty($settings.gateways)}
                    {$settings.gateways = []}
                {/if}
                <select {if $settings.resellerInvoice}disabled{/if} multiple class="form-control select2" name="settings[gateways][]">
                    {foreach from=$gateways key=name item=gateway}
                        <option value="{$name}" {if $name|in_array:$settings.gateways}selected{/if}>
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
                <label>{$MGLANG->T('billing','invoicenumber','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       name="settings[invoicenumber]"
                       value="{if $settings.invoicenumber}{$settings.invoicenumber}{else}{ldelim}NUMBER{rdelim}{/if}"/>
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
                       name="settings[creditlinelimit]"
                       value="{if $settings.creditlinelimit}{$settings.creditlinelimit}{else}0{/if}"/>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('billing','creditlinelimit','help')}</div>
            </div>
        </div>

    </div>
</div>       
       
            
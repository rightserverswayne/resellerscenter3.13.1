<form action="">
    
    {* Show documentation and dashboard status*}
    <input hidden name="settings[docsDoNotShowAgain]" value="{$settings->docsDoNotShowAgain}" />
    <input hidden name="settings[skipResellerDashboard]" value="{$settings->skipResellerDashboard}" />
     
    <div class="row">
        <div class="col-md-6">
            
            {if $globalSettings->cname}
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','domain','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[domain]" value="{$settings->domain}" />
                            <div class="help-block">{$MGLANG->T('general','domain','help')}</div>
                        </div>
                    </div>
                </div>
            {else}
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','resellerurl','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input readonly="readonly" class="form-control input-sm" value="{$resellerUrl}"/>
                            <div class="help-block">{$MGLANG->T('general','resellerurl','help')}</div>
                        </div>
                    </div>
                </div>
            {/if}
                    
            {if $globalSettings->branding}
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','companyname','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[companyName]" value="{$settings->companyName}" />
                            <div class="help-block">{$MGLANG->T('general','companyname','help')}</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','email','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[email]" value="{$settings->email}" />
                            <div class="help-block">{$MGLANG->T('general','email','help')}</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','tos','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[tos]" value="{$settings->tos}" />
                            <div class="help-block">{$MGLANG->T('general','tos','help')}</div>
                        </div>
                    </div>
                </div>
            {/if}
                    
            {if not $globalSettings->resellerInvoice}
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','paypalemail','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[paypalEmail]" value="{$settings->paypalEmail}" />
                            <div class="help-block">{$MGLANG->T('general','paypalemail','help')}</div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>

        <div class="col-md-6">
            {if $globalSettings->branding}
                
                {if $globalSettings->whmcsTemplates}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label>{$MGLANG->T('general','template','label')}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control input-sm" name="settings[whmcsTemplate]">
                                    {foreach from=$globalSettings->whmcsTemplates item=template}
                                        <option value="{$template}" {if $settings->whmcsTemplate eq $template}selected{/if}>{$template}</option>
                                    {/foreach}
                                </select>
                                <div class="help-block">{$MGLANG->T('general','template','help')}</div>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $globalSettings->orderTemplates}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label>{$MGLANG->T('general','ordertemplate','label')}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control input-sm" name="settings[orderTemplate]">
                                    {foreach from=$globalSettings->orderTemplates item=template}
                                        <option value="{$template}" {if $settings->orderTemplate eq $template}selected{/if}>{$template}</option>
                                    {/foreach}
                                </select>
                                <div class="help-block">{$MGLANG->T('general','ordertemplate','help')}</div>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $globalSettings->customDateFormat}
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','dateFormat','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-control input-sm" name="settings[dateFormat]">
                                {foreach from=$dateFormats item=format}
                                    <option value="{$format}" {if $settings->dateFormat eq $format}selected{/if}>{$format}</option>
                                {/foreach}
                            </select>
                            <div class="help-block">{$MGLANG->T('general','dateFormat','help')}</div>
                        </div>
                    </div>
                </div>
                {/if}
            {/if}
                    
        </div>
    </div>
        
    {if $globalSettings->invoiceBranding && !$globalSettings->disableEndClientInvoices}
        <hr />
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','invoicenumber','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[invoicenumber]" value="{$settings->invoicenumber}" />
                            <div class="help-block">{$MGLANG->T('general','invoicenumber','help')}</div>
                        </div>
                    </div>
                </div>
                        
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','nextinvoicenumber','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control input-sm" name="settings[nextinvoicenumber]" value="{if $settings->nextinvoicenumber neq ''}{$settings->nextinvoicenumber}{else}1{/if}" />
                            <div class="help-block">{$MGLANG->T('general','nextinvoicenumber','help')}</div>
                        </div>
                    </div>
                </div>
                        
                {if $globalSettings->resellerInvoice}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label" style="padding-top: 6px;">{$MGLANG->T('general','autoWhmcsInvoicePayment','label')}</label>
                            </div>
                            <div class='col-md-2 col-sm-2'>
                                <div class="checkbox-container">
                                    <input  class="checkbox-switch"
                                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                            data-on-color="success" 
                                            data-off-color="default"  
                                            data-size="mini" 
                                            data-label-width="15" 
                                            type="checkbox"  
                                            name="settings[autoWhmcsInvoicePayment]"
                                            {if $settings->autoWhmcsInvoicePayment}checked{/if}
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <div class='help-block'>{$MGLANG->T('general','autoWhmcsInvoicePayment','help')}</div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','payto','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <textarea class="form-control input-sm" name="settings[payto]" style="min-height: 100px">{$settings->payto}</textarea>
                            <div class="help-block">{$MGLANG->T('general','payto','help')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if $globalSettings->branding}
        <hr />

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','signature','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <textarea class="form-control input-sm" name="settings[signature]" style="min-height: 100px">{$settings->signature}</textarea>
                            <div class="help-block">{$MGLANG->T('general','signature','help')}</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','showInvoiceLogo','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="checkbox-container">
                                <input  class="checkbox-switch"
                                        data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                        data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                        data-on-color="success"
                                        data-off-color="default"
                                        data-size="mini"
                                        data-label-width="15"
                                        type="checkbox"
                                        name="settings[showInvoiceLogo]"
                                        {if $settings->showInvoiceLogo}checked{/if}/>
                            </div>
                            <div class="help-block">{$MGLANG->T('general','showInvoiceLogo','help')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">

                <div class="form-group">
                    {* Logo file - required for upload *}
                    <input type="file" name="logoFile" value="" style="display: none;" /> 

                    {* Logo URL for settings *}
                    <input hidden type="text" name="settings[logo]" value="{$settings->logo}" /> 

                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','logo','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="logo-container">
                                <img style="max-width: 100%" src="modules/addons/ResellersCenter/storage/logo/{$settings->logo}" alt="Click here to upload logo">
                                <span class="uploadLogoButton">{$MGLANG->T('general','logo','uploadBtn')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <div class="help-block">{$MGLANG->T('general','logo','help')}</div>
                            <a class="deleteLogo" href="#">{$MGLANG->T('general','logo','delete')}</a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="form-group">
                    {* Logo file - required for upload *}
                    <input type="file" name="invoiceLogoFile" value="" style="display: none;" />

                    {* Logo URL for settings *}
                    <input hidden type="text" name="settings[invoiceLogo]" value="{$settings->invoiceLogo}" />

                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('general','invoiceLogo','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="invoiceLogo-container">
                                <img style="max-width: 100%" src="modules/addons/ResellersCenter/storage/logo/{$settings->invoiceLogo}" alt="Load image failed">
                                <span class="uploadLogoButton">{$MGLANG->T('general','invoiceLogo','uploadBtn')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <div class="help-block">{$MGLANG->T('general','invoiceLogo','help')}</div>
                            <a class="deleteInvoiceLogo" href="#" >{$MGLANG->T('general','invoiceLogo','delete')}</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    {/if}
    
    {if !$globalSettings->branding && !$globalSettings->cname && !$globalSettings->paypalAutoTransfer}
        <center>
            <div class="help-block">
                {$MGLANG->T('general','noConfigurationAvailable')}
            </div>
        </center>
    {/if}
                    
</form>

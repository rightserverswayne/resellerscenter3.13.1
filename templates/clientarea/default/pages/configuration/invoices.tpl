<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label>{$MGLANG->T('invoices','paidinvoicenumbering','label')}</label>
                </div>
                <div class="col-md-6">
                    <input  class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[paidinvoicenumbering]"
                            {if $settings->paidinvoicenumbering}checked{/if}
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="help-block">{$MGLANG->T('invoices','paidinvoicenumbering','help')}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">         
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label>{$MGLANG->T('invoices','nextinvoicenumber','label')}</label>
                </div>
                <div class="col-md-6">
                    <input class="form-control input-sm" 
                           name="settings[nextinvoicenumber]"
                           value="{if $settings->nextinvoicenumber}{$settings->nextinvoicenumber}{else}{$globalsettings->nextinvoicenumber}{/if}" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="help-block">{$MGLANG->T('invoices','nextinvoicenumber','help')}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
       
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label>{$MGLANG->T('invoices','invoicenumber','label')}</label>
                </div>
                <div class="col-md-12">
                    <input class="form-control input-sm" 
                           name="settings[invoicenumber]" 
                           value="{if $settings->invoicenumber}{$settings->invoicenumber}{else}{$globalsettings->invoicenumber}{/if}" />

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="help-block">{$MGLANG->T('invoices','invoicenumber','help')}</div>
                </div>
            </div>
        </div>
                
    </div>
</div>
<form action="">

    {* Show documentation and dashboard status*}
    <input hidden name="settings[docsDoNotShowAgain]" value="{$settings->docsDoNotShowAgain}" />
    <input hidden name="settings[skipResellerDashboard]" value="{$settings->skipResellerDashboard}" />

    {if $endClientConsolidatedInvoices eq 'on'}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label>{$MGLANG->T('billings','enableConsolidatedInvoices','label')}</label>
                        </div>
                        <div class="col-md-6">
                            <div class="checkbox-container">
                                <input  class="checkbox-switch"
                                        data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                        data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                        data-on-color="success"
                                        data-off-color="default"
                                        data-size="mini"
                                        data-label-width="15"
                                        type="checkbox"
                                        name="settings[enableConsolidatedInvoices]"
                                        {if $settings->enableConsolidatedInvoices}checked{/if}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label>{$MGLANG->T('billings','consolidatedInvoicesDay','label')}</label>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control input-sm" type="number" name="settings[consolidatedInvoicesDay]" min="1" max="31"
                                   value="{if $settings->consolidatedInvoicesDay neq ''}{$settings->consolidatedInvoicesDay}{else}1{/if}"/>
                            <div class="help-block">{$MGLANG->T('billings','consolidatedInvoicesDay','help')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if $globalSettings->allowcreditline}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label>{$MGLANG->T('billings','defaultCreditLineLimit','label')}</label>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control input-sm" type="number" name="settings[defaultCreditLineLimit]" min="1" max="31"
                                   value="{if $settings->defaultCreditLineLimit neq ''}{$settings->defaultCreditLineLimit}{else}0{/if}"/>
                            <div class="help-block">{$MGLANG->T('billings','defaultCreditLineLimit','help')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</form>

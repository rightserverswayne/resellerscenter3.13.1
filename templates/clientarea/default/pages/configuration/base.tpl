{if $noGatewayEnabled && !$globalSettings->disableEndClientInvoices}
    <div class="noGatewayEnabled alert alert-danger">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
        <p><strong>{$MGLANG->T('noGatewaysInfo')}</strong></p>
    </div>
{/if}

<div id="RCConfiguration" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-cogs font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('title')}
            </span>
        </div>
            
        <ul class="nav nav-tabs">
            <li class="active">
                <a id="RCConfigGeneralLi" href="#RCConfigGeneral" data-toggle="tab" >
                    {$MGLANG->T('general', 'title')}
                </a>
            </li>
            
            <li>
                <a id="RCConfigEmailTemplatesLi" href="#RCConfigEmailTemplates" data-toggle="tab" >
                    {$MGLANG->T('emails', 'title')}
                </a>
            </li>

            {if $globalSettings->resellerInvoice eq 'on' && !$globalSettings->disableEndClientInvoices}
                <li class="generalPaymentsItem">
                    <a href="#RCConfigPayments" data-toggle="tab">
                        {$MGLANG->T('payments', 'title')}
                    </a>
                </li>
            {/if}

            {if $endClientConsolidatedInvoices eq 'on' || $globalSettings->allowcreditline}
                <li>
                    <a href="#RCConfigBillings" data-toggle="tab" >
                        {$MGLANG->T('billings', 'title')}
                    </a>
                </li>
            {/if}

            {if $globalSettings->customMailSettings eq 'on'}
                <li class="customMailSettings">
                    <a href="#RCEmailSettings" data-toggle="tab">
                        {$MGLANG->T('emailSettings', 'title')}
                    </a>
                </li>
            {/if}

            <li class="dataExportTab">
                <a href="#RCAPIAccess" data-toggle="tab" >
                    {$MGLANG->T('apiAccess', 'title')}
                </a>
            </li>
        </ul>
        
    </div>
    <div class="box-body">
        <form action=''>
            <div class="tab-content">
                <div class="tab-pane active" id="RCConfigGeneral">
                    <div class="scroller">
                        {include file='general.tpl'}
                    </div>
                </div>
                    
                <div class="tab-pane" id="RCConfigEmailTemplates">
                    <div class="scroller">
                        {include file='emailTemplates/list.tpl'}
                    </div>
                </div>

               {if $globalSettings->resellerInvoice eq 'on' && !$globalSettings->disableEndClientInvoices}
                    <div class="tab-pane payments" id="RCConfigPayments">
                        <div class="scroller">
                            {include file='payments.tpl'}
                        </div>
                    </div>
                {/if}

                <div class="tab-pane" id="RCConfigServiceTerms">
                    <div class="scroller">
                        {include file='serviceTerms/base.tpl'}
                    </div>
                </div>

                <div class="tab-pane" id="RCConfigBillings">
                    <div class="scroller">
                        {include file='billings.tpl'}
                    </div>
                </div>

                {if $globalSettings->customMailSettings eq 'on'}
                    <div class="tab-pane customMailSettings" id="RCEmailSettings">
                        <div class="scroller">
                            {include file='emailSettings.tpl'}
                        </div>
                    </div>
                {/if}

                <div class="tab-pane" id="RCAPIAccess">
                    <div class="scroller">
                        {include file='dataExport/base.tpl'}
                    </div>
                </div>
           
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <button class="btn btn-lg btn-success btn-inverse" onclick="ResellersCenter_Configuration.submitConfigForm();">{$MGLANG->T('form','save')}</button>
        </div>
    </div>

</div>

<script type="text/javascript">
    {include file='controller.js'}
</script>

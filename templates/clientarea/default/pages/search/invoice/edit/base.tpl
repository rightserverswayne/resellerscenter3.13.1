<div id="RCInvoiceEdit" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->absoluteT('addonCA','invoices','edit','title')}</h4>
                <div class="box-title tabbable-line pull-right">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#RCInvoiceEditDetails" aria-controls="RCInvoiceEditDetails" role="tab" data-toggle="tab">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'title')}</a>
                        </li>
                        {if !($reseller->settings->admin->disableEndClientInvoices && $reseller->settings->admin->resellerInvoice)}
                        <li role="presentation">
                            <a href="#RCInvoiceEditTransactions" aria-controls="RCInvoiceEditTransactions" role="tab" data-toggle="tab">{$MGLANG->absoluteT('addonCA','invoices','edit', 'transactions','title')}</a>
                        </li>
                        {/if}
                    </ul>
                </div>

            </div>
            <div class="modal-body">

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="RCInvoiceEditDetails">
                        {include file='invoice/edit/details.tpl'}
                    </div>
                    {if !($reseller->settings->admin->disableEndClientInvoices && $reseller->settings->admin->resellerInvoice)}
                    <div role="tabpanel" class="tab-pane" id="RCInvoiceEditTransactions">
                        {include file='invoice/edit/transactions.tpl'}
                    </div>
                    {/if}
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick="ResellersCenter_InvoiceEdit.submitEditForm();" data-dismiss="modal">{$MGLANG->absoluteT('form','button','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
            
<script type="text/javascript">
    {include file='invoice/edit/controller.js'}
</script>
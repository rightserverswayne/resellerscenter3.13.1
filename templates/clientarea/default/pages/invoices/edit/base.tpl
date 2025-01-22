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
<div id="RCInvoiceEdit" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('edit','title')}</h4>

                <div class="box-title tabbable-line pull-right">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#RCInvoiceEditDetails" aria-controls="RCInvoiceEditDetails" role="tab" data-toggle="tab">{$MGLANG->T('edit', 'details', 'title')}</a>
                        </li>
                        {if !($reseller->settings->admin->disableEndClientInvoices && $reseller->settings->admin->resellerInvoice)}
                        <li role="presentation">
                            <a href="#RCInvoiceEditTransactions" aria-controls="RCInvoiceEditTransactions" role="tab" data-toggle="tab">{$MGLANG->T('edit', 'transactions','title')}</a>
                        </li>
                        {/if}
                    </ul>
                </div>

            </div>
            <div class="modal-body">

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="RCInvoiceEditDetails">
                        {include file='edit/details.tpl'}
                    </div>
                    {if !($reseller->settings->admin->disableEndClientInvoices && $reseller->settings->admin->resellerInvoice)}
                    <div role="tabpanel" class="tab-pane" id="RCInvoiceEditTransactions">
                        {include file='edit/transactions.tpl'}
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
    {include file='edit/controller.js'}
</script>
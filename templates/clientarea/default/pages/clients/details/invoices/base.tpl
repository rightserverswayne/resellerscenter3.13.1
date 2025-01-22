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
    
<div class="box light">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                {if $reseller->settings->admin->resellerInvoice}
                    <div id="RCClientsInvoicesRC">
                        <table class="table table-hover" width="100%" data-invoicetype="rc">
                            <thead>
                                <th>{$MGLANG->T('invoices','table','id')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','date')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','date')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','duedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','total')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','paymentmethod')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','status')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','actions')}&nbsp;&nbsp;</th>
                            </thead>
                        </table>
                    </div>
                {else}
                    <div id="RCClientsInvoicesWHMCS">
                        <table class="table table-hover" width="100%">
                            <thead>
                                <th>{$MGLANG->T('invoices','table','id')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','date')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','date')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','duedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','total')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','paymentmethod')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','status')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','actions')}&nbsp;&nbsp;</th>
                            </thead>
                        </table>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
                    
{* WHMCS Invoice Details *}                    
{include file='details/invoices/details.tpl'}

{* RC Invoice Details *}                    
{include file='details/invoices/edit/base.tpl'}
                    
<script type="text/javascript">
    {include file='details/invoices/controller.js'}
</script>


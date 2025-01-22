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
<div id="invoices" class="box light">
    <div class="box-title tabbable-line">
        <ul class="nav nav-tabs nav-left">
            {if $settings->resellerInvoice}
                <li class="active">
                    <a href="#invoicesTabRC" data-toggle="tab">{$MGLANG->T('invoices','rc','title')}</a>
                </li>
            {else}
                <li class="active">
                    <a href="#invoicesTabWHMCS" data-toggle="tab">{$MGLANG->T('invoices','whmcs','title')}</a>
                </li>
            {/if}
        </ul>
            
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="invoicesListSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="invoicesListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="RC_ResellersInvoices.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" class="btn btn-circle btn-outline btn-inverse btn-default btn-icon-only" data-toggle="tooltip"  title="{$MGLANG->T('invoices','help')}">
                <i class="fa fa-question"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="tab-content">
            {if $settings->resellerInvoice}
                <div class="tab-pane active" id="invoicesTabRC">
                    <div class="scroller">
                        <table id='rcInvoicesList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                            <thead>
                                <th>{$MGLANG->T('invoices','table','#ID')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','client')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','invoicedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','duedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','total')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','paymentmethod')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','status')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','actions')}&nbsp;&nbsp;</th>
                            </thead>
                        </table>
                    </div>
                </div>
            {else}
                <div class="tab-pane active" id="invoicesTabWHMCS">
                    <div class="scroller">
                        <table id='whmcsInvoicesList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                            <thead>
                                <th>{$MGLANG->T('invoices','table','#ID')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','client')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','invoicedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','duedate')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','total')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','paymentmethod')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','status')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','table','actions')}&nbsp;&nbsp;</th>
                            </thead>
                        </table>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>
            
{* Edit Form *}
{include file='details/invoices/edit.tpl'}
                        
<script type="text/javascript">
    {include file='details/invoices/controller.js'}
</script>
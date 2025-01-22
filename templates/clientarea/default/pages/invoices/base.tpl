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
    
<div id="RCInvoices">
    <div class="box light">
        <div class="box-title tabbable-line">
            <div class="caption">
                <i class="fa fa-dollar font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
                
            <div class='rc-actions with-tabs' style="display: inline-flex">
                <div class="invoicesListSearch input-group" style="width: 200px; display: none;">
                    <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                    <input placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
                </div>
                <a href="javascript:;" onclick="ResellersCenter_Invoices.export();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-download"></i>
                </a>

                <a href="javascript:;" onclick="ResellersCenter_Invoices.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-search"></i>
                </a>
                {if $reseller->settings->admin->resellerInvoice}
                    <a href="javascript:;" onclick="ResellersCenter_Invoices.openCreateModal();" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                        <i class="fa fa-plus"></i>
                    </a>
                {/if}
            </div>
                
        </div>
        <div class="box-body">

            {if $reseller->settings->admin->disableEndClientInvoices && $reseller->settings->admin->resellerInvoice}
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning">{$MGLANG->T('disableEndClientInvoices')}</div>
                    </div>
                </div>
            {/if}

            <div class="row">
                <div class="col-md-12">
                    {if $reseller->settings->admin->resellerInvoice}
                        <div id="RCInvoicesRC">
                            <table class="table table-hover" data-invoicetype='rc' width="100%">
                                <thead>
                                    <th>{$MGLANG->T('table','id')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','client')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','date')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','duedate')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','total')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','paymentmethod')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','status')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
                                </thead>
                            </table>
                        </div>
                    {else}
                        <div id="RCInvoicesWHMCS">
                            <table class="table table-hover" width="100%">
                                <thead>
                                    <th>{$MGLANG->T('table','id')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','client')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','date')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','duedate')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','total')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','paymentmethod')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','status')}&nbsp;&nbsp;</th>
                                    <th>{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
                                </thead>
                            </table>
                        </div>
                    {/if}
                </div>
            </div>
        </Kdiv>

    </div>
</div>

{* Details for WHMCS invoice *}
{include file='details.tpl'}

{* Create for RC invoice *}
{include file='create/base.tpl'}

{* Edit for RC invoice *}
{include file='edit/base.tpl'}
                        
<script type="text/javascript">
    {include file='controller.js'}
</script>


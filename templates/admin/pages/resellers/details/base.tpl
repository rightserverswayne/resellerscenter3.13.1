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

{if not $reseller->exists}
    <div class="note note-danger">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
        <strong>{$MGLANG->T('ResellerNotFound')}</strong>
    </div>
{else}
    <div class="row" style="display:flex">
        <div class="col-lg-4 col-sm-12">
            {include file='details/main/main.tpl'}
        </div>

        <div class="col-lg-8 col-sm-12">
            {include file='details/settings/base.tpl'}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div id="assignations" class="box light">
                <div class="box-title tabbable-line">
                    <div class="caption">
                        <i class="fa fa-list font-red-thunderbird"></i>
                        <span class="caption-subject bold font-red-thunderbird uppercase">
                            {$MGLANG->T('assignations', 'title')}
                        </span>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#invoicesTab" data-toggle="tab" target="invoicesTab">
                                {$MGLANG->T('invoices', 'title')}
                            </a>
                        </li>
                        <li onclick="RC_ResellersTransactions.show()">
                            <a href="#transactionsTab" data-toggle="tab" target="transactionsTab">
                                {$MGLANG->T('transactions', 'title')}
                            </a>
                        </li>
                        <li onclick="RC_ResellersClients.show()">
                            <a href="#clientsTab" data-toggle="tab" target="clientsTab">
                                {$MGLANG->T('clients', 'title')}
                            </a>
                        </li>
                        <li onclick="RC_ResellersServices.init()">
                            <a href="#servicesTab" data-toggle="tab" target="servicesTab">
                                {$MGLANG->T('services', 'title')}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="invoicesTab">
                            <div class="scroller">
                                {include file='details/invoices/invoices.tpl'}
                            </div>
                        </div>
                        <div class="tab-pane " id="transactionsTab">
                            <div class="scroller">
                                {include file='details/transactions/transactions.tpl'}
                            </div>
                        </div>
                        <div class="tab-pane" id="clientsTab">
                            <div class="scroller">
                                {include file='details/clients/clients.tpl'}
                            </div>
                        </div>

                        <div class="tab-pane" id="servicesTab">
                            <div class="scroller">
                                {include file='details/services/services.tpl'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
{/if}
{*<div class="row">
    <div class="col-md-12">
        {include file='details/statistics/statistics.tpl'}
    </div>
</div>*}
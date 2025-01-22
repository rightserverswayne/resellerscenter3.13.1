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
<div id="transactions" class="box light">
    <div class="box-title tabbable-line">
        <ul class="nav nav-tabs nav-left">
            <li class="active">
                <a href="#transactionsTab" data-toggle="tab">{$MGLANG->T('transactions','title')}</a>
            </li>
        </ul>
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="transactionsListSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="transactionsListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="RC_ResellersTransactions.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" class="btn btn-circle btn-outline btn-inverse btn-default btn-icon-only" data-toggle="tooltip"  title="{$MGLANG->T('transactions','help')}" >
                <i class="fa fa-question"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="scroller">
            <div class="tab-content">
                <div class="tab-pane active" id="transactionsTab">
                    <table id='transactionsList' class="table table-hover" data-resellerid="{$reseller->id}" data-resellerinvoice="{$settings->resellerInvoice}" width="100%">
                        <thead>
                            <th>{$MGLANG->T('transactions','table','#ID')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','client')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','date')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','paymentmethod')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','description')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','amountin')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','fees')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','amountout')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('transactions','table','actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
                
        </div>
    </div>
</div>
                
<script type="text/javascript">
    {include file='details/transactions/controller.js'}
</script>
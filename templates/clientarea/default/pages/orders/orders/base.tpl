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
{if not $reseller->settings->admin->resellerInvoice}
    <div class='alert alert-info'>
        {$MGLANG->T('resellerpaymentinfo')}
    </div>
{/if}

<div class="col-md-offset-6 col-md-6">
    <div class="row">
        <div class="ordersListSearch col-md-offset-4 col-md-8">
            <input class="form-control input-sm pull-right" placeholder="{$MGLANG->T('table','search','placeholder')}" name="search">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-hover" width="100%">
            <thead>
                <th>{$MGLANG->T('table','ordernum')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','date')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','clientname')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','paymentmethod')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','total')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','paymentstatus')}&nbsp;&nbsp;</th>
                <th style="min-width: 80px;">{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
            </thead>
        </table>
    </div>
</div>


{* Order Details *}
{include file='orders/details.tpl'}

{* Order Accept *}
{include file='orders/dialogboxes/accept.tpl'}

{* Order Cancel *}
{include file='orders/dialogboxes/cancel.tpl'}

{* Order Fraud *}
{include file='orders/dialogboxes/fraud.tpl'}

{* Order Delete *}
{include file='orders/dialogboxes/delete.tpl'}


<script type="text/javascript">
    {include file='orders/controller.js'}
</script>



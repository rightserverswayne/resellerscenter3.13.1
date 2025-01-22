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
<div class='alert alert-info'>
    {$MGLANG->T('orders','resellerpaymentinfo')}
</div>

<div class="row">
    <div class='col-md-12'>
        <table id="ordersTable" class="table table-hover" width="100%">
            <thead>
                <th>{$MGLANG->T('orders','table','id')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','date')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','client')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','paymentmethod')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','total')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','status')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','paymentstatus')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('orders','table','actions')}&nbsp;&nbsp;</th>
            </thead>
        </table>
    </div>
</div>


{* Order Details *}
{include file='details/orders/details.tpl'}

{* Order Accept *}
{include file='details/orders/dialogboxes/accept.tpl'}

{* Order Cancel *}
{include file='details/orders/dialogboxes/cancel.tpl'}

{* Order Fraud *}
{include file='details/orders/dialogboxes/fraud.tpl'}

{* Order Delete *}
{include file='details/orders/dialogboxes/delete.tpl'}

<script type="text/javascript">
    {include file='details/orders/controller.js'}
</script>

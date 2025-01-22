<div id="RCOrderDetails" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        {$MGLANG->absoluteT('addonCA', 'orders','details','title')}{if $reseller->settings->admin->resellerInvoice} - <a class="order-invoiceid" href=""></a>{/if}
                    </h4>
                </div>
                <div class="modal-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{$MGLANG->absoluteT('addonCA', 'orders','details','item')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA', 'orders','details','description')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA', 'orders','details','billingcycle')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA', 'orders','details','amount')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA', 'orders','details','status')}&nbsp;&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="rc-actions pull-left">
                        <button type="button" class="openAcceptOrder btn btn-inverse btn-success" data-dismiss="modal">{$MGLANG->absoluteT('addonCA', 'orders','details','accept')}</button>
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
                </div>
            </div>
        </form>
    </div>

{* prototype for order items *}
<table style="display: none;">
    <tr data-prototype>
        <td>+type+</td>
        <td>+description+</td>
        <td>+billingcycle+</td>
        <td>+amount+</td>
        <td>+status+</td>
    </tr>
</table>
</div>
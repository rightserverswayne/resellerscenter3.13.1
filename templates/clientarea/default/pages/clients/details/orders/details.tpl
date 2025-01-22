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
<div id="RCOrderDetails" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->T('orders','title')}</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{$MGLANG->T('orders','item')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('orders','description')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('orders','billingcycle')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('orders','amount')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('orders','status')}&nbsp;&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="rc-actions pull-left">
                        <button type="button" class="openAcceptOrder btn btn-inverse btn-success" data-dismiss="modal">{$MGLANG->T('orders','button','accept')}</button>
{*                        <button type="button" class="openCancelOrder btn btn-inverse btn-warning" data-dismiss="modal">{$MGLANG->T('orders','button','cancel')}</button>*}
{*                        <button type="button" class="openFraudOrder btn btn-inverse btn-black" data-dismiss="modal">{$MGLANG->T('orders','button','fraud')}</button>*}
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
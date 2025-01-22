<div id="RCAcceptOrder" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->absoluteT('addonCA','orders','accept','title')}</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="help-block">{$MGLANG->absoluteT('addonCA','orders','accept','help')}</div>
                        </div>
                    </div>
                    <div class="warningNote row" style="display: none;">
                        <div class="col-md-12">
                            <div class="note note-warning">
                                {if not $reseller->settings->admin->resellerInvoice}
                                    {$MGLANG->absoluteT('addonCA','orders','accept','unpaid', 'withoutRcInvoice')}
                                {else}
                                    {$MGLANG->absoluteT('addonCA','orders','accept','unpaid', 'rcInvoice')} <br /> <br />
                                    <a class="order-invoiceid" href="+href+">+link+</a>
                                {/if}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='ResellersCenter_Search.submitAcceptForm();'>{$MGLANG->absoluteT('form','button','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
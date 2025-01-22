<div id="RCCancelOrder" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->absoluteT('addonCA','orders','cancel','title')}</h4>
            </div>
            <div class="modal-body">
                <form action=''>
                    <div class="control-group">
                        <div class="row-fluid">
                            <div class="help-block">{$MGLANG->absoluteT('addonCA','orders','cancel','help')}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning btn-inverse" onclick='ResellersCenter_Search.submitCancelForm();'>{$MGLANG->absoluteT('form','button','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
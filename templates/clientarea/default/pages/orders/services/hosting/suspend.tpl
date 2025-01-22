<div id="RCSuspendService" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <input hidden name="relid" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('suspend','title')}</h4>
            </div>
            <div class="modal-body">
                <form action=''>
                    <div class="control-group">
                        <div class="row-fluid">
                            <div class="help-block">{$MGLANG->T('suspend','help')}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-inverse" data-type="confirm">{$MGLANG->T('suspend','buttons','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('suspend','buttons','close')}</button>
            </div>
        </div>
    </div>
</div>

<div id="RCUnsuspendService" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <input hidden name="relid" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('unsuspend','title')}</h4>
            </div>
            <div class="modal-body">
                <form action=''>
                    <div class="control-group">
                        <div class="row-fluid">
                            <div class="help-block">{$MGLANG->T('unsuspend','help')}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning btn-inverse" data-type="confirm">{$MGLANG->T('unsuspend','buttons','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('unsuspend','buttons','close')}</button>
            </div>
        </div>
    </div>
</div>
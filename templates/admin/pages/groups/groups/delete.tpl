<div id="groupDeleteFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('group','delete','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='groupDeleteForm' action=''>
                    <div class="control-group">
                        <div class="row-fluid">
                            <div class="help-block">{$MGLANG->T('group','delete','help')}</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-inverse" onclick='RC_ConfigurationGroups.deleteGroup();'>{$MGLANG->T('group','delete','buttons','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('group','delete','buttons','close')}</button>
            </div>
        </div>
    </div>
</div>
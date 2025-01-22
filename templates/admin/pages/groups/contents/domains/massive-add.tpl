<div id="domainMassiveAddModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','domains','massiveAdd','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='domainMassiveAddForm' action=''>
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">{$MGLANG->T('settings','domains','massiveAdd','warning')}</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_SettingsDomains.submitMassiveAddForm()'>{$MGLANG->T('settings','domains','massiveAdd','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('settings','domains','massiveAdd','close')}</button>
            </div>
        </div>
    </div>
</div>
<div id="DocumentationAddFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('resellerDocumentation','create','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='DocumentationAddForm' onkeypress="if(event.keyCode == 13) RC_ResellerDocumentation.submitForm(); return event.keyCode != 13;">
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="name">{$MGLANG->T('resellerDocumentation','create','form','name','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <input class="form-control" name="name" placeholder="{$MGLANG->T('resellerDocumentation','create','form','name','placeholder')}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('resellerDocumentation','create','form','name','help')}</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_ResellerDocumentation.submitForm();'>{$MGLANG->absoluteT('form','button','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
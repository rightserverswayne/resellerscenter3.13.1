<div id="groupCreateFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('group','create','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='groupCreateForm' onkeypress="if(event.keyCode == 13) return false; return event.keyCode != 13;">
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="name">{$MGLANG->T('group','create','form','name','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <input class="form-control" name="name" placeholder="{$MGLANG->T('group','create','form','name','placeholder')}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('group','create','form','name','help')}</div>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label style="padding-top: 0px;" class="label-control">
                                    {$MGLANG->T('group','create','form','consolidatedInvoiceSettings','label')}
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col-md-11">
                                <div class="row margin-top-10">
                                    <div class="col-md-3">
                                        <label style="padding-top: 0px;" class="label-control" for="enableConsolidatedInvoices">{$MGLANG->T('group','create','form','consolidatedInvoiceEnable','label')}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input class="checkbox-switch"
                                               data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                               data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                               data-on-color="success"
                                               data-off-color="default"
                                               data-size="mini"
                                               data-label-width="15"
                                               type="checkbox"
                                               name="settings[enableConsolidatedInvoices]"
                                        />
                                    </div>
                                </div>
                                <div class="row margin-top-10">
                                    <div class="col-md-3">
                                        <label style="padding-top: 0px;" class="label-control" for="endClientConsolidatedInvoices">{$MGLANG->T('group','create','form','endClientConsolidatedInvoices','label')}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input class="checkbox-switch"
                                               data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                               data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                               data-on-color="success"
                                               data-off-color="default"
                                               data-size="mini"
                                               data-label-width="15"
                                               type="checkbox"
                                               name="settings[endClientConsolidatedInvoices]"
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="label-control" for="consolidatedInvoicesDay">{$MGLANG->T('group','create','form','consolidatedInvoiceDay','label')}</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input class="form-control"
                                               type="number"
                                               name="settings[consolidatedInvoicesDay]"
                                               min="1"
                                               max="31"
                                               value="1"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_ConfigurationGroups.submitForm();'>{$MGLANG->T('group','create','buttons','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('group','create','buttons','close')}</button>
            </div>
        </div>
    </div>
</div>
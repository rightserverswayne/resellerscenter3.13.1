<div id="addReseller" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('add','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='resellerAddForm' action=''>
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="clientid">{$MGLANG->T('add','from','client','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control select2" name="clientid">
                                    {foreach from=$clients item=client}
                                        <option value='{$client->id}'>{$client->firstname} {$client->lastname} {if $client->companyname}({$client->companyname}){/if} </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('add','from','client','help')}</div>
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="groupid">{$MGLANG->T('add','from','group','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control select2" name="groupid">
                                    {foreach from=$groups item=group}
                                        <option value='{$group->id}'>{$group->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('add','from','group', 'help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <label style="padding-top: 0px;" class="label-control" for="generateDefaultProducts">{$MGLANG->T('add','from','products','label')}</label>
                        </div>
                        <div class="col-md-10">
                            <input class="checkbox-switch"
                                   data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                   data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                   data-on-color="success"
                                   data-off-color="default"
                                   data-size="mini"
                                   data-label-width="15"
                                   type="checkbox"
                                   name="generateDefaultProducts"
                                   checked
                            />
                            <div class="help-block">{$MGLANG->T('add','from','products', 'help')}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-2 col-md-10">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_Resellers.submitAddForm();'>{$MGLANG->T('add','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('add','close')}</button>
            </div>
        </div>
    </div>
</div>
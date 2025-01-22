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
<div id="addonAddModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','addons','add','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='addAddonForm' action=''>
                    <input hidden name='type' value='addon'>
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="relid">{$MGLANG->T('settings','addons','add','from','addon','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <select id="addAddonSelect" class="form-control select2" name="relid">
                                    <option></option>
                                    {foreach from=$addons item=addon}
                                        <option value='{$addon->id}'>{$addon->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('settings','addons','add','from','addon','help')}</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_SettingsAddons.submitAddForm()'>{$MGLANG->T('settings','addons','add','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('settings','addons','add','close')}</button>
            </div>
        </div>
    </div>
</div>
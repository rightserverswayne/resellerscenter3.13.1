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

{* Lang path: settings → products → config → ... *}
<div id="productConfigModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','content','config','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='productConfigForm' action='#'>

                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="label-control" for="counting_type">{$MGLANG->T('settings','content','config','counting','label')}</label>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control" name="counting_type">
                                    {foreach from=$counting_types item='type'}
                                        <option value='{$type.name}'>{$MGLANG->T('settings','content','config','counting','option', $type.friendlyName)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <div class="help-block">
                                    {$MGLANG->T('settings','content','config','counting','help', 'default')} 
                                    <br />
                                    <span class="select-description">
                                        {foreach from=$counting_types item='type'}
                                            <span class="countingType_{$type.name} counting-help" style="display: none; color: #737373">{$MGLANG->T('settings','content','config','counting','help', $type.description)}</span>
                                        {/foreach}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                                    
                    <div class='additional-config'>{* Filled by AJAX *}</div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_SettingsProducts.submitProductConfigForm()'>{$MGLANG->T('settings','content','config','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('settings','content','config','close')}</button>
            </div>
        </div>
    </div>
</div>
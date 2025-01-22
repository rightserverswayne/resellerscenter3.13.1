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
<div id="RCAddClient" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->T('add','title')}</h4>
                </div>
                <div class="modal-body">
                    <div class="alertContainter">
                        <div style="display:none;" data-prototype="error">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                                <strong></strong>
                                <a style="display:none;" class="errorID" href=""></a>
                            </div>
                        </div>
                        <div style="display:none;" data-prototype="success">
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                                <strong></strong>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#RCAddClientGeneral" aria-controls="RCAddClientGeneral"  data-toggle="tab">{$MGLANG->T('add', 'general', 'title')}</a>
                        </li>
                        <li role="presentation">
                            <a href="#RCAddClientAddress" aria-controls="RCAddClientAddress"  data-toggle="tab">{$MGLANG->T('add', 'address', 'title')}</a>
                        </li>
                        {if $customFields && count($customFields) != 0}
                            <li role="presentation">
                                <a href="#RCAddClientCustomFields" aria-controls="RCAddClientCustomFields" role="tab" data-toggle="tab">{$MGLANG->T('add', 'customfields', 'title')}</a>
                            </li>
                        {/if}
                        {if $reseller->settings->admin->allowcreditline}
                            <li role="presentation">
                                <a href="#RCAddClientCreditLine" aria-controls="RCAddClientCreditLine" role="tab" data-toggle="tab">{$MGLANG->T('add', 'creditline', 'title')}</a>
                            </li>
                        {/if}
                    </ul>

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="RCAddClientGeneral">

                            <div class="row">
                                <div class="col-md-3">
                                    <label class="label-control" for="client[firstname]" >{$MGLANG->T('add','general','firstname','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[firstname]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[lastname]" >{$MGLANG->T('add','general','lastname','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[lastname]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[companyname]" >{$MGLANG->T('add','general','companyname','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[companyname]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[email]" >{$MGLANG->T('add','general','email','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[email]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="phonenumber" >{$MGLANG->T('add','general','phonenumber','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="phonenumber" type="tel" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[password2]" >{$MGLANG->T('add','general','password','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[password2]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[currency]">{$MGLANG->T('add','general','currency','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control" name="client[currency]">
                                        {foreach from=$currencies item=currency}
                                            <option value="{$currency->id}">{$currency->code}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="RCAddClientAddress">

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[address1]" >{$MGLANG->T('add','address','address1','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[address1]" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[address2]" >{$MGLANG->T('add','address','address2','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[address2]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[city]" >{$MGLANG->T('add','address','city','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[city]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[state]" >{$MGLANG->T('add','address','state','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[state]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[postcode]" >{$MGLANG->T('add','address','postcode','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" name="client[postcode]" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[country]" >{$MGLANG->T('add','address','country','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control" name="client[country]">
                                        {foreach from=$countries key=code item=country}
                                            <option value="{$code}">{$country}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="RCAddClientCustomFields">

                            {foreach from=$customFields item=field}
                                {assign var=splitAt value=$field.fieldname|strpos:"|"}
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="client[customfields][{$field.id}]">{if $splitAt}{$field.fieldname|substr:($splitAt+1)}{else}{$field.fieldname}{/if}</label>
                                    </div>
                                    <div class="col-md-9">
                                        {if $field.fieldtype eq 'dropdown'}
                                            <select name="client[customfields][{$field.id}]" class="form-control">
                                                <option value="">{$MGLANG->T('add','customfields','option','none')}</option>
                                                {foreach from=","|explode:$field.fieldoptions item=optionvalue}
                                                    <option value="{$optionvalue}">{$optionvalue}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $field.fieldtype eq 'tickbox'}
                                            <input class="checkbox-switch"
                                                data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                                data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                                data-on-color="success" 
                                                data-off-color="default"  
                                                data-size="mini" 
                                                data-label-width="15" 
                                                type="checkbox"  
                                                name="client[customfields][{$field.id}]"
                                                value="1"      
                                            />
                                        {elseif $field.fieldtype eq 'textarea'}
                                            <textarea class="form-control" name="client[customfields][{$field.id}]"></textarea>
                                        {else}
                                            <input class="form-control" name="client[customfields][{$field.id}]"/>
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                            
                        </div>

                        <div role="tabpanel" class="tab-pane" id="RCAddClientCreditLine">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="client[creditlinelimit]" >{$MGLANG->T('add','creditlinelimit','limit','label')}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control"
                                           type ="number"
                                           min="0"
                                           step="0.01"
                                           name="client[creditlinelimit]"
                                           value="0"/>
                                </div>
                            </div>
                        </div>
                    </div>
                                    
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <label>
                            <input type="checkbox" name="client[sendWelcomeMsg]" checked="checked"/>
                            <span class="help-inline">{$MGLANG->T('add','sendwelcomemsg')}</span>
                        </label>
                    </div>

                    <button type="button" class="btn btn-success btn-inverse" onclick='ResellersCenter_Clients.submitAddForm();'>{$MGLANG->T('add','save')}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('add','close')}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $('.nav-tabs a').click(function(){
        var id = $(this).attr('href')

        $('.nav-tabs a[href="'+id+'"]').tab('show')
    })                   
</script>
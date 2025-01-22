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

<div id="RCPromotionDetails" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-edit font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('details','title')}
            </span>
        </div>
    </div>
    <div class="box-body">
        <form id="RCEditPromotionFrom">
            <input type="hidden" class="form-control" name='promotion[id]' value="{$promotion->id}"/> 
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','code','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input class="form-control" name='promotion[code]' value="{$promotion->code}"/> 
                                <div class='help-block'>{$MGLANG->T('details','code','help')} <a onclick="ResellersCenter_PromotionsDetails.generateRandomPromotionCode();">{$MGLANG->T('details','code','generate')}</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','type','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <select class="form-control" name='promotion[type]'>
                                    <option {if $promotion->type eq 'Percentage'}selected{/if} value="Percentage">{$MGLANG->T('details','type','percentage')}</option>
                                    <option {if $promotion->type eq 'Fixed Amount'}selected{/if} value="Fixed Amount">{$MGLANG->T('details','type','fixedamount')}</option>
                                    <option {if $promotion->type eq 'Price Override'}selected{/if} value="Price Override">{$MGLANG->T('details','type','priceoverride')}</option>
                                    <option {if $promotion->type eq 'Free Setup'}selected{/if} value="Free Setup">{$MGLANG->T('details','type','freesetup')}</option>
                                </select> 
                                <div class='help-block'>{$MGLANG->T('details','type','help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','recurring','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[recurring]"
                                    value="1"
                                    {if $promotion->recurring}checked{/if}
                                />
                                {$MGLANG->T('details','recurring','for')}
                                <input class="form-control" name='promotion[recurfor]' value='{if $promotion->recurfor}{$promotion->recurfor}{else}0{/if}' style="display: inline; width: 80px;"/> 
                                <div class='help-block'>{$MGLANG->T('details','recurring','help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','value','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input class="form-control" name='promotion[value]' value="{$promotion->value}"/> 
                                <div class='help-block'>{$MGLANG->T('details','value','help')}</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6 col-sm-12">

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','appliesto','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <select multiple class="form-control select2" name="promotion[appliesto][]">
                                    {if $promotion}
                                        {foreach from=$promotion->getAppliesTo() key=$key item=element}
                                            <option value="{$key}" selected="selected">{if $element->name}#{$element->id} {$element->name}{else}{$element->extension}{/if}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                                <div class='help-block'>{$MGLANG->T('details','appliesto','help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','requires','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <select multiple class="form-control select2" name="promotion[requires][]">
                                    {if $promotion}
                                        {foreach from=$promotion->getRequires() key=$key item=element}
                                            <option value="{$key}" selected="selected">{if $element->name}#{$element->id} {$element->name}{else}{$element->extension}{/if}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                                <div class='help-block'>{$MGLANG->T('details','requires','help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','billingcycles','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <select multiple class="form-control select2" name="promotion[cycles][]">
                                    {assign var=promocycles value=','|explode:$promotion->cycles}
                                    <optgroup label="{$MGLANG->T('details','billingcycles','productandservices')}">
                                    {foreach from=$cycles key=key item=billingcycle}
                                        <option value="{$billingcycle}" {if $billingcycle|in_array:$promocycles}selected{/if}>{$key}</option>
                                    {/foreach}
                                    </optgroup>

                                    <optgroup label="{$MGLANG->T('details','billingcycles','domains')}">
                                    {foreach from=$periods key=key item=period}
                                        <option value="{$period}" {if $periods|in_array:$promocycles}selected{/if}>
                                            {$key+1} {if $key eq 0}{$MGLANG->T('details','billingcycles','year')}{else}{$MGLANG->T('details','billingcycles','years')}{/if}
                                        </option>
                                    {/foreach}
                                    </optgroup>
                                </select>
                                <div class='help-block'>{$MGLANG->T('details','billingcycles','help')}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','startdate','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <div class="startDate input-group">
                                    <input class="form-control" name="promotion[startdate]" value="{$promotion->startdate}" />
                                    <span class="input-group-addon">
                                        <span class="font-red bold icon-calendar"></span>
                                    </span>
                                </div> 
                                <div class='help-block'>{$MGLANG->T('details','startdate','help')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','expirationdate','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <div class="expiryDate input-group">
                                    <input class="form-control" name="promotion[expirationdate]" value="{$promotion->expirationdate}" />
                                    <span class="input-group-addon">
                                        <span class="font-red bold icon-calendar"></span>
                                    </span>
                                </div> 
                                <div class='help-block'>{$MGLANG->T('details','expirationdate','help')}</div>
                            </div>
                        </div>
                    </div>
                            
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','maxuses','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input class="form-control" name='promotion[maxuses]' value="{$promotion->maxuses}" /> 
                                <div class='help-block'>{$MGLANG->T('details','maxuses','help')}</div>
                            </div>
                        </div>
                    </div>
                            
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">{$MGLANG->T('details','uses','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input class="form-control" name='promotion[uses]' value="{$promotion->uses}" disabled="disabled"/> 
                                <div class='help-block'>{$MGLANG->T('details','uses','help')}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label">{$MGLANG->T('details','lifetimepromo','label')}</label>
                            </div>
                            <div class='col-md-2'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[lifetimepromo]"
                                    value="1"
                                    {if $promotion->lifetimepromo}checked{/if}
                                />
                            </div>
                            <div class='col-md-7'>
                                <div class='help-block'>{$MGLANG->T('details','lifetimepromo','help')}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label">{$MGLANG->T('details','applyonce','label')}</label>
                            </div>
                            <div class='col-md-2'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[applyonce]"
                                    value="1"
                                    {if $promotion->applyonce}checked{/if}
                                />
                            </div>
                            <div class='col-md-7'>
                                <div class='help-block'>{$MGLANG->T('details','applyonce','help')}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label">{$MGLANG->T('details','newsignups','label')}</label>
                            </div>
                            <div class='col-md-2'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[newsignups]"
                                    value="1"
                                    {if $promotion->newsignups}checked{/if}
                                />
                            </div>
                            <div class='col-md-7'>
                                <div class='help-block'>{$MGLANG->T('details','newsignups','help')}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label">{$MGLANG->T('details','onceperclient','label')}</label>
                            </div>
                            <div class='col-md-2'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[onceperclient]"
                                    value="1"
                                    {if $promotion->onceperclient}checked{/if}
                                />
                            </div>
                            <div class='col-md-7'>
                                <div class='help-block'>{$MGLANG->T('details','onceperclient','help')}</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="checkbox-switch-label">{$MGLANG->T('details','existingclient','label')}</label>
                            </div>
                            <div class='col-md-2'>
                                <input  class="checkbox-switch"
                                    data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                    data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                    data-on-color="success" 
                                    data-off-color="default"  
                                    data-size="mini" 
                                    data-label-width="15" 
                                    type="checkbox"  
                                    name="promotion[existingclient]"
                                    value="1"
                                    {if $promotion->existingclient}checked{/if}
                                />
                            </div>
                            <div class='col-md-7'>
                                <div class='help-block'>{$MGLANG->T('details','existingclient','help')}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                            
            <hr>
            
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label class="checkbox-switch-label">{$MGLANG->T('details','upgrades','label')}</label>
                    </div>
                    <div class='col-md-1'>
                        <input  class="checkbox-switch"
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="promotion[upgrades]"
                            value="1"
                            {if $promotion->upgrades}checked{/if}
                        />
                    </div>
                    <div class='col-md-8'>
                        <div class='help-block'>{$MGLANG->T('details','upgrades','help')}</div>
                    </div>
                </div>
            </div>
                        
            <div class="upgradeOptions" {if !$promotion->upgrades}style="display: none;"{/if}>
                
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{$MGLANG->T('details','upgradetype','label')}</label>
                        </div>
                        <div class='col-md-9'>
                            <select class="form-control" name="promotion[upgradeconfig][type]">
                                <option {if $promotion->upgradeconfig.type eq 'product'}selected{/if} value="product">{$MGLANG->T('details','upgradetype','product')}</option>
                                <option {if $promotion->upgradeconfig.type eq 'configoptions'}selected{/if} value="configoptions">{$MGLANG->T('details','upgradetype','configoptions')}</option>
                            </select>
                            <div class='help-block'>{$MGLANG->T('details','upgradetype','help')}</div>
                        </div>
                    </div>
                </div>
                        
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{$MGLANG->T('details','upgradediscounttype','label')}</label>
                        </div>
                        <div class='col-md-9'>
                            <select class="form-control" name="promotion[upgradeconfig][discounttype]">
                                <option {if $promotion->upgradeconfig.discounttype eq 'percentage'}selected{/if} value="Percentage">{$MGLANG->T('details','upgradediscounttype','percentage')}</option>
                                <option {if $promotion->upgradeconfig.discounttype eq 'fixedamount'}selected{/if} value="Fixed Amount">{$MGLANG->T('details','upgradediscounttype','fixedamount')}</option>
                            </select>
                            <div class='help-block'>{$MGLANG->T('details','upgradediscounttype','help')}</div>
                        </div>
                    </div>
                </div>
                        
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{$MGLANG->T('details','upgradevalue','label')}</label>
                        </div>
                        <div class='col-md-9'>
                            <input class="form-control" name='promotion[upgradeconfig][value]' value='{$promotion->upgradeconfig.value}'/> 
                            <div class='help-block'>{$MGLANG->T('details','upgradevalue','help')}</div>
                        </div>
                    </div>
                </div>                    
                        
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">{$MGLANG->T('details','upgradeconfigoptions','label')}</label>
                        </div>
                        <div class='col-md-9'>
                            <select multiple class="form-control select2" name="promotion[upgradeconfig][configoptions][]" width="100%">
                                {* AJAX *}
                                {if $promotion->upgradeconfig.configoptions}
                                    {foreach from=$promotion->upgradeconfig.configoptions item=option}
                                        <option value="{$option}" selected="selected">#{$configoptions.$option->optionname}</option>
                                    {/foreach}
                                {/if}
                            </select>
                            <div class='help-block'>{$MGLANG->T('details','upgradeconfigoptions','help')}</div>
                        </div>
                    </div>
                </div>
            </div>
                        
            <hr>
                        
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">{$MGLANG->T('details','notes','label')}</label>
                    </div>
                    <div class='col-md-9'>
                        <textarea class="form-control" name="promotion[notes]">{$promotion->notes}</textarea>
                        <div class='help-block'>{$MGLANG->T('details','notes','help')}</div>
                    </div>
                </div>
            </div>
                        
        </form>

        <div class="row">
            <div class="col-md-12">
                <center>
                    <button class="savePromotionBtn btn btn-success btn-inverse">{$MGLANG->T('details','save')}</button>
                    <a class="btn btn-default" href="index.php?m=ResellersCenter&mg-page=promotions">{$MGLANG->T('details','goback')}</a>
                </center>
            </div>
        </div>
                    
    </div>
</div>
                
<script type="text/javascript">
    {include file='details/controller.js'}
</script>

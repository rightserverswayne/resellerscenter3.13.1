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
<div id="RCPricingAddonsEdit" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:55%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{* Set in JS *}</h4>
            </div>
            <div class="modal-body">

                <div id="addonPricingMessages">
                    <div style="display:none;" data-prototype="error">
                        <div class="note note-danger">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                            <strong></strong>
                            <a style="display:none;" class="errorID" href=""></a>
                        </div>
                    </div>
                    <div style="display:none;" data-prototype="success">
                        <div class="note note-success">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                            <strong></strong>
                        </div>
                    </div>
                </div>

                <form class="form-horizontal" role="form">
                    <input hidden name="type" value="addon">
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="relid" style="padding-top: 8px;">{$MGLANG->T('addons','add','addon','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control select2" name="relid">
                                        
                                    {* Filled by AJAX *}
                                    
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('addons','add','addon','help')}</div>
                            </div>
                        </div>
                    </div>
                            
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs" role="tablist">
                                    {foreach from=$currencies key=index item=currency}
                                        <li role="presentation" class="{if $currency->default eq 1}active{/if}">
                                            <a href="#RCPricingAddonsCurrency{$currency->id}" aria-controls="RCPricingAddonsCurrency{$currency->id}" data-currencyid="currid{$currency->id}" role="tab" data-toggle="tab">{$currency->code}</a>
                                        </li>
                                    {/foreach}

                                    {if $currencies->count() neq 1}
                                        <div class="rc-actions pull-right" style="display: inline-flex">
                                            <a href="" onclick="ResellersCenter_PricingAddons.autofillCurrencies(); return false;" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('tooltip','autofillcurrency')}">
                                                <i class="fa fa-exchange"></i>
                                            </a>
                                            <a href="" onclick="ResellersCenter_PricingAddons.refreshCurrenciesValues(); return false;" class="btn btn-circle btn-outline btn-inverse btn-green-cyan btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('tooltip','refreshcurrency')}">
                                                <i class="fa fa-refresh"></i>
                                            </a>
                                        </div>
                                    {/if}
                                </ul>

                                <div class="tab-content">
                                    {foreach from=$currencies key=index item=currency}
                                        <div  id="RCPricingAddonsCurrency{$currency->id}" class="tab-pane {if $currency->default eq 1}active defaultCurrency{/if}" data-currencyid="{$currency->id}"  role="tabpanel">
                                            {include file='billingcycles.tpl'}
                                        </div>
                                    {/foreach}
                                </div>
                                <div class="help-block">{$MGLANG->T('addons','add','pricing','help')}</div>
                            </div>
                        </div>
                    </div>        
                                                        
                </form>
                            
                <div class="freeBilingcycle alert alert-warning" style="display: none;">
                    {$MGLANG->T('addons','free')}
                </div>
                
                <div class="errorContainer alert alert-danger" style="display: none;">
                    {$MGLANG->absoluteT('form','validate','empty')}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='ResellersCenter_PricingAddons.submitPricingForm();'>{$MGLANG->absoluteT('form', 'button', 'save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form', 'button','close')}</button>
            </div>
        </div>
    </div>
</div>
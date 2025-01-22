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
<div id="productPricingModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 40%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','products','pricing','title')}</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                
                <div id="productPricingMessages">
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
                
                {if $currencies->count() neq 1}
                    <div class="rc-actions pull-right" style="display: inline-flex">
                        <a href="" onclick="RC_SettingsProducts.autofillCurrencies(); return false;" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','autofillcurrency')}">
                          <i class="fa fa-exchange"></i>
                        </a>
                        <a href="" onclick="RC_SettingsProducts.refreshCurreciesValues(); return false;" class="btn btn-circle btn-outline btn-inverse btn-green-cyan btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','refreshcurrency')}">
                          <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                {/if}
                
                <form id='productPricingForm' action=''>
                    <ul class="nav nav-tabs" role="tablist" {if $currencies->count() eq 1}style="display: none;"{/if}>
                        {foreach from=$currencies key=index item=currency}
                            <li role="presentation" class="{if $currency->default eq 1}active{/if} hidden" >
                                <a href="#products{$currency->code}" aria-controls="products{$currency->code}" data-currencyid="currid{$currency->id}" role="tab" data-toggle="tab">{$currency->code}</a>
                            </li>
                        {/foreach}
                    </ul>

                    <div class="tab-content">
                        {foreach from=$currencies key=index item=currency}
                            <div role="tabpanel" class="tab-pane {if $currency->default eq 1}active defaultCurrency{/if}" id="products{$currency->code}" data-currencyid="{$currency->id}">
                                {include file='contents/billingcycles.tpl' type='Product'}
                            </div>
                        {/foreach}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_SettingsProducts.submitProductPricingForm()'>{$MGLANG->T('settings','content','pricing','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('settings','content','pricing','close')}</button>
            </div>
        </div>
    </div>
</div>
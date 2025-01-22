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
<div id="domainPricingModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="top: 0%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','domains','pricing','title')}</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                
                <div id="domainPricingMessages">
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
                
                <div class="rc-actions pull-right" style="display: inline-flex">
                    <a href="" onclick="RC_SettingsDomains.autofillCurrencies(); return false;" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','autofillcurrency')}">
                      <i class="fa fa-exchange"></i>
                    </a>
                    <a href="" onclick="RC_SettingsDomains.refreshCurreciesValues(); return false;" class="btn btn-circle btn-outline btn-inverse btn-green-cyan btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','refreshcurrency')}">
                      <i class="fa fa-refresh"></i>
                    </a>
                </div>
                
                <form id='domainPricingForm' action=''>
                    
                    <ul class="nav nav-tabs" role="tablist">
                        {foreach from=$currencies key=index item=currency}
                            <li role="presentation" class="{if $currency->default eq 1}active{/if}">
                                <a href="#domains{$currency->code}" aria-controls="domains{$currency->code}" role="tab" data-toggle="tab" data-currencyid="currid{$currency->id}">{$currency->code}</a>
                            </li>
                        {/foreach}
                    </ul>

                    <div class="tab-content">
                        {foreach from=$currencies key=index item=currency}
                            <div role="tabpanel" class="tab-pane {if $currency->default eq 1}active defaultCurrency{/if}" id="domains{$currency->code}" data-currencyid="{$currency->id}">
                                {include file='contents/domains/billingcycles.tpl'}
                            </div>
                        {/foreach}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_SettingsDomains.submitPricingForm()'>{$MGLANG->T('settings','content','pricing','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('settings','content','pricing','close')}</button>
            </div>
        </div>
    </div>
</div>
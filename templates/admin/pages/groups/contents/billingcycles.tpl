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
<div class="billingcycles panel-group" id="{$type}BillingCyclesFor{$currency->code}" role="tablist" aria-multiselectable="false">
    {assign var=billingcycles value=['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially']}
    
    {foreach from=$billingcycles item=billing}
        {assign var=panelid value=$billing|cat: $currency->code|cat: {$type}}
        <div class="panel panel-default" data-billingcycle='{$billing}'>
            <div class="panel-heading accordionSwitch collapsed" role="tab" id="heading{$panelid|ucfirst}" role="button" data-toggle="collapse" data-parent="#{$type}BillingCyclesFor{$currency->code}" href="#collapse{$panelid|ucfirst}" aria-expanded="true" aria-controls="collapse{$panelid|ucfirst}">
                <h4 class="panel-title">
                    <a>{$MGLANG->T('settings','content','pricing','billingcycles',{$billing})}</a>
                    
                    <div class="rc-actions pull-right" style="display: none">
                        <a href="javascript:;" data-billing="{$billing}" data-currencyid="{$currency->id}" class="fillBillingCyclesBtn btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','autofillcycles')}">
                            <i class="glyphicon glyphicon-sort"></i>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="collapse{$panelid|ucfirst}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{$panelid|ucfirst}">
                <div class="panel-body">
                    
                    {* BILLINGCYCLE PRICING *}
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][adminprice][{$billing}]'>{$MGLANG->T('settings','content','pricing','adminprice')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][adminprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','adminprice_help')}</div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][highestprice][{$billing}]'>{$MGLANG->T('settings','content','pricing','highestprice')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][highestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','highestprice_help')}</div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][lowestprice][{$billing}]'>{$MGLANG->T('settings','content','pricing','lowestprice')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][lowestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','lowestprice_help')}</div>
                        </div>
                    </div>
                    
                    <hr />

                    {* SETUP FEES *}
                    {assign var=setupfees value=['monthly' => 'msetupfee', 'quarterly' => 'qsetupfee', 'semiannually' => 'ssetupfee', 'annually' => 'asetupfee', 'biennially' => 'bsetupfee', 'triennially' => 'tsetupfee']}
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][adminprice][{$setupfees.$billing}]'>{$MGLANG->T('settings','content','pricing','setupfee')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][adminprice][{$setupfees.$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','setupfee_help')}</div>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][highestprice][{$setupfees.$billing}]'>{$MGLANG->T('settings','content','pricing','highestsetupfee')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][highestprice][{$setupfees.$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','highestsetupfee_help')}</div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-6'>
                            <label for='pricing[{$currency->id}][lowestprice][{$setupfees.$billing}]'>{$MGLANG->T('settings','content','pricing','lowestsetupfee')}</label>
                        </div>
                        <div class='col-md-4 col-sm-4 col-xs-6'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='pricing[{$currency->id}][lowestprice][{$setupfees.$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-5 col-sm-5 col-xs-12'>
                            <div class='help-block'>{$MGLANG->T('settings','content','pricing','lowestsetupfee_help')}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                        
    {/foreach}
 
</div>
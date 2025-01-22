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
<div class="billingcycles panel-group" id="domainsBillingCyclesFor{$currency->code}" role="tablist" aria-multiselectable="false">
    {assign var=billingcycles value=["msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially"]}
    
    {foreach from=$billingcycles item=billing}
        {assign var=panelid value=$billing|cat: $currency->code}
        <div class="panel panel-default" data-billingcycle='{$billing}'>
            <div class="panel-heading accordionSwitch" role="tab" id="heading{$panelid|ucfirst}" role="button" data-toggle="collapse" data-parent="#domainsBillingCyclesFor{$currency->code}" href="#domainsCollapse{$panelid|ucfirst}" aria-expanded="true" aria-controls="domainsCollapse{$panelid|ucfirst}">
                <h4 class="panel-title">
                    <a>{$MGLANG->T('settings','domains','pricing','billingcycles',{$billing})}</a>
                    
                    <div class="rc-actions pull-right" {if $billing neq 'msetupfee'}style="display: none"{/if}>
                        <a href="javascript:;" data-billing="{$billing}" data-currencyid="{$currency->id}" class="fillDomainBillingCyclesBtn btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                            <i class="glyphicon glyphicon-sort"></i>
                        </a>
                    </div>
                </h4>
            </div>
            <div id="domainsCollapse{$panelid|ucfirst}" class="panel-collapse collapse {if $billing eq 'msetupfee'}in{/if}" role="tabpanel" aria-labelledby="heading{$panelid|ucfirst}">
                <div class="panel-body">
                    <div class='row'>
                        <div class="col-md-3 col-sm-3 col-xs-3"></div>
                        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                            <label>{$MGLANG->T('settings','domains','types','register')}</label>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                            <label>{$MGLANG->T('settings','domains','types','transfer')}</label>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                            <label>{$MGLANG->T('settings','domains','types','renew')}</label>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <label>{$MGLANG->T('settings','content','pricing','adminprice')}</label>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainregister[{$currency->id}][adminprice][{$billing}]'/>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domaintransfer[{$currency->id}][adminprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainrenew[{$currency->id}][adminprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />

                    <div class='row' style="margin-bottom: 10px">
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <label>{$MGLANG->T('settings','content','pricing','highestprice')}</label>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainregister[{$currency->id}][highestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domaintransfer[{$currency->id}][highestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainrenew[{$currency->id}][highestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                    </div>
                                
                    <div class='row'>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <label>{$MGLANG->T('settings','content','pricing','lowestprice')}</label>
                        </div>

                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainregister[{$currency->id}][lowestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domaintransfer[{$currency->id}][lowestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3 col-sm-3 col-xs-3'>
                            <div class="controls">
                                <div class="input-group has-addon-left">
                                    <div class="input-group-addon">{$currency->prefix}</div>
                                    <input class='form-control' name='domainrenew[{$currency->id}][lowestprice][{$billing}]' />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                        
    {/foreach}
 
</div>
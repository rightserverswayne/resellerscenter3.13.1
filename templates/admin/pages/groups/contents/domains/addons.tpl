<div id="domainAddonsPricingModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="top: 0%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('settings','addondomains','pricing','title')}</h4>
            </div>
            <div class="modal-body">
                
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
                
                <div class="rc-actions pull-right">
                    <a href="javascript:;" onclick="RC_SettingsDomains.autofillCurrencies(); return false;" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','autofillcurrency')}">
                        <i class="fa fa-exchange"></i>
                    </a>
                    <a href="javascript:;" onclick="RC_SettingsDomains.refreshCurreciesValues(); return false;" class="btn btn-circle btn-outline btn-inverse btn-green-cyan btn-icon-only" data-toggle="tooltip" title="{$MGLANG->T('settings','tooltip','refreshcurrency')}">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>
                
                <form id='domainAddonsPricingForm' action=''>
                    
                    <ul class="nav nav-tabs" role="tablist">
                        {foreach from=$currencies key=index item=currency}
                            <li {if $index eq 0}class="active"{/if}>
                                <a href="#domainsAddonCurrency{$currency->code}" data-toggle="tab">
                                    {$currency->code}
                                </a>
                            </li>
                        {/foreach}
                    </ul>

                    <div class="tab-content">
                        {foreach from=$currencies key=index item=currency}
                            <div class="tab-pane{if $index eq 0} active{/if}" id="domainsAddonCurrency{$currency->code}">
                                <div class='row'>
                                    <div class="col-md-3 col-sm-3 col-xs-3"></div>
                                    <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                                        <label>{$MGLANG->T('settings','domainaddons','DNSManagement')}</label>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                                        <label>{$MGLANG->T('settings','domainaddons','EmailForwarding')}</label>
                                    </div>
                                    <div class="col-md-3 col-sm-3 col-xs-3 text-center">
                                        <label>{$MGLANG->T('settings','domainaddons','IDProtection')}</label>
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
                                                <input class='form-control' name='DNSManagement[{$currency->id}][adminprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='EmailForwarding[{$currency->id}][adminprice]' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='IDProtection[{$currency->id}][adminprice]' />
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
                                                <input class='form-control' name='DNSManagement[{$currency->id}][highestprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='EmailForwarding[{$currency->id}][highestprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='IDProtection[{$currency->id}][highestprice]'/>
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
                                                <input class='form-control' name='DNSManagement[{$currency->id}][lowestprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='EmailForwarding[{$currency->id}][lowestprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-sm-3 col-xs-3'>
                                        <div class="controls">
                                            <div class="input-group has-addon-left">
                                                <div class="input-group-addon">{$currency->prefix}</div>
                                                <input class='form-control' name='IDProtection[{$currency->id}][lowestprice]'/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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


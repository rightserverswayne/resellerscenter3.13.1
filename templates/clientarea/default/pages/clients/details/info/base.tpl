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
    
{assign var='clientProfileFields' value=['firstname', 'lastname', 'companyname','email','phonenumber','password2','currency','address1','address2','city','state','postcode','country']}
<div class="row">
    <form class="profileDetails" action='index.php?m=ResellersCenter&mg-page=clients&mg-action=updateProfile'>
        <div class="col-md-12 mg-float-left">
            <h4>{$MGLANG->T('details', 'profile', 'title')}</h4>
            <div class="col-md-6 col-xs-12 mg-float-left">
            {foreach from=$clientProfileFields key=index item=field}
                {if $whmcs8 && $field eq 'password2'}
                    {continue}
                {/if}

                {if $index eq ($clientProfileFields|@count+1) / 2 }</div> <div class="col-md-6 mg-float-left">{/if}
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('details','profile',$field)}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="inputValues">
                                {if $field eq 'country'}
                                    <select class="form-control" name="client[country]">
                                        {foreach from=$countries key=code item=country}
                                            <option value="{$code}" {if $code eq $client->$field}selected{/if}>{$country}</option>
                                        {/foreach}
                                    </select>
                                {elseif $field eq 'currency'}
                                    <select class="form-control" name="client[currency]">
                                        {foreach from=$currencies item=currency}
                                            <option value="{$currency->id}" {if $currency->id eq $client->$field}selected{/if}>{$currency->code}</option>
                                        {/foreach}
                                    </select>
                                {elseif $field eq 'phonenumber'}
                                    <input name="{$field}" class="form-control" value="{$client->$field}" type="tel">
                                {else}
                                    <input class="form-control" name="client[{$field}]" value="{if $field neq 'password2'}{$client->$field}{/if}" {if $field eq 'password2'} placeholder='{$MGLANG->T('details','profile','entertochange')}'{/if}/>
                                {/if}
                            </div>
                        </div>
                    </div>
            {/foreach}
            </div>
        </div>
            
        {if !$customFields->isEmpty()}
            <div class="col-md-12 mg-float-left">
                <h4>{$MGLANG->T('details', 'customfields', 'title')}</h4>
                <div class="col-md-6 col-xs-12 mg-float-left">
                {foreach from=$customFields key=index item=field}
                    {if $index eq ($customFields|@count+1) / 2 }</div> <div class="col-md-6 mg-float-left">{/if}
                        {assign var=splitAt value=$field.fieldname|strpos:"|"}
                        {assign var=fieldid value=$field.id}
                        <div class="row">
                            <div class="col-md-3">
                                <label for="client[customfields][{$field.id}]">{if $splitAt}{$field.fieldname|substr:($splitAt+1)}{else}{$field.fieldname}{/if}</label>
                            </div>
                            <div class="col-md-9">
                                {if $field.fieldtype eq 'dropdown'}
                                    <select class="form-control">
                                        <option value="">{$MGLANG->T('add','customfields','option','none')}</option>
                                        {foreach from=","|explode:$field.fieldoptions item=optionvalue}
                                            <option {if $client->customFields->$fieldid->value eq $optionvalue}selected{/if} value="{$optionvalue}">{$optionvalue}</option>
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
                                        {if $client->customFields->$fieldid->value}checked{/if}
                                    />
                                {elseif $field.fieldtype eq 'textarea'}
                                    <textarea class="form-control" name="client[customfields][{$field.id}]">{$client->customFields->$fieldid->value}</textarea>
                                {elseif $field.fieldtype eq 'password'}
                                    <input type="password" class="form-control" name="client[customfields][{$field.id}]" value="{$client->customFields->$fieldid->value}"/>
                                {else}
                                    <input class="form-control" name="client[customfields][{$field.id}]" value="{$client->customFields->$fieldid->value}"/>
                                {/if}
                            </div>
                        </div>
                {/foreach}
                </div>
            </div>
        {/if}

        {if $reseller->settings->admin->allowcreditline}
            <div class="col-md-12 mg-float-left">
                <h4>{$MGLANG->T('details', 'creditline', 'title')}</h4>
                <div class="col-md-6 col-xs-12 mg-float-left">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="clientSettings[useCustomCreditLineLimit]">{$MGLANG->T('details','creditline','useCustomCreditLineLimit','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="checkbox-container text-right">
                                <input  class="checkbox-switch"
                                        data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                        data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                        data-on-color="success"
                                        data-off-color="default"
                                        data-size="mini"
                                        data-label-width="15"
                                        type="checkbox"
                                        name="clientSettings[useCustomCreditLineLimit]"
                                        {if $clientSettings->useCustomCreditLineLimit}checked{/if}/>
                            </div>

                            <h1>{$creditLine->creditlinelimit}</h1>

                            <input class="form-control mt-1"
                                   type ="number"
                                   min="0"
                                   step="0.01"
                                   name="client[creditlinelimit]"
                                   value="{if $creditLine->originalLimit}{$creditLine->originalLimit}{else}0{/if}"/>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {if $endClientConsolidatedInvoices eq 'on'}
            <div class="col-md-12 mg-float-left">
                <h4>{$MGLANG->T('details', 'billings', 'title')}</h4>
                <div class="col-md-6 col-xs-12 mg-float-left">
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('billings','enable','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <div class="checkbox-container text-right">
                                <input  class="checkbox-switch"
                                        data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                        data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                        data-on-color="success"
                                        data-off-color="default"
                                        data-size="mini"
                                        data-label-width="15"
                                        type="checkbox"
                                        name="clientSettings[enableConsolidatedInvoices]"
                                        {if $clientSettings->enableConsolidatedInvoices}checked{/if}/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label>{$MGLANG->T('billings','day','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control"
                                   type ="number"
                                   min="1"
                                   max="31"
                                   name="clientSettings[consolidatedInvoicesDay]"
                                   value="{if $clientSettings->consolidatedInvoicesDay}{$clientSettings->consolidatedInvoicesDay}{else}1{/if}"/>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <div id="ClientsActionPanel" class="col-md-12 mg-float-left">
            <h4>{$MGLANG->T('details', 'actions', 'title')}</h4>
            <div class="action-row col-md-12">
                <a id="resetpw" data-clientid="{$client->id}" href="#"><img src="{$assetsURL}/img/resetpw.png" > {$MGLANG->T('details', 'actions', 'resetpassword')}</a>
            </div>
            {if $reseller->settings->admin->login == 'on'}
                <div class="action-row col-md-12">
                    <a href="index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAsClient&clientid={$client->id}"><img src="{$assetsURL}/img/clientlogin.png"> {$MGLANG->T('details', 'actions', 'loginasclient')}</a>
                </div>
            {/if}

            <div class="row mg-display-block" style="text-align: center">
               <button class="btn btn-lg btn-success btn-inverse" onclick="ResellersCenter_ClientsDetails.submitEditProfile(); return false;">{$MGLANG->T('details','save')}</button>
            </div>
        </div>
            
    </form>
</div>
        
<script type="text/javascript">
    {include file='details/info/controller.js'}
</script>
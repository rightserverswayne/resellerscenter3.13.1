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
<div class="billingcycles panel-group" role="tablist" aria-multiselectable="false">
    {assign var=periods value=["msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially"]}
    
    <div class='row'>
        <div class="col-md-2 col-sm-2 col-xs-2"></div>
        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
            <label>{$MGLANG->T('domains','types','register')}</label>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
            <label>{$MGLANG->T('domains','types','transfer')}</label>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-3 text-center">
            <label>{$MGLANG->T('domains','types','renew')}</label>
        </div>
    </div>
    {foreach from=$periods item=period}
        <div class='row'>
            <div class='col-md-2 col-sm-2 col-xs-2 text-right' style="margin-bottom: 25px;">
                <label>{$MGLANG->T('domainperiods', $period)}</label>
            </div>
            <div class='col-md-3 col-sm-3 col-xs-3'>
                <div class="controls">
                    <div class="input-group has-addon-left">
                        <div class="input-group-addon">{$currency->prefix}</div>
                        <input class='form-control' name='domainregister[{$currency->id}][{$period}]'/>
                    </div>
                </div>
                <div class="text-muted priceRange">
                </div>
            </div>
            <div class='col-md-3 col-sm-3 col-xs-3'>
                <div class="controls">
                    <div class="input-group has-addon-left">
                        <div class="input-group-addon">{$currency->prefix}</div>
                        <input class='form-control' name='domaintransfer[{$currency->id}][{$period}]' />
                    </div>
                </div>
                <div class="text-muted priceRange">
                </div>
            </div>
            <div class='col-md-3 col-sm-3 col-xs-3'>
                <div class="controls">
                    <div class="input-group has-addon-left">
                        <div class="input-group-addon">{$currency->prefix}</div>
                        <input class='form-control' name='domainrenew[{$currency->id}][{$period}]' />
                    </div>
                </div>
                <div class="text-muted priceRange">
                </div>
            </div>
        </div>
    {/foreach}
 
</div>
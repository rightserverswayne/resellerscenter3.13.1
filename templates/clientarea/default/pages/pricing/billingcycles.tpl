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
{assign var=billingcycles value=['monthly', 'quarterly', 'semiannually','annually','biennially','triennially']}

{foreach from=$billingcycles item=billing}
    <div class="billingcycle row">
        <div class="col-md-3">
            <label>{$MGLANG->T('billingcycles', $billing)}</label>
        </div>
        <div class="col-md-9">
            
            <div class="row">
                <div class="col-md-3">
                    {$MGLANG->T('billingcycles', 'setupfee')}
                </div>
                
                <div class="col-md-4">
                    <div class="controls">
                        {assign var=setupfees value=['monthly' => 'msetupfee', 'quarterly' => 'qsetupfee', 'semiannually' => 'ssetupfee', 'annually' => 'asetupfee', 'biennially' => 'bsetupfee', 'triennially' => 'tsetupfee']}
                        <div class="input-group has-addon-left">
                            <div class="input-group-addon">{$currency->prefix}</div>
                            <input class='form-control' name='pricing[{$currency->id}][{$setupfees.$billing}]' />
                        </div>
                    </div>
                </div>
                        
                <div class="col-md-5 text-muted priceRange">
                    {* Filled By AJAX *}
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    {$MGLANG->T('billingcycles', 'recurring')}
                </div>
                
                <div class="col-md-4">
                    <div class="controls">
                        <div class="input-group has-addon-left">
                            <div class="input-group-addon">{$currency->prefix}</div>
                            <input class='form-control' name='pricing[{$currency->id}][{$billing}]' />
                        </div>
                    </div>
                </div>
                        
                <div class="col-md-5 text-muted priceRange">
                    {* Filled By AJAX *}
                </div>
            </div>
        </div>

    </div>
{/foreach}
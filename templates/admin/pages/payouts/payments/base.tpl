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
 <div id="payments" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-share-square-o font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('massPayment','title')}
            </span>
        </div>

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#paypal" data-toggle="tab">
                    {$MGLANG->T('paypal', 'title')}
                </a>
            </li>
            <li>
                <a href="#credits" data-toggle="tab">
                    {$MGLANG->T('credits', 'title')}
                </a>
            </li>
        </ul>
    </div>
    <div class="box-body" style='min-height: 300px;'>

        <div class="tab-content">
            <div class="tab-pane active" id="paypal">
                <div class="help-block">
                    {$MGLANG->T('paypal', 'help')}
                </div>
                        
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','resellers','title')}</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <select class="form-control select2" multiple="" name="resellers">
                                    {* Filled by AJAX *}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('paypal','resellers','help')}</div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','summary','title')}</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>{$MGLANG->T('paypal','summary','total')}</strong>
                                <span class="totalResellersProfit"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('paypal','summary','help')}</div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','accept','title')}</h4>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('paypal','accept','help1')}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="note note-warning">{$MGLANG->T('paypal','accept','help2')}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col-md-12'>
                                <div class='rc-actions text-center'>
                                    <button class="makeMassPayment btn btn-success btn-inverse" data-type="paypal">{$MGLANG->T('paypal','accept','button')}</button>
                                </div>
                            </div>
                        </div>
                            
                    </div>
                </div>
            </div>
                            
            <div class="tab-pane" id="credits">
                
                 <div class="row">
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','resellers','title')}</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <select class="form-control select2" multiple="" name="resellers">
                                    {* Filled by AJAX *}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('paypal','resellers','help')}</div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','summary','title')}</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <strong>{$MGLANG->T('paypal','summary','total')}</strong>
                                <span class="totalResellersProfit"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('paypal','summary','help')}</div>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-4 col-sm-12">
                        
                        <h4>{$MGLANG->T('paypal','accept','title')}</h4>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="help-block">{$MGLANG->T('credits','accept','help1')}</div>
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class='col-md-12'>
                                <div class='rc-actions text-center'>
                                    <button class="makeMassPayment btn btn-success btn-inverse" data-type="credits">{$MGLANG->T('paypal','accept','button')}</button>
                                </div>
                            </div>
                        </div>
                            
                    </div>
                </div>
                
                
            </div>
        </div>
        
    </div>
</div>
                            
 <div class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-cogs font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('configuration','title')}
            </span>
        </div>
    </div>
    <div class="box-body" style='min-height: 300px;'>
        <form id="paypalConfiguration">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <label>{$MGLANG->T('paypal','appKey','label')}</label>
                        </div>
                        <div class='col-md-12'>
                            <input class="form-control" 
                                   name="settings[RCPayPalAppKey]" 
                                   value="{if $paypal.appKey}{$paypal.appKey}{/if}"
                                   placeholder="{$MGLANG->T('paypal','appKey','placeholder')}"
                                   />
                        </div>
                        <div class='col-md-12'>
                            <div class='help-block'>{$MGLANG->T('paypal','appKey','help')}</div>
                        </div>
                    </div>
                        
                    <div class="row">
                        <div class="col-md-12">
                            <label>{$MGLANG->T('paypal','sandbox','label')}</label>
                        </div>
                        <div class='col-md-12'>
                               <div class="checkbox-container">
                                <input class="checkbox-switch" 
                                        data-on-text="{$MGLANG->T('paypal','sandbox','ontext')}" 
                                        data-off-text="{$MGLANG->T('paypal','sandbox','offtext')}" 
                                        data-on-color="warning"
                                        data-off-color="success"
                                        data-size="mini" 
                                        data-label-width="15" 
                                        type="checkbox"  
                                        name="settings[RCPayPalPayoutSandBox]"
                                        {if $paypal.sandbox}checked{/if}
                                />
                            </div>
                        </div>
                        <div class='col-md-12'>
                            <div class='help-block'>{$MGLANG->T('paypal','sandbox','help')}</div>
                        </div>
                    </div>
                </div>

                 <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <label>{$MGLANG->T('paypal','secret','label')}</label>
                        </div>
                        <div class='col-md-12'>
                           <input class="form-control" 
                                   name="settings[RCPayPalSecret]" 
                                   value="{if $paypal.secret}{$paypal.secret}{/if}"
                                   placeholder="{$MGLANG->T('paypal','secret','placeholder')}"
                                   />
                        </div>
                        <div class='col-md-12'>
                            <div class='help-block'>{$MGLANG->T('paypal','secret','help')}</div>
                        </div>
                    </div>
                </div>
           </div>
        </form>

        <div class='row'>
            <div class='col-md-12'>
                <div class='rc-actions text-center' style="margin-top: 50px;">
                    <button class='btn btn-lg btn-success btn-inverse' onclick="RC_Payouts_PayPal.submitConfigurationForm();">{$MGLANG->T('paypal','save')}</button>
                </div>
            </div>
        </div>
    </div>
</div>
                                    
{* Confirm dialog box *}                        
{include file='payments/confirm.tpl'}
                        
<script type="text/javascript">
    {include file="payments/controller.js"}
</script>
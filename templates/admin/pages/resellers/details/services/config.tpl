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
<div id="serviceConfigModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('services','config','title')}</h4>
            </div>
            <div class="modal-body">
                <form id='serviceConfigForm'>
                    <input hidden name="resellerid" value="{$reseller->id}">
                    
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="price">{$MGLANG->T('services','config','price','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <input class="form-control" name="price" value="{* filled by AJAX *}"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('services','config','price','help')}</div>
                            </div>
                        </div>
                    </div>
                            
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control" for="billingcycle">{$MGLANG->T('services','config','billingcycle','label')}</label>
                            </div>
                            <div class="col-md-10">
                                
                                <div style="display: none;">
                                    <select class="form-control select2" name="billingcycle">
                                        <option value="freeaccount">{$MGLANG->T('hosting','billingcycles','free')}</option>
                                        <option value="onetime">{$MGLANG->T('hosting','billingcycles','onetime')}</option>
                                        <option value="monthly">{$MGLANG->T('hosting','billingcycles','monthly')}</option>
                                        <option value="quarterly">{$MGLANG->T('hosting','billingcycles','quarterly')}</option>
                                        <option value="semiannually">{$MGLANG->T('hosting','billingcycles','semiannually')}</option>
                                        <option value="annually">{$MGLANG->T('hosting','billingcycles','annually')}</option>
                                        <option value="biennially">{$MGLANG->T('hosting','billingcycles','biennially')}</option>
                                        <option value="triennially">{$MGLANG->T('hosting','billingcycles','triennially')}</option>
                                    </select>
                                </div>
                                
                                <div style="display: none;">
                                    <select class="form-control select2" name="registrationperiod">
                                        <option value='1'>{$MGLANG->T('domains','registerperiods','one')}</option>
                                        <option value='2'>{$MGLANG->T('domains','registerperiods','two')}</option>
                                        <option value='3'>{$MGLANG->T('domains','registerperiods','tree')}</option>
                                        <option value='4'>{$MGLANG->T('domains','registerperiods','four')}</option>
                                        <option value='5'>{$MGLANG->T('domains','registerperiods','five')}</option>
                                        <option value='6'>{$MGLANG->T('domains','registerperiods','six')}</option>
                                        <option value='7'>{$MGLANG->T('domains','registerperiods','seven')}</option>
                                        <option value='8'>{$MGLANG->T('domains','registerperiods','eight')}</option>
                                        <option value='9'>{$MGLANG->T('domains','registerperiods','nine')}</option>
                                        <option value='10'>{$MGLANG->T('domains','registerperiods','ten')}</option>
                                    </select>
                                </div>
                                
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-offset-2 col-md-10">
                                <div class="help-block">{$MGLANG->T('services','config','billingcycle','help')}</div>
                            </div>
                        </div>
                    </div>
                            
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_ResellersServices.submitConfigForm();'>{$MGLANG->T('services','config','save')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('services','config','close')}</button>
            </div>
        </div>
    </div>
</div>
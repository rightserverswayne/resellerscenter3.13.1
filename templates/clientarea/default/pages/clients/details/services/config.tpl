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
<div id="RCServiceEdit" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->T('services','edit','title')}</h4>
                </div>
                <div class="modal-body">
                    
                    <div class='form-group'>
                        <div class='row'>
                            <div class='col-md-3'>
                                <label>{$MGLANG->T('services','edit','price','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <input class='form-control' name="service[price]" />
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-md-offset-3 col-md-9'>
                                <div class='help-block'>{$MGLANG->T('services','edit', 'price', 'help')}</div>
                            </div>
                        </div>
                    </div>
                        
                    <div class='form-group'>
                        <div class='row'>
                            <div class='col-md-3'>
                                <label>{$MGLANG->T('services','edit','billingcycle','label')}</label>
                            </div>
                            <div class='col-md-9'>
                                <select class='form-control select2' name="service[billingcycle]">
                                    <option value='monthly'>{$MGLANG->T('services','edit','billingcycle','monthly')}</option>
                                    <option value='quarterly'>{$MGLANG->T('services','edit','billingcycle','quarterly')}</option>
                                    <option value='semiannually'>{$MGLANG->T('services','edit','billingcycle','semiannually')}</option>
                                    <option value='annually'>{$MGLANG->T('services','edit','billingcycle','annually')}</option>
                                    <option value='biennially'>{$MGLANG->T('services','edit','billingcycle','biennially')}</option>
                                    <option value='triennially'>{$MGLANG->T('services','edit','billingcycle','triennially')}</option>
                                </select>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-md-offset-3 col-md-9'>
                                <div class='help-block'>{$MGLANG->T('services','edit', 'billingcycle', 'help')}</div>
                            </div>
                        </div>
                    </div>
                        
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-inverse" onclick="ResellersCenter_ClientsServices.submitEditService();">{$MGLANG->T('add','save')}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('add','close')}</button>
                </div>
            </div>
        </form>
    </div>
</div>
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
<div id="RCInvoiceCreate" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('create','title')} - <strong class="invoice-status text-grey">{$MGLANG->T('create', 'status', 'draft')}</strong></h4>
            </div>
            <div class="modal-body">
                <form action=''>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label style="padding-top: 0px">{$MGLANG->T('create', 'details', 'client')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <select class="form-control" name="invoice[userid]">
                                        {* AJAX *}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label style="padding-top: 0px">{$MGLANG->T('create', 'details', 'paymentMethod')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <select class="form-control" name="invoice[paymentmethod]">
                                        {foreach from=$gateways item=gateway}
                                            {if $gateway->enabled}
                                                <option value="{$gateway->name}">{$gateway->displayName}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label for="invoice[invoicenum]">{$MGLANG->T('create', 'details', 'invoicenum')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <div class="form-group">
                                        <input class="form-control" name="invoice[invoicenum]" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label for="invoice[tax1]">{$MGLANG->T('create', 'details', 'taxrate1')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input class="form-control" name="invoice[tax1]" value="">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">

                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label for="date">{$MGLANG->T('create', 'details', 'date')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <div class="form-group">
                                        <div class='input-group date'>
                                            <input class="form-control" name="invoice[date]" value="">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label for="invoice[tax2]">{$MGLANG->T('create', 'details', 'taxrate2')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <div class="input-group">
                                        <input class="form-control" name="invoice[tax2]" value="">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-lg-3 col-md-5">
                                    <label for="duedate">{$MGLANG->T('create', 'details', 'duedate')}</label>
                                </div>
                                <div class="col-lg-9 col-md-7">
                                    <div class="form-group">
                                        <div class='input-group date'>
                                            <input class="form-control" name="invoice[duedate]" value="">
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">

                        </div>
                    </div>


                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width: 70%">{$MGLANG->T('create', 'details', 'description')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('create', 'details', 'amount')}&nbsp;&nbsp;</th>
                            <th style="text-align: center;">{$MGLANG->T('create', 'details', 'taxed')}&nbsp;&nbsp;</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><input class="form-control" name="invoice[items][0][description]" value=""/></td>
                            <td style="vertical-align: middle"><input class="form-control" name="invoice[items][0][amount]"                 value="" style="display: inline; width: 70%"></td>
                            <td style="vertical-align: middle; text-align:center;"><input type="checkbox" name="invoice[items][0][taxed]"  value="1"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <button class="btn btn-xs btn-success btn-inverse" type="button" onclick="ResellersCenter_InvoiceCreate.addItem()">
                                    <i class="fa fa-plus"></i> {$MGLANG->T('create', 'details', 'additem')}
                                </button>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </form>

                {* prototype for invoice items *}
                <table style="display: none;">
                    <tr data-prototype>
                        <td><input class="form-control" name="invoice[items][+itemid+][description]" value="" /></td>
                        <td style="vertical-align: middle"><input class="form-control" name="invoice[items][+itemid+][amount]"                 value="" style="display: inline; width: 70%"></td>
                        <td style="vertical-align: middle; text-align:center;"><input type="checkbox" style="text-align:center;" name="invoice[items][+itemid+][taxed]" value="1"></td>
                        <th style="text-align: center;"><button class="deleteItemRowBtn btn btn-icon-only btn-danger btn-inverse"><i class="fa fa-trash"></i></button></th>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick="ResellersCenter_InvoiceCreate.submitCreateForm(0);" data-dismiss="modal">{$MGLANG->T('create', 'button', 'create')}</button>
                <button type="button" class="btn btn-info btn-inverse" onclick="ResellersCenter_InvoiceCreate.submitCreateForm(1);" data-dismiss="modal">{$MGLANG->T('create', 'button', 'publish')}</button>
                <button type="button" class="btn btn-warning btn-inverse" onclick="ResellersCenter_InvoiceCreate.submitCreateForm(2);" data-dismiss="modal">{$MGLANG->T('create', 'button', 'publishAndSend')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
            
<script type="text/javascript">
    {include file='create/controller.js'}
</script>
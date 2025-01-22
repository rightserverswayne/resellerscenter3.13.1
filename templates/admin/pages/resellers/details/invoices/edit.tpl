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
<div id="invoiceEdit" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%;">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->T('invoices','edit','title')}</h4>
                </div>
                <div class="modal-body">
                    <input hidden name="invoice[invoiceid]" value="" />

                    <div class="row">
                        <div class="col-md-3">
                            <label for="date">{$MGLANG->T('invoices','edit','date')}</label>
                        </div>
                        <div class="col-lg-5 col-md-7">
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
                    <div class="row">
                        <div class="col-md-3">
                            <label for="duedate">{$MGLANG->T('invoices','edit','duedate')}</label>
                        </div>
                        <div class="col-lg-5 col-md-7">
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 70%">{$MGLANG->T('invoices','edit','description')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','edit','amount')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->T('invoices','edit','taxed')}&nbsp;&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-inverse" onclick="RC_ResellersInvoices.submitEditForm()" data-dismiss="modal">{$MGLANG->absoluteT('form','button','save')}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
                </div>
            </div>
        </form>
    </div>

{* prototype for invoice items *}                
<table style="display: none;">
    <tr data-prototype>
        <td><input class="form-control" name="invoice[itemdescription][+itemid+]" value="" ></td>
        <td><input class="form-control" name="invoice[itemamount][+itemid+]"      value="" ></td>
        <td><input type="checkbox" class="form-control invoice-checkbox" name="invoice[itemtaxed][+itemid+]" value="1"></td>
    </tr>
</table>                
</div>
<div id="RCInvoiceDetails" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 60%">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'title')}</h4>
                </div>
                <div class="modal-body">
                    <input hidden name="invoice[invoiceid]" value="" />
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="date">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'date')}</label>
                                    </div>
                                    <div class="col-lg-5 col-md-7 my-auto">
                                        <span data-name="invoice[date]"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="duedate">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'duedate')}</label>
                                    </div>
                                    <div class="col-lg-5 col-md-7 my-auto">
                                        <span data-name="invoice[duedate]"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="totaldue">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'totaldue')}</label>
                                    </div>
                                    <div class="col-lg-5 col-md-7 my-auto">
                                        <span data-name="invoice[totaldue]"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <center>
                                    <h4><strong class="invoice-status"></strong></h4>
                                    <div style="margin-bottom: 2px;">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'paymentMethod')}: <span class="invoice-paymentmethod"></span></div>
                                    <div class="rc-invoice-actions-downloadpdf mt-3">
                                        <button class="downloadPdfBtn btn btn-sm btn-info btn-inverse">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'downloadpdf')} <i class="fas fa-download"></i></button>
                                    </div>
                                </center>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 70%">{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'description')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'amount')}&nbsp;&nbsp;</th>
                                <th>{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'taxed')}&nbsp;&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
                </div>
            </div>
        </form>
    </div>

{* prototype for invoice items *}                
<table style="display: none;">
    <tr data-prototype>
        <td><span disabled data-name="invoice[itemdescription][+itemid+]" value="" ></span></td>
        <td><span disabled data-name="invoice[itemamount][+itemid+]"      value="" ></span></td>
        <td><input disabled type="checkbox" name="invoice[itemtaxed][+itemid+]" value="1" style="width: 35px;"></td>
    </tr>
</table>
</div>
<form action=''>
    <input hidden name="invoice[invoiceid]" value="" />

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-lg-3 col-md-5">
                    <label for="date">{$MGLANG->T('invoices', 'edit', 'details', 'date')}</label>
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
            <div class="row">
                <div class="col-lg-3 col-md-5">
                    <label for="duedate">{$MGLANG->T('invoices', 'edit', 'details', 'duedate')}</label>
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
            <center>
                <h4><strong class="invoice-status"></strong></h4>
                <div>{$MGLANG->T('invoices', 'edit', 'details', 'paymentMethod')}: <span class="invoice-paymentmethod"></span></div>
                <div class="rc-invoice-actions">
                    <button class="markPaidBtn btn btn-sm btn-info btn-inverse">{$MGLANG->T('invoices', 'edit', 'details', 'markPaid')}</button>
                    <button class="markUnpaidBtn btn btn-sm btn-default btn-inverse">{$MGLANG->T('invoices', 'edit', 'details', 'markUnpaid')}</button>
                    <button class="markCancelledBtn btn btn-sm btn-default btn-inverse">{$MGLANG->T('invoices', 'edit', 'details', 'markCancelled')}</button>
                </div>
                <div class="rc-invoice-actions-downloadpdf">
                    <button class="downloadPdfBtn btn btn-sm btn-info btn-inverse">{$MGLANG->T('edit', 'details', 'downloadpdf')} <i class="fas fa-download"></i></button>
                </div>
            </center>
        </div>

    </div>
                
    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width: 70%">{$MGLANG->T('invoices', 'edit', 'details', 'description')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('invoices', 'edit', 'details', 'amount')}&nbsp;&nbsp;</th>
                <th style="text-align: center;">{$MGLANG->T('invoices', 'edit', 'details', 'taxed')}&nbsp;&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr class="rc-invoice-summary">
                <td>{$MGLANG->T('invoices', 'edit', 'details', 'subtotal')}:</td>
                <td class='rc-invoice-summary-subtotal'></td>
                <td></td>
            </tr>
            
            {if $taxes.tax1}
                <tr class="rc-invoice-summary">
                    <td class='rc-invoice-summary-tax1'>{$taxes.tax1->taxrate}% {$MGLANG->T('invoices', 'edit', 'details', 'tax')} {$taxes.tax1->name}:</td>
                    <td class='rc-invoice-summary-tax1amount'></td>
                    <td></td>
                </tr>
            {/if}
            
            {if $taxes.tax2}
                <tr class="rc-invoice-summary">
                    <td class='rc-invoice-summary-tax2'>{$taxes.tax2->taxrate}% {$MGLANG->T('invoices', 'edit', 'details', 'tax')} {$taxes.tax2->name}:</td>
                    <td class='rc-invoice-summary-tax2amount'></td>
                    <td></td>
                </tr>
            {/if}
            
            <tr class="rc-invoice-summary">
                <td>{$MGLANG->T('invoices', 'edit', 'details', 'total')}:</td>
                <td class='rc-invoice-summary-total'></td>
                <td></td>
            </tr>
            <tr class="rc-invoice-summary">
                <td>{$MGLANG->T('invoices', 'edit', 'details', 'credit')}:</td>
                <td class='rc-invoice-summary-credit'></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</form>

{* prototype for invoice items *}
<table style="display: none;">
    <tr data-prototype>
        <td><textarea class="form-control" name="invoice[itemdescription][+itemid+]" value="" ></textarea></td>
        <td style="vertical-align: middle;"><input class="form-control" name="invoice[itemamount][+itemid+]" value="" style="display: inline; width: 70%"></td>
        <td style="vertical-align: middle;"><input type="checkbox" name="invoice[itemtaxed][+itemid+]" value="1"></td>
    </tr>
</table>

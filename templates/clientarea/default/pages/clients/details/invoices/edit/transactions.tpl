<form action="">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                
                <div class="row">
                    <div class="col-md-4">
                        <label>{$MGLANG->T('invoices', 'edit','addpayment', 'date', 'label')}</label>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <div class='input-group date'>
                                <input class="form-control" name="payment[date]" value=""/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="row">
                    <div class="col-md-4">
                        <label>{$MGLANG->T('invoices', 'edit', 'addpayment', 'paymentmethod', 'label')}</label>
                    </div>
                    <div class="col-md-8">
                        <select class="form-control" name="payment[gateway]">
                            {foreach from=$gateways item=gateway}
                                <option value="{$gateway->name}">{$gateway->displayName}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                    
                <div class="row">
                    <div class="col-md-4">
                        <label>{$MGLANG->T('invoices', 'edit', 'addpayment', 'transactionid', 'label')}</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control" name="payment[transid]" value=""/>
                    </div>
                </div>
                    
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                
                <div class="row">
                    <div class="col-md-4">
                        <label>{$MGLANG->T('invoices', 'edit', 'addpayment', 'amount', 'label')}</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control" name="payment[amount]" value=""/>
                    </div>
                </div>
            
                <div class="row">
                    <div class="col-md-4">
                        <label>{$MGLANG->T('invoices', 'edit', 'addpayment', 'fees', 'label')}</label>
                    </div>
                    <div class="col-md-8">
                        <input class="form-control" name="payment[fees]" value=""/>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
                    
    <center>
        <a class="AddPaymentBtn btn btn-primary btn-inverse">{$MGLANG->T('invoices', 'edit', 'addpayment', 'button')}</a>
    </center>
</form>

<div class="row" style="margin-top: 50px;">
    <div class="col-md-12">
        <table id='transactionsTable' class='table table-hover' width="100%" style="margin-bottom: 50px">
            <thead>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'date')}</th>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'paymentmethod')}</th>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'transactionid')}</th>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'amount')}</th>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'transactionfees')}</th>
                <th>{$MGLANG->T('invoices', 'edit', 'transactions', 'table', 'actions')}</th>
            <thead>
        </table>
    </div>
</div>
<form method="post" action="{$whmcsUrl}rccreditcard.php" name="paymentfrm">
    <input name="resellerInvoice" value="1" type="hidden">
    <input name="invoiceid" value="{$invoiceid}" type="hidden">
    <button type="submit" class="btn btn-success btn-sm" id="btnPayNow">
        <i class="fa fa-credit-card"></i>&nbsp; {$payNowText}
    </button>
</form>
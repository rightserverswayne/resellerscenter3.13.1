<form method="post" action="{$systemurl}rccreditcard.php" name="paymentfrm">
    <input name="invoiceid" value="{$invoiceid}" type="hidden">
    <input name="action" value="submit" type="hidden">
    <button type="submit" class="btn btn-success btn-sm" id="btnPayNow">
        <i class="fa fa-credit-card"></i>&nbsp; {$langpaynow} 
    </button>
</form>
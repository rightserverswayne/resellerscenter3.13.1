<form name="paymentfrm" method="post" action="{$systemUrl}/modules/addons/ResellersCenter/gateways/BillPlzPay/core/billplzBills.php">
    <input type="hidden" name="resellerid"          value = "{$resellerid}">
    <input type="hidden" name="email"               value = "{$email}">
    <input type="hidden" name="basecurrencyamount"  value = "{$baseCurrencyAmount}">
    <input type="hidden" name="basecurrency"        value = "{$baseCurrency}">
    <input type="hidden" name="userid"              value = "{$userid}">
    <input type="hidden" name="mobile"              value = "{$mobile}">
    <input type="hidden" name="name"                value = "{$name}">
    <input type="hidden" name="amount"              value = "{$amount}">
    <input type="hidden" name="invoiceid"           value = "{$invoiceid}">
    <input type="hidden" name="description"         value = "{$description}">
    <input type="hidden" name="hash"                value = "{$hash}">
    <input src="{$systemUrl}/modules/addons/ResellersCenter/gateways/BillPlzPay/core/btn-pay.png" name="submit" type="image">
</form><p>{$instructions}</p>
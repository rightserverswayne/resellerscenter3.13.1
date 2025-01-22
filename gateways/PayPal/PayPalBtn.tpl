<table>
    <tbody>
        <tr>
            <td>
                <form action="{$paypalUrl}" method="post">
                    <input name="cmd" value="_xclick" type="hidden">
                    <input name="business" value="{$paypalemail}" type="hidden">
                    <input name="item_name" value="{$description}" type="hidden">
                    <input name="amount" value="{$amount}" type="hidden">
                    <input name="tax" value="0.00" type="hidden">
                    <input name="no_note" value="1" type="hidden">
                    <input name="no_shipping" value="1" type="hidden">
                    <input name="address_override" value="0" type="hidden">
                    <input name="first_name" value="{$clientdetails->firstname}" type="hidden">
                    <input name="last_name" value="{$clientdetails->lastname}" type="hidden">
                    <input name="email" value="{$clientdetails->email}" type="hidden">
                    <input name="address1" value="{$clientdetails->address}" type="hidden">
                    <input name="city" value="{$clientdetails->city}" type="hidden">
                    <input name="state" value="{$clientdetails->state}" type="hidden">
                    <input name="zip" value="{$clientdetails->postcode}" type="hidden">
                    <input name="country" value="{$clientdetails->country}" type="hidden">
                    <input name="night_phone_a" value="{$phone1}" type="hidden">
                    <input name="night_phone_b" value="{$phone2}" type="hidden">
                    <input name="night_phone_c" value="{$phone3}" type="hidden">
                    <input name="charset" value="{$charset}" type="hidden">
                    <input name="currency_code" value="{$currency}" type="hidden">
                    <input name="custom" value="{$invoice->id}" type="hidden">
                    <input name="return" value="{$returnSuccess}" type="hidden">
                    <input name="cancel_return" value="{$returnCancel}" type="hidden">
                    <input name="notify_url" value="{$notifyUrl}" type="hidden">
                    <input name="bn" value="WHMCS_ST" type="hidden">
                    <input name="rm" value="2" type="hidden">
                    <input src="https://www.paypal.com/en_US/i/btn/x-click-but03.gif" name="submit" alt="Make a one time payment with PayPal" border="0" type="image">
                </form>
            </td>
        </tr>
    </tbody>
</table>
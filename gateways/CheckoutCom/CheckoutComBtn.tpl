<form class='payment-form' method='POST' action='{$successurl}'>
    <script>
        window.CKOConfig = {
            publicKey: '{$apikey}',
            paymentToken: '{$token}',
            customerEmail: '{$email}',
            paymentMode: 'cards',
            value: '{$amount}',
            currency: '{$currency}',
            renderMode: 1,
            buttonLabel: '{$langpaynow}',
            widgetColor: '#FFFFFF',
            buttonColor: '#5cb85c',
            buttonLabelColor: '#FFFFFF',
        };
    </script>
    <script async src='https://cdn.checkout.com/sandbox/js/checkout.js'></script>
</form>
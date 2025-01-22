/*
 * WHMCS Stripe Javascript
 *
 * @copyright Copyright (c) WHMCS Limited 2005-2016
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
var paymentForm = '',
    stateNotRequired = true,
    ccForm = '';
jQuery(document).ready(function(){
    if (jQuery('#frmNewCc').length) {
        initNewCcForm();
    } else if (jQuery('#frmPayment').length) {
        paymentForm = jQuery('#frmPayment');
        initPaymentForm();
    } else if (jQuery('#frmCheckout').length) {
        paymentForm = jQuery('#frmCheckout');
        initPaymentForm();
    } else if (jQuery('#frmCreditCardDetails').length) {
         initAdminCreditCard();
    }
});

jQuery(document).on("ifChanged", "input[type=radio][name=paymentmethod]", function () {
    changeCcInfo();
});

function enable_payment_stripe(rc = false) {
    if(rc){
        rcStripeModule.rcMountInputs();
    } else {
        rcStripeModule.mountInputs();
    }

    paymentForm.find('#inputAddress1').attr('data-stripe', 'address_line1');
    paymentForm.find('#inputAddress2').attr('data-stripe', 'address_line2');
    paymentForm.find('#inputCity').attr('data-stripe', 'address_city');
    paymentForm.find('#inputState').attr('data-stripe', 'address_state');
    paymentForm.find('#inputPostcode').attr('data-stripe', 'address_zip');
    paymentForm.find('#inputCountry').attr('data-stripe', 'address_country');
    paymentForm.find('#inputPostcode').attr('data-stripe', 'address_zip');
    paymentForm.find('#cctype').removeAttr('name').parents('div.form-group').remove();
    paymentForm.find('#inputCardNumber').removeAttr('name').attr('data-stripe', 'number').payment('formatCardNumber');
    paymentForm.find('#inputCardExpiry').removeAttr('name').attr('data-stripe', 'exp_month');
    paymentForm.find('#inputCardExpiryYear').removeAttr('name').attr('data-stripe', 'exp_year');
    paymentForm.find('#inputCardCVV').removeAttr('name')
        .attr('data-stripe', 'cvc').parents('div.form-group').show('fast').payment('formatCardCVC');

    paymentForm.off('submit', validateCreditCardInput);
    paymentForm.on('submit', validatePaymentStripe);
}

 function validatePaymentStripe(e) {
    e.preventDefault();
    jQuery('#btnSubmit').attr('disabled', 'disabled').addClass('disabled');
    var result = rcStripeModule.submitForm(paymentForm, stripePaymentResponseHandler);
}

function stripePaymentResponseHandler(status, response) {
    if (response.error) { // Problem!
        // Show the errors on the form:
        paymentForm.find('.gateway-errors').text(response.error.message).slideUp();

        scrollToError();
       let btnSubmit = jQuery('#btnSubmit').length != 0 ? jQuery('#btnSubmit') : jQuery('#btnCompleteOrder');
        btnSubmit.removeAttr('disabled').removeClass('disabled')
            .find('span').toggleClass('hidden').find('i.fas,i.far,i.fal,i.fab').removeClass('fa-spinner');
        btnSubmit.find('i.fas,i.far,i.fal,i.fab').removeClass('fa-spinner fa-spin').addClass('fa-arrow-circle-right');// Re-enable submission

    } else { // Token was created!
        paymentForm.find('.gateway-errors').text('').addClass('hidden');
        // Insert the token ID into the form so it gets submitted to the server:
        paymentForm.append(jQuery('<input type="hidden" name="stripeToken">').val(response.id));

        // Submit the form:
        paymentForm.off('submit', validatePaymentStripe);
        paymentForm.find('#btnSubmit').removeAttr('disabled').removeClass('disabled')
            .click().addClass('disabled').attr('disabled', 'disabled');
    }
}

function initNewCcForm()
{
    var newCcForm = jQuery('#frmNewCc');
    rcStripeModule.mountInputs();
    // Remove name from CC Input fields, but add stripe-data
    newCcForm.find('#inputCardType').removeAttr('name').parents('div.form-group').remove();
    newCcForm.find('#inputCardNumber').removeAttr('name').attr('data-stripe', 'number').payment('formatCardNumber');
    newCcForm.find('#inputCardExpiry').removeAttr('name').attr('data-stripe', 'exp_month');
    newCcForm.find('select[name="ccexpiryyear"]').removeAttr('name').attr('data-stripe', 'exp_year');
    newCcForm.find('#inputCardCvv').removeAttr('name').attr('data-stripe', 'cvc').payment('formatCardCVC');

    newCcForm.off('submit', validateCreditCardInput);
    newCcForm.on('submit', validateNewCcStripe);
}

function validateNewCcStripe(event) {
    var newCcForm = jQuery('#frmNewCc');
    event.preventDefault();
    jQuery('#btnSubmitNewCard').attr('disabled', 'disabled').addClass('disabled');

    Stripe.card.createToken(newCcForm, stripeNewCcResponseHandler);
    return false;
}

function stripeNewCcResponseHandler(status, response) {
    var newCcForm = jQuery('#frmNewCc');
    if (response.error) { // Problem!

        // Show the errors on the form:
        newCcForm.find('.gateway-errors').text(response.error.message).removeClass('hidden');
        scrollToError();
        jQuery('#btnSubmitNewCard').removeAttr('disabled').removeClass('disabled'); // Re-enable submission

    } else { // Token was created!
        newCcForm.find('.gateway-errors').text('').addClass('hidden');
        // Insert the token ID into the form so it gets submitted to the server:
        newCcForm.append(jQuery('<input type="hidden" name="stripeToken">').val(response.id));

        // Submit the form:
        newCcForm.off('submit', validateNewCcStripe);
        newCcForm.find('#btnSubmitNewCard').removeAttr('disabled').removeClass('disabled')
            .click().addClass('disabled').attr('disabled', 'disabled');
    }
}

function initPaymentForm()
{
    changeCcInfo();
    jQuery('input[name="ccinfo"]').on('ifChecked', function(){
        changeCcInfo();
    });

    jQuery('input[name="ccinfo"]:checked').on('change', function(){
        changeCcInfo();
    });
}

function changeCcInfo()
{
    var selectedPaymentMethod = jQuery('input[name="paymentmethod"]:checked').val();
    if ((selectedPaymentMethod == 'stripe' || selectedPaymentMethod == 'Stripe') &&
        jQuery('input[name="ccinfo"]:checked').val() == 'new') {
        paymentForm.find('#inputCardCvv').parents('div.form-group').show('fast');
        enable_payment_stripe();
    }
    else if(jQuery('#iCheck-newCCInfo').find('input[name="ccinfo"]:checked').val() == 'new')
    {
        //for rccreditcard.php form only
        paymentForm.find('#inputCardCvv').parents('div.form-group').show('fast');
        enable_payment_stripe(true);
    } else {
        var inputCardCVV = $('#inputCardCvv').length == 0 ? '#inputCardCVV2' : '#inputCardCvv';
        paymentForm.find(inputCardCVV).parents('div.form-group').hide('fast');
        paymentForm.off('submit', validateCreditCardInput);
        paymentForm.off('submit', validatePaymentStripe);
    }
}

function scrollToError() {
    jQuery('html, body').animate(
        {
            scrollTop: jQuery('.gateway-errors').offset().top - 50
        },
        500
    );
}


function initPaymentMethod(){
    console.log('depracated: initPaymentMethod not supported');

}

function initAdminCreditCard(){
    console.log('depracated: initAdminCreditCard not supported');

}

function validateCreditCardInput(e)
{
    var newOrExisting = jQuery('input[name="ccinfo"]:checked').val(),
        submitButton = jQuery('#btnSubmit'),
        cardType = null,
        submit = true,
        cardNumber = jQuery('#inputCardNumber');

    ccForm.find('.form-group').removeClass('has-error');
    ccForm.find('.field-error-msg').hide();

    if (newOrExisting === 'new') {
        cardType = jQuery.payment.cardType(ccForm.find('#inputCardNumber').val());
        if (!jQuery.payment.validateCardNumber(ccForm.find('#inputCardNumber').val()) || cardNumber.hasClass('unsupported')) {
            var error = cardNumber.data('message-invalid');
            if (cardNumber.hasClass('unsupported')) {
                error = cardNumber.data('message-unsupported');
            }
            ccForm.find('#inputCardNumber').setInputError(error).showInputError();
            submit = false;
        }
        if (
            !jQuery.payment.validateCardExpiry(
                ccForm.find('#inputCardExpiry').payment('cardExpiryVal')
            )
        ) {
            ccForm.find('#inputCardExpiry').showInputError();
            submit = false;
        }
    }
    if (!jQuery.payment.validateCardCVC(ccForm.find('#inputCardCvv').val(), cardType)) {
        ccForm.find('#inputCardCvv').showInputError();
        submit = false;
    }
    if (!submit) {
        submitButton.prop('disabled', false).removeClass('disabled')
            .find('span').toggleClass('hidden');
        e.preventDefault();
    }
}
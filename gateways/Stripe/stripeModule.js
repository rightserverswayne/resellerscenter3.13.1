const rcStripeModule = {

    complete:{
        cardNumber: false,
        cardExpiry: false,
        cardCvc:    false,
    },
    existingToken: null,
    htmlStorage: {
        inputNumber:    '',
        inputExpiry:    '',
        inputCcv:       '',
    },
    mountInputs(){
        this.rebuildFormInputs();
        this.mountStripeApp();
    },
    rcMountInputs(){
        this.rcRebuildFormInputs();
        this.rcMountStripeApp();
    },
    unmount(){
        this.restoreFormInputs();
    },
    rebuildFormInputs(){
            $('#newCardInfo').addClass('stripe-cards-inputs');
            // $('#inputDescriptionContainer').addClass('col-md-offset-3 offset-md-3');
            //
            $('#cardNumberContainer').removeClass('col-sm-6').addClass('col-md-6');
            var inputNumber = $('#inputCardNumber').parent();
            inputNumber.attr('id','inputCardNumberSection');
            this.htmlStorage.inputNumber = inputNumber.contents();
            inputNumber.text('');
            inputNumber.prepend('<label for="inputCardNumberStripe">' + lang.creditCardInput + '</label>');
            inputNumber.append('<div class="form-control newccinfo cc-number-field " id="inputCardNumberStripe"></div>');
            var inputExpiry = $('#inputCardExpiry').parent();
            inputExpiry.attr('id','inputCardExpirySection');
            this.htmlStorage.inputExpiry = inputExpiry.contents();
            inputExpiry.text('');
            inputExpiry.prepend('<label for="inputCardExpiryStripe">' + lang.creditCardExpiry + '</label>');
            inputExpiry.append('<div class="form-control input-inline" id="inputCardExpiryStripe"></div>');

            var inputCardCvv = $('#inputCardCVV').parent();

            if(inputCardCvv.length == 0)
            {
                inputCardCvv = $('#newCardInfo #cvv-field-container div.form-group.prepend-icon div.input-group #inputCardCVV').parent();
            }
            inputCardCvv.attr('id','inputCardCvvSection');
            inputCardCvv.removeClass('input-group');
            this.htmlStorage.inputCcv = inputCardCvv.contents();
            inputCardCvv.text('');
            inputCardCvv.addClass('col-md-14');
            inputCardCvv.parent().find('label').remove();
            inputCardCvv.prepend('<label for="inputCardCvvStripe">' + lang.creditCardCvc + '</label>');
            inputCardCvv.append('<div class="form-control input-inline" id="inputCardCvvStripe"></div>');
    },
    rcRebuildFormInputs(){
        var inputNumber = $('#inputCardNumber').parent();
        inputNumber.attr('id','inputCardNumberSection');
        this.htmlStorage.inputNumber = inputNumber.contents();
        inputNumber.text('');
        inputNumber.append('<div class="form-control newccinfo cc-number-field" id="inputCardNumber"></div>');
        var inputExpiry = $('#inputCardExpiry').parent();
        inputExpiry.attr('id','inputCardExpirySection');
        this.htmlStorage.inputExpiry = inputExpiry.contents();
        inputExpiry.text('');
        inputExpiry.append('<div class="form-control field input-inline" id="inputCardExpiry"></div>');

        var inputCardCvv = $('#inputCardCvv').parent();
        inputCardCvv.attr('id','inputCardCvvSection');
        this.htmlStorage.inputCcv = inputCardCvv.contents();
        inputCardCvv.text('');
        inputCardCvv.append('<div class="form-control input-inline" id="inputCardCvv"></div>');

        document.getElementById('inputDescriptionContainer').querySelector('div').classList.replace('col-sm-6','col-sm-7');
    },
    restoreFormInputs(){
        var inputNumber = $('#inputCardNumber').parent();
        inputNumber.attr('id','inputCardNumberSection');
        inputNumber.text('');
        inputNumber.append(this.htmlStorage.inputNumber);

        var inputExpiry = $('#inputCardExpiry').parent();
        inputExpiry.attr('id','inputCardExpirySection');
        inputExpiry.text('');
        inputExpiry.append(this.htmlStorage.inputExpiry );

        var inputCardCvv = $('#inputCardCvv').parent();
        inputCardCvv.attr('id','inputCardCvvSection');
        inputCardCvv.text('');
        inputCardCvv.append(this.htmlStorage.inputCcv);

    },
    mountStripeApp(){

        var self = this;

        card.mount('#inputCardNumberStripe');
        card.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardNumberStripe', '');
        });

        cardExpiryElements.mount("#inputCardExpiryStripe");
        cardExpiryElements.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardExpiryStripe','');
        });

        cardCvcElements.mount("#inputCardCvvStripe");
        cardCvcElements.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardCvvStripe', '');
        });
    },

    rcMountStripeApp(){

        var self = this;

        card.mount('#inputCardNumber');
        card.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardNumber', '');
        });

        cardExpiryElements.mount("#inputCardExpiry");
        cardExpiryElements.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardExpiry','<br>');
        });

        cardCvcElements.mount("#inputCardCvv");
        cardCvcElements.addEventListener("change", function(result){
            self.inputEventListener(result,'inputCardCvv', '<br>');
        });
    },

    inputEventListener(result, id, prepend){

        var input = $('#'+id);

        input.parent().find('.field-error-msg').remove();
        if(result.error)
        {
            input.parent().parent().addClass('has-error');
            input.parent().append(prepend+'<span class="field-error-msg" style="display: inline;">'+result.error.message+'</span>');
        }else{
            input.parent().parent().removeClass('has-error');
        }
    },

    submitForm(form, callbackError){

        var self = this;
        stripe.createPaymentMethod("card", card).then(function (e) {
            if (e.error) {
                callbackError(400, e);
                return false;
            }else{
                form.off('submit', validatePaymentStripe);
                form.append('<input type="hidden" name="stripeToken" value="'+e.paymentMethod.id+'">');

                if($('#btnCompleteOrder').length == 0)
                {
                    form.append('<button type="submit" id="btnCompleteOrder"></button>');
                    $('#btnCompleteOrder').trigger('click');
                } else {
                    $('#btnCompleteOrder').trigger('click');
                }
            }
        });
    },


};
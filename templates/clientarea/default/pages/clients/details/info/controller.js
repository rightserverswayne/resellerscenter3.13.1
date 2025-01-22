{literal}
var ResellersCenter_ClientsDetails = 
{
    init: function()
    {
        ResellersCenter_ClientsDetails.checkboxHandler();
        ResellersCenter_ClientsDetails.resetPw();
        ResellersCenter_ClientsDetails.deferredPaymentsLimitHandler();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    submitEditProfile: function()
    {
        var form = $("#RCClientDetails form").serialize();
        form += "&client[id]=" + getParameterByName("cid");
            
        JSONParser.request("updateProfile", form, function(result)
        {
            if(result.validateError)
            {
                $("#MGAlerts").alerts("error", result.validateError);
            }
        });
    },

    checkboxHandler: function()
    {
         $(".checkbox-switch").bootstrapSwitch();
         $(".checkbox-switch[name^='client[customfields]']").each(function(index, element)
         {
             $(element).on("switchChange.bootstrapSwitch", function (event, state) {
                 if(!state)
                 {
                     $(this).after("<input type='hidden' class='form-helper' name='"+$(this).attr("name")+"' value='' />");
                 }
                else
                {
                    $(this).parent().find(".form-helper").remove();
                }
             })
         });
    },
    
    resetPw: function()
    {
        $("#resetpw").on("click", function()
        {
            var clientid = $(this).data("clientid");
            JSONParser.request("resetPassword", {clientid: clientid}, function(){});
        });
    },

    deferredPaymentsLimitHandler: function()
    {
        var customLimitSwitcher = $("input[name='clientSettings[useCustomCreditLineLimit]']");

        if (customLimitSwitcher.length === 0) {
            return;
        }

        customLimitSwitcher.on("switchChange.bootstrapSwitch", function (event, state)
        {
            if (state) {
                $("input[name='client[creditlinelimit]']").show();
            } else {
                $("input[name='client[creditlinelimit]']").hide();
            }
        });

        ResellersCenter_ClientsDetails.deferredPaymentsLimitInitialCheck(customLimitSwitcher[0].checked);
    },

    deferredPaymentsLimitInitialCheck: function(state)
    {
        if (state) {
            $("input[name='client[creditlinelimit]']").show();
        } else {
            $("input[name='client[creditlinelimit]']").hide();
        }
    },
    
    goBack: function()
    {
        window.history.back();
    }
}

ResellersCenter_ClientsDetails.init();
{/literal}
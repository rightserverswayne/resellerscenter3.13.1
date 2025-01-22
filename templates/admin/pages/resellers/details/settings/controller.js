{literal}
var RC_ResellersSettings = 
{
    init: function()
    {
        $("#settings .checkbox-switch").bootstrapSwitch();
        $(".select2").select2();
        
        this.relatedOptionsHandler();
        RC_ResellersSettings.submitSettingForm();
    },
    
    relatedOptionsHandler: function()
    {
        //Reseller Invoice and Invoice Branding
        $("[name='settings[resellerInvoice]']").on("switchChange.bootstrapSwitch", function(event, state)
        {
            if(state)
            {
                $("[name='settings[gateways][]']").attr("disabled", true);
                $("[name='settings[invoiceBranding]']").bootstrapSwitch('state', true);

                $("[name='settings[allowCreditPayment]']").bootstrapSwitch('state', false);
                $("[name='settings[allowCreditPayment]']").bootstrapSwitch("disabled", false);

                //Default off
                $("[name='settings[disableEndClientInvoices]']").bootstrapSwitch('state', false);
                $("[name='settings[disableEndClientInvoices]']").bootstrapSwitch('disabled', false);

                $("[name='settings[configoptions]']").bootstrapSwitch('state', false);
                $("[name='settings[configoptions]']").bootstrapSwitch("disabled", true);
            }
            else
            {
                $("[name='settings[gateways][]']").attr("disabled", false);

                $("[name='settings[allowCreditPayment]']").bootstrapSwitch('state', false);
                $("[name='settings[allowCreditPayment]']").bootstrapSwitch("disabled", true);

                $("[name='settings[disableEndClientInvoices]']").bootstrapSwitch('state', false);
                $("[name='settings[disableEndClientInvoices]']").bootstrapSwitch('disabled', true);

                $("[name='settings[configoptions]']").bootstrapSwitch('state', false);
                $("[name='settings[configoptions]']").bootstrapSwitch("disabled", false);
            }
        });
        
        $("[name='settings[invoiceBranding]']").on("switchChange.bootstrapSwitch", function(event, state)
        {
            if(!state)
            {
                $("[name='settings[resellerInvoice]']").bootstrapSwitch('state', false);
            }
        });
    },
    
    submitSettingForm: function()
    {
        $(".saveConfigBtn").on("click", function(e){
            e.preventDefault();
            
            //We cannot send "/" as array key
            $("#resellerConfigurationForm [name^='settings[emailTemplates]']").each(function(index, element)
            {
                var name = $(element).attr("name");
                if(name.indexOf("/")) 
                {
                    name = name.replace("/", "-slash-");
                    $(element).attr("name", name);
                }
            });
            
            var form = $("#resellerConfigurationForm").serialize();
            JSONParser.request("saveConfiguration|configuration", form, function(){});
        });
    }
}
RC_ResellersSettings.init();
{/literal}
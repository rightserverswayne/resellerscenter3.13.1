{literal}
var RC_Configuration = 
{    
    tokenDesc: null,
    init: function()
    {
        $("#configurationForm .select2").select2();
        $("#configurationForm .checkbox-switch").bootstrapSwitch();
        
        this.relatedOptionsHandler();
    },
    
    relatedOptionsHandler: function()
    {
        //Reseller Invoice and Invoice Branding
        $("[name='settings[resellerInvoice]']").on("switchChange.bootstrapSwitch", function(event, state)
        {
            if (state) {
                $("[name='settings[gateways][]']").attr("disabled", true);
                $("[name='settings[invoiceBranding]']").bootstrapSwitch('state', true);
            } else {
                $("[name='settings[gateways][]']").attr("disabled", false);
            }
        });
        
        $("[name='settings[invoiceBranding]']").on("switchChange.bootstrapSwitch", function(event, state)
        {
            if (!state) {
                $("[name='settings[resellerInvoice]']").bootstrapSwitch('state', false);
            }
        });
    },
    
    submitConfigurationForm: function()
    {
        if(!this.validateToken())
        {
            return;
        }
        //We cannot send "/" as array key
        $("#configurationForm [name^='settings[emailTemplates]']").each(function(index, element)
        {
            var name = $(element).attr("name");
            if (name.indexOf("/")) {
                name = name.replace("/", "-slash-");
                $(element).attr("name", name);
            }
        });

        var form = $("#configurationForm").serialize();
        JSONParser.request("saveConfiguration|configuration", form, function(){});
    },
    validateToken: function()
    {
        var token = $('#apitoken').val();
        
        if (!token || token.length >= 32) {
            $('#apitoken_info').removeClass('error-validate');
            $('#apitoken_info').html(RC_Configuration.tokenDesc);
            return true;
        }
        RC_Configuration.tokenDesc = $('#apitoken_info').html();
        $('#apitoken_info').html("{/literal}{$MGLANG->T('configuration','general','token','error')}{literal}");
        $('#apitoken_info').addClass('error-validate');
        
        $("html, body").animate({
            scrollTop: 0
        }, 500);
        
        return false;
    }
}
RC_Configuration.init();
{/literal}
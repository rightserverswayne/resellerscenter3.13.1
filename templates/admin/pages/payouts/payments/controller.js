{literal}
var RC_Payouts_PayPal = 
{
    paymentType: null,
    
    init: function()
    {
        $(".checkbox-switch").bootstrapSwitch();

        RC_Payouts_PayPal.paypalResellerSelectHandler();
        RC_Payouts_PayPal.creditsResellerSelectHandler();
        RC_Payouts_PayPal.recalculateSummary();
        RC_Payouts_PayPal.makeMassPayPalPaymentHandler();
    },
    
    makeMassPayPalPaymentHandler: function()
    {
        $(".makeMassPayment").on("click", function()
        {
            RC_Payouts_PayPal.paymentType = $(this).data("type");
            $("#confirmPayout").modal("show");
        });
    },
    
    submitMassPayment: function()
    {
        if(RC_Payouts_PayPal.paymentType == 'paypal')
        {
            var resellersids = $("#paypal [name='resellers']").val();
            JSONParser.request('makeMassPayPalPayment', {resellersids: resellersids}, function()
            {
                $("#paypal [name='resellers']").select2("val", "");
                RC_Payouts_List.table.draw();
                $("#confirmPayout").modal("hide");
            });
        }
        else
        {
            var resellersids = $("#credits [name='resellers']").val();
            JSONParser.request('makeMassCreditPayment', {resellersids: resellersids}, function()
            {
                $("#credits [name='resellers']").select2("val", "");
                RC_Payouts_List.table.draw();
                $("#confirmPayout").modal("hide");
            });
        }
        
    },
    
    submitConfigurationForm: function()
    {
        var form = $("#paypalConfiguration").serialize();
        JSONParser.request("saveConfigration", form, function(){});
    },
    
    paypalResellerSelectHandler: function()
    {
        $("#paypal [name='resellers']").select2({
            ajax: 
            {
                url: 'addonmodules.php?module=ResellersCenter&mg-page=payouts&mg-action=getResellers&json=1',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var groups = {};
                    $.each(result.data, function(index, value)
                    {
                        if(typeof groups[value.groupname] == 'undefined') {
                            groups[value.groupname] = {id: 'group', text: value.groupname, children: []};
                        }
                        
                        var text = '#' + value.id + ' ' + value.name;
                        groups[value.groupname].children.push({id: value.id, text: text});
                    });
                    
                    result = [];
                    $.each(groups, function(index, value) {
                        result.push(value);
                    });

                    return {results: result};
                },
                delay: 250
            },
            placeholder: "{/literal}{html_entity_decode($MGLANG->T('paypal','resellers','placeholder'))}{literal}",
        });
    },
    
    creditsResellerSelectHandler: function()
    {
        $("#credits [name='resellers']").select2({
            ajax: 
            {
                url: 'addonmodules.php?module=ResellersCenter&mg-page=payouts&mg-action=getResellers&json=1',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var groups = {};
                    $.each(result.data, function(index, value)
                    {
                        if(typeof groups[value.groupname] == 'undefined') {
                            groups[value.groupname] = {id: 'group', text: value.groupname, children: []};
                        }
                        
                        var text = '#' + value.id + ' ' + value.name;
                        groups[value.groupname].children.push({id: value.id, text: text});
                    });
                    
                    result = [];
                    $.each(groups, function(index, value) {
                        result.push(value);
                    });

                    return {results: result};
                },
                data: 
                {
                    type: 'all'
                },
                delay: 250
            },

            placeholder: "{/literal}{html_entity_decode($MGLANG->T('paypal','resellers','placeholder'))}{literal}",
        });
    },
    
    recalculateSummary: function()
    {
        $("#paypal [name='resellers']").on("change", function()
        {
            var resellersids = $(this).val();
            JSONParser.request("calculateTotalResellersProfit", {resellersids: resellersids}, function(result){
                $("#paypal .totalResellersProfit").html(result);
            });
        });
        
        $("#credits [name='resellers']").on("change", function()
        {
            var resellersids = $(this).val();
            JSONParser.request("calculateTotalResellersProfit", {resellersids: resellersids}, function(result){
                $("#credits .totalResellersProfit").html(result);
            });
        });
    },
}
RC_Payouts_PayPal.init();
{/literal}
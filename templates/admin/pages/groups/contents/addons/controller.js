{literal}
var RC_SettingsAddons = {
    
    activeContentid: null,
    table:   null,
    
    init: function()
    {
        RC_SettingsAddons.loadAddons();
    },
    
    refreshHandlers: function()
    {
        RC_SettingsAddons.openConfigModal();
        RC_SettingsAddons.openPricingModal();
        RC_SettingsAddons.openDeleteModal();
        
        RC_SettingsAddons.autofillBillingCycles();
        RC_SettingsAddons.autofillButtonHanlder();
    },
    
    showSearchInput: function()
    {
        if($(".addonListSearch").is(":visible")) {
            $(".addonListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".addonListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    /**
     *  Add Addon Form
     */
    openAddForm: function()
    {
        $("#addAddonSelect option").each(function(index, option){
            $(option).attr("disabled", false);
        });
        
        new Promise(function(resolve) {
            JSONParser.request('getGroupContents|Contents', {groupid: RC_ConfigurationSettings.groupid, type: 'addon'}, function(result){
                $("#addAddonSelect option").each(function(index, option){
                    $.each(result, function(i, content){
                        if($(option).val() == content.relid)
                        {
                            $(option).attr("disabled", true);
                        }
                    });
                });
                
                resolve();
            });
        }).then(function(){
            RC_ConfigurationSettings.initSelect2Fields();
            $("#addAddonSelect").select2("val", "");
            $("#addAddonSelect").val([]).trigger('change');
            $("#addonAddModal").modal("show");
        });
    },
    
    submitAddForm: function()
    {
        var form = $("#addAddonForm").serialize();
        form += '&groupid='+RC_ConfigurationSettings.groupid;
        
        JSONParser.request('addContentToGroup|Contents', form, function(result){
            RC_SettingsAddons.table.draw();
            
            $("#addonAddModal").modal("hide");
        });
    },
    
    /**
     *  Pricing Form
     */
    openPricingModal: function()
    {
        $(".openAddonPricing").unbind("click");
        $(".openAddonPricing").on("click", function()
        {
            //Reset errors
            $("#addonPricingForm .has-error").removeClass("has-error");
            
            RC_SettingsAddons.activeContentid = $(this).data("contentid");
            $("#addonPricingMessages").alerts("clear");
            $("#addonPricingForm [name^='pricing']").val("");
            $("#addonPricingForm [name^='pricing']").attr("disabled", true);
            $("#addonPricingForm .billingcycles .panel").hide();
            $('[data-currencyid^=currid]').parent().addClass('hidden');

            //Enable available pricing
            JSONParser.request('getContentAvailablePricing|Contents', {contentid: RC_SettingsAddons.activeContentid}, function(data){
                $.each(data, function(currencyid, cycles){
                    $(`[data-currencyid=currid${currencyid}]`).parent().removeClass('hidden');
                    $.each(cycles, function(index, cycle){
                        $("#addonPricingForm [data-currencyid='"+currencyid+"'] [data-billingcycle='"+cycle+"']").show();

                        $("#addonPricingForm [name='pricing["+currencyid+"][adminprice]["+cycle+"]']").attr("disabled", false);
                        $("#addonPricingForm [name='pricing["+currencyid+"][highestprice]["+cycle+"]']").attr("disabled", false);
                        $("#addonPricingForm [name='pricing["+currencyid+"][lowestprice]["+cycle+"]']").attr("disabled", false);
                    });
                });
            });

            JSONParser.request('getContentPricing|Contents', {contentid: RC_SettingsAddons.activeContentid}, function(data){
                if(data == '') {
                    $("#addonPricingForm [name^='pricing']").val('');
                }
                
                $.each(data, function(index, values){
                    $.each(values.pricing, function(cycle, price){
                        if(price == -1 || price == '') {
                            $("#addonPricingForm [name='pricing["+values.currency+"]["+values.type+"]["+cycle+"]']").val();
                        }
                        else {
                            $("#addonPricingForm [name='pricing["+values.currency+"]["+values.type+"]["+cycle+"]']").val(price);
                        }
                    });
                });
                
                $("#addonPricingModal").modal("show");
            });
        });
    },
        
    submitPricingForm: function()
    {
        //Reset errors
        $("#addonPricingForm .has-error").removeClass("has-error");
        
        var form =$("#addonPricingForm").serialize();
        form += '&contentid=' + RC_SettingsAddons.activeContentid;
        
        JSONParser.request('saveContentPricing|Contents', form, function(result)
        {
            if(result.errors)
            {
                $.each(result.errors, function(index, error)
                {
                    var hrefTab = $("#addonPricingModal [data-currencyid='"+error.currency+"']").attr("id");
                    $("#addonPricingModal .nav").find("[href='#"+hrefTab+"']").parent().addClass("has-error");
                    
                    $("#addonPricingModal [name='pricing["+error.currency+"]["+error.type+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                });
            }
            else
            {
                $("#addonPricingModal").modal("hide");
            }
        });
    },
    
    refreshCurreciesValues: function()
    {
        JSONParser.request('getCurrenciesRates|Contents', {null: null}, function(data){
            var base = $(".defaultCurrency").data("currencyid");
            
            $.each(data, function(index, currency)
            {
                $.each(RC_SettingsProducts.billingcycles, function(index, cycle)
                {
                    //Billing Cycles
                    $("#addonPricingForm [name='pricing["+currency.id+"][adminprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#addonPricingForm [name='pricing["+currency.id+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#addonPricingForm [name='pricing["+currency.id+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * currency.rate * 100) /100
                    );
            
                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#addonPricingForm [name='pricing["+currency.id+"][adminprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#addonPricingForm [name='pricing["+currency.id+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#addonPricingForm [name='pricing["+currency.id+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#addonPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * currency.rate * 100) /100
                    );
                });
            });
            
            $("#addonPricingMessages").alerts("clear");
            $("#addonPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autorefreshcurrency','success')}{literal}");
        });
    },
    
    autofillCurrencies: function()
    {
        var base = $("#addonPricingForm .tab-pane.active").data("currencyid");
        $("#addonPricingForm .tab-pane").each(function(index, element)
        {
            if($(element).is(".active")) {
                return;
            }
        
            //Reset 
            $("#addonPricingForm .has-error").removeClass("has-error");
            
            //Validate
            var types = ['adminprice', 'highestprice', 'lowestprice'];
            $.each(RC_SettingsProducts.billingcycles, function(index, cycle)
            {
                $.each(types, function(index, type)
                {
                    var value = $("#addonPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").val();
                    if(! $.isNumeric(value) && value != "")
                    {
                        $("#addonPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                        $("#addonPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");                        
                    }
                });
            });
            
            if($("#addonPricingForm").find(".has-error").length > 0)
            {
                return;
            }
            
            var currency = $(element).data("currencyid")
            $.each(RC_SettingsProducts.billingcycles, function(index, cycle)
            {
                //Billingcycles
                $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val()
                );
                $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val()
                );
                $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val()
                );
        
                //Setup Fees
                cycle = cycle.charAt(0) + "setupfee";
                $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val()
                );
                $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val()
                );
                $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+cycle+"]']").val(
                    $("#addonPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val()
                );
            });
        });

        $("#addonPricingMessages").alerts("clear");
        $("#addonPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcurrencies','success')}{literal}");
    },
    
    autofillBillingCycles: function()
    {
        $(".fillBillingCyclesBtn").on("click", function(event)
        {
            var billing = $(this).data("billing");
            var currency = $(this).data("currencyid");

            var feebilling = billing.charAt(0) + "setupfee";
            var values = {
                admin:      $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(),
                highest:    $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(),
                lowest:     $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(),

                adminfee:   $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+feebilling+"]']").val(),
                highestfee: $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+feebilling+"]']").val(),
                lowestfee:  $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+feebilling+"]']").val()
            };
            
            //Validate
            $.each(values, function(type, value)
            {
                var cycle = billing;
                if(type.indexOf("fee") >= 0)
                {
                    cycle = feebilling;
                }

                type = type.replace("fee", "") + "price";
                if(!$.isNumeric(value) && value != "")
                {
                    $("#addonPricingForm [name='pricing["+currency+"]["+type+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                    event.stopPropagation();
                }
           });
           
           if($("#addonPricingForm .has-error").length)
           {
               return;
           }

            //Get One month price
            var oneMonth;
            var source = RC_SettingsProducts.billingcycles.indexOf(billing);
            if(source == 0) {
                oneMonth = values;
            }
            else if(source == 1) {
                oneMonth = {admin: values.admin / 3,  highest: values.highest /3, lowest: values.lowest /3, 
                            adminfee: values.adminfee / 3,  highestfee: values.highestfee /3, lowestfee: values.lowestfee /3};
            }
            else {
                var multiple = (Math.floor(source*source/2)) * 3;
                oneMonth = {admin: values.admin / multiple, highest: values.highest / multiple, lowest: values.lowest / multiple,
                            adminfee: values.adminfee / multiple, highestfee: values.highestfee / multiple, lowestfee: values.lowestfee / multiple};
            }                      

            $.each(RC_SettingsProducts.billingcycles, function(index, billing){
                if(index == 0) {
                    multiple = 1;
                }
                else if(index == 1) {
                    multiple = 3;
                }
                else {
                    var multiple = (Math.floor(index*index/2)) * 3;
                }

                $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(oneMonth.admin * multiple);
                $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(oneMonth.highest * multiple);
                $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(oneMonth.lowest * multiple);

                billing = billing.charAt(0) + "setupfee";
                if(! $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").is(":disabled")) 
                {
                    $("#addonPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(oneMonth.adminfee * multiple);
                    $("#addonPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(oneMonth.highestfee * multiple);
                    $("#addonPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(oneMonth.lowestfee * multiple);
                }
            });

            $("#addonPricingMessages").alerts("clear");
            $("#addonPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcycles','success')}{literal}");
        });
    },
    
    autofillButtonHanlder: function()
    {
        $(".billingcycles .accordionSwitch").unbind("click");
        $(".billingcycles .accordionSwitch").on("click", function()
        {
            $(this).parents(".billingcycles").find(".rc-actions").hide();

            if($(this).hasClass("collapsed")) 
            {
                $(this).parent().find(".rc-actions").show();
            }
        });
    },
    
    /**
     * Edit Form
     */
    openConfigModal: function()
    {
        $(".openAddonConfig").unbind("click");
        $(".openAddonConfig").on("click", function(){
            RC_SettingsAddons.activeContentid = $(this).data("contentid");
            RC_SettingsAddons.showConfigForCountingTypeHandler();

            JSONParser.request('getContentConfig|Contents',{contentid: RC_SettingsAddons.activeContentid},function(result) 
            {
                $(".additional-config").empty();
                if(result.html) {
                    $(".additional-config").append("<hr />" + result.html);
                }
                
                 //Display help 
                $(".counting-help").hide();
                $(".countingType_" + result.name).show();
                $("[name='counting_type']").val(result.name);

                //Disable Submit on Enter
                $("#addonConfigForm input").on('keyup keypress', function(e) 
                {
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) 
                    { 
                        e.preventDefault();
                        return false;
                    }
                });
                
                $("#addonConfigModal").modal("show");
            });
        });
    },
    
    submitConfigForm: function()
    {
        var form = $("#addonConfigForm").serializeArray();
        form = RC_Helper.parseToAssoc(form);
        
        JSONParser.request
        (
            'saveContentConfig|Contents',
            {data: form, contentid: RC_SettingsAddons.activeContentid, groupid: RC_ConfigurationSettings.groupid}, 
            function(result)
            {
                if(result.errors)
                {
                    $.each(result.errors, function(index, errors)
                    {
                       $("#addonConfigForm").find("[name*='"+errors.field+"']").closest(".control-group").addClass("has-error");
                    });
                }
                else
                {
                    RC_SettingsAddons.table.draw();
                    $("#addonConfigModal").modal("hide");
                }
            }
        );
    },
    
    showConfigForCountingTypeHandler: function()
    {
        $("#addonConfigForm [name='counting_type']").unbind("change");
        $("#addonConfigForm [name='counting_type']").on("change", function()
        {
            var type = $(this).val();
            JSONParser.request('getContentConfig|Contents', {contentid: RC_SettingsAddons.activeContentid, counting_type: type}, function(result)
            {
                $(".additional-config").empty();
                if(result.html) 
                {
                    $(".additional-config").append("<hr />" + result.html);
                }
                
                //Disable Submit on Enter
                $("#addonConfigForm input").on('keyup keypress', function(e) 
                {
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) 
                    { 
                        e.preventDefault();
                        return false;
                    }
                });
            });
            
            //Display help 
            $(".counting-help").hide();
            $(".countingType_" + type).show();
        });
    },
    
    /**
     * Delete Form
     */
    openDeleteModal: function()
    {
        $(".openAddonDelete").unbind("click");
        $(".openAddonDelete").on("click", function(){
            RC_SettingsAddons.activeContentid = $(this).data("contentid");
            
            $("#addonDeleteModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request('deleteContentFromGroup|Contents',
            {contentid: RC_SettingsAddons.activeContentid}, 
            function(result){
                RC_SettingsAddons.table.draw();
                $("#addonDeleteModal").modal("hide");
        });
    },
    
   
    loadAddons: function()
    {
        if($.fn.DataTable.isDataTable("#addonsTable"))
        {
            RC_SettingsAddons.table.draw();
        }
        else
        {
            RC_SettingsAddons.table = $("#addonsTable").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=contents&mg-action=getContentTableData&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_SettingsAddons.refreshHandlers();
                },
                fnServerParams: function(data) {
                    data.push({ name: "groupid", value: RC_ConfigurationSettings.groupid});
                    data.push({ name: "type", value: 'addon'});
                },
                columns: [
                    { data: "addon_name",     orderable: true, sortable: false, targets: 0 },
                    { data: "payment_type",   orderable: true, sortable: false, targets: 0 },
                    { data: "counting_type",  orderable: true, sortable: false, targets: 0 },
                    { data: "profit_percent", orderable: true, sortable: false, targets: 0 },
                    { data: "profit_rate",    orderable: true, sortable: false, targets: 0 },
                    { data: "actions",        orderable: false, sortable: false, targets: 0 },
                  ],
                bPaginate: true,
                sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-4 text-center"i><"col-sm-4"p>>>',
                oLanguage: {
                    sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                    sInfo : "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                    sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                    sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                    sProcessing: "",
                sLengthMenu: "{/literal}{$MGLANG->absoluteT('datatable','lengthMenu')}{literal}",
                    oPaginate: {
                        sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                        sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                    }
                }
            });

            RC_SettingsAddons.customDataTableSearch();
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#addonsListFilter").keyup(function()
        {
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_SettingsAddons.table.search(filter).draw();
            }, 500);
        });
    },
}
{/literal}
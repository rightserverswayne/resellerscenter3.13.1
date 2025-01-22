{literal}
var RC_SettingsProducts = {
       
    activeContentid: null,
    productsTable: null,
   
    //                  1,          3,            6,           12,           24,           36          months
    billingcycles: ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'],
    
    init: function()
    {
        //Init hanlders
        RC_SettingsProducts.refreshHandlers();
        RC_SettingsProducts.loadProducts();       
    },
    
    refreshHandlers: function()
    {
        RC_SettingsProducts.openProductPricing();
        RC_SettingsProducts.openProductConfig();
        RC_SettingsProducts.openProductDelete();
        
        RC_SettingsProducts.autofillBillingCycles();
        RC_SettingsProducts.autofillButtonHanlder();               
    },
    
    showSearchInput: function()
    {
        if($(".productListSearch").is(":visible")) {
            $(".productListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".productListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    /**
     *  Add Product Form
     */
    openAddProductForm: function()
    {
        $("#addProductSelect option").each(function(index, option){
            $(option).attr("disabled", false);
        });

        new Promise(function(resolve) {
            JSONParser.request('getGroupContents|Contents', {groupid: RC_ConfigurationSettings.groupid, type: 'product'}, function(result){
                $("#addProductSelect option").each(function(index, option){
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
            $("#addProductSelect").select2("val", "");
            $("#addProductSelect").val([]).trigger('change');

            $("#productAddModal").modal("show");
        });
    },
    
    submitAddProductForm: function()
    {
        var form = $("#addProductForm").serialize();
        form += '&groupid=' + RC_ConfigurationSettings.groupid;
        
        JSONParser.request('addContentToGroup|Contents', form, function(result){
            RC_SettingsProducts.productsTable.draw();
            
            $("#productAddModal").modal("hide");
        });
    },
    
    /**
     * Pricing Form
     */
    openProductPricing: function()
    {
        $(".openProductPricing").unbind("click");
        $(".openProductPricing").on("click", function()
        {
            //Reset errors
            $("#productPricingForm .has-error").removeClass("has-error");
            
            RC_SettingsProducts.activeContentid = $(this).data("contentid");
            $("#productPricingMessages").alerts("clear");
            $("#productPricingForm [name^='pricing']").val("");
            $("#productPricingForm [name^='pricing']").attr("disabled", true);
            $("#productPricingForm .billingcycles .panel").hide();
            $('[data-currencyid^=currid]').parent().addClass('hidden');
            //Enable available pricing
            JSONParser.request('getContentAvailablePricing|Contents', {contentid: RC_SettingsProducts.activeContentid}, function(data){
                $.each(data, function(currencyid, cycles){
                    $(`[data-currencyid=currid${currencyid}]`).parent().removeClass('hidden');
                    $.each(cycles, function(index, cycle){
                        $("#productPricingForm [data-currencyid='"+currencyid+"'] [data-billingcycle='"+cycle+"']").show();

                        $("#productPricingForm [name='pricing["+currencyid+"][adminprice]["+cycle+"]']").attr("disabled", false);
                        $("#productPricingForm [name='pricing["+currencyid+"][highestprice]["+cycle+"]']").attr("disabled", false);
                        $("#productPricingForm [name='pricing["+currencyid+"][lowestprice]["+cycle+"]']").attr("disabled", false);
                    });
                });
            });

            JSONParser.request('getContentPricing|Contents', {contentid: RC_SettingsProducts.activeContentid}, function(data){
                if(data == '') {
                    $("#productPricingForm [name^='pricing']").val('');
                }
                
                $.each(data, function(index, values){
                    $.each(values.pricing, function(cycle, price){
                        if(price == -1 || price == '') {
                            $("#productPricingForm [name='pricing["+values.currency+"]["+values.type+"]["+cycle+"]']").val();
                        }
                        else {
                            $("#productPricingForm [name='pricing["+values.currency+"]["+values.type+"]["+cycle+"]']").val(price);
                        }
                    });
                });
                
                $("#productPricingModal").modal("show");
            });
        });
    },
    
    submitProductPricingForm: function()
    {
        //Reset errors
        $("#productPricingForm .has-error").removeClass("has-error");
        
        var form = $("#productPricingForm").serialize();
        form += '&contentid=' + RC_SettingsProducts.activeContentid;
        
        JSONParser.request('saveContentPricing|Contents', form, function(result)
        {
            if(result.errors)
            {
                $.each(result.errors, function(index, error)
                {
                    var hrefTab = $("#productPricingForm [data-currencyid='"+error.currency+"']").attr("id");
                    $("#productPricingForm .nav").find("[href='#"+hrefTab+"']").parent().addClass("has-error");
                    
                    var panelid = $("#productPricingForm [name='pricing["+error.currency+"]["+error.type+"]["+error.billingcycle+"]']").closest(".panel-collapse").attr('aria-labelledby');
                    $("#"+panelid).addClass("has-error");
                    
                    $("#productPricingForm [name='pricing["+error.currency+"]["+error.type+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                });
            }
            else
            {
                $("#productPricingModal").modal("hide");
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
                    //Billingcycles
                    $("#productPricingForm [name='pricing["+currency.id+"][adminprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#productPricingForm [name='pricing["+currency.id+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#productPricingForm [name='pricing["+currency.id+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * currency.rate * 100) /100
                    );
            
                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#productPricingForm [name='pricing["+currency.id+"][adminprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#productPricingForm [name='pricing["+currency.id+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                    $("#productPricingForm [name='pricing["+currency.id+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * currency.rate * 100) /100
                    );
                });
            });
            
            $("#productPricingMessages").alerts("clear");
            $("#productPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autorefreshcurrency','success')}{literal}");
        });
    },
    
    autofillCurrencies: function()
    {
        var base = $("#productPricingForm .tab-pane.active").data("currencyid");
        $("#productPricingForm .tab-pane").each(function(index, element)
        {
            if($(element).is(".active")) {
                return;
            }
            
            //Reset 
            $("#productPricingForm .has-error").removeClass("has-error");
            
            //Validate
            var types = ['adminprice', 'highestprice', 'lowestprice'];
            $.each(RC_SettingsProducts.billingcycles, function(index, cycle)
            {
                $.each(types, function(index, type)
                {
                    var value = $("#productPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").val();
                    if(! $.isNumeric(value) && value != "")
                    {
                        $("#productPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                        $("#productPricingForm [name='pricing["+base+"]["+type+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");                        
                    }
                });
            });
            
            if($("#productPricingForm").find(".has-error").length > 0)
            {
                return;
            }
            
            var currency = $(element).data("currencyid");
            JSONParser.request('getCurrenciesRates|Contents', {currencyid: currency}, function(data)
            {
                $.each(RC_SettingsProducts.billingcycles, function(index, cycle)
                {                
                    //Billingcycles
                    $("#productPricingForm [name='pricing["+currency+"][adminprice]["+cycle+"]']").val(
                       Math.round($("#productPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                    $("#productPricingForm [name='pricing["+currency+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                    $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );

                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#productPricingForm [name='pricing["+currency+"][adminprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][adminprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                    $("#productPricingForm [name='pricing["+currency+"][highestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][highestprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                    $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+cycle+"]']").val(
                        Math.round($("#productPricingForm [name='pricing["+base+"][lowestprice]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                });
            });
        });

        $("#productPricingMessages").alerts("clear");
        $("#productPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcurrencies','success')}{literal}");
    },
    
    autofillBillingCycles: function()
    {
        $(".fillBillingCyclesBtn").on("click", function(event)
        {
            //clear
            $("#productPricingForm .has-error").removeClass("has-error");
            
            var billing = $(this).data("billing");
            var currency = $(this).data("currencyid");

            var feebilling = billing.charAt(0) + "setupfee";
            var values = {
                admin:      $("#productPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(),
                highest:    $("#productPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(),
                lowest:     $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(),

                adminfee:   $("#productPricingForm [name='pricing["+currency+"][adminprice]["+feebilling+"]']").val(),
                highestfee: $("#productPricingForm [name='pricing["+currency+"][highestprice]["+feebilling+"]']").val(),
                lowestfee:  $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+feebilling+"]']").val()
            };
            
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
                    $("#productPricingForm [name='pricing["+currency+"]["+type+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                    event.stopPropagation();
                }
           });
           
           if($("#productPricingForm .has-error").length)
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

                $("#productPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(oneMonth.admin * multiple);
                $("#productPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(oneMonth.highest * multiple);
                $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(oneMonth.lowest * multiple);

                billing = billing.charAt(0) + "setupfee";
                if(! $("#productPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").is(":disabled")) 
                {
                    $("#productPricingForm [name='pricing["+currency+"][adminprice]["+billing+"]']").val(oneMonth.adminfee * multiple);
                    $("#productPricingForm [name='pricing["+currency+"][highestprice]["+billing+"]']").val(oneMonth.highestfee * multiple);
                    $("#productPricingForm [name='pricing["+currency+"][lowestprice]["+billing+"]']").val(oneMonth.lowestfee * multiple);
                }
            });

            $("#productPricingMessages").alerts("clear");
            $("#productPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcycles','success')}{literal}");
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
    openProductConfig: function()
    {
        $(".openProductConfig").unbind("click");
        $(".openProductConfig").on("click", function()
        {
            RC_SettingsProducts.activeContentid = $(this).data("contentid");
            RC_SettingsProducts.showConfigForCountingTypeHandler();
            
            JSONParser.request('getContentConfig|Contents', {contentid: RC_SettingsProducts.activeContentid}, function(result)
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
                $("#productConfigForm input").on('keyup keypress', function(e) 
                {
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) 
                    { 
                        e.preventDefault();
                        return false;
                    }
                });
                
                $("#productConfigModal").modal("show");
            });
        });
    },
    
    submitProductConfigForm: function()
    {
        var form = $("#productConfigForm").serializeArray();
        form = RC_Helper.parseToAssoc(form);

        JSONParser.request('saveContentConfig|Contents',
            {data: form, contentid: RC_SettingsProducts.activeContentid, groupid: RC_ConfigurationSettings.groupid}, 
            function(result)
            {
                if(result.errors)
                {
                    $.each(result.errors, function(index, errors)
                    {
                       $("#productConfigForm").find("[name*='"+errors.field+"']").closest(".control-group").addClass("has-error");
                    });
                }
                else
                {
                    RC_SettingsProducts.productsTable.draw();
                    $("#productConfigModal").modal("hide");    
                }
            }
        );
    },
    
    showConfigForCountingTypeHandler: function()
    {
        $("#productConfigForm [name='counting_type']").unbind("change");
        $("#productConfigForm [name='counting_type']").on("change", function()
        {
            var type = $(this).val();
            JSONParser.request('getContentConfig|Contents', {contentid: RC_SettingsProducts.activeContentid, counting_type: type}, function(result)
            {
                $(".additional-config").empty();
                if(result.html) 
                {
                    $(".additional-config").append("<hr />" + result.html);
                }
                
                //Disable Submit on Enter
                $("#productConfigForm input").on('keyup keypress', function(e) 
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
    openProductDelete: function()
    {
        $(".openProductDelete").unbind("click");
        $(".openProductDelete").on("click", function(){
            RC_SettingsProducts.activeContentid = $(this).data("contentid");
            
            $("#productDeleteModal").modal("show");
        });
    },
    
    submitProductDeleteForm: function()
    {
        JSONParser.request('deleteContentFromGroup|Contents',
            {contentid: RC_SettingsProducts.activeContentid}, 
            function(result){
                RC_SettingsProducts.productsTable.draw();
                $("#productDeleteModal").modal("hide");
        });
    },
    
    /**
     * DATA TABLE
     */
    loadProducts: function()
    {
        if($.fn.DataTable.isDataTable("#productsTable"))
        {
            RC_SettingsProducts.productsTable.draw();
        }
        else
        {
            RC_SettingsProducts.productsTable = $("#productsTable").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=contents&mg-action=getContentTableData&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_SettingsProducts.refreshHandlers();
                },
                fnServerParams: function(data) {
                    data.push({ name: "groupid", value: RC_ConfigurationSettings.groupid});
                    data.push({ name: "type", value: 'product'});
                },
                columns: [
                    { data: "product_name",   orderable: true, sortable: false, targets: 0 },
                    { data: "product_group",   orderable: true, sortable: false, targets: 0 },
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

            RC_SettingsProducts.customDataTableSearch();
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#productsListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_SettingsProducts.productsTable.search(filter).draw();
            }, 500);
        });
    },
}
{/literal}
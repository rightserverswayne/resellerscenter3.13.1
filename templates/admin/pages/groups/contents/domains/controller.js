{literal}
RC_SettingsDomains = {
    
    activeContents: null,
    table:   null,
    
    billingcycles: ["msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially"],
    types: ['domainregister', 'domaintransfer', 'domainrenew'],
    
    init: function()
    {
        RC_SettingsDomains.loadDomains();
    },
    
    refreshHandlers: function()
    {
        RC_SettingsDomains.openConfigModal();
        RC_SettingsDomains.openPricingModal();
        RC_SettingsDomains.openDeleteModal();
        
        RC_SettingsDomains.showChildRowsHandler();
        RC_SettingsDomains.autofillBillingCycles();
    },
    
    /**
     *  Add Addon Form
     */
    openAddForm: function()
    {
        $("#domainTldSelect option").each(function(index, option){
            $(option).attr("disabled", false);
        });
        
        new Promise(function(resolve) {
            JSONParser.request('getGroupContents|Contents', {groupid: RC_ConfigurationSettings.groupid, type: 'domainregister'}, function(result)
            {
                $("#domainTldSelect option").each(function(index, option){
                    $.each(result, function(i, content)
                    {
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

            $("#domainTypeSelect").select2();
            $("#domainTldSelect").select2("val", "");
            $("#domainTldSelect").val([]).trigger('change');
            
            $("#domainTypeSelect").trigger("change");
            $("#domainAddModal").modal("show");
        });
    },
    
    submitAddForm: function()
    {
        var form = $("#domainAddForm").serialize();
        form += '&groupid='+RC_ConfigurationSettings.groupid;

        var disableAlert = true;
        var counter = 0;
        $.each(RC_SettingsDomains.types, function(index, type)
        {
            if(counter == 2) {
                disableAlert = false;
            }
            
            form += "&type="+type;
            JSONParser.request('addContentToGroup|Contents', form, function(result){
                RC_SettingsDomains.table.draw();

                $("#domainAddModal").modal("hide");
            }, "#MGLoader", false, disableAlert);
            
            counter++;
        });
 

    },

    /**
     *  Massive Add TLDs Form
     */
    openMassiveAddForm: function()
    {
        $("#domainMassiveAddModal").modal("show");
    },

    submitMassiveAddForm: function()
    {
        var form = $("#domainMassiveAddForm").serialize();
        form += '&groupid='+RC_ConfigurationSettings.groupid;

        JSONParser.request('addMassiveTldToGroup|Contents', form, function (result) {
            RC_SettingsDomains.table.draw();

            $("#domainMassiveAddModal").modal("hide");
        }, "#MGLoader", false, false);
    },

    /**
     * Pricing Form
     */
    openPricingModal: function()
    {
        $(".openDomainPricing").unbind("click");
        $(".openDomainPricing").on("click", function(){
            RC_SettingsDomains.getContentIds(this);
            
            $("#domainPricingMessages").alerts("clear");
            $("#domainPricingForm input").attr("disabled", true);
            $("#domainPricingForm .billingcycles .panel").hide();
            $("#domainPricingForm input").val('');
            $("#domainPricingForm .has-error").removeClass("has-error");
            $('[data-currencyid^=currid]').parent().addClass('hidden');
            
            //Enable available pricing
            new Promise(
                function(resolve, reject) 
                {
                    var counter = 0;
                    $.each(RC_SettingsDomains.activeContents, function(index, contentid)
                    {
                        JSONParser.request('getContent|Contents', {contentid: contentid}, function(result)
                        {
                            //set contentid in pricing forms
                            var type = result.type;
                            $("#domainPricingForm").data(type, contentid)

                            JSONParser.request('getContentAvailablePricing|Contents', {contentid: contentid}, function(data)
                            {
                                $.each(data, function(currencyid, cycles)
                                {
                                    $(`[data-currencyid=currid${currencyid}]`).parent().removeClass('hidden');
                                    $.each(cycles, function(index, cycle)
                                    {
                                        $("#domainPricingForm [data-currencyid='"+currencyid+"'] [data-billingcycle='"+cycle+"']").show();

                                        $("#domainPricingForm [name='"+type+"["+currencyid+"][adminprice]["+cycle+"]']").attr("disabled", false);
                                        $("#domainPricingForm [name='"+type+"["+currencyid+"][highestprice]["+cycle+"]']").attr("disabled", false);
                                        $("#domainPricingForm [name='"+type+"["+currencyid+"][lowestprice]["+cycle+"]']").attr("disabled", false);
                                    });
                                });
                            });

                            JSONParser.request('getContentPricing|Contents', {contentid: contentid}, function(data)
                            {
                                if(data == '') {
                                    $("#domainPricingForm [name^='"+type+"']").val('');
                                }

                                $.each(data, function(index, values)
                                {
                                    $.each(values.pricing, function(cycle, price)
                                    {
                                        if(price == -1 || price == '') {
                                            $("#domainPricingForm [name='"+type+"["+values.currency+"]["+values.type+"]["+cycle+"]']").val();
                                        }
                                        else {                            
                                            $("#domainPricingForm [name='"+type+"["+values.currency+"]["+values.type+"]["+cycle+"]']").val(price);
                                        }
                                    });
                                });

                            });
                            
                            counter++;
                            if(counter == RC_SettingsDomains.activeContents.length)
                            {
                                resolve();
                            }
                        });
                    });
                    

                }
            )
            .then(function(){
                $("#domainPricingModal").modal("show");
            });
            
        });
    },
    
    submitPricingForm: function()
    {
        //Reset
        var hasErrors = false;
        var counter = 0;
        var disableAlert = true;
        $("#domainPricingForm .has-error").removeClass("has-error");
        
        var form = $("#domainPricingForm").serializeArray();
        $.each(RC_SettingsDomains.types, function(index, type)
        {
            if(counter == 2) {
                disableAlert = false;
            }
            
            var pricing = [];
            $.each(form, function(index, input)
            {
                if(input.name.startsWith(type)){
                    input.name = input.name.replace(type, "pricing");
                    pricing.push(input);
                }
            });
            
            var contentid = $("#domainPricingForm").data(type);
            pricing.push({name: 'contentid', value: contentid});
            
            pricing = RC_Helper.parseToAssoc(pricing);
            
            var resultMessage = "";
            new Promise(function(resolve){
                JSONParser.request(
                    'saveContentPricing|Contents', 
                    pricing, 
                    function(result)
                    {
                        if(result.errors)
                        {
                            hasErrors = true;
                            $.each(result.errors, function(index, error)
                            {
                                var hrefTab = $("#domainPricingForm [data-currencyid='"+error.currency+"']").attr("id");
                                $("#domainPricingForm .nav").find("[href='#"+hrefTab+"']").parent().addClass("has-error");

                                var panelid = $("#domainPricingForm [name='"+type+"["+error.currency+"]["+error.type+"]["+error.billingcycle+"]']").closest(".panel-collapse").attr('aria-labelledby');
                                $("#"+panelid).addClass("has-error");

                                $("#domainPricingForm [name='"+type+"["+error.currency+"]["+error.type+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                            });
                        }
                        
                        resolve();
                        resultMessage = result.success;
                    },
                    "#MGLoader", 
                    false, 
                    disableAlert
                ); 
        
            }).then(function(){
                counter++;
                
                if(counter == 3 && !hasErrors)
                {
                    $("#MGAlerts").alerts("success", resultMessage);
                    $("#domainPricingModal").modal("hide");
                }
            });
        });
    },
    
    refreshCurreciesValues: function()
    {
        JSONParser.request('getCurrenciesRates|Contents', {null: null}, function(data){
            var base = $(".defaultCurrency").data("currencyid");
            
            $.each(RC_SettingsDomains.types, function(index, type)
            {
                $.each(data, function(index, currency){
                    $.each(RC_SettingsDomains.billingcycles, function(index, cycle){
                        $("#domainPricingForm [name='"+type+"["+currency.id+"][adminprice]["+cycle+"]']").val(
                            Math.round($("#domainPricingForm [name='"+type+"["+base+"][adminprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                        );
                        $("#domainPricingForm [name='"+type+"["+currency.id+"][highestprice]["+cycle+"]']").val(
                            Math.round($("#domainPricingForm [name='"+type+"["+base+"][highestprice]["+cycle+"]']").val() * currency.rate * 100) / 100
                        );
                        $("#domainPricingForm [name='"+type+"["+currency.id+"][lowestprice]["+cycle+"]']").val(
                            Math.round($("#domainPricingForm [name='"+type+"["+base+"][lowestprice]["+cycle+"]']").val() * currency.rate * 100) /100
                        );
                    });
                });
            });
            
            $("#domainPricingMessages").alerts("clear");
            $("#domainPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autorefreshcurrency','success')}{literal}");
        });
    },
    
    autofillCurrencies: function()
    {
        var base = $("#domainPricingForm .tab-pane.active").data("currencyid");
        $("#domainPricingForm .tab-pane").each(function(index, element)
        {
            if($(element).is(".active")) {
                return;
            }
            
            //Reset 
            $("#domainPricingForm .has-error").removeClass("has-error");
            
            //Validate
            var types = ['adminprice', 'highestprice', 'lowestprice'];
            $.each(RC_SettingsDomains.types, function(index, type)
            {
                $.each(RC_SettingsDomains.billingcycles, function(index, cycle)
                {
                    $.each(types, function(index, priceType)
                    {
                        var value = $("#domainPricingForm [name='"+type+"["+base+"]["+priceType+"]["+cycle+"]']").val();
                        if(! $.isNumeric(value) && value != "")
                        {
                            $("#domainPricingForm [name='"+type+"["+base+"]["+priceType+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                            $("#domainPricingForm [name='"+type+"["+base+"]["+priceType+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");                        
                        }
                    });
                });
            });
            
            if($("#domainPricingForm").find(".has-error").length > 0)
            {
                return;
            }
            
            var currency = $(element).data("currencyid");
            $.each(RC_SettingsDomains.types, function(index, type)
            {
                $.each(RC_SettingsDomains.billingcycles, function(index, cycle){
                    $("#domainPricingForm [name='"+type+"["+currency+"][adminprice]["+cycle+"]']").val(
                        $("#domainPricingForm [name='"+type+"["+base+"][adminprice]["+cycle+"]']").val()
                    );
                    $("#domainPricingForm [name='"+type+"["+currency+"][highestprice]["+cycle+"]']").val(
                        $("#domainPricingForm [name='"+type+"["+base+"][highestprice]["+cycle+"]']").val()
                    );
                    $("#domainPricingForm [name='"+type+"["+currency+"][lowestprice]["+cycle+"]']").val(
                        $("#domainPricingForm [name='"+type+"["+base+"][lowestprice]["+cycle+"]']").val()
                    );
                });
            });
            
        });

        $("#domainPricingMessages").alerts("clear");
        $("#domainPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcurrencies','success')}{literal}");
    },
    
    autofillBillingCycles: function()
    {
        $(".fillDomainBillingCyclesBtn").unbind("click");
        $(".fillDomainBillingCyclesBtn").on("click", function(event)
        {
            //Reset
            $("#domainPricingForm .has-error").removeClass("has-error");
            
            var billing = $(this).data('billing');
            var currency = $(this).data('currencyid');

            $.each(RC_SettingsDomains.types, function(index, type)
            {
                var values = {
                    admin:      $("#domainPricingForm [name='"+type+"["+currency+"][adminprice]["+billing+"]']").val(),
                    highest:    $("#domainPricingForm [name='"+type+"["+currency+"][highestprice]["+billing+"]']").val(),
                    lowest:     $("#domainPricingForm [name='"+type+"["+currency+"][lowestprice]["+billing+"]']").val()
                };

                $.each(values, function(priceType, value)
                {
                    priceType = priceType + "price";
                    if(!$.isNumeric(value) && value != "")
                    {
                        $("#domainPricingForm [name='"+type+"["+currency+"]["+priceType+"]["+billing+"]']").closest(".controls").addClass("has-error");
                        event.stopPropagation();
                    }
                });

                if($("#domainPricingForm .has-error").length > 0)
                {
                    return;
                }

                var source = RC_SettingsDomains.billingcycles.indexOf(billing)  + 1;
                $.each(RC_SettingsDomains.billingcycles, function(index, billing){
                    $("#domainPricingForm [name='"+type+"["+currency+"][adminprice]["+billing+"]']:enabled").val(values.admin / source * (index+1));
                    $("#domainPricingForm [name='"+type+"["+currency+"][highestprice]["+billing+"]']:enabled").val(values.highest / source * (index+1));
                    $("#domainPricingForm [name='"+type+"["+currency+"][lowestprice]["+billing+"]']:enabled").val(values.lowest / source * (index+1));
                });
            });

            $("#domainPricingMessages").alerts("clear");
            $("#domainPricingMessages").alerts("success", "{/literal}{$MGLANG->T('settings','content','pricing','autofillcycles','success')}{literal}");
        });
    },
    
    /**
     * Edit Form
     */
    openConfigModal: function()
    {
        $(".openDomainConfig").unbind("click");
        $(".openDomainConfig").on("click", function()
        {
            RC_SettingsDomains.getContentIds(this);
            RC_SettingsDomains.showConfigForCountingTypeHandler();
            
            //Reset config modal - hide tabs
            $("#domainregisterConfig").addClass("hidden");
            $("#domaintransferConfig").addClass("hidden");
            $("#domainrenewConfig").addClass("hidden");
            $(".domainregisterTab").addClass("hidden");
            $(".domaintransferTab").addClass("hidden");
            $(".domainrenewTab").addClass("hidden");
            $("#domainConfigModal .nav li").removeClass("has-error");
            
            $.each(RC_SettingsDomains.activeContents, function(index, id)
            {
                //Show only required tabs
                JSONParser.request('getContent|Contents',{contentid: id}, function(result)
                {
                    $("#" +result.type+ "Config").removeClass("hidden");
                    $("." +result.type+ "Tab").removeClass("hidden");

                    //Set fist tab active
                    if($("#domainConfigModal .nav-tabs li.active").length < 1){
                        $("#" +result.type+ "Config").addClass("active");
                        $("." +result.type+ "Tab").addClass("active");
                    }
                    
                    //Set contentid in form
                    $("#" +result.type+ "Config").find("form").data("contentid", id);

                    var type = result.type;
                    JSONParser.request('getContentConfig|Contents', {contentid: id}, function(result)
                    {
                        $("#" +type+ "Config .additional-config").empty();
                        $("#" +type+ "Config [name='counting_type']").val(result.name);
                        if(result.html) {
                            $("#" +type+ "Config .additional-config").append("<hr />" + result.html);
                        }
                        
                        //Display help
                        $("#" +type+ "Config").find(".counting-help").hide();
                        $("#" +type+ "Config").find(".countingType_" + result.name).show();
                        
                        //Disable Submit on Enter
                        $("#domainConfigModal input").on('keyup keypress', function(e) 
                        {
                            var keyCode = e.keyCode || e.which;
                            if (keyCode === 13) 
                            { 
                                e.preventDefault();
                                return false;
                            }
                        });

                        $("#domainConfigModal").modal("show");                     
                    });
                });
            });

        });
    },
    
    submitConfigForm: function()
    {
        //Reset
        var hasError = false;
        var counter = 0;
        var disableAlert = true;
        $("#domainConfigModal .nav li").removeClass("has-error");
        
        $(".domainConfigForm").each(function(index, form)
        {
            if(counter == 2) {
                disableAlert = false;
            }
            
            var data = $(form).serializeArray();  
            data = RC_Helper.parseToAssoc(data);
        
            new Promise(function(resolve, reject){
                JSONParser.request
                (
                    'saveContentConfig|Contents',
                    {data: data, contentid: $(form).data("contentid"), groupid: RC_ConfigurationSettings.groupid}, 
                    function(result)
                    {
                        if(result.errors)
                        {
                            hasError = true;
                            
                            var href = $(form).parent().attr("id");
                            $("#domainConfigModal .nav").find("[href='#"+href+"']").parent().addClass("has-error");
                            $.each(result.errors, function(index, errors)
                            {
                                $(form).find("[name*='"+errors.field+"']").closest(".control-group").addClass("has-error");
                            });
                        }
                        
                        resolve();
                    }, 
                    "#MGLoader", 
                    false, 
                    disableAlert
                );
        
            }).then(function(){
                counter++;
                
                if(counter == 3 && !hasError) 
                {
                    RC_SettingsDomains.table.draw();
                    $("#domainConfigModal").modal("hide");
                }
            });
        });

    },
    
    showConfigForCountingTypeHandler: function()
    {
        $(".domainConfigForm [name='counting_type']").unbind("change");
        $(".domainConfigForm [name='counting_type']").on("change", function()
        {
            var form = $(this).closest(".domainConfigForm");
            var type = $(this).val();
            JSONParser.request('getContentConfig|Contents', {contentid: RC_SettingsProducts.activeContentid, counting_type: type}, function(result)
            {
                $(form).find(".additional-config").empty();
                if(result.html) 
                {
                    $(form).find(".additional-config").append("<hr />" + result.html);
                }
                
                //Disable Submit on Enter
                $("#domainConfigModal input").on('keyup keypress', function(e) 
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
            $(form).find(".counting-help").hide();
            $(form).find(".countingType_" + type).show();
        });
    },
    
    /**
     * Delete Form
     */
    openDeleteModal: function()
    {
        $(".openDomainDelete").unbind("click");
        $(".openDomainDelete").on("click", function(){
            RC_SettingsDomains.activeContents = $(this).data("contentid");
            
            $("#domainDeleteModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request('deleteContentFromGroup|Contents',
            {contentid: RC_SettingsDomains.activeContents}, 
            function(result){
                RC_SettingsDomains.table.draw();
                $("#domainDeleteModal").modal("hide");
        });
    },
   
    
    /**
     *  Show domain configurations
     */
    showChildRowsHandler: function()
    {
        $(".openDomainDetails").unbind("click");
        $(".openDomainDetails").on("click", function()
        {
            var source = $(this).data("tldextension");
            var found = false;
            $("#domainsTable tr").each(function(index, element){
                
                if($(element).find("[data-tldextension*='.']").length > 0){
                    found = false;
                }
                
                if(found === true){
                    if($(element).hasClass("hidden")){
                        $(element).removeClass("hidden");
                    }
                    else
                    {
                        $(element).addClass("hidden");
                    }
                }

                if(source == $(element).find("[data-tldextension='"+source+"']").data("tldextension")){
                    found = true;
                }
            });
            
            var icon = $(this).find("i");
            if(icon.hasClass("fa-angle-double-down")){
                $(this).find("i").removeClass("fa-angle-double-down");
                $(this).find("i").addClass("fa-angle-double-up");
            }
            else {
                $(this).find("i").addClass("fa-angle-double-down");
                $(this).find("i").removeClass("fa-angle-double-up");
            }
        });
    },
    
    loadDomains: function()
    {
        if($.fn.DataTable.isDataTable("#domainsTable"))
        {
            RC_SettingsDomains.table.draw();
        }
        else
        {
            RC_SettingsDomains.table = $("#domainsTable").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=contents&mg-action=getContentTableData&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_SettingsDomains.refreshHandlers();
                },
                fnServerParams: function(data) {
                    data.push({ name: "groupid", value: RC_ConfigurationSettings.groupid});
                    data.push({ name: "type", value: 'domain'}); //this could be also domaintransfer or domainrenew
                },
                columns: [
                    { data: "domain_name",    orderable: true, sortable: false, targets: 0 },
                    { data: "type",           orderable: true, sortable: false, targets: 0 },
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

            RC_SettingsDomains.customDataTableSearch();
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#domainsListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_SettingsDomains.table.search(filter).draw();
            }, 500);
        });
    },
    
    getContentIds: function(element)
    {
        if($(element).data("contentid").length > 1) { 
            RC_SettingsDomains.activeContents = $(element).data("contentid").split(",");
        }  
        else {
            RC_SettingsDomains.activeContents = [$(element).data("contentid")];
        }
    },
}
{/literal}
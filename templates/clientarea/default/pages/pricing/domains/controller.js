{literal}
var ResellersCenter_PricingDomains = 
{
    billingcycles: ["msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially"],
    types: ['domainregister', 'domaintransfer', 'domainrenew'],

    contentsToDelete: null,
    activeContent: null,
    table: null,
    
    init: function()
    {
        this.loadDomainsTable();
    
        $("#RCPricingDomainsEdit [name='tld']").on("change", function() {
            ResellersCenter_PricingDomains.enableAvailablePeriods($(this).val(), true);
        });
    
        /**
         * If Datatable is not wisible width will be set to 0. 
         * This function refresh table width by forcing it to adjust columns
         */
        $(".nav a").on("click", function(){
            $("#RCPricingDomains table").css("width","100%");
        });
    },
    
    refreshHandlers: function()
    {
        this.openPricingModal();
        this.openDeleteModal();
    },
    
    //Add
    openAddPricingModal: function()
    {
        //Reset Modal
        $(".errorContainer").hide();
        $("#RCPricingDomainsEdit .has-error").removeClass("has-error");
        $("#RCPricingDomainsEdit [name^='domainregister']").each(function(index, input){
            $(input).val('');
            $(input).attr('disabled', true);
        });
        $("#RCPricingDomainsEdit [name^='domaintransfer']").each(function(index, input){
            $(input).val('');
            $(input).attr('disabled', true);
        });
        $("#RCPricingDomainsEdit [name^='domainrenew']").each(function(index, input){
            $(input).val('');
            $(input).attr('disabled', true);
        });
        $("#RCPricingDomainsEdit .priceRange").each(function(index, element){
            $(element).html('');
        });
        
        $("#RCPricingDomainsEdit [name='tld']").parents().eq(2).show();
        $("#RCPricingDomainsEdit [name='tld']").select2({
            ajax: {
                url: 'index.php?m=ResellersCenter&mg-page=pricing&mg-action=getAvailableItems&json=1&type=domainregister',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var items = [];
                    $.each(result.data, function(index, value){
                        var domain = value.name;
                        items.push({id: value.relid, text: domain});
                    });
                    return {results: items};
                },
                delay: 350,
            },
            width: '100%',
            placeholder: "{/literal}{$MGLANG->T('select','placeholder')}{literal}",
        });
        
        //Set title
        $("#RCPricingDomainsEdit .modal-title").html("{/literal}{$MGLANG->T('domains','add','title')}{literal}");
        
        $("#domainTldSelect").select2("val", "");
        $("#RCPricingDomainsEdit").modal("show");
    },
    
    //Edit
    openPricingModal: function()
    {
        $("#RCPricingDomains .openPricingModal").unbind("click");
        $("#RCPricingDomains .openPricingModal").on("click", function()
        {
            $(".errorContainer").hide();
            $("#RCPricingDomainsEdit .has-error").removeClass("has-error");
            
            var relid = $(this).data('relid');
            if(relid !== undefined) //Edit Pricing
            {
                ResellersCenter_PricingDomains.activeContent = relid;
                $("#RCPricingDomainsEdit [name='tld']").parents().eq(2).hide();
                ResellersCenter_PricingDomains.enableAvailablePeriods(relid);
                $('[data-currencyid^=currid]').parent().addClass('hidden');
                $.each(ResellersCenter_PricingDomains.types, function(index, type)
                {
                    JSONParser.request("getPricing", {relid: relid, type: type}, function(result)
                    {
                        if(result === undefined) {
                            return;
                        }

                        $.each(result, function(currencyid, data)
                        {
                            $(`[data-currencyid=currid${currencyid}]`).parent().removeClass('hidden');
                            $.each(data.pricing, function(billingcycle, value)
                            {
                                $("#RCPricingDomainsEdit [name='"+type+"["+currencyid+"]["+billingcycle+"]']").val(value);
                            });
                        });
                    });
                });

                // Adding initial active class for first element for TwentyOne Template
                if($('.active-client').length
                    && $('#RCPricingDomainsEdit li[role=presentation].active').length === 0
                    && $('#RCPricingDomainsEdit li[role=presentation]').length)
                {
                    $('#RCPricingDomainsEdit li[role=presentation]').first().removeClass('active').addClass('active');
                }

                //Set title
                $("#RCPricingDomainsEdit .modal-title").html("{/literal}{$MGLANG->T('domains','edit','title')}{literal}");

                $("#RCPricingDomainsEdit").modal("show");
            }
        });
    },
    
    submitPricingForm: function()
    {
        new Promise(
            function(resolve, reject) 
            {
                var counter = 0;
                var form = $("#RCPricingDomainsEdit form").serializeArray();
                
                //Reset from
                $("#RCPricingDomainsEdit .has-error").removeClass("has-error");
                
                //Dont send empty from
                var isEmpty = true;
                $("#RCPricingDomainsEdit form").find("input").each(function(index, element)
                {
                    if($(element).val() != '') {
                        isEmpty = false;
                    }
                });
                
                if( ($("#RCPricingDomainsEdit form").find("[name^='tld']").val() == null && ResellersCenter_PricingDomains.activeContent == null) || isEmpty) 
                {
                    $(".errorContainer").html("{/literal}{$MGLANG->absoluteT('form','validate','empty')}{literal}").show();
                    return;
                }
                
                $.each(ResellersCenter_PricingDomains.types, function(index, type)
                {
                    var pricing = [];
                    pricing.push({name: 'type', value: type});

                    $.each(form, function(index, input)
                    {
                        if(input.name.startsWith(type)){
                            input.name = input.name.replace(type, "pricing");
                            pricing.push(input);
                        }
                    });
                    

                    if(ResellersCenter_PricingDomains.activeRelid !== null) {
                        pricing.push({name: 'relid', value: ResellersCenter_PricingDomains.activeRelid});
                    }

                    pricing = RC_Helper.parseToAssoc(pricing);
                    JSONParser.request("savePricing", pricing, function(result) 
                    {
                        if(result.success)
                        {
                            $("#MGAlerts").alerts("clear");
                        }
                        else
                        {
                            $.each(result.errors, function(index, error)
                            {
                                $("[name='"+type+"["+error.currency+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                            });
                        }
                        
                        counter++;
                        if(counter >= 3)
                        {
                            resolve();
                        }
                        
                        if($("#RCPricingDomainsEdit .has-error").length > 0)
                        {
                            $(".errorContainer").html("{/literal}{$MGLANG->absoluteT('form','validate','pricing')}{literal}").show();

                            //Switch to the tab with error
                            //pane
                            $("#RCPricingDomainsEdit").find(".tab-pane.active").removeClass("active");
                            $("#RCPricingDomainsEdit .has-error").first().parents(".tab-pane").addClass("active");

                            //tab
                            var index = $("#RCPricingDomainsEdit .has-error").first().parents(".tab-pane").index() + 1;
                            $("#RCPricingDomainsEdit").find("li.active").removeClass("active");
                            $("#RCPricingDomainsEdit").find("li:nth-child("+index+")").addClass("active");
                        }
                    });
                });
            }
        ).then(function(){
            if($("#RCPricingDomainsEdit .has-error").length == 0)
            {
                $("#RCPricingDomainsEdit").modal("hide");
                ResellersCenter_PricingDomains.table.draw();
                ResellersCenter_PricingDomains.activeContent = null;

                $("#MGAlerts").alerts("clear");
                $("#MGAlerts").alerts("success", "{/literal}{$MGLANG->T('save','success')}{literal}");
            }
        });
    },
    
    //Delete
    openDeleteModal: function()
    {
        $("#RCPricingDomains .openDeleteModal").unbind("click");
        $("#RCPricingDomains .openDeleteModal").on("click", function()
        {
            ResellersCenter_PricingDomains.activeRelid = $(this).data("relid");
            $("#RCPricingDomainsDelete").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deletePricing", {relid: this.activeRelid, type: 'domain'}, function()
        {
            ResellersCenter_PricingDomains.activeRelid = null;
            ResellersCenter_PricingDomains.table.draw();
        });
        
        $("#RCPricingDomainsDelete").modal("hide");
    },
    
    loadDomainsTable: function()
    {
        this.table = $("#RCPricingDomains table").DataTable({
            autoWidth: false,
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=pricing&mg-action=getPricingForTable",
            fnDrawCallback: function(){
                ResellersCenter_PricingDomains.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "type", value: 'domain'});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "extension",      orderable: true, sortable: false, targets: 0 },
                { data: "billingcycles",  orderable: false, sortable: false, targets: 0 },
                { data: "actions",        orderable: false, sortable: false, targets: 0 },
              ],
            columnDefs: [
                { width: "25%", targets: 0 },
                { width: "60%", targets: 1 },
                { width: "15%", targets: 2 }
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>',
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

    },
    
    enableAvailablePeriods: function(relid, setDefaultValues)
    {
        //Reset all inputs
        $("#RCPricingDomainsEdit input").attr("disabled", true);
        $("#RCPricingDomainsEdit input").val("");
        $("#RCPricingDomainsEdit").find(".priceRange").empty();
        $(".billingcycles .row").each(function(index, row) {
            $(row).show();
        });

        ResellersCenter_PricingDomains.activeRelid = relid;

        //And enable only those that have pricing set by admin
        $.each(this.types, function(index, type)
        {
            JSONParser.request("getAvailableBillingCycles", {relid: relid, type: type}, function(result)
            {
                if(result === undefined) {
                    return;
                }

                $.each(result, function(currency, billingcycles)
                {
                    $.each(billingcycles, function(billingcycle, pricing)
                    {
                        var input = $("#RCPricingDomainsCurrency"+currency).find("[name='"+type+"["+currency+"]["+billingcycle+"]']");
                        input.removeAttr("disabled");
                        input.parents().eq(2).find(".priceRange").append("("+pricing.lowestprice+" - "+pricing.highestprice+")  {/literal}{$MGLANG->T('adminprice')}{literal}: " + pricing.adminprice);

                        if(setDefaultValues)
                        {
                            input.val(pricing.highestprice);
                        }
                    });
                });

                setTimeout(function()
                {
                    $(".billingcycles .row").each(function(index, row)
                    {
                        var inputs = $(row).find("input");
                        if(inputs.length)
                        {
                            if($(inputs[0]).is(":disabled") == true && $(inputs[1]).is(":disabled") == true && $(inputs[2]).is(":disabled") == true)
                            {
                                $(row).hide();
                            }
                        }
                    });
                }, 10);

            });
        });

    },

    refreshCurrenciesValues: function()
    {
        JSONParser.request('getCurrenciesRates', {null: null}, function(data)
        {
            var base = $(".defaultCurrency").data("currencyid");
            $.each(ResellersCenter_PricingDomains.types, function(index, type)
            {
                $.each(data, function(index, currency)
                {
                    $.each(ResellersCenter_PricingDomains.billingcycles, function(index, cycle)
                    {
                        $("#RCPricingDomainsEdit [name='"+type+"["+currency.id+"]["+cycle+"]']").val(
                            Math.round($("#RCPricingDomainsEdit [name='"+type+"["+base+"]["+cycle+"]']").val() * currency.rate * 100) / 100
                        );
                    });
                });
            });

            $("#domainPricingMessages").alerts("clear");
            $("#domainPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autorefreshcurrency','success')}{literal}");
        });
    },

    autofillCurrencies: function()
    {
        var base = $("#RCPricingDomainsEdit .tab-pane.active").data("currencyid");
        $("#RCPricingDomainsEdit .tab-pane").each(function(index, element)
        {
            if($(element).is(".active"))
            {
                return;
            }

            //Reset
            $("#RCPricingDomainsEdit .has-error").removeClass("has-error");

            //Validate
            $.each(ResellersCenter_PricingDomains.types, function(index, type)
            {
                $.each(ResellersCenter_PricingDomains.billingcycles, function(index, cycle)
                {
                    var value = $("#RCPricingDomainsEdit [name='"+type+"["+base+"]["+cycle+"]']").val();
                    if(! $.isNumeric(value) && value != "")
                    {
                        $("#RCPricingDomainsEdit [name='"+type+"["+base+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                        $("#RCPricingDomainsEdit [name='"+type+"["+base+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");
                    }
                });
            });

            if($("#RCPricingDomainsEdit").find(".has-error").length > 0)
            {
                return;
            }

            var currency = $(element).data("currencyid");
            $.each(ResellersCenter_PricingDomains.types, function(index, type)
            {
                $.each(ResellersCenter_PricingDomains.billingcycles, function(index, cycle)
                {
                    $("#RCPricingDomainsEdit [name='"+type+"["+currency+"]["+cycle+"]']").val(
                        $("#RCPricingDomainsEdit [name='"+type+"["+base+"]["+cycle+"]']").val()
                    );
                });
            });

            ResellersCenter_PricingDomains.refreshCurrenciesValues();
        });

        $("#domainPricingMessages").alerts("clear");
        $("#domainPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autofillcurrencies','success')}{literal}");
    },
}
ResellersCenter_PricingDomains.init();
{/literal}
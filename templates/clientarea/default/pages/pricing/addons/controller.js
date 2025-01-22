{literal}
var ResellersCenter_PricingAddons = 
{
    billingcycles: ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'],

    activeRelid: null,
    table: null,
    
    init: function()
    {
        this.loadAddonsTable();
        
        $("#RCPricingAddonsEdit [name='relid']").on("change", function(){
            ResellersCenter_PricingAddons.enableAvailableBillingCycles($(this).val(), true);
        });
            
        /**
         * If Datatable is not wisible width will be set to 0. 
         * This function refresh table width by forcing it to adjust columns
         */
        $(".nav a").on("click", function(){
            $("#RCPricingAddons table").css("width","100%");
        });
    },
    
    refreshHandlers: function()
    {
        this.openPricingModal();
        this.openDeleteModal();
    },
    
    openAddPricingModal: function()
    {
        //Reset Form
        $(".priceRange").empty();
        $(".errorContainer").hide();
        $("#RCPricingAddonsEdit").find(".has-error").removeClass("has-error");
        $("#RCPricingAddonsEdit [name^='pricing']").each(function(index, input){
            $(input).val('');
            $(input).attr('disabled', true);
            $(input).closest(".billingcycle").hide();
        });
        ResellersCenter_PricingAddons.activeRelid = null;
        
        $("#RCPricingAddonsEdit [name='relid']").parents().eq(2).show();
        $("#RCPricingAddonsEdit [name='relid']").select2({
            ajax: {
                url: 'index.php?m=ResellersCenter&mg-page=pricing&mg-action=getAvailableItems&json=1&type=addon',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var items = [];
                    $.each(result.data, function(index, value) {
                        var addon = "#"+value.relid+" "+value.name;
                        items.push({id: value.relid, text: addon});
                    });
                    return {results: items};
                },
                delay: 350,
            },
            width: '100%',
            placeholder: "{/literal}{html_entity_decode($MGLANG->T('select','placeholder'))}{literal}",
        });
        
        //Set title
        $("#RCPricingAddonsEdit .modal-title").html("{/literal}{$MGLANG->T('addons','add','title')}{literal}");
        
        $("#RCPricingAddonsEdit [name='relid']").select2("val", "");
        $("#RCPricingAddonsEdit").modal("show");
    },
    
    //Add & Edit
    openPricingModal: function()
    {
        $("#RCPricingAddons .openPricingModal").unbind("click");
        $("#RCPricingAddons .openPricingModal").on("click", function()
        {
            //Reset Form
            $(".errorContainer").hide();
            $("#RCPricingAddonsEdit").find(".has-error").removeClass("has-error");
            $("#RCPricingAddonsEdit [name^='pricing']").each(function(index, input){
                $(input).val('');
            });
            $('[data-currencyid^=currid]').parent().addClass('hidden');

            var relid = $(this).data('relid');
            if(relid !== undefined) //Edit Pricing
            {
                ResellersCenter_PricingAddons.activeRelid = relid;
                $("#RCPricingAddonsEdit [name='relid']").parents().eq(2).hide();
                
                JSONParser.request("getPricing", {relid: relid, type: 'addon'}, function(result)
                {
                    $.each(result, function(index, data)
                    {
                        $(`[data-currencyid=currid${data.currency}]`).parent().removeClass('hidden');
                        $.each(data.pricing, function(billingcycle, value)
                        {
                            $("[name='pricing["+data.currency+"]["+billingcycle+"]']").val(value);
                        });
                    });
                });
                
                ResellersCenter_PricingAddons.enableAvailableBillingCycles(relid);

                // Adding initial active class for first element for TwentyOne Template
                if($('.active-client').length
                    && $('#RCPricingAddonsEdit li[role=presentation].active').length === 0
                    && $('#RCPricingAddonsEdit li[role=presentation]').length)
                {
                    $('#RCPricingAddonsEdit li[role=presentation]').first().removeClass('active').addClass('active');
                }

                //Set title
                $("#RCPricingAddonsEdit .modal-title").html("{/literal}{$MGLANG->T('addons','edit','title')}{literal}");
                $("#RCPricingAddonsEdit").modal("show");
            }
        });
    },
    
    submitPricingForm: function()
    {
        var form = $("#RCPricingAddonsEdit form").serialize();
        if(ResellersCenter_PricingAddons.activeRelid !== null) {
            form += "&relid="+ResellersCenter_PricingAddons.activeRelid;
        }
        
        var isEmpty = true;
        if($("#RCPricingAddonsEdit form[name^='pricing']").length > 0)
        {
            $("#RCPricingAddonsEdit form").find("[name^='pricing']").each(function(index, element)
            {
                if($(element).val() != '') {
                    isEmpty = false;
                }
            });
        }
        else
        {
            //We deal with free addon
            isEmpty = false;
        }
        
        if(! isEmpty && ($("#RCPricingAddonsEdit form").find("[name^='relid']").val() || ResellersCenter_PricingAddons.activeRelid)) 
        {
            JSONParser.request("savePricing", form, function(result)
            {
                if(result.success)
                {
                    $("#RCPricingAddonsEdit").modal("hide");
                    ResellersCenter_PricingAddons.activeRelid = null;
                    ResellersCenter_PricingAddons.table.draw();
                }
                else
                {
                    $.each(result.errors, function(index, error)
                    {
                        $("[name='pricing["+error.currency+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                    });
                }
                
                if($("#RCPricingAddonsEdit .has-error").length > 0)
                {
                    $(".errorContainer").html("{/literal}{$MGLANG->absoluteT('form','validate','pricing')}{literal}").show()

                    //Switch to the tab with error
                    //pane
                    $("#RCPricingAddonsEdit").find(".tab-pane.active").removeClass("active");
                    $("#RCPricingAddonsEdit .has-error").first().parents(".tab-pane").addClass("active");

                    //tab
                    var index = $("#RCPricingAddonsEdit .has-error").first().parents(".tab-pane").index() + 1;
                    $("#RCPricingAddonsEdit").find("li.active").removeClass("active");
                    $("#RCPricingAddonsEdit").find("li:nth-child("+index+")").addClass("active");
                }
            });
        }
        else
        {
            $(".errorContainer").html("{/literal}{$MGLANG->absoluteT('form','validate','empty')}{literal}").show();
        }
    },
    
    //Delete
    openDeleteModal: function()
    {
        $("#RCPricingAddons .openDeleteModal").unbind("click");
        $("#RCPricingAddons .openDeleteModal").on("click", function()
        {
            ResellersCenter_PricingAddons.activeRelid = $(this).data("relid");
            $("#RCPricingAddonsDelete").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deletePricing", {relid: ResellersCenter_PricingAddons.activeRelid, type: 'addon'}, function()
        {
            $("#RCPricingAddonsDelete").modal("hide");
            ResellersCenter_PricingAddons.activeRelid = null;
            ResellersCenter_PricingAddons.table.draw();
        });
    },
    
    loadAddonsTable: function()
    {
        this.table = $("#RCPricingAddons table").DataTable({
            autoWidth: false,
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=pricing&mg-action=getPricingForTable",
            fnDrawCallback: function(){
                ResellersCenter_PricingAddons.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "type", value: 'addon'});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "name",   orderable: true, sortable: false, targets: 0 },
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
    
    enableAvailableBillingCycles: function(relid, setDefaultValues)
    {
        //disable unavailable billingcycles
        $("#RCPricingAddonsEdit [name^='pricing']").attr("disabled", true);
        $("#RCPricingAddonsEdit [name^='pricing']").closest(".billingcycle").hide();
        $("#RCPricingAddonsEdit [name^='pricing']").val("");
        $("#RCPricingAddonsEdit").find(".priceRange").empty();
        $("#RCPricingAddonsEdit .freeBilingcycle").hide();
        
        JSONParser.request("getAvailableBillingCycles", {relid: relid, type: 'addon'}, function(result) 
        {
            $.each(result, function(currency, billingcycles) 
            {
                $.each(billingcycles, function(billingcycle, pricing)
                {
                    if(billingcycle == "free")
                    {
                        $("#RCPricingAddonsEdit .freeBilingcycle").show();
                    }
                    else
                    {
                        var input = $("#RCPricingAddonsCurrency"+currency).find("[name='pricing["+currency+"]["+billingcycle+"]']");
                        input.removeAttr("disabled");
                        input.closest(".billingcycle").show();
                        input.closest(".row").find(".priceRange").append("("+pricing.lowestprice+" - "+pricing.highestprice+")  {/literal}{$MGLANG->T('adminprice')}{literal}: " + pricing.adminprice);

                        if(setDefaultValues)
                        {
                            input.val(pricing.highestprice);
                        }   
                    }
                });
            });
        });
    },

    refreshCurrenciesValues: function()
    {
        JSONParser.request('getCurrenciesRates', {null: null}, function(data)
        {
            var base = $(".defaultCurrency").data("currencyid");
            $.each(data, function(index, currency)
            {
                $.each(ResellersCenter_PricingAddons.billingcycles, function(index, cycle)
                {
                    //Billingcycles
                    $("#RCPricingAddonsEdit [name='pricing["+currency.id+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );

                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#RCPricingAddonsEdit [name='pricing["+currency.id+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                });
            });

            $("#addonPricingMessages").alerts("clear");
            $("#addonPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autorefreshcurrency','success')}{literal}");
        });
    },

    autofillCurrencies: function()
    {
        var base = $("#RCPricingAddonsEdit .tab-pane.active").data("currencyid");
        $("#RCPricingAddonsEdit .tab-pane").each(function(index, element)
        {
            if($(element).is(".active"))
            {
                return;
            }

            //Reset
            $("#RCPricingAddonsEdit .has-error").removeClass("has-error");

            //Validate
            $.each(ResellersCenter_PricingAddons.billingcycles, function(index, cycle)
            {
                var value = $("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").val();
                if(! $.isNumeric(value) && value != "")
                {
                    $("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                    $("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");
                }
            });

            if($("#RCPricingAddonsEdit").find(".has-error").length > 0)
            {
                return;
            }

            var currency = $(element).data("currencyid");
            JSONParser.request('getCurrenciesRates', {currencyid: currency}, function(data)
            {
                $.each(ResellersCenter_PricingAddons.billingcycles, function(index, cycle)
                {
                    //Billingcycles
                    $("#RCPricingAddonsEdit [name='pricing["+currency+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").val() * data.rate * 100) /100
                    );

                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#RCPricingAddonsEdit [name='pricing["+currency+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingAddonsEdit [name='pricing["+base+"]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                });
            });
        });

        $("#addonPricingMessages").alerts("clear");
        $("#addonPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autofillcurrencies','success')}{literal}");
    },
}
ResellersCenter_PricingAddons.init();
{/literal}
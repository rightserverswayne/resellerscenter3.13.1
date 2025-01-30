{literal}
var ResellersCenter_PricingProducts = 
{
    activeRelid: null,
    table: null,

    billingcycles: ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'],
    
    init: function()
    {
        this.loadProductsTable();
        
        $("#RCPricingProductsEdit [name='relid']").on("change", function(){
            ResellersCenter_PricingProducts.enableAvailableBillingCycles($(this).val(), true);
        });
    },
    
    refreshHandlers: function()
    {
        this.openPricingModal();
        this.openDeleteModal();
        this.copyCartUrlHandler();
        this.openLinkModal();
    },
    
    openAddPricingModal: function()
    {
        //Reset Form
        $(".errorContainer").hide();
        $(".priceRange").empty();
        $("#RCPricingProductsEdit").find(".has-error").removeClass("has-error");
        $("#RCPricingProductsEdit [name^='pricing']").each(function(index, element){
            $(element).val('');
            $(element).attr('disabled', true);
            $(element).closest(".billingcycle").hide();
        });
        ResellersCenter_PricingProducts.activeRelid = null;
        
        $("#RCPricingProductsEdit [name='relid']").parents().eq(2).show();
        $("#RCPricingProductsEdit [name='relid']").select2({
            ajax: {
                url: 'index.php?m=ResellersCenter&mg-page=pricing&mg-action=getAvailableItems&json=1&type=product',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var items = [];
                    $.each(result.data, function(index, value){
                        var product = "#"+value.relid+" "+value.name;
                        items.push({id: value.relid, text: product});
                    });
                    return {results: items};
                },
                delay: 350,
            },
            width: '100%',
            placeholder: "{/literal}{$MGLANG->T('select','placeholder')}{literal}",
        });
        
        //Set title
        $("#RCPricingProductsEdit .modal-title").html("{/literal}{$MGLANG->T('products','add','title')}{literal}");
        
        $("#RCPricingProductsEdit [name='relid']").select2("val", "");
        $("#RCPricingProductsEdit").modal("show");
    },
    
    //Add & Edit
    openPricingModal: function()
    {
        $("#RCPricingProducts .openPricingModal").unbind("click");
        $("#RCPricingProducts .openPricingModal").on("click", function()
        {
            //Reset Form
            $(".errorContainer").hide();
            $("#RCPricingProductsEdit").find(".has-error").removeClass("has-error");
            $("#RCPricingProductsEdit [name^='pricing']").each(function(index, element){
                $(element).val('');
            });
            
            var relid = $(this).data('relid');
            if(relid !== undefined) //Edit Pricing
            {
                $("#RCPricingProductsEdit [name='relid']").parents().eq(2).hide();
                
                ResellersCenter_PricingProducts.activeRelid = relid;
                JSONParser.request("getPricing", {relid: relid, type: 'product'}, function(result)
                {
                    $.each(result, function(index, data)
                    {
                        $.each(data.pricing, function(billingcycle, value)
                        {
                            $("[name='pricing["+data.currency+"]["+billingcycle+"]']").val(value);
                        });
                    });
                });
                
                ResellersCenter_PricingProducts.enableAvailableBillingCycles(relid);

                // Adding initial active class for first element for TwentyOne Template
                if($('.active-client').length
                    && $('#RCPricingProductsEdit li[role=presentation].active').length === 0
                    && $('#RCPricingProductsEdit li[role=presentation]').length)
                {
                    $('#RCPricingProductsEdit li[role=presentation]').first().removeClass('active').addClass('active');
                }

                //Set title
                $("#RCPricingProductsEdit .modal-title").html("{/literal}{$MGLANG->T('products','edit','title')}{literal}");
                
                $("#RCPricingProductsEdit").modal("show");
            }
        });
    },
    
    submitPricingForm: function()
    {
        $(".errorContainer").hide();
        $("#RCPricingProductsEdit").find(".has-error").removeClass("has-error");
        var form = $("#RCPricingProductsEdit form").serialize();
        
        if(ResellersCenter_PricingProducts.activeRelid !== null) {
            form += "&relid="+ResellersCenter_PricingProducts.activeRelid;
        }
        
        var isEmpty = true;
        if($("#RCPricingProductsEdit form[name^='pricing']").length > 0)
        {
            $("#RCPricingProductsEdit form").find("[name^='pricing']").each(function(index, element)
            {
                if($(element).val() != '') {
                    isEmpty = false;
                }
            });
        }
        else
        {
            //We deal with free product
            isEmpty = false;
        }
        
        if(! isEmpty && ($("#RCPricingProductsEdit form").find("[name^='relid']").val() || ResellersCenter_PricingProducts.activeRelid)) 
        {
            JSONParser.request("savePricing", form, function(result)
            {
                if(result.success)
                {
                    $("#RCPricingProductsEdit").modal("hide");
                    ResellersCenter_PricingProducts.activeRelid = null;
                    ResellersCenter_PricingProducts.table.draw();
                }
                else
                {
                    $.each(result.errors, function(index, error)
                    {
                        $("[name='pricing["+error.currency+"]["+error.billingcycle+"]']").closest(".controls").addClass("has-error");
                    });
                }
                
                if($("#RCPricingProductsEdit .has-error").length > 0)
                {
                    $(".errorContainer").html("{/literal}{$MGLANG->absoluteT('form','validate','pricing')}{literal}").show()

                    //Switch to the tab with error
                    //pane
                    $("#RCPricingProductsEdit").find(".tab-pane.active").removeClass("active");
                    $("#RCPricingProductsEdit .has-error").first().parents(".tab-pane").addClass("active");

                    //tab
                    var index = $("#RCPricingProductsEdit .has-error").first().parents(".tab-pane").index() + 1;
                    $("#RCPricingProductsEdit").find("li.active").removeClass("active");
                    $("#RCPricingProductsEdit").find("li:nth-child("+index+")").addClass("active");
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
        $("#RCPricingProducts .openDeleteModal").unbind("click");
        $("#RCPricingProducts .openDeleteModal").on("click", function()
        {
            ResellersCenter_PricingProducts.activeRelid = $(this).data("relid");
            $("#RCPricingProductDelete").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deletePricing", {relid: ResellersCenter_PricingProducts.activeRelid, type: 'product'}, function()
        {
            $("#RCPricingProductDelete").modal("hide");
            ResellersCenter_PricingProducts.activeRelid = null;
            ResellersCenter_PricingProducts.table.draw();
        });
    },

    //Generate Link
    openLinkModal: function()
    {
        $("#RCPricingProducts .openLinkModal").unbind("click");
        $("#RCPricingProducts .openLinkModal").on("click", function()
        {
            ResellersCenter_PricingProducts.activeRelid = $(this).data("relid");
            JSONParser.request("generateCartURL", {relid: ResellersCenter_PricingProducts.activeRelid}, function(data)
            {
                if (data['productLink']) {
                    $("#RCPricingProductLink .productLinkContainer input.generatedLinkField").val(data['productLink']);
                } else {
                    $("div.productLinkContainer p.copyCartUrl").attr('disabled', true);
                }

                if (data['productGroupLink']) {
                    $("#RCPricingProductLink .productGroupLinkContainer input.generatedLinkField").val(data['productGroupLink']);
                } else {
                    $("div.productGroupLinkContainer p.copyCartUrl").attr('disabled', true);
                }
            });

            $("#RCPricingProductLink").modal("show");
            $(".copyCartUrlBtn span").hide();
        });
    },
    
    //Copy to clipboard
    copyCartUrlHandler: function()
    {
        $("#RCPricingProducts .copyCartUrl").unbind("click");
        $("#RCPricingProducts .copyCartUrl").on("click", function(e)
        {
            const disabledAttribute = e.target.parentElement.attributes.getNamedItem("disabled");
            if (disabledAttribute) {
                return;
            }
            e.preventDefault();

            var trigger = $(e.currentTarget);
            var contentElement = $(trigger).data('clipboard-target');
            var container = $(contentElement).parent();

            $(".copyCartUrlBtn span").hide();
            copiedMessage = $(e.currentTarget).parent().find("span");

            try {
                var tempElement = $('<textarea>')
                    .css('position', 'fixed')
                    .css('opacity', '0')
                    .css('width', '1px')
                    .css('height', '1px')
                    .val($(contentElement).val());

                container.append(tempElement);
                tempElement.focus().select();
                document.execCommand('copy');
            } finally {
                tempElement.remove();
                copiedMessage.show();
            }
        });
    },
    
    loadProductsTable: function()
    {
        this.table = $("#RCPricingProducts table").DataTable({
            autoWidth: false,
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=pricing&mg-action=getPricingForTable",
            fnDrawCallback: function(){
                ResellersCenter_PricingProducts.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "type", value: 'product'});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "name",   orderable: true, sortable: false, targets: 0 },
                { data: "billingcycles",  orderable: false, sortable: false, targets: 0 },
                { data: "actions",        orderable: false, sortable: false, targets: 0 },
                { data: "group", orderable: true, sortable: false, targets: 0 }
              ],
            columnDefs: [
                { width: "20%", targets: 0 }, // Adjust the width as needed
                { width: "20%", targets: 1 },
                { width: "20%", targets: 2 },
                { width: "20%", targets: 3 },
                { width: "20%", targets: 4 },
                { width: "20%", targets: 5 }
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
        $("#RCPricingProductsEdit [name^='pricing']").attr("disabled", true);
        $("#RCPricingProductsEdit [name^='pricing']").closest(".billingcycle").hide();
        $("#RCPricingProductsEdit [name^='pricing']").val("");
        $("#RCPricingProductsEdit").find(".priceRange").empty();
        $("#RCPricingProductsEdit .freeBilingcycle").hide();
        $('[data-currencyid^=currid]').parent().addClass('hidden');

        JSONParser.request("getAvailableBillingCycles", {relid: relid, type: 'product'}, function(result)
        {
            $.each(result, function(currency, billingcycles) 
            {
                $(`[data-currencyid=currid${currency}]`).parent().removeClass('hidden');
                $.each(billingcycles, function(billingcycle, pricing)
                {
                    if(billingcycle == "free")
                    {
                        $("#RCPricingProductsEdit .freeBilingcycle").show();
                    }
                    else
                    {
                        var input = $("#RCPricingProductsCurrency"+currency).find("[name='pricing["+currency+"]["+billingcycle+"]']");
                        input.removeAttr("disabled");
                        input.closest(".billingcycle").show();
                        input.closest(".row").find(".priceRange").append("("+pricing.lowestprice+" - "+pricing.highestprice+")  {/literal}{$MGLANG->T('adminprice')}{literal}: "+pricing.adminprice);

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
                $.each(ResellersCenter_PricingProducts.billingcycles, function(index, cycle)
                {
                    //Billingcycles
                    $("#RCPricingProductsEdit [name='pricing["+currency.id+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );

                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#RCPricingProductsEdit [name='pricing["+currency.id+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").val() * currency.rate * 100) / 100
                    );
                });
            });

            $("#productPricingMessages").alerts("clear");
            $("#productPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autorefreshcurrency','success')}{literal}");
        });
    },

    autofillCurrencies: function()
    {
        var base = $("#RCPricingProductsEdit .tab-pane.active").data("currencyid");
        $("#RCPricingProductsEdit .tab-pane").each(function(index, element)
        {
            if($(element).is(".active"))
            {
                return;
            }

            //Reset
            $("#RCPricingProductsEdit .has-error").removeClass("has-error");

            //Validate
            $.each(ResellersCenter_PricingProducts.billingcycles, function(index, cycle)
            {
                var value = $("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").val();
                if(! $.isNumeric(value) && value != "")
                {
                    $("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").closest(".controls").addClass("has-error");
                    $("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").closest(".panel").find(".panel-heading").addClass("has-error");
                }
            });

            if($("#RCPricingProductsEdit").find(".has-error").length > 0)
            {
                return;
            }

            var currency = $(element).data("currencyid");
            JSONParser.request('getCurrenciesRates', {currencyid: currency}, function(data)
            {
                $.each(ResellersCenter_PricingProducts.billingcycles, function(index, cycle)
                {
                    //Billingcycles
                    $("#RCPricingProductsEdit [name='pricing["+currency+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").val() * data.rate * 100) /100
                    );

                    //Setup Fees
                    cycle = cycle.charAt(0) + "setupfee";
                    $("#RCPricingProductsEdit [name='pricing["+currency+"]["+cycle+"]']").val(
                        Math.round($("#RCPricingProductsEdit [name='pricing["+base+"]["+cycle+"]']").val() * data.rate * 100) /100
                    );
                });
            });
        });

        $("#productPricingMessages").alerts("clear");
        $("#productPricingMessages").alerts("success", "{/literal}{$MGLANG->T('autofillcurrencies','success')}{literal}");
    }
}
ResellersCenter_PricingProducts.init();
{/literal}
{literal}
var ResellersCenter_ClientOrders = 
{
    activeOrder: null,
    activeOrderInvoiceId: null,
    table: null,
    
    init: function()
    {
        this.loadTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        this.openOrderDetails();
        this.openAcceptForm();
        this.openDeleteForm();
        this.openCancelForm();
        this.openFraudForm();
    },
  
    openOrderDetails: function()
    {
        $(".openDetailsOrder").unbind("click");
        $(".openDetailsOrder").on("click", function()
        {
            ResellersCenter_ClientOrders.activeOrder = $(this).data("orderid");
            var isAwaitingReseller = $(this).parents("tr").hasClass("awaiting-reseller");
            
            //reset table
            $("#RCOrderDetails").find("tbody").first().empty();
            
            var orderid = $(this).data("orderid");
            var paymentstatus = $(this).data("paymentstatus");
            
            JSONParser.request("getOrderDetails|orders", {orderid: orderid}, function(result)
            {
                ResellersCenter_ClientOrders.activeOrderInvoiceId = result.invoiceid;

                //Set payment status
                if(isAwaitingReseller || result.status != 'Pending'){
                    $("#RCOrderDetails .openAcceptOrder").hide();
                }
                else {
                    $("#RCOrderDetails .openAcceptOrder").show();
                    $("#RCOrderDetails .openAcceptOrder").data("paymentstatus", paymentstatus);
                }
                
                //Hostings
                $.each(result.hostings, function(index, hosting)
                {
                    var productAmount = result.currency.prefix + hosting.firstpaymentamount + result.currency.suffix;
                    ResellersCenter_ClientOrders.addRowToDetailsTable('hosting', hosting.product.name, hosting.domain, hosting.billingcycle, productAmount, hosting.domainstatus);
                });
                
                //Addons
                if(result.hostingAddons)
                {
                    $.each(result.hostingAddons, function(index, hostingAddon)
                    {
                        var addonAmount = result.currency.prefix + (Math.round((parseFloat(hostingAddon.recurring) + parseFloat(hostingAddon.setupfee)) * 100) / 100).toFixed(2) + result.currency.suffix;
                        ResellersCenter_ClientOrders.addRowToDetailsTable('addon', hostingAddon.addon.name, '', hostingAddon.billingcycle, addonAmount, hostingAddon.status);
                    });
                }
                
                //Domains
                $.each(result.domains, function(index, domain)
                {
                    var domainAmount = result.currency.prefix + domain.firstpaymentamount + result.currency.suffix;
                    if(domain.typeraw == "renewal")
                    {
                        domainAmount = result.currency.prefix + domain.recurringamount + result.currency.suffix;
                    }
                    
                    ResellersCenter_ClientOrders.addRowToDetailsTable('domain', domain.type, domain.domain, domain.registrationperiod, domainAmount, domain.status);
                });
                
                $.each(result.upgrades, function(index, upgrades)
                {
                    var upgradeAmount = result.currency.prefix + upgrades.amount + result.currency.suffix;
                    ResellersCenter_ClientOrders.addRowToDetailsTable('upgrade', upgrades.description, "", upgrades.billingcycle, upgradeAmount, upgrades.status);
                });
            });
                
            
            $("#RCOrderDetails").modal("show");
        });
    },
    
    openDeleteForm: function()
    {
        $(".openDeleteOrder").unbind("click");
        $(".openDeleteOrder").on("click", function(){
            ResellersCenter_ClientOrders.activeOrder = $(this).data("orderid"),
            $("#RCDeleteOrder").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deleteOrder|Orders", {orderid: ResellersCenter_ClientOrders.activeOrder}, function(result)
        {
            //Refresh table
            ResellersCenter_ClientOrders.table.draw();
            $("#RCDeleteOrder").modal("hide");
        });
    },
    
    openAcceptForm: function()
    {
        $(".openAcceptOrder").unbind("click");
        $(".openAcceptOrder").on("click", function()
        {
            if(typeof($(this).data("orderid")) !== 'undefined') 
            {
                ResellersCenter_ClientOrders.activeOrder = $(this).data("orderid");
                ResellersCenter_ClientOrders.activeOrderInvoiceId = $(this).data("invoiceid");
            }
            
            //add invoice url
            if($("#RCAcceptOrder .order-invoiceid"))
            {
                var invoiceHref = 'viewinvoice.php?id=' + ResellersCenter_ClientOrders.activeOrderInvoiceId;
                $("#RCAcceptOrder .order-invoiceid").html("{/literal}{$MGLANG->T('orders', 'accept','unpaid', 'invoicenum')}{literal}"+ResellersCenter_ClientOrders.activeOrderInvoiceId);
                $("#RCAcceptOrder .order-invoiceid").attr("href", invoiceHref);
            }
            
            if($(this).data("paymentstatus") == 'Unpaid') {
                $(".warningNote").show();
            }
            else {
                $(".warningNote").hide();
            }
            
            $("#RCAcceptOrder").modal("show");
        });
    },
    
    submitAcceptForm: function()
    {
        JSONParser.request("acceptOrder|Orders", {orderid: ResellersCenter_ClientOrders.activeOrder}, function(result)
        {
            //Refresh table
            ResellersCenter_ClientOrders.table.draw();
            $("#RCAcceptOrder").modal("hide");
        });
    },
    
    openCancelForm: function()
    {
        $(".openCancelOrder").unbind("click");
        $(".openCancelOrder").on("click", function(){
            $("#RCCancelOrder").modal("show");
        });
    },
    
    submitCancelForm: function()
    {
        JSONParser.request("cancelOrder|Orders", {orderid: ResellersCenter_ClientOrders.activeOrder}, function(result)
        {
            //Refresh table
            ResellersCenter_ClientOrders.table.draw();
            $("#RCCancelOrder").modal("hide");
        });
    },
    
    openFraudForm: function()
    {
        $(".openFraudOrder").unbind("click");
        $(".openFraudOrder").on("click", function(){
            $("#RCFraudOrder").modal("show");
        });
    },
    
    submitFraudForm: function()
    {
        JSONParser.request("fraudOrder|Orders", {orderid: ResellersCenter_ClientOrders.activeOrder}, function(result)
        {
            //Refresh table
            ResellersCenter_ClientOrders.table.draw();
            $("#RCFraudOrder").modal("hide");
        });
    },
  
  
    loadTable: function()
    {
        this.table = $("#ordersTable").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=orders&mg-action=getOrderForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientOrders.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "ordernum",     orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0, visible: false },
                { data: "paymentmethod",orderable: true, sortable: false, targets: 0 },
                { data: "amount",       orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "paymentstatus",orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
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
    
    addRowToDetailsTable: function(type, productName, domain, billingcycle, amount, status)
    {
        var clone = $("#RCOrderDetails [data-prototype='']").clone();
         if(type == 'hosting'){
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->T('orders','type','hosting')}{literal}");
            });
        }
        else if(type == 'addon') {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->T('orders','type','addon')}{literal}");
            });
        }
        else if(type == 'domain') {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->T('orders','type','domain')}{literal}");
            });
        }
        else if(type == 'upgrade') {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->T('orders','type','upgrade')}{literal}");
            });
        }
        
        clone.html(function(index, text) {
            if(domain.length > 0) {
                domain = ' - ' + domain;
            }
            return text.replace(/(\+description\+)/g, productName + domain);
        });
        
        clone.html(function(index, text) {
            if($.isNumeric(billingcycle)){
                if(billingcycle == 1) {
                    billingcycle = billingcycle + " {/literal}{$MGLANG->T('orders','registerperiod','year')}{literal}";
                }
                else {
                    billingcycle = billingcycle + " {/literal}{$MGLANG->T('orders','registerperiod','years')}{literal}";
                }
            }
            return text.replace(/(\+billingcycle\+)/g, billingcycle);
        });
        clone.html(function(index, text) 
        {
            if(amount.indexOf("$") !== -1) {
                amount = "$" + amount;
            }
            
            return text.replace(/(\+amount\+)/g, amount);
        });
        clone.html(function(index, text) {
            return text.replace(/(\+status\+)/g, status);
        });


        clone.removeAttr("data-prototype");
        $("#RCOrderDetails form table").append(clone);
    },
}
ResellersCenter_ClientOrders.init();
{/literal}
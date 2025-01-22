{literal}
var ResellersCenter_Search = {

    table: null,
    clientId: null,
    itemId: null,
    activeOrderInvoiceId: null,
    activeOrder: null,
    filterKey: null,

    init: function()
    {
        this.dataTableSearch();

        RC_TwentyOne_Helper.twentyOneLiSelector();
    },

    refreshHandlers: function()
    {
        this.loginAsClientHandler();
        this.createOrderHandler();
        this.loginAndShowDomainHandler();
        this.loginAndShowServiceHandler();
        this.loginAndShowAddonHandler();
        this.loginAndShowTicketHandler();
        this.openClientDetails();
        this.openDeleteClientModal();
        this.openDetailsTicket();
        this.openDeleteServiceModal();
        this.openDeleteAddonModal();
        this.openDeleteDomainModal();
        this.openSuspendServiceModal();
        this.openUnsuspendServiceModal();
        this.openDetailsOrderModal();
        this.openDetailsInvoiceModal();
        this.openEditInvoiceModal();
    },

    loginAsClientHandler: function()
    {
        $(".loginAsClientBtn").unbind("click");
        $(".loginAsClientBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAsClient&clientid="+clientid;
        });
    },

    createOrderHandler: function()
    {
        $(".openAddOrderClient").unbind("click");
        $(".openAddOrderClient").on("click", function()
        {
            var clientid = $(this).data("clientid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=createOrder&clientid="+clientid;
        });
    },

    loginAndShowDomainHandler: function()
    {
        $(".loginAndShowDomainBtn").unbind("click");
        $(".loginAndShowDomainBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");
            var domainid = $(this).data("domainid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAndShowDomain&clientid="+clientid+"&domainid="+domainid;
        });
    },

    loginAndShowServiceHandler: function()
    {
        $(".loginAndShowServiceBtn").unbind("click");
        $(".loginAndShowServiceBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");
            var serviceid = $(this).data("serviceid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAndShowService&clientid="+clientid+"&serviceid="+serviceid;
        });
    },

    loginAndShowAddonHandler: function()
    {
        $(".loginAndShowAddonBtn").unbind("click");
        $(".loginAndShowAddonBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");
            var addonid = $(this).data("addonid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAndShowAddon&clientid="+clientid+"&addonid="+addonid;
        });
    },

    loginAndShowTicketHandler: function()
    {
        $(".loginAndShowTicketBtn").unbind("click");
        $(".loginAndShowTicketBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");
            var ticketid = $(this).data("ticketid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAndShowTicket&clientid="+clientid+"&ticketid="+ticketid;
        });
    },

    openClientDetails: function()
    {
        $(".openDetailsClient").unbind("click");
        $(".openDetailsClient").on("click", function(){
            var clientid = $(this).data("clientid");
            window.location.href = 'index.php?m=ResellersCenter&mg-page=clients&mg-action=details&cid='+clientid;
        });
    },

    openDeleteClientModal: function()
    {
        $(".openDeleteClient").unbind("click");
        $(".openDeleteClient").on("click", function()
        {
            ResellersCenter_Search.clientId = $(this).data("clientid");
            $("#RCDeleteClient").modal("show");
        });
    },

    openDeleteServiceModal: function()
    {
        $(".openDeleteService").unbind("click");
        $(".openDeleteService").on("click", function()
        {
            ResellersCenter_Search.itemId = $(this).data("serviceid");
            $("#RCServicesTabDelete").modal("show");
        });
    },

    openDetailsTicket: function()
    {
        $(".openDetailsTicket").unbind("click");
        $(".openDetailsTicket").on("click", function(){
            var ticketid = $(this).data("ticketid");
            window.location.href = "index.php?m=ResellersCenter&mg-page=tickets&mg-action=details&tid="+ticketid;
        });
    },

    submitDeleteClientForm: function()
    {
        JSONParser.request("delete|Clients", {clientid: ResellersCenter_Search.clientId}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCDeleteClient").modal("hide");
        });
    },

    submitDeleteServiceForm: function()
    {
        JSONParser.request("terminateService|Orders", {relid: ResellersCenter_Search.itemId, type: 'hosting'}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCServicesTabDelete").modal("hide");
        });
    },

    openDeleteAddonModal: function()
    {
        $(".openDeleteAddon").unbind("click");
        $(".openDeleteAddon").on("click", function()
        {
            ResellersCenter_Search.itemId = $(this).data("addonid");
            $("#RCAddonsTabDelete").modal("show");
        });
    },

    submitDeleteAddonForm: function()
    {
        JSONParser.request("terminateService|Orders", {relid: ResellersCenter_Search.itemId, type: 'addon'}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCAddonsTabDelete").modal("hide");
        });
    },

    openDeleteDomainModal: function()
    {
        $(".openDeleteDomain").unbind("click");
        $(".openDeleteDomain").on("click", function()
        {
            ResellersCenter_Search.itemId = $(this).data("domainid");
            $("#RCDomainsTabDelete").modal("show");
        });
    },

    submitDeleteDomainForm: function()
    {
        JSONParser.request("terminateService|Orders", {relid: ResellersCenter_Search.itemId, type: 'domain'}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCDomainsTabDelete").modal("hide");
        });
    },

    openSuspendServiceModal: function()
    {
        $(".openSuspendService").unbind("click");
        $(".openSuspendService").on("click", function()
        {
            ResellersCenter_Search.itemId = $(this).data("serviceid");
            $("#RCSuspendService").modal("show");
        });
    },

    openUnsuspendServiceModal: function()
    {
        $(".openUnsuspendService").unbind("click");
        $(".openUnsuspendService").on("click", function()
        {
            ResellersCenter_Search.itemId = $(this).data("serviceid");
            $("#RCUnsuspendService").modal("show");
        });
    },

    submitSuspendServiceForm: function()
    {
        state = 'suspend';
        JSONParser.request("suspend|Orders", {relid: ResellersCenter_Search.itemId, state: state}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCSuspendService").modal("hide");
        });
    },

    submitUnsuspendServiceForm: function()
    {
        state = 'unsuspend';
        JSONParser.request("suspend|Orders", {relid: ResellersCenter_Search.itemId, state: state}, function(result){
            self.loadTable(ResellersCenter_Search.filterKey );
            $("#RCUnsuspendService").modal("hide");
        });
    },

    openDetailsOrderModal: function()
    {
        $(".openDetailsOrder").unbind("click");
        $(".openDetailsOrder").on("click", function()
        {
            ResellersCenter_Search.activeOrder = $(this).data("orderid");
            var isAwaitingReseller = $(this).parents("tr").hasClass("awaiting-reseller");

            //reset table
            $("#RCOrderDetails").find("tbody").first().empty();

            var orderid = $(this).data("orderid");
            var paymentstatus = $(this).data("paymentstatus");
            JSONParser.request("getOrderDetails|Orders", {orderid: orderid}, function(result)
            {
                ResellersCenter_Search.activeOrderInvoiceId = result.invoiceid;
                if($("#RCOrderDetails .order-invoiceid"))
                {
                    var invoiceHref = 'viewinvoice.php?id=' + ResellersCenter_Search.activeOrderInvoiceId;
                    $("#RCOrderDetails .order-invoiceid").html("{/literal}{$MGLANG->absoluteT('addonCA','orders','accept','unpaid', 'invoicenum')}{literal}"+ResellersCenter_Search.activeOrderInvoiceId);
                    $("#RCOrderDetails .order-invoiceid").attr("href", invoiceHref);
                }

                //Set payment status
                if(isAwaitingReseller || result.status != 'Pending'){
                    $("#RCOrderDetails .openAcceptOrder").hide();
                }
                else {
                    $("#RCOrderDetails .openAcceptOrder").show();
                    $("#RCOrderDetails .openAcceptOrder").data("paymentstatus", paymentstatus);
                }

                //Hostings && Addons
                if(result.hostings)
                {
                    $.each(result.hostings, function(index, hosting)
                    {
                        var productAmount = result.currency.prefix + hosting.firstpaymentamount + result.currency.suffix;
                        ResellersCenter_Search.addRowToDetailsTable('hosting', hosting.product.name, hosting.domain, hosting.billingcycle, productAmount, hosting.domainstatus);
                    });
                }

                //Addons
                if(result.hostingAddons)
                {
                    $.each(result.hostingAddons, function(index, hostingAddon)
                    {
                        var addonAmount = result.currency.prefix + (Math.round((parseFloat(hostingAddon.recurring) + parseFloat(hostingAddon.setupfee)) * 100) / 100).toFixed(2) + result.currency.suffix;
                        ResellersCenter_Search.addRowToDetailsTable('addon', hostingAddon.addon.name, '', hostingAddon.billingcycle, addonAmount, hostingAddon.status);
                    });
                }

                //Domains
                if(result.domains)
                {
                    $.each(result.domains, function(index, domain)
                    {
                        var domainAmount = result.currency.prefix + domain.firstpaymentamount + result.currency.suffix;
                        if(domain.typeraw == "renewal")
                        {
                            domainAmount = result.currency.prefix + domain.recurringamount + result.currency.suffix;
                        }

                        ResellersCenter_Search.addRowToDetailsTable('domain', domain.type, domain.domain, domain.registrationperiod, domainAmount, domain.status);
                    });
                }

                //Upgrades
                if(result.upgrades)
                {
                    $.each(result.upgrades, function(index, upgrades)
                    {
                        var upgradeAmount = result.currency.prefix + upgrades.amount + result.currency.suffix;
                        ResellersCenter_Search.addRowToDetailsTable('upgrade', upgrades.description, "", upgrades.billingcycle, upgradeAmount, upgrades.status);
                    });
                }
            });

            $("#RCOrderDetails").modal("show");
            ResellersCenter_Search.openAcceptForm();
        });
    },

    addRowToDetailsTable: function(type, productName, domain, billingcycle, amount, status)
    {
        var clone = $("#RCOrderDetails [data-prototype='']").clone();
        if(type == 'hosting'){
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->absoluteT('addonCA','orders','details','type','hosting')}{literal}");
            });
        }
        else if(type == 'addon') {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->absoluteT('addonCA','orders','details','type','addon')}{literal}");
            });
        }
        else if(type == 'domain') {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->absoluteT('addonCA','orders','details','type','domain')}{literal}");
            });
        }
        else if(type == 'upgrade')
        {
            clone.html(function(index, text) {
                return text.replace(/(\+type\+)/g, "{/literal}{$MGLANG->absoluteT('addonCA','orders','details','type','upgrade')}{literal}");
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
                    billingcycle = billingcycle + " {/literal}{$MGLANG->absoluteT('addonCA','orders','details','registerperiod','year')}{literal}";
                }
                else {
                    billingcycle = billingcycle + " {/literal}{$MGLANG->absoluteT('addonCA','orders','details','registerperiod','years')}{literal}";
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

    openAcceptForm: function()
    {
        $(".openAcceptOrder").unbind("click");
        $(".openAcceptOrder").on("click", function()
        {
            //if accept was opened from
            if(typeof($(this).data("orderid")) !== 'undefined')
            {
                ResellersCenter_Search.activeOrder = $(this).data("orderid");
                ResellersCenter_Search.activeOrderInvoiceId = $(this).data("invoiceid");
            }

            //add invoice url
            if($("#RCAcceptOrder .order-invoiceid"))
            {
                var invoiceHref = 'viewinvoice.php?id=' + ResellersCenter_Search.activeOrderInvoiceId;
                $("#RCAcceptOrder .order-invoiceid").html("{/literal}{$MGLANG->absoluteT('accept','unpaid', 'invoicenum')}{literal}"+ResellersCenter_Search.activeOrderInvoiceId);
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
        JSONParser.request("acceptOrder|Orders", {orderid: ResellersCenter_Search.activeOrder}, function(result)
        {
            $("#RCAcceptOrder").modal("hide");
            self.loadTable(ResellersCenter_Search.filterKey );
        });
    },

    submitCancelForm: function()
    {
        JSONParser.request("cancelOrder|Orders", {orderid: ResellersCenter_Search.activeOrder}, function(result)
        {
            $("#RCCancelOrder").modal("hide");
            Rself.loadTable(ResellersCenter_Search.filterKey );
        });
    },

    submitDeleteForm: function()
    {
        JSONParser.request("deleteOrder|Orders", {orderid: ResellersCenter_Search.activeOrder}, function(result)
        {
            $("#RCDeleteOrder").modal("hide");
            self.loadTable(ResellersCenter_Search.filterKey );
        });
    },

    submitFraudForm: function()
    {
        JSONParser.request("fraudOrder|Orders", {orderid: ResellersCenter_Search.activeOrder}, function(result)
        {
            $("#RCFraudOrder").modal("hide");
            self.loadTable(ResellersCenter_Search.filterKey );
        });
    },

    openDetailsInvoiceModal: function()
    {
        $(".openDetailsInvoice").unbind("click");
        $(".openDetailsInvoice").on("click", function()
        {
            //Reset Modal
            $("#RCInvoiceDetails tbody tr:not([data-prototype=''])").remove();
            $("#RCInvoiceDetails form input").val('');

            var invoiceid = $(this).data("invoiceid");
            var type = 'whmcs';
            ResellersCenter_Search.activeInvoice = invoiceid;
            JSONParser.request("getInvoiceDetails|Invoices", {invoiceid: invoiceid, type: type}, function(result)
            {
                $("#RCInvoiceDetails [name='invoice[invoiceid]']").val(result.invoice.id);
                $("#RCInvoiceDetails [data-name='invoice[date]']").text(result.invoice.date);
                $("#RCInvoiceDetails [data-name='invoice[duedate]']").text(result.invoice.duedate);
                $("#RCInvoiceDetails [data-name='invoice[totaldue]']").text(result.currency.prefix + result.invoice.total + result.currency.suffix);

                $.each(result.items, function(index, item)
                {
                    var clone = $("#RCInvoiceDetails [data-prototype='']").clone();
                    clone.html(function(index, text) {
                        return text.replace(/(\+itemid\+)/g, item.id);
                    });

                    clone.find("[data-name^='invoice[itemdescription]']").text(item.description);
                    clone.find("[data-name^='invoice[itemamount]']").text(result.currency.prefix + item.amount + result.currency.suffix);
                    if(item.taxed == 1) {
                        clone.find("[name^='invoice[itemtaxed]']").attr("checked", true);
                    }

                    clone.removeAttr("data-prototype");
                    $("#RCInvoiceDetails form table").append(clone);
                });

                $("#RCInvoiceDetails .invoice-status").text(result.invoice.status);
                $("#RCInvoiceDetails .invoice-status").addClass("invoice-status-" + result.invoice.status);
                $("#RCInvoiceDetails .invoice-paymentmethod").text(result.invoice.paymentmethod);
            });

            $("#RCInvoiceDetails").modal("show");
        });
    },

    openEditInvoiceModal: function()
    {
        $(".openEditInvoice").unbind("click");
        $(".openEditInvoice").on("click", function()
        {
            var invoiceid = $(this).data("invoiceid");
            var type = 'rc';

            ResellersCenter_InvoiceEdit.init(invoiceid, type);
        });
    },

    resetTable: function()
    {
        if (ResellersCenter_Search.table) {
            ResellersCenter_Search.table.destroy();
        }

        dataSet = [];

        ResellersCenter_Search.table = $("#RCSearch table").DataTable({
            data: dataSet,
            searching: false,
            bProcessing: false,
            bServerSide: false,
            bPaginate: false,
            bInfo : false,
            columns: [
                { data: "id",           orderable: true, sortable: false, targets: 0 },
                { data: "type",         orderable: true, sortable: false, targets: 0 },
                { data: "name",         orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
            ],
            oLanguage: {
                sEmptyTable: "{/literal}{$MGLANG->T('table','search','initInfo')}{literal}",
            }
        });
    },

    loadTable: function(filter)
    {
        if (ResellersCenter_Search.table) {
            ResellersCenter_Search.table.destroy();
        }

        ResellersCenter_Search.table = $("#RCSearch table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: false,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=search&mg-action=getSearchForTable&json=1&datatable=1&filter=" + filter,
            fnDrawCallback: function(){
                ResellersCenter_Search.refreshHandlers();
            },
            columns: [
                { data: "id",           orderable: true, sortable: false, targets: 0 },
                { data: "type",         orderable: true, sortable: false, targets: 0 },
                { data: "name",         orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
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

    dataTableSearch: function() {
        self = this;
        $(".globalSearch input").keypress(function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                var filter = $(this).val();
                filter = filter.trim();
                ResellersCenter_Search.filterKey = filter;
                if (filter.length > 0) {
                    self.loadTable(filter);
                } else {
                    self.resetTable();
                }
            }
        });
    },

};
ResellersCenter_Search.init();
{/literal}
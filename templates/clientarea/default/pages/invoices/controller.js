{literal}
var ResellersCenter_Invoices = 
{
    activeInvoice: null,
    whmcsInvoicesTable: null,
    rcInvoicesTable: null,
    
    init: function()
    {
        this.loadWHMCSInvoicesTable();
        this.loadRCInvoicesTable();
        this.downloadPdfHandler();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        this.openDetailsModal();
        this.openEditModal();
    },
  
    openDetailsModal: function()
    {
        $(".openDetailsInvoice").unbind("click");
        $(".openDetailsInvoice").on("click", function()
        {
            //Reset Modal
            $("#RCInvoiceDetails tbody tr:not([data-prototype=''])").remove();
            $("#RCInvoiceDetails form input").val('');
            
            var invoiceid = $(this).data("invoiceid");
            var type = $(this).parents("table").data("invoicetype");
            ResellersCenter_Invoices.activeInvoice = invoiceid;
            JSONParser.request("getInvoiceDetails", {invoiceid: invoiceid, type: type}, function(result)
            {
                $("#RCInvoiceDetails .modal-title").html("{/literal}{$MGLANG->T('invoices','edit','title')}{literal}" + " (#" + (result.invoice.invoicenum ? result.invoice.invoicenum.toString() : result.invoice.id.toString()) + ")");

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

                $("#RCInvoiceDetails .invoice-status").text(result.invoice.statusTranslated);
                $("#RCInvoiceDetails .invoice-status").addClass("invoice-status-" + result.invoice.status);
                $("#RCInvoiceDetails .invoice-paymentmethod").text(result.invoice.paymentmethod);
            });
            
            $("#RCInvoiceDetails").modal("show");
        });
    },
    
    openEditModal: function()
    {
        $(".openEditInvoice").unbind("click");
        $(".openEditInvoice").on("click", function()
        {
            var invoiceid = $(this).data("invoiceid");
            var type = $(this).parents("table").data("invoicetype");

            ResellersCenter_InvoiceEdit.init(invoiceid, type);
        });
    },

    openCreateModal: function()
    {
        ResellersCenter_InvoiceCreate.init();
    },

    loadWHMCSInvoicesTable: function()
    {
        this.whmcsInvoicesTable = $("#RCInvoicesWHMCS table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=invoices&mg-action=getWHMCSInvoicesForTable",
            fnDrawCallback: function(){
                ResellersCenter_Invoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "invoicenum",       orderable: true, sortable: false, targets: 0 },
                { data: "client",   orderable: true, sortable: false, targets: 0 },
                { data: "date",     orderable: true, sortable: false, targets: 0 },
                { data: "duedate",  orderable: true, sortable: false, targets: 0 },
                { data: "total",    orderable: true, sortable: false, targets: 0 },
                { data: "paymentmethod",   orderable: true, sortable: false, targets: 0 },
                { data: "status",   orderable: true, sortable: false, targets: 0 },
                { data: "actions",  orderable: false, sortable: false, targets: 0 },
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
        
        this.customDataTableSearch();
    },
    
    loadRCInvoicesTable: function()
    {
        this.rcInvoicesTable = $("#RCInvoicesRC table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=invoices&mg-action=getRCInvoicesForTable",
            fnDrawCallback: function(){
                ResellersCenter_Invoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "invoicenum",       orderable: true, sortable: false, targets: 0 },
                { data: "client",   orderable: true, sortable: false, targets: 0 },
                { data: "date",     orderable: true, sortable: false, targets: 0 },
                { data: "duedate",  orderable: true, sortable: false, targets: 0 },
                { data: "total",    orderable: true, sortable: false, targets: 0 },
                { data: "paymentmethod",   orderable: true, sortable: false, targets: 0 },
                { data: "status",   orderable: true, sortable: false, targets: 0 },
                { data: "actions",  orderable: false, sortable: false, targets: 0 },
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
        
        this.customDataTableSearch();
    },
    
    showSearch: function()
    {
        if($(".invoicesListSearch").is(":visible")) {
            $(".invoicesListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".invoicesListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function() {
        var timer = null;
        $(".invoicesListSearch input").keyup(function () {
            clearTimeout(timer);

            var filter = $(this).val();
            timer = setTimeout(function () {
                ResellersCenter_Invoices.whmcsInvoicesTable.search(filter).draw();
                ResellersCenter_Invoices.rcInvoicesTable.search(filter).draw();
            }, 500);
        });
    },
    export: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType=Invoices');
    },

    downloadPdf: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=invoices&mg-action=processDownloadPdf&invoiceid='+ResellersCenter_Invoices.activeInvoice);
    },

    downloadPdfHandler: function()
    {
        $(".downloadPdfBtn").unbind();
        $(".downloadPdfBtn").on("click", function(event)
        {
            event.preventDefault();
            ResellersCenter_Invoices.downloadPdf();
        });
    }
}
ResellersCenter_Invoices.init();
{/literal}
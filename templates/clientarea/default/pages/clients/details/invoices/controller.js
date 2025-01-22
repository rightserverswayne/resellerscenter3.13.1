{literal}
var ResellersCenter_ClientsInvoices = 
{
    rcInvoicesTable: null,
    whmcsInvoicesTable: null,
    activeInvoice: null,
    
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
            ResellersCenter_ClientsInvoices.activeInvoice = invoiceid;
            JSONParser.request("getInvoiceDetails|invoices", {invoiceid: invoiceid}, function(result)
            {
                $("#RCInvoiceDetails .modal-title").html("{/literal}{$MGLANG->T('invoices','edit','title')}{literal}" + " (#" + (result.invoice.invoicenum ? result.invoice.invoicenum.toString() : result.invoice.id.toString()) + ")");

                $("#RCInvoiceDetails [name='invoice[invoiceid]']").val(result.invoice.id);
                $("#RCInvoiceDetails [name='invoice[date]']").html(result.invoice.date);
                $("#RCInvoiceDetails [name='invoice[duedate]']").html(result.invoice.duedate);
                
                $.each(result.items, function(index, item)
                {
                    var clone = $("#RCInvoiceDetails [data-prototype='']").clone();
                    clone.html(function(index, text) {
                        return text.replace(/(\+itemid\+)/g, item.id);
                    });

                    clone.find("[name^='invoice[itemdescription]']").html(item.description);
                    clone.find("[name^='invoice[itemamount]']").html(item.amount);
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
  
    loadWHMCSInvoicesTable: function()
    {
        this.whmcsInvoicesTable = $("#RCClientsInvoicesWHMCS table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=invoices&mg-action=getWHMCSInvoicesForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientsInvoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "invoicenum", orderable: true, sortable: false, targets: 0 },
                { data: "client",     orderable: true, sortable: false, targets: 0, visible: false },
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
    },
    
    loadRCInvoicesTable: function()
    {
        this.rcInvoicesTable = $("#RCClientsInvoicesRC table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=invoices&mg-action=getRCInvoicesForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientsInvoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "invoicenum",   orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0, visible: false },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "duedate",      orderable: true, sortable: false, targets: 0 },
                { data: "total",        orderable: true, sortable: false, targets: 0 },
                { data: "paymentmethod",   orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
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

    downloadPdf: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=invoices&mg-action=processDownloadPdf&invoiceid='+ResellersCenter_ClientsInvoices.activeInvoice);
    },

    downloadPdfHandler: function()
    {
        $(".downloadPdfBtn").unbind();
        $(".downloadPdfBtn").on("click", function(event)
        {
            event.preventDefault();
            ResellersCenter_ClientsInvoices.downloadPdf();
        });
    }
}
ResellersCenter_ClientsInvoices.init();
{/literal}
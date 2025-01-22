{literal}
var RC_ResellersInvoices = {
    
    rcTable: null,
    whmcsTable: null,
    
    init: function()
    {
        RC_ResellersInvoices.loadRCTable();
        RC_ResellersInvoices.loadWHMCSTable();
    },
    
    refreshHandlers: function()
    {
        this.openEditModal();
    },
    
    openEditModal: function()
    {
        //WHMCS invoices
        $("#whmcsInvoicesList .openInvoiceEdit").unbind("click");
        $("#whmcsInvoicesList .openInvoiceEdit").on("click", function()
        {
            var invoiceid = $(this).data("invoiceid");
            location.href = "invoices.php?action=edit&id="+invoiceid;
        });
        
        //RC invoices
        $("#rcInvoicesList .openInvoiceEdit").unbind("click");
        $("#rcInvoicesList .openInvoiceEdit").on("click", function()
        {
            //Reset Modal
            $("#invoiceEdit tbody tr:not([data-prototype=''])").remove();
            $("#invoiceEdit form input").val('');
            
            var invoiceid = $(this).data("invoiceid");
            RC_ResellersInvoices.activeInvoice = invoiceid;
            JSONParser.request("getRCInvoiceDetails|invoices", {invoiceid: invoiceid}, function(result)
            {
                $("#invoiceEdit [name='invoice[invoiceid]']").val(result.invoice.id);
                $("#invoiceEdit [name='invoice[date]']").val(result.invoice.date);
                $("#invoiceEdit [name='invoice[duedate]']").val(result.invoice.duedate);
                
                $.each(result.items, function(index, item)
                {
                    var clone = $("#invoiceEdit [data-prototype='']").clone();
                    clone.html(function(index, text) {
                        return text.replace(/(\+itemid\+)/g, item.id);
                    });

                    clone.find("[name^='invoice[itemdescription]']").val(item.description);
                    clone.find("[name^='invoice[itemamount]']").val(item.amount);
                    if(item.taxed == 1) {
                        clone.find("[name^='invoice[itemtaxed]']").attr("checked", true);
                    }
                    
                    clone.removeAttr("data-prototype");
                    $("#invoiceEdit form table").append(clone);
                });
            });
            
            RC_ResellersInvoices.enableDatapicker();
            $("#invoiceEdit").modal("show");
        });
    },
    
    submitEditForm: function()
    {
        var form = $("#invoiceEdit form").serialize();
        
        JSONParser.request("updateRCInvoice|Invoices", form, function()
        {
            RC_ResellersInvoices.rcTable.ajax.reload(null, false);
            $("#invoiceEdit").modal("hide");
        });
    },
 
    loadRCTable: function()
    {
        RC_ResellersInvoices.rcTable = $("#rcInvoicesList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=invoices&mg-action=getRcInvoicesForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersInvoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#rcInvoicesList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "invoicenum",   orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "duedate",      orderable: true, sortable: false, targets: 0 },
                { data: "total",        orderable: true, sortable: false, targets: 0 },
                { data: "paymentmethod", orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
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
        
        RC_ResellersInvoices.customDataTableSearch();
    },
    
    loadWHMCSTable: function()
    {
        RC_ResellersInvoices.whmcsTable = $("#whmcsInvoicesList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=invoices&mg-action=getWhmcsInvoicesForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersInvoices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#whmcsInvoicesList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "invoicenum",   orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "duedate",      orderable: true, sortable: false, targets: 0 },
                { data: "total",        orderable: true, sortable: false, targets: 0 },
                { data: "paymentmethod", orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
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
        
        RC_ResellersInvoices.customDataTableSearch();
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
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#invoicesListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_ResellersInvoices.rcTable.search(filter).draw();
                RC_ResellersInvoices.whmcsTable.search(filter).draw();
            }, 200);
        });
    },
    
    enableDatapicker: function()
    {
        $("#invoiceEdit [name='invoice[date]']").datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $("#invoiceEdit [name='invoice[duedate]']").datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false //Important! See issue #1075
        });

        $("#invoiceEdit [name='invoice[date]']").on("dp.change", function (e) {
            $("#invoiceEdit [name='invoice[duedate]']").data("DateTimePicker").minDate(e.date);
        });
        $("#invoiceEdit [name='invoice[duedate]']").on("dp.change", function (e) {
            $("#invoiceEdit [name='invoice[date]']").data("DateTimePicker").maxDate(e.date);
        });
    },
}
RC_ResellersInvoices.init();
{/literal}
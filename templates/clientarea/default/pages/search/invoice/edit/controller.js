{literal}
var ResellersCenter_InvoiceEdit = 
{
    transactionsTable: null,
    invoiceid: null,
    
    init: function(invoiceid, type)
    {
        this.invoiceid = invoiceid;
        
        this.enableDatapicker();
        this.loadInvoiceData(invoiceid, type);
        this.loadTransactionsTable();

        this.markPaidHandler();
        this.markUnpaidHandler();
        this.markCancelledHandler();
        this.publishHandler();
        this.publishAndSendHandler();
        this.downloadPdfHandler();

        $("#RCInvoiceEdit").modal("show");
    },
    
    loadInvoiceData: function(invoiceid, type)
    {
        //Reset Modal
        $("#RCInvoiceEdit").find(".has-error").removeClass("has-error");
        $("#RCInvoiceEdit form tbody tr:not(.rc-invoice-summary)").remove();
        $("#RCInvoiceEdit form input").val('');
        $("#RCInvoiceEdit .rc-invoice-summary-tax1").parent().hide();
        $("#RCInvoiceEdit .rc-invoice-summary-tax2").parent().hide();
        $("#RCInvoiceEdit .markPaidBtn").show();
        $("#RCInvoiceEdit .markUnpaidBtn").show();
        $("#RCInvoiceEdit .markCancelledBtn").show();
        $("#RCInvoiceEdit .publishBtn").hide();
        $("#RCInvoiceEdit .publishAndSendBtn").hide();
        $("#RCInvoiceEdit .invoice-status").removeClass("invoice-status-Paid invoice-status-Unpaid invoice-status-Cancelled");
        $("#RCInvoiceEdit .invoice-title-number").html("");

        //Set current date for transaction
        var today = moment().format('YYYY-MM-DD HH:mm:ss');
        $("#RCInvoiceEdit [name='payment[date]']").val(today);
        $("#RCInvoiceEdit [name='payment[fees]']").val(0);
        
        JSONParser.request("getInvoiceDetails|Invoices", {invoiceid: invoiceid, type: type}, function(result)
        {
            if(result.invoice.status == "Draft")
            {
                $("#RCInvoiceEdit .markPaidBtn").hide();
                $("#RCInvoiceEdit .markUnpaidBtn").hide();
                $("#RCInvoiceEdit .markCancelledBtn").hide();

                $("#RCInvoiceEdit .publishBtn").show();
                $("#RCInvoiceEdit .publishAndSendBtn").show();
            }

            //Set invoice edit modal title
            $("#RCInvoiceEdit .modal-title").html("{/literal}{$MGLANG->absoluteT('addonCA','invoices','edit','title')}{literal}" + " (#" + (result.invoice.invoicenum ? result.invoice.invoicenum.toString() : result.invoice.id.toString()) + ")");

            var prefix = result.currency.prefix;
            var suffix = result.currency.suffix;

            //Invoice Details
            $("#RCInvoiceEdit [name='invoice[invoiceid]']").val(result.invoice.id);
            $("#RCInvoiceEdit [name='invoice[date]']").val(result.invoice.date);
            $("#RCInvoiceEdit [name='invoice[duedate]']").val(result.invoice.duedate);
            $("#RCInvoiceEdit [name='payment[amount]']").val(result.amounttopay);
            $("#RCInvoiceEdit [name='payment[gateway]']").val(result.invoice.paymentmethod);
            
            $("#RCInvoiceEdit .invoice-status").text(result.invoice.status);
            $("#RCInvoiceEdit .invoice-status").addClass("invoice-status-" + result.invoice.status);
            $("#RCInvoiceEdit .invoice-paymentmethod").text(result.invoice.paymentmethod);
            $("#RCInvoiceEdit .mark"+result.invoice.status+"Btn").hide();

            //Invoice Summary
            $("#RCInvoiceEdit .rc-invoice-summary-subtotal").text(prefix + result.invoice.subtotal + suffix);
            if(result.invoice.taxrate !== '0.00')
            {
                $("#RCInvoiceEdit .rc-invoice-summary-tax1").parent().show();
                $("#RCInvoiceEdit .rc-invoice-summary-tax1").text(result.invoice.taxrate + "% {/literal}{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'tax')}{literal} " + result.taxes.tax1.name + ":");
                $("#RCInvoiceEdit .rc-invoice-summary-tax1amount").text(prefix + result.invoice.tax + suffix);
            }
            
            if(result.invoice.taxrate2 !== '0.00')
            {
                $("#RCInvoiceEdit .rc-invoice-summary-tax2").parent().show();
                $("#RCInvoiceEdit .rc-invoice-summary-tax2").text(result.invoice.taxrate2 + "% {/literal}{$MGLANG->absoluteT('addonCA','invoices','edit', 'details', 'tax')}{literal} " + result.taxes.tax2.name + ":");
                $("#RCInvoiceEdit .rc-invoice-summary-tax2amount").text(prefix + result.invoice.tax2 + suffix);
            }

            $("#RCInvoiceEdit .rc-invoice-summary-total").text(prefix + result.invoice.total + suffix);
            $("#RCInvoiceEdit .rc-invoice-summary-credit").text(prefix + result.invoice.credit + suffix);
                        
            $.each(result.items, function(index, item)
            {
                var clone = $("#RCInvoiceEditDetails [data-prototype='']").clone();
                clone.html(function(index, text) {
                    return text.replace(/(\+itemid\+)/g, item.id);
                });

                clone.find("[name^='invoice[itemdescription]']").val(item.description);
                clone.find("[name^='invoice[itemamount]']").before(prefix + " ");
                clone.find("[name^='invoice[itemamount]']").after(suffix);
                clone.find("[name^='invoice[itemamount]']").val(item.amount);
                if(item.taxed == 1) {
                    clone.find("[name^='invoice[itemtaxed]']").attr("checked", true);
                }

                clone.removeAttr("data-prototype");
                $("#RCInvoiceEdit form tbody tr").first().before(clone);
            });            
        });
    },
    
    markPaidHandler: function()
    {
        $(".markPaidBtn").unbind();
        $(".markPaidBtn").on("click", function(event) 
        {
            event.preventDefault();            
            ResellersCenter_InvoiceEdit.updateInvoiceStatus("Paid");
        });
    },
    
    markUnpaidHandler: function()
    {
        $(".markUnpaidBtn").unbind();
        $(".markUnpaidBtn").on("click", function(event) 
        {
            event.preventDefault();
            ResellersCenter_InvoiceEdit.updateInvoiceStatus("Unpaid");
        });
    },
    
    markCancelledHandler: function()
    {
        $(".markCancelledBtn").unbind();
        $(".markCancelledBtn").on("click", function(event) 
        {
            event.preventDefault();
            ResellersCenter_InvoiceEdit.updateInvoiceStatus("Cancelled");
        });
    },

    publishHandler: function(send)
    {
        $(".publishBtn").unbind();
        $(".publishBtn").on("click", function(event) {
            event.preventDefault();

            JSONParser.request("publish|Invoices",
            {
                invoiceid: ResellersCenter_InvoiceEdit.invoiceid,
            },
            function ()
            {
                $("#RCInvoiceEdit").modal("hide");
                ResellersCenter_Search.loadTable(ResellersCenter_Search.filterKey);
            });

        });
    },

    publishAndSendHandler: function(send)
    {
        $(".publishAndSendBtn").unbind();
        $(".publishAndSendBtn").on("click", function(event) {
            event.preventDefault();

            JSONParser.request("publish|Invoices",
                {
                    invoiceid: ResellersCenter_InvoiceEdit.invoiceid,
                    send: 1
                },
                function ()
                {
                    $("#RCInvoiceEdit").modal("hide");
                    ResellersCenter_Search.loadTable(ResellersCenter_Search.filterKey);
                });

        });
    },

    updateInvoiceStatus: function(status)
    {
        JSONParser.request("updateInvoiceStatus|Invoices", {invoiceid: ResellersCenter_InvoiceEdit.invoiceid, status: status}, function(){
            $("#RCInvoiceEdit").modal("hide");
            ResellersCenter_Search.loadTable(ResellersCenter_Search.filterKey);
        });
    },
        
    deleteTransactionHandler: function()
    {
        $(".deleteTransaction").unbind();
        $(".deleteTransaction").on("click", function(event)
        {
            var transactionid = $(this).data("transactionid");

            JSONParser.request("deleteTransaction|Invoices", {transactionid: transactionid}, function(){
                ResellersCenter_InvoiceEdit.transactionsTable.ajax.reload(null, false);
            });
        });
    },
  
    addTransactionHandler: function()
    {
        $(".AddPaymentBtn").unbind();
        $(".AddPaymentBtn").on("click", function(event)
        {
            event.preventDefault();
            
            var form = $("#RCInvoiceEditTransactions form").serialize();
            form += "&invoiceid="+ResellersCenter_InvoiceEdit.invoiceid;
            
            //Validate payment field
            var amount = $("#RCInvoiceEdit [name='payment[amount]']").val();
            if(!$.isNumeric(amount) || amount == 0)
            {
                $("#RCInvoiceEdit [name='payment[amount]']").parent().addClass("has-error");
                return false;
            }

            //Validate gateway field
            var paymentMethod = $("#RCInvoiceEdit [name='payment[gateway]']").val();
            if (!paymentMethod || !paymentMethod.length) {
                $("#RCInvoiceEdit [name='payment[gateway]']").parent().addClass("has-error");
                return false;
            }

            $("#RCInvoiceEdit [name='payment[amount]']").parent().removeClass("has-error");
            JSONParser.request("addTransaction|Invoices", form, function()
            {
                ResellersCenter_InvoiceEdit.transactionsTable.ajax.reload(null, false);
                ResellersCenter_Search.loadTable(ResellersCenter_Search.filterKey);
                
                ResellersCenter_InvoiceEdit.loadInvoiceData(ResellersCenter_InvoiceEdit.invoiceid, "rc");
            });
           
            return false;
        });
    },
    
    submitEditForm: function()
    {
        var form = $("#RCInvoiceEdit form").serialize();
        
        JSONParser.request("updateInvoice|Invoices", form, function()
        {
            $("#RCInvoiceEdit").modal("hide");
            ResellersCenter_Search.loadTable(ResellersCenter_Search.filterKey);
        });
    },
    
    loadTransactionsTable: function()
    {
        if(this.transactionsTable !== null)
        {
            this.transactionsTable.draw();
        }
        else
        {
            this.transactionsTable = $("#transactionsTable").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "index.php?m=ResellersCenter&mg-page=invoices&mg-action=getInvoiceTransactionsForTable",
                fnDrawCallback: function(){
                    ResellersCenter_InvoiceEdit.refreshHandlers();
                },
                fnServerParams: function(data) {
                    data.push({ name: "invoiceid", value: ResellersCenter_InvoiceEdit.invoiceid});
                    data.push({ name: "json", value: 1});
                    data.push({ name: "datatable", value: 1});
                },
                columns: [
                    { data: "date",    orderable: true, sortable: false, targets: 0 },
                    { data: "gateway", orderable: true, sortable: false, targets: 0 },
                    { data: "transid", orderable: true, sortable: false, targets: 0 },
                    { data: "amountin",orderable: true, sortable: false, targets: 0 },
                    { data: "fees",    orderable: true, sortable: false, targets: 0 },
                    { data: "actions", orderable: false, sortable: false, targets: 0 },
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
        }
    },

    downloadPdf: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=invoices&mg-action=processDownloadPdf&invoiceid='+ResellersCenter_InvoiceEdit.invoiceid);
    },
    
    refreshHandlers: function()
    {
        this.addTransactionHandler();
        this.deleteTransactionHandler();
    },
    
    enableDatapicker: function()
    {
        $("#RCInvoiceEdit [name='payment[date]']").parent().datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: true
        });
        
        $("#RCInvoiceEditDetails [name='invoice[date]']").parent().datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $("#RCInvoiceEditDetails [name='invoice[duedate]']").parent().datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false //Important! See issue #1075
        });

        $("#RCInvoiceEdit [name='invoice[date]']").parent().on("dp.change", function (e) {
            $("#RCInvoiceEdit [name='invoice[duedate]']").parent().data("DateTimePicker").minDate(e.date);
        });
        $("#RCInvoiceEdit [name='invoice[duedate]']").parent().on("dp.change", function (e) {
            $("#RCInvoiceEdit [name='invoice[date]']").parent().data("DateTimePicker").maxDate(e.date);
        });
        
        $("#RCInvoiceEdit [name='invoice[date]']").trigger("dp.change");
        $("#RCInvoiceEdit [name='invoice[duedate]']").trigger("dp.change");
    },

    downloadPdfHandler: function()
    {
        $(".downloadPdfBtn").unbind();
        $(".downloadPdfBtn").on("click", function(event)
        {
            event.preventDefault();
            ResellersCenter_InvoiceEdit.downloadPdf();
        });
    }
}
{/literal}
{literal}
var ResellersCenter_InvoiceCreate =
{
    itemindex: 0,
    itemTaxedFlag: false,

    init: function(type)
    {
        this.enableDatapicker();
        this.enableClientSelect();

        this.loadInvoiceData();

        $("#RCInvoiceCreate [name='invoice[paymentmethod]']").select2({width: '100%'});
        $("#RCInvoiceCreate").modal("show");
    },
    
    loadInvoiceData: function(invoiceid, type)
    {
        var form = $("#RCInvoiceCreate form").serialize();

        JSONParser.request("getCreateInvoiceData", form, function(result)
        {
            $("#RCInvoiceCreate [name='invoice[invoicenum]']").val(result.invoicenum);
            $("#RCInvoiceCreate [name='invoice[date]']").val(result.date);
            $("#RCInvoiceCreate [name='invoice[duedate]']").val(result.duedate);
            $("#RCInvoiceCreate [name='invoice[tax1]']").val(result.tax1);
            $("#RCInvoiceCreate [name='invoice[tax2]']").val(result.tax2);
            $("#RCInvoiceCreate [name ^= 'invoice[items]'][name $= '[taxed]']").prop( "checked", result.itemTaxed );
            ResellersCenter_InvoiceCreate.itemTaxedFlag = result.itemTaxed;
        });
    },

    submitCreateForm: function(type)
    {
        $("#RCInvoiceCreate form").append("<input hidden name='publish' value='"+type+"'/>");
        var form = $("#RCInvoiceCreate form").serialize();

        JSONParser.request("createInvoice", form, function(result)
        {
            $("#RCInvoiceCreate").modal("hide");
            if(typeof(result.success) != "undefined")
            {
                $("#RCInvoiceCreate form input[type!='checkbox']").val('');
                $("#RCInvoiceCreate [name='invoice[userid]']").val('');

                $("#RCInvoiceCreate .addditonal-item").remove();
                ResellersCenter_InvoiceCreate.itemindex = 0;
            }

            ResellersCenter_Invoices.rcInvoicesTable.ajax.reload(null, false);
        });
    },

    addItem: function()
    {
        //Clone and set item index
        ResellersCenter_InvoiceCreate.itemindex++;
        var clone = $("#RCInvoiceCreate [data-prototype='']").clone();
        clone.removeAttr("data-prototype");
        clone.addClass("addditonal-item");
        clone.html(function(index, text)
        {
            return text.replace(/(\+itemid\+)/g, ResellersCenter_InvoiceCreate.itemindex);
        });

        var itemTaxedCheckboxName = 'invoice[items]['+ResellersCenter_InvoiceCreate.itemindex+'][taxed]';
        var itemTaxedCheckbox = clone.find("[name = '" + itemTaxedCheckboxName + "']");
        itemTaxedCheckbox.prop( "checked", ResellersCenter_InvoiceCreate.itemTaxedFlag );

        $("#RCInvoiceCreate form tbody tr").eq(-2).after(clone);
        ResellersCenter_InvoiceCreate.deleteItemRowHandler();
    },

    deleteItemRowHandler()
    {
        $(".deleteItemRowBtn").unbind("click");
        $(".deleteItemRowBtn").on("click", function (e) {
            e.preventDefault();

            $(this).parents("tr").remove();
        });
    },

    enableClientSelect: function()
    {
        $("#RCInvoiceCreate [name='invoice[userid]']").select2({
            ajax:
                {
                    url: 'index.php?m=ResellersCenter&mg-page=clients&mg-action=getAssigned&json=1',
                    processResults: function (data)
                    {
                        var result = JSONParser.getJSON(data);
                        var clients = [];
                        $.each(result.data, function (index, client)
                        {
                            var text = "#" + client.id + " " + client.firstname + " " + client.lastname;
                            if(client.companyname)
                            {
                                text += " (" + client.companyname + ")";
                            }
                            text += " - " + client.email;

                            clients.push({id: client.id, text: text});
                        });

                        return {results: clients};
                    },
                    delay: 250
                },
            width: '100%',
            placeholder: "{/literal}{html_entity_decode($MGLANG->absoluteT('form','select','placeholder'))}{literal}",
        });

        jQuery('select[name="invoice[userid]"]').on('change', function(){
            ResellersCenter_InvoiceCreate.loadInvoiceData();
        });
    },

    enableDatapicker: function()
    {
        $("#RCInvoiceCreate [name='payment[date]']").parent().datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: true
        });
        
        $("#RCInvoiceCreate [name='invoice[date]']").parent().datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $("#RCInvoiceCreate [name='invoice[duedate]']").parent().datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false //Important! See issue #1075
        });

        $("#RCInvoiceCreate [name='invoice[date]']").parent().on("dp.change", function (e) {
            $("#RCInvoiceCreate [name='invoice[duedate]']").parent().data("DateTimePicker").minDate(e.date);
        });
        $("#RCInvoiceCreate [name='invoice[duedate]']").parent().on("dp.change", function (e) {
            $("#RCInvoiceCreate [name='invoice[date]']").parent().data("DateTimePicker").maxDate(e.date);
        });

        $("#RCInvoiceCreate [name='invoice[date]']").trigger("dp.change");
        $("#RCInvoiceCreate [name='invoice[duedate]']").trigger("dp.change");
    },
}
{/literal}
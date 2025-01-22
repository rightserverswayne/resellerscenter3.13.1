{literal}
var RC_CreditLinesLogs =
    {
        table: null,

        init: function()
        {
            RC_CreditLinesLogs.loadTable();
        },

        loadTable: function()
        {
            RC_CreditLinesLogs.table = $("#creditLinesLogs table").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=getCreditLinesLogsForTable&json=1&datatable=1",
                fnDrawCallback: function(){
                    addCustomPagination("logstable");
                },
                fnServerParams: function(data) {
                    var resellerid = $("#rcInvoicesList").data("resellerid");
                    data.push({ name: "resellerid", value: resellerid});
                },
                columns: [
                    { data: "id",               orderable: true, sortable: false, targets: 0 },
                    { data: "client",           orderable: true, sortable: false, targets: 0 },
                    { data: "creditLineId",     orderable: true, sortable: false, targets: 0 },
                    { data: "balance",          orderable: true, sortable: false, targets: 0 },
                    { data: "amount",           orderable: true, sortable: false, targets: 0 },
                    { data: "invoiceItemId",    orderable: true, sortable: false, targets: 0 },
                    { data: "invoiceId",        orderable: true, sortable: false, targets: 0 },
                    { data: "invoiceType",      orderable: true, sortable: false, targets: 0 },
                    { data: "date",             orderable: true, sortable: false, targets: 0 }
                ],

                order: [[ 0, "desc" ]],
                bPaginate: true,
                pagingType: "full_numbers",
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

            RC_CreditLinesLogs.customDataTableSearch();
        },

        openSearchContainer: function()
        {
            if($("#logsSearch").is(":visible")) {
                $("#logsSearch").hide("slide", { direction: "right" }, 250);
            }
            else {
                $("#logsSearch").show("slide", { direction: "right" }, 250);
            }
        },

        customDataTableSearch: function()
        {
            var timer = null;
            $("#logsListFilter").keyup(function(){
                clearTimeout(timer);

                var filter = $(this).val();
                timer = setTimeout(function(){
                    RC_CreditLinesLogs.table.search(filter).draw();
                }, 500);
            });
        }
    }
RC_CreditLinesLogs.init();
{/literal}
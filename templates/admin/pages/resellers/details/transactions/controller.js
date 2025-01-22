{literal}
var RC_ResellersTransactions = {
    
    table: null,
    
    init: function()
    {
        RC_ResellersTransactions.customDataTableSearch();
    },

    show:  function()
    {
        if (!this.table ) {
            RC_ResellersTransactions.loadTable();
        }
    },
    
    refreshHandlers: function()
    {
        RC_ResellersTransactions.openTransactionDetailsHandler();
    },
    
    openTransactionDetailsHandler: function()
    {
        $(".openTransactionEdit").on("click", function(){
            var transactionid = $(this).data("transactionid");
            location.href = "transactions.php?action=edit&id="+transactionid;
        });
    },
    
    loadTable: function()
    {
        var tableSource = "addonmodules.php?module=ResellersCenter&mg-page=invoices&mg-action=getWHMCSTransactionsForTable&json=1&datatable=1";
        if($("#transactionsList").data("resellerinvoice")) {
            tableSource = "addonmodules.php?module=ResellersCenter&mg-page=invoices&mg-action=getRCTransactionsForTable&json=1&datatable=1";
        }
        
        RC_ResellersTransactions.table = $("#transactionsList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: tableSource,
            fnDrawCallback: function(){
                RC_ResellersTransactions.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#transactionsList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",           orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0 },
                { data: "date",         orderable: true, sortable: false, targets: 0 },
                { data: "gateway",      orderable: true, sortable: false, targets: 0 },
                { data: "description",  orderable: true, sortable: false, targets: 0 },
                { data: "amountin",     orderable: true, sortable: false, targets: 0 },
                { data: "fees",         orderable: true, sortable: false, targets: 0 },
                { data: "amountout",    orderable: true, sortable: false, targets: 0 },
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
    },
    
    showSearch: function()
    {
        if($(".transactionsListSearch").is(":visible")) {
            $(".transactionsListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".transactionsListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#transactionsListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_ResellersTransactions.table.search(filter).draw();
            }, 500);
        });
    },
    
}
RC_ResellersTransactions.init();
{/literal}
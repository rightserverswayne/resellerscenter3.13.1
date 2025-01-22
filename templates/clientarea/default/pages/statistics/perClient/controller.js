{literal}
var ResellersCenter_StatisticsPerClient = 
{
    table: null,
    
    init: function()
    {
        ResellersCenter_StatisticsPerClient.loadTable();
    },
    
    loadTable: function()
    {
        ResellersCenter_StatisticsPerClient.table = $("#perClient table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=statistics&mg-action=getClientsStatisticTable&json=1&datatable=1",
            fnDrawCallback: function(){
            },
            fnServerParams: function(data) {
                var resellerid = $("#rcInvoicesList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",       orderable: true, sortable: false, targets: 0 },
                { data: "name",     orderable: true, sortable: false, targets: 0 },
                { data: "orders",   orderable: true, sortable: false, targets: 0 },
                { data: "value",    orderable: true, sortable: false, targets: 0 },
                { data: "income",   orderable: true, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>',
        });
    },
}
ResellersCenter_StatisticsPerClient.init();
{/literal}
{literal}
var RC_Statistics_Incomes = 
{
    table: null,
    
    init: function()
    {
        RC_Statistics_Incomes.loadTable();
    },
    
    loadTable: function()
    {
        RC_Statistics_Incomes.table = $("#incomes table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=statistics&mg-action=getIncomesTable&json=1&datatable=1",
            fnDrawCallback: function(){
            },
            fnServerParams: function(data) {
                var resellerid = $("#rcInvoicesList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",              orderable: true, sortable: false, targets: 0 },
                { data: "name",            orderable: true, sortable: false, targets: 0 },
                { data: "totalsale",       orderable: true, sortable: false, targets: 0 },
                { data: "resellerincome",  orderable: true, sortable: false, targets: 0 },
                { data: "income",          orderable: true, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 't<"table-bottom"<"row"<"col-sm-4"i><"col-sm-8"p>>>',
            oLanguage: {
                sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                sInfo : "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                oPaginate: {
                    sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                    sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                }
            }
        });
    },
}
RC_Statistics_Incomes.init();
{/literal}
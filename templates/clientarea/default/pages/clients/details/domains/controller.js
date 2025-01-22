{literal}
var ResellersCenter_ClientsDomains = 
{
    table: null,
    
    init: function()
    {
        this.loadAddonsTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        
    },
  
    loadAddonsTable: function()
    {
        this.table = $("#RCClientsDomains table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=clients&mg-action=getDomainsForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientsDomains.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "domain",   orderable: true, sortable: false, targets: 0 },
                { data: "client",   orderable: true, sortable: false, targets: 0, visible: false },
                { data: "status",  orderable: false, sortable: false, targets: 0 },
                { data: "price",    orderable: true, sortable: false, targets: 0 },
                { data: "period",   orderable: true, sortable: false, targets: 0 },
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
}
ResellersCenter_ClientsDomains.init();
{/literal}
{literal}
var ResellersCenter_ClientsAddons = 
{
    table: null,
    activeService: null,
    
    init: function()
    {
        this.loadAddonsTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        ResellersCenter_ClientsAddons.openDeleteService();
    },
    
    
    openDeleteService: function()
    {
        $(".openDeleteAddon").unbind("click");
        $(".openDeleteAddon").on("click", function()
        {
            ResellersCenter_ClientsAddons.activeService = $(this).data("addonid");
            $("#RCDeleteAddon").modal("show");
        });
    },
    
    submitDeleteFrom: function()
    {
        JSONParser.request("termianteService", {relid: ResellersCenter_ClientsAddons.activeService}, function(result) 
        {
            ResellersCenter_ClientsAddons.table.draw();
            $("#RCDeleteAddon").modal("hide");
        });
    },
  
    loadAddonsTable: function()
    {
        this.table = $("#RCClientsAddons table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=clients&mg-action=getAddonsForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientsAddons.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "addon",        orderable: true, sortable: false, targets: 0 },
                { data: "domain",       orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0, visible: false},
                { data: "price",        orderable: true, sortable: false, targets: 0 },
                { data: "billingcycle", orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>',
        });
    },
}
ResellersCenter_ClientsAddons.init();
{/literal}
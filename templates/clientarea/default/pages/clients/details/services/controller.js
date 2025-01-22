{literal}
var ResellersCenter_ClientsServices = 
{
    table: null,
    activeService: null,
    
    init: function()
    {
        this.loadHostingTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        this.openDeleteService();
        this.openSuspendService();
        this.openUnsuspendService();
    },

    openDeleteService: function()
    {
        $(".openDeleteService").unbind("click");
        $(".openDeleteService").on("click", function()
        {
            ResellersCenter_ClientsServices.activeService = $(this).data("serviceid");
            $("#RCDeleteService").modal("show");
        });
    },
    
    submitDeleteFrom: function()
    {
        JSONParser.request("termianteService", {relid: ResellersCenter_ClientsServices.activeService}, function(result){
            ResellersCenter_ClientsServices.table.draw();
            $("#RCDeleteService").modal("hide");
        });
    },

    openSuspendService: function()
    {
        $(".openSuspendService").unbind("click");
        $(".openSuspendService").on("click", function()
        {
            ResellersCenter_ClientsServices.activeService = $(this).data("hosting_id");
            $("#RCSuspendService").modal("show");
        });
    },

    submitSuspendFrom: function()
    {
        state = 'suspend';
        JSONParser.request("suspend", {relid: ResellersCenter_ClientsServices.activeService, state: state}, function(result){
            ResellersCenter_ClientsServices.table.draw();
            $("#RCSuspendService").modal("hide");
        });
    },

    openUnsuspendService: function()
    {
        $(".openUnsuspendService").unbind("click");
        $(".openUnsuspendService").on("click", function()
        {
            ResellersCenter_ClientsServices.activeService = $(this).data("hosting_id");
            $("#RCUnsuspendService").modal("show");
        });
    },

    submitUnsuspendFrom: function()
    {
        state = 'unsuspend';
        JSONParser.request("suspend", {relid: ResellersCenter_ClientsServices.activeService, state: state}, function(result){
            ResellersCenter_ClientsServices.table.draw();
            $("#RCUnsuspendService").modal("hide");
        });
    },
    
    loadHostingTable: function()
    {
        this.table = $("#RCClientsServices table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=clients&mg-action=getServicesForTable",
            fnDrawCallback: function(){
                ResellersCenter_ClientsServices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var clientid = getParameterByName("cid");
                data.push({ name: "clientid", value: clientid});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "hosting_id",   orderable: true, sortable: false, targets: 0 },
                { data: "product",      orderable: true, sortable: false, targets: 0 },
                { data: "domain",       orderable: true, sortable: false, targets: 0 },
                { data: "client",       orderable: true, sortable: false, targets: 0 },
                { data: "price",        orderable: true, sortable: false, targets: 0 },
                { data: "billingcycle", orderable: true, sortable: false, targets: 0 },
                { data: "status",       orderable: true, sortable: false, targets: 0 },
                { data: "nextduedate",  orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>',
        });
    },
}
ResellersCenter_ClientsServices.init();
{/literal}
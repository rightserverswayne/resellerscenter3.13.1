{literal}
var ResellersCenter_Clients = {
    
    table: null,
    clientid: null,
    isAllowCreditOn: false,

    init: function()
    {
        $(".checkbox-switch").bootstrapSwitch();

        ResellersCenter_Clients.initTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    refreshHandlers: function()
    {
        this.loginAsClientHandler();
        this.createOrderHandler();
        this.openClientDetails();
        this.openDeleteModal();
    },
    
    /**
     * Add Form
     */
    openAddModal: function()
    {
        $("#RCAddClient .alertContainter").alerts("clear");
        $("#RCAddClient").modal("show");
    },

    submitAddForm: function()
    {
        $("#RCAddClient .alertContainter").alerts("clear");
        
        var form = $("#RCAddClient form").serialize();
        JSONParser.request("create", form, function(result){
            if(result.validateError) 
            {
                $("#RCAddClient .alertContainter").alerts("error", result.validateError);
            }
            else
            {
                $("#RCAddClient form").trigger('reset');
                $("#RCAddClient").modal("hide");
                ResellersCenter_Clients.table.draw();
            }
        });
    },

    /**
     * Login As Client
     */
    loginAsClientHandler: function()
    {
        $(".loginAsClientBtn").unbind("click");
        $(".loginAsClientBtn").on("click", function()
        {
            var clientid = $(this).data("clientid");

            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=loginAsClient&clientid="+clientid;
        });
    },
    
    /**
     * Create Order
     */
    createOrderHandler: function()
    {
        $(".openAddOrderClient").unbind("click");
        $(".openAddOrderClient").on("click", function()
        {
            var clientid = $(this).data("clientid");
            
            var url = window.location.href;
            url = url.substring(0, url.lastIndexOf("/") + 1);
            window.location.href = url + "index.php?m=ResellersCenter&mg-page=clients&mg-action=createOrder&clientid="+clientid;
        });
    },
    
    /**
     * Open Details
     */
    openClientDetails: function()
    {
        $(".openDetailsClient").unbind("click");
        $(".openDetailsClient").on("click", function(){
            var clientid = $(this).data("clientid");
            window.location.href = 'index.php?m=ResellersCenter&mg-page=clients&mg-action=details&cid='+clientid;
        });
    },
    
    /**
     * Delete Form
     */
    openDeleteModal: function()
    {
        $(".openDeleteClient").unbind("click");
        $(".openDeleteClient").on("click", function()
        {
            ResellersCenter_Clients.clientid = $(this).data("clientid");
            $("#RCDeleteClient").modal("show");
        });
    },
    
    submitDeleteFrom: function()
    {
        JSONParser.request("delete", {clientid: ResellersCenter_Clients.clientid}, function(result){
            ResellersCenter_Clients.table.draw();
            $("#RCDeleteClient").modal("hide");
        });
    },

    loadTable: function()
    {
        ResellersCenter_Clients.table = $("#RCClients table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=clients&mg-action=getAssignedForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                ResellersCenter_Clients.refreshHandlers();
            },
            columns: [
                { data: "id",           orderable: true, sortable: false, targets: 0 },
                { data: "firstname",    orderable: true, sortable: false, targets: 0 },
                { data: "lastname",     orderable: true, sortable: false, targets: 0 },
                { data: "companyname",  orderable: true, sortable: false, targets: 0 },
                { data: "income",       orderable: true, sortable: false, targets: 0 },
                { data: "creditLine",   orderable: false, sortable: false, targets: 0, visible: ResellersCenter_Clients.isAllowCreditOn},
                { data: "created_at",   orderable: true, sortable: false, targets: 0 },
                { data: "actions",      orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>',
        });

        ResellersCenter_Clients.customDataTableSearch();
    },
    
    showSearch: function()
    {
        if($(".clientsListSearch").is(":visible")) {
            $(".clientsListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".clientsListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $(".clientsListSearch input").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                ResellersCenter_Clients.table.search(filter).draw();
            }, 500);
        });
    },

    export: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType=Clients');
    },

    initTable: function()
    {
        new Promise(function(resolve){
            JSONParser.request("checkIsAllowCreditLine", {}, function(data) {
                ResellersCenter_Clients.isAllowCreditOn =  data['result'] == 'on';
                resolve();
            });
        }).then(function(){
            ResellersCenter_Clients.loadTable();
        });
    },
};
ResellersCenter_Clients.init();
{/literal}
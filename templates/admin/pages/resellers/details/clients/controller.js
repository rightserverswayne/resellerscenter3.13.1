{literal}
var RC_ResellersClients = {
    
    table: null,
    activeClientid: null,
    
    init: function()
    {
    },

    show:  function()
    {
        if (!this.table ) {
            RC_ResellersClients.loadTable();
        }
    },
    
    refreshHandlers: function()
    {
        RC_ResellersClients.openDeleteModal();
    },
    
    /**
     * Add
     */
    openAddModal: function()
    {
        $("#clientAddForm [name='relid']").select2({
            ajax: {
                url: 'addonmodules.php?module=ResellersCenter&mg-page=clients&mg-action=getNotAssigned&json=1',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var items = [];
                    $.each(result.data, function(index, value){
                        var client = "#"+value.id+" "+value.firstname+" "+value.lastname;
                        items.push({id: value.id, text: client});
                    });
                    return {results: items};
                },
                delay: 250
            },
            placeholder: "{/literal}{$MGLANG->T('form','select','placeholder')}{literal}",
        });
        
        //Clear selection
        $("#clientAddForm [name='relid']").select2("val", "");
        
        $("#clientAddModal").modal("show");
    },
    
    submitAddForm: function()
    {
        var form = $("#clientAddForm").serialize();
        
        JSONParser.request("assignToReseller|Clients", form, function(){
            RC_ResellersClients.table.draw();
            $("#clientAddModal").modal("hide");
        });
    },
    
    /**
     * Delete
     */
    openDeleteModal: function()
    {
        $(".openDeleteClient").on("click", function(){
            RC_ResellersClients.activeClientid = $(this).data("clientid");
            $("#clientDeleteModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request(
            "deleteFromReseller|Clients", 
            {assignationid: RC_ResellersClients.activeClientid, type: "client"}, 
            function(){
                RC_ResellersClients.table.draw();
                $("#clientDeleteModal").modal("hide");
            }
        );
    },
    
    /**
     * Table
     */
    loadTable: function()
    {
        RC_ResellersClients.table = $("#clientsList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=clients&mg-action=getAssignedClientsForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersClients.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#clientsList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "client_id",    orderable: true, sortable: false, targets: 0 },
                { data: "firstname",    orderable: true, sortable: false, targets: 0 },
                { data: "lastname",     orderable: true, sortable: false, targets: 0 },
                { data: "companyname",  orderable: true, sortable: false, targets: 0 },
                { data: "income",       orderable: true, sortable: false, targets: 0 },
                { data: "created_at",   orderable: true, sortable: false, targets: 0 },
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
        
        RC_ResellersClients.customDataTableSearch();
    },
    
    showSearch: function()
    {
        if($(".clientListSearch").is(":visible")) {
            $(".clientListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".clientListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#clientListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_ResellersClients.table.search(filter).draw();
            }, 500);
        });
    },
    
}
RC_ResellersClients.init();
{/literal}
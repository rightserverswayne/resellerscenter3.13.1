{literal}
var RC_Resellers = 
{
    activeResellerid: null,
    table: null,
    
    init: function()
    {
        RC_Resellers.loadResellers();
        $(".select2").select2();
        $("#addReseller .checkbox-switch").bootstrapSwitch();
    },
    
    refreshHandlers: function()
    {
        RC_Resellers.openResellerDetails();
        RC_Resellers.openDeleteModal();
        
    },
    
    openResellerDetails: function()
    {
        $(".openDetailsReseller").unbind("click");
        $(".openDetailsReseller").on("click", function(){
            var rid = $(this).data("resellerid");
            window.location.href = "addonmodules.php?module=ResellersCenter&mg-page=resellers&mg-action=details&customHTML=1&rid="+rid;
        });
    },
    
    /**
     *  Add Reseller
     */
    openAddForm: function()
    {
        $("#resellerAddForm [name='clientid']").select2({
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
            placeholder: "{/literal}{html_entity_decode($MGLANG->T('form','select','placeholder'))}{literal}",
        });
        
        //Clear selection
        $("#resellerAddForm [name='clientid']").select2("val", "");
        
        $("#addReseller").modal("show");
    },
    
    submitAddForm: function()
    {
        var form = $("#resellerAddForm").serialize();
        
        JSONParser.request('createReseller', form, function()
        {
            RC_Resellers.table.draw();
            $("#addReseller").modal("hide");
        });
    },
    
    /**
     * Delete Reseller
     */
    openDeleteModal: function()
    {
        $(".openDeleteReseller").unbind("click");
        $(".openDeleteReseller").on("click", function(){
            RC_Resellers.activeResellerid = $(this).data("resellerid");
            
            $("#deleteResellerModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deleteReseller", {resellerid: RC_Resellers.activeResellerid}, function(){
            RC_Resellers.table.draw();
            
            $("#deleteResellerModal").modal("hide");
        });
    },
    
    openSearchContainer: function()
    {
        if($("#resellerSearch").is(":visible")) {
            $("#resellerSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $("#resellerSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    loadResellers: function()
    {
        if($.fn.DataTable.isDataTable("#resellerList"))
        {
            RC_Resellers.table.draw();
        }
        else
        {
            RC_Resellers.table = $("#resellerList").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=resellers&mg-action=getResellersForDataTable&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_Resellers.refreshHandlers();
                },
                columns: [
                    { data: "groupname",    orderable: true, sortable: false, targets: 0 },
                    { data: "firstname",    orderable: true, sortable: false, targets: 0 },
                    { data: "lastname",     orderable: true, sortable: false, targets: 0 },
                    { data: "companyname",  orderable: true, sortable: false, targets: 0 },
                    { data: "totalsale",    orderable: true, sortable: false, targets: 0 },
                    { data: "monthsale",    orderable: true, sortable: false, targets: 0 },
                    { data: "creditline",   orderable: false, sortable: false, targets: 0 },
                    { data: "status",       orderable: true, sortable: false, targets: 0 },
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

            RC_Resellers.customDataTableSearch();
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#resellerListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_Resellers.table.search(filter).draw();
            }, 500);
        });
    }
}
RC_Resellers.init();
{/literal}
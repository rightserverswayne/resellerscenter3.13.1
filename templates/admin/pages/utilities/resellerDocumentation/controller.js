{literal}
var RC_ResellerDocumentation = 
{
    table: null,
    
    acivteid: null,
    
    init: function()
    {
        this.loadDocumentations();
    },

    openAddForm: function()
    {
        $("#DocumentationAddFormModal").modal("show");
    },
    
    submitForm: function()
    {
        var form = $("#DocumentationAddForm").serialize();
        JSONParser.request('saveDocumentation', form, function()
        {
            RC_ResellerDocumentation.table.draw();
            $("#DocumentationAddFormModal").modal("hide");
        });
    },
    
    openDetailsHandler: function()
    {
        $(".openDetails").unbind("click");
        $(".openDetails").on("click", function() 
        {
            var id = $(this).data("documentationid");
            window.location.href = "addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=documentationDetails&id="+id;
        });
    },
    
    openDeleteHandler: function()
    {
        $(".openDelete").unbind("click");
        $(".openDelete").on("click", function()
        {
            var id = $(this).data("documentationid");
            RC_ResellerDocumentation.acivteid = id;
            $("#deleteDocumentationModal").modal("show");

        });
    },
    
    submitDelete: function()
    {
        JSONParser.request("deleteDocumentation", {id: RC_ResellerDocumentation.acivteid}, function()
        {
            RC_ResellerDocumentation.table.draw();
            $("#deleteDocumentationModal").modal("hide");
        });
    },
    
    loadDocumentations: function()
    {
        if($.fn.DataTable.isDataTable("#DocumentationList"))
        {
            RC_ResellerDocumentation.table.draw();
        }
        else
        {
            RC_ResellerDocumentation.table = $("#DocumentationList").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=getDocumentationsForTable&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_ResellerDocumentation.openDetailsHandler();
                    RC_ResellerDocumentation.openDeleteHandler();
                },
                columns: [
                    { data: "id",           orderable: true, sortable: false, targets: 0 },
                    { data: "name",         orderable: true, sortable: false, targets: 0 },
                    { data: "created_at",   orderable: true, sortable: false, targets: 0 },
                    { data: "updated_at",   orderable: true, sortable: false, targets: 0 },
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

            RC_ResellerDocumentation.customDataTableSearch();
        }
    },
    
    openSearchContainer: function()
    {
        if($("#DocumentationListSearch").is(":visible")) {
            $("#DocumentationListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $("#DocumentationListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#DocumentationListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_ResellerDocumentation.table.search(filter).draw();
            }, 500);
        });
    }    
    
}
RC_ResellerDocumentation.init();
{/literal}
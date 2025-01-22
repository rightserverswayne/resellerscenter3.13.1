{literal}
var RC_exportData = 
{
    activeType: null,
    resellers: null,
    
    init: function()
    {
        RC_exportData.loadData();
    },

    loadData: function()
    {
        if($.fn.DataTable.isDataTable("#exportDataList"))
        {
            RC_exportData.table.draw();
        }
        else
        {
            RC_exportData.table = $("#exportDataList").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: true,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=getDataForTable&json=1&datatable=1",
                fnDrawCallback: function(){
                    RC_exportData.openExportModal();
                },
                columns: [
                    { data: "data",    orderable: true, sortable: false, targets: 0 },
                    { data: "actions", orderable: false, sortable: false, targets: 0 },
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

        }
    },
    openExportModal: function(){
        $(".openExportModal").on("click", function() 
        {                       
            RC_exportData.activeType = $(this).data("datatype");
            $('#exportModal').modal('show');
        });
        
    },
    processExport: function(){
            var resellerId = $(".resellerId").val(); 
            var url = 'addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=processExportData&dataType=' + RC_exportData.activeType + "&resellerId=" + resellerId;
            window.open(url, '_blank');
    }
}
RC_exportData.init();
{/literal}


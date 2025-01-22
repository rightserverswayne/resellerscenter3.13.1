{literal}
var ResellersCenter_Promotions = 
{
    table: null,
    tablefilter: "active",
    promotionid: null,
    
    init: function()
    {
        this.promoFilterHandler();
        this.loadTable();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    promoFilterHandler: function()
    {
        $(".promo-filters li").on("click", function()
        {
            ResellersCenter_Promotions.tablefilter = $(this).data("type");
            ResellersCenter_Promotions.table.draw();
        });
    },
    
    openDeleteModal: function()
    {
        $(".openDeletePromo").on("click", function()
        {
            ResellersCenter_Promotions.promotionid = $(this).data("promoid");
            $("#RCDeletePromotion").modal("show");
        });
    },
    
    submitDeleteFrom: function()
    {
        JSONParser.request("delete", {id: ResellersCenter_Promotions.promotionid}, function()
        {
            ResellersCenter_Promotions.table.draw();
            $("#RCDeletePromotion").modal("hide");
        });
    },
    
    loadTable: function()
    {
        this.table = $("#RCPromotions table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=promotions&mg-action=getForTable",
            fnDrawCallback: function()
            {
                ResellersCenter_Promotions.openDeleteModal();
            },
            fnServerParams: function(data) 
            {                
                data.push({ name: "filter", value: ResellersCenter_Promotions.tablefilter});
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "code",          orderable: true, sortable: false, targets: 0 },
                { data: "type",          orderable: true, sortable: false, targets: 0 },
                { data: "value",         orderable: true, sortable: false, targets: 0 },
                { data: "recurring",     orderable: true, sortable: false, targets: 0 },
                { data: "uses",          orderable: true, sortable: false, targets: 0 },
                { data: "startdate",     orderable: true, sortable: false, targets: 0 },
                { data: "expirationdate",orderable: true, sortable: false, targets: 0 },
                { data: "actions",       orderable: false, sortable: false, targets: 0 },
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
        
        ResellersCenter_Promotions.customDataTableSearch();
    },
    
    showSearch: function()
    {
        if($(".promotionSearch").is(":visible")) 
        {
            $(".promotionSearch").hide("slide", { direction: "right" }, 250);
        }
        else 
        {
            $(".promotionSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $(".promotionSearch input").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function() {
                ResellersCenter_Promotions.table.search(filter).draw();
            }, 500);
        });
    }
}
ResellersCenter_Promotions.init();
{/literal}
{literal}
var RC_Payouts_List = 
{
    table: null,
    activeProfit: null,
    
    init: function()
    {
        $("#payouts .checkbox-switch").bootstrapSwitch();
        
        $(document).ready(function(){
            RC_Payouts_List.loadTable();
        });
    },
    
    refreshHanlders: function()
    {
        RC_Payouts_List.setAsCollectedHandler();
        RC_Payouts_List.makeSinglePayPalPaymentHandler();
        RC_Payouts_List.makeSingleCreditPaymentHandler();
    },
    
    setAsCollectedHandler: function()
    {
        $(".setAsCollected").on("click", function(){
            var profitid = $(this).data("profitid");
            
            JSONParser.request('setAsCollected', {profitid: profitid}, function(){
                RC_Payouts_List.table.ajax.reload(null, false);
            });
        });
    },
    
    makeSinglePayPalPaymentHandler: function()
    {
        $(".payByPaypal").on("click", function()
        {
            var profitid = $(this).data("profitid");
            RC_Payouts_List.activeProfit = profitid;
            
            $("#confirmSinglePayout .acceptBtn").attr("onclick", "RC_Payouts_List.makeSinglePayPalPayment();");
            $("#confirmSinglePayout").modal("show");
        });
    },
    
    makeSinglePayPalPayment: function()
    {
        JSONParser.request('makeSinglePayPalPayment', {profitid: RC_Payouts_List.activeProfit}, function()
        {
            RC_Payouts_List.activeProfit = null;
            RC_Payouts_List.table.ajax.reload(null, false);
            $("#confirmSinglePayout").modal("hide");
        });
    },
    
    makeSingleCreditPaymentHandler: function()
    {
        $(".payByCredits").on("click", function()
        {
            var profitid = $(this).data("profitid");
            RC_Payouts_List.activeProfit = profitid;
            
            $("#confirmSinglePayout .acceptBtn").attr("onclick", "RC_Payouts_List.makeSingleCreditPayment();");
            $("#confirmSinglePayout").modal("show");
        });
    },
    
    makeSingleCreditPayment: function()
    {
        JSONParser.request('makeSingleCreditsPayment', {profitid: RC_Payouts_List.activeProfit}, function()
        {
            RC_Payouts_List.activeProfit = null;
            RC_Payouts_List.table.ajax.reload(null, false);
            $("#confirmSinglePayout").modal("hide");
        });
    },
    
    loadTable: function()
    {
        RC_Payouts_List.table = $("#payoutsList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=payouts&mg-action=getPayoutsTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_Payouts_List.refreshHanlders();
            },
            fnServerParams: function(data) {
                var resellerid = $("#rcInvoicesList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",          orderable: true, sortable: false, targets: 0 },
                { data: "firstname",   orderable: true, sortable: false, targets: 0 },
                { data: "lastname",    orderable: true, sortable: false, targets: 0 },
                { data: "companyname", orderable: true, sortable: false, targets: 0 },
                { data: "invoice",     orderable: true, sortable: false, targets: 0 },
                { data: "description", orderable: true, sortable: false, targets: 0 },
                { data: "amount",      orderable: true, sortable: false, targets: 0 },
                { data: "status",      orderable: true, sortable: false, targets: 0 },
                { data: "created_at",  orderable: true, sortable: false, targets: 0 },
                { data: "actions",     orderable: false, sortable: false, targets: 0 },
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
        
        RC_Payouts_List.customDataTableSearch();
    },
    
    openSearchContainer: function()
    {
        if($("#payoutsSearch").is(":visible")) {
            $("#payoutsSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $("#payoutsSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#payoutsListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                RC_Payouts_List.table.search(filter).draw();
            }, 500);
        });
    }
    
}
RC_Payouts_List.init();
{/literal}
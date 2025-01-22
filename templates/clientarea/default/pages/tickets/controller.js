{literal}
var ResellersCenter_Tickets = 
{
    table: null,
    activeRelid: null,
    
    init: function()
    {
        this.loadTicketsTable();
    },
    
    refreshHandlers: function()
    {
        this.openDetailsTicket();
        this.deleteTicketModal();
    },
  
    openDetailsTicket: function()
    {
        $(".openDetailsTicket").unbind("click");
        $(".openDetailsTicket").on("click", function(){
            var ticketid = $(this).data("ticketid");
            window.location.href = "index.php?m=ResellersCenter&mg-page=tickets&mg-action=details&tid="+ticketid;
        });
    },
    
    deleteTicketModal: function()
    {
        $(".openDeleteTicket").unbind("click");
        $(".openDeleteTicket").on("click", function()
        {
            ResellersCenter_Tickets.activeRelid = $(this).data("ticketid");
            $("#RCTicketDeleteModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request("deleteTicket", {ticketid: this.activeRelid}, function()
        {
            ResellersCenter_Tickets.activeRelid = null;
            ResellersCenter_Tickets.table.draw();
        });
        
        $("#RCTicketDeleteModal").modal("hide");
    },
  
    loadTicketsTable: function()
    {
        this.table = $("#RCTickets table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "index.php?m=ResellersCenter&mg-page=tickets&mg-action=getTicketsForTable",
            fnDrawCallback: function(){
                ResellersCenter_Tickets.refreshHandlers();
            },
            fnServerParams: function(data) {
                data.push({ name: "json", value: 1});
                data.push({ name: "datatable", value: 1});
            },
            columns: [
                { data: "priority",   orderable: true, sortable: false, targets: 0 },
                { data: "department", orderable: true, sortable: false, targets: 0 },
                { data: "subject",    orderable: true, sortable: false, targets: 0 },
                { data: "client",     orderable: true, sortable: false, targets: 0 },
                { data: "status",     orderable: true, sortable: false, targets: 0 },
                { data: "lastreply",  orderable: true, sortable: false, targets: 0 },
                { data: "actions",    orderable: false, sortable: false, targets: 0 },
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
        
        ResellersCenter_Tickets.customDataTableSearch();
    },
    
    showSearch: function()
    {
        if($(".ticketsListSearch").is(":visible")) {
            $(".ticketsListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".ticketsListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $(".ticketsListSearch input").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                ResellersCenter_Tickets.table.search(filter).draw();
            }, 500);
        });
    }
}
ResellersCenter_Tickets.init();
{/literal}
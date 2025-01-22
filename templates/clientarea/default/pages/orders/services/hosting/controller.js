{literal}
var ResellersCenter_Services_Hosting =
{
    table: null,

    init: function()
    {
        this.loadTable();
    },

    loadTable: function()
    {
        var table = new ResellersCenter_Datatable();
        table.setSource("orders", "getServicesTable");
        table.setSelector("#RCServicesTab table");

        table.addColumn(new ResellersCenter_Datatable_Column("hosting_id"));
        table.addColumn(new ResellersCenter_Datatable_Column("product"));
        table.addColumn(new ResellersCenter_Datatable_Column("domain"));
        table.addColumn(new ResellersCenter_Datatable_Column("client"));
        table.addColumn(new ResellersCenter_Datatable_Column("price"));
        table.addColumn(new ResellersCenter_Datatable_Column("billingcycle"));
        table.addColumn(new ResellersCenter_Datatable_Column("status"));
        table.addColumn(new ResellersCenter_Datatable_Column("nextduedate"));
        table.addColumn(new ResellersCenter_Datatable_Column("actions", false, false));
        table.addSearch(new ResellersCenter_Datatable_Search("#RCServicesTabFilters [name='search']"));

        $("#RCServicesTabFilters select").each(function(key, value)
        {
            var filter = new ResellersCenter_Datatable_Filter(value);
            filter.initSelect2("orders", "getFiltersData");
            table.addFilter(filter);
        });

        var deleteButton = new ResellersCenter_Datatable_Button(".openDeleteService", "#RCServicesTabDelete");
        deleteButton.addClickAction(this.deleteOpenHandler);
        deleteButton.addSuccessAction(this.deleteSubmitHandler);
        table.addButton(deleteButton);

        var suspendButton = new ResellersCenter_Datatable_Button(".openSuspendService", "#RCSuspendService");
        suspendButton.addClickAction(this.suspendOpenHandler);
        suspendButton.addSuccessAction(this.suspendSubmitHandler);
        table.addButton(suspendButton);

        var unsuspendButton = new ResellersCenter_Datatable_Button(".openUnsuspendService", "#RCUnsuspendService");
        unsuspendButton.addClickAction(this.unsuspendOpenHandler);
        unsuspendButton.addSuccessAction(this.unsuspendSubmitHandler);
        table.addButton(unsuspendButton);
        table.init();
        ResellersCenter_Services_Hosting.table = table;
    },

    deleteSubmitHandler: function (self)
    {
        var hostingid = $("#RCServicesTabDelete [name='relid']").val();
        JSONParser.request("terminateService", {relid: hostingid, type: 'hosting'}, function(result)
        {
            $("#RCServicesTabDelete").modal("hide");
        });
    },

    deleteOpenHandler: function(self)
    {
        var hostingid = $(self).data("hosting_id");
        $("#RCServicesTabDelete [name='relid']").val(hostingid);
    },

    suspendSubmitHandler: function (self)
    {
        var hostingid = $("#RCSuspendService [name='relid']").val();
        state = 'suspend';
        JSONParser.request("suspend", {relid: hostingid, state: state}, function(result)
        {
            $("#RCSuspendService").modal("hide");
        });
        ResellersCenter_Services_Hosting.table.draw();
    },

    suspendOpenHandler: function(self)
    {
        var hostingid = $(self).data("hosting_id");
        $("#RCSuspendService [name='relid']").val(hostingid);
    },

    unsuspendSubmitHandler: function (self)
    {
        hostingid = $("#RCUnsuspendService [name='relid']").val();
        state = 'unsuspend';
        JSONParser.request("suspend", {relid: hostingid, state: state }, function(result)
        {
            $("#RCUnsuspendService").modal("hide");
        });
        ResellersCenter_Services_Hosting.table.draw();
    },

    unsuspendOpenHandler: function(self)
    {
        var hostingid = $(self).data("hosting_id");
        $("#RCUnsuspendService [name='relid']").val(hostingid);
    },


    export: function ()
    {
        window.open('index.php?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType=Hosting');
    }
};
ResellersCenter_Services_Hosting.init();
{/literal}
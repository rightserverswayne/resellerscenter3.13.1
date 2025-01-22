{literal}
var ResellersCenter_Services_Addons =
{
    init: function()
    {
        this.loadTable();
    },

    refreshHandlers: function()
    {

    },

    loadTable: function()
    {
        var table = new ResellersCenter_Datatable();
        table.setSource("orders", "getAddonsTable");
        table.setSelector("#RCAddonsTab table");

        table.addColumn(new ResellersCenter_Datatable_Column("hostingaddonid"));
        table.addColumn(new ResellersCenter_Datatable_Column("addon"));
        table.addColumn(new ResellersCenter_Datatable_Column("product"));
        table.addColumn(new ResellersCenter_Datatable_Column("client"));
        table.addColumn(new ResellersCenter_Datatable_Column("billingcycle"));
        table.addColumn(new ResellersCenter_Datatable_Column("price"));
        table.addColumn(new ResellersCenter_Datatable_Column("status"));
        table.addColumn(new ResellersCenter_Datatable_Column("nextduedate"));
        table.addColumn(new ResellersCenter_Datatable_Column("actions", false, false));
        table.addSearch(new ResellersCenter_Datatable_Search("#RCAddonsTabFilters [name='search']"));

        $("#RCAddonsTabFilters select").each(function(key, value)
        {
            var filter = new ResellersCenter_Datatable_Filter(value);
            filter.initSelect2("orders", "getFiltersData");
            table.addFilter(filter);
        });

        var button = new ResellersCenter_Datatable_Button(".openDeleteAddon", "#RCAddonsTabDelete");
        button.addClickAction(this.deleteOpenHandler);
        button.addSuccessAction(this.deleteSubmitHandler);
        table.addButton(button);

        table.init();
    },

    deleteSubmitHandler: function (self)
    {
        var addonid = $("#RCAddonsTabDelete [name='relid']").val();
        JSONParser.request("terminateService", {relid: addonid, type: 'addon'}, function(result)
        {
            $("#RCAddonsTabDelete").modal("hide");
        });
    },

    deleteOpenHandler: function(self)
    {
        var domainid = $(self).data("addon_id");
        $("#RCAddonsTabDelete [name='relid']").val(domainid);
    },
    export: function ()
    {
        window.open('index.php?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType=Addons');
    }
}
ResellersCenter_Services_Addons.init();
{/literal}
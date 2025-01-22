{literal}
var ResellersCenter_Services_Domains =
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
        table.setSource("orders", "getDomainsTable");
        table.setSelector("#RCDomainsTab table");

        table.addColumn(new ResellersCenter_Datatable_Column("domain_id"));
        table.addColumn(new ResellersCenter_Datatable_Column("domain"));
        table.addColumn(new ResellersCenter_Datatable_Column("client"));
        table.addColumn(new ResellersCenter_Datatable_Column("period"));
        table.addColumn(new ResellersCenter_Datatable_Column("registrar"));
        table.addColumn(new ResellersCenter_Datatable_Column("price"));
        table.addColumn(new ResellersCenter_Datatable_Column("status"));
        table.addColumn(new ResellersCenter_Datatable_Column("nextduedate"));
        table.addColumn(new ResellersCenter_Datatable_Column("expirydate"));
        table.addColumn(new ResellersCenter_Datatable_Column("actions", false, false));
        table.addSearch(new ResellersCenter_Datatable_Search("#RCDomainsTabFilters [name='search']"));

        $("#RCDomainsTabFilters select").each(function(key, value)
        {
            var filter = new ResellersCenter_Datatable_Filter(value);
            filter.initSelect2("orders", "getFiltersData");
            table.addFilter(filter);
        });

        var button = new ResellersCenter_Datatable_Button(".openDeleteDomain", "#RCDomainsTabDelete");
        button.addClickAction(this.deleteOpenHandler);
        button.addSuccessAction(this.deleteSubmitHandler);
        table.addButton(button);

        table.init();
    },

    deleteSubmitHandler: function (self)
    {
        var domainid = $("#RCDomainsTabDelete [name='relid']").val();
        JSONParser.request("terminateService", {relid: domainid, type: 'domain'}, function(result)
        {
            $("#RCDomainsTabDelete").modal("hide");
        });
    },

    deleteOpenHandler: function(self)
    {
        var domainid = $(self).data("domain_id");
        $("#RCDomainsTabDelete [name='relid']").val(domainid);
    },
    export: function()
    {
        window.open('index.php?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType=Domains');
    }
}
ResellersCenter_Services_Domains.init();
{/literal}
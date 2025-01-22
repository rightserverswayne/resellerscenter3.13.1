function ResellersCenter_Datatable()
{
    this.table       = null;

    this.selector    = null;

    this.view        = 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-3 text-center"i><"col-sm-5"p>>>'; //default

    this.source      = "";

    this.columns     = [];

    this.filters     = [];

    this.search      = "";

    this.buttons     = [];

    this.setSelector = function(selector)
    {
        this.selector = selector;
    };

    this.setSource = function(page, action)
    {
        var url = "index.php?m=ResellersCenter&mg-page=" + page + "&mg-action=" + action;
        this.source = url;
    };

    this.setView = function(view)
    {
        this.view = view;
    };

    this.addColumn = function(column)
    {
        this.columns.push(column);
    };

    this.addFilter = function(filter)
    {
        this.filters.push(filter);
    };

    this.addSearch = function(search)
    {
        this.search = search;
    };

    this.addButton = function(button)
    {
        this.buttons.push(button);
    };

    // this.refreshHandlers = function(action)
    // {
    //     this.actions.push(action);
    // };

    this.draw = function()
    {
        var self  = this;
        if (this.table != null) {
            self.initButtons();
            this.table.draw();
            $(this.selector).DataTable().ajax.reload();
        }
    };

    this.init = function()
    {
        //TODO: Add alert with info about missing required variables


        var self  = this;
        var table = this.table = $(this.selector).DataTable(
        {
            bProcessing:    true,
            bServerSide:    true,
            searching:      true,

            columns:        this.buildColumns(),
            sAjaxSource:    this.source,
            sDom:           this.view,

            fnDrawCallback: function()
            {
                self.initButtons();
            },
            fnServerParams: function(data)
            {
                //Required by server
                data.push({name: "json", value: 1});
                data.push({name: "datatable", value: 1});

                //Filters
                data.push({name: "filters", value: self.buildFilters()});
            },
        });

        this.initFilters(table);
        this.initSearch(table);
    };

    this.initFilters = function()
    {
        //Add handler to refresh DataTable contents on filter change
        var self = this;
        $(this.filters).each(function(key, filter)
        {
            $(filter.selector.parent()).on("change.select2", function(e)
            {
                filter.value = $(e.target).val();
                self.table.draw();
            });
        });
    };

    this.initSearch = function()
    {
        var self  = this;
        var timer = null;

        $(this.search.selector).keyup(function()
        {
            clearTimeout(timer);

            var filter = $(this).val();
            timer = setTimeout(function()
            {
                self.table.search(filter).draw();
            },
            500);
        });
    };

    this.initButtons = function()
    {
        var self = this;
        $(this.buttons).each(function(index, button)
        {
            //Remove all previous button handlers - we do not want to stack them
            $(button.selector).unbind("click");
            $(button.selector).on("click", function ()
            {
                button.actions.init(this);
                $(button.modal).modal("show");
            });

            $(button.modal).find("[data-type='confirm']").unbind("click")
            $(button.modal).find("[data-type='confirm']").on("click", function()
            {
                button.actions.success(this);
                self.table.draw();
            });
        });
    };

    this.buildColumns = function()
    {
        var result = [];
        $(this.columns).each(function(key, column)
        {
            result.push(column);
        });

        return result;
    };

    this.buildFilters = function()
    {
        var result = [];
        $(this.filters).each(function(key, filter)
        {
            result.push(filter.getSendData());
        });

        return result;
    };
}
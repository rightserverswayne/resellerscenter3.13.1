function ResellersCenter_Datatable_Filter(selector)
{
    this.selector = selector;

    this.value    = $(selector).val();

    this.name     = $(selector).data("ajaxload");

    this.select2  = null;

    this.getSendData = function()
    {
        return {value: this.value, name: this.name};
    };

    this.initSelect2 = function(page, action)
    {
        this.selector = $(this.selector).select2(
        {
            ajax:
            {
                url: 'index.php?m=ResellersCenter&mg-page=' + page + '&mg-action=' + action + '&json=1&type='+this.name,
                processResults: function (data)
                {
                    var result = JSONParser.getJSON(data);
                    return {results: result.data};
                },
                delay: 250,
            },
            width: '100%',
            allowClear: true,
            placeholder: "",
            selectionTitleAttribute: false
        });

        var self = this;
        $(this.selector).on("select2:select", function(e)
        {
            self.value = e.params.data.id;
            $(".select2-selection__rendered").removeAttr('title');
        });


    };
}

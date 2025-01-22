function ResellersCenter_Datatable_Search(selector)
{
    this.selector = selector;

    this.value    = $(selector).val();

    this.getSendData = function()
    {
        return this.value;
    };
}

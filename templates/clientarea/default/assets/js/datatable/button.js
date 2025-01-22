function ResellersCenter_Datatable_Button(selector, modal)
{
    this.selector   = selector;

    this.modal      = modal;

    this.actions    =
    {
        click:      null,
        success:    null,
        failed:     null
    };

    this.addClickAction = function(callable)
    {
        this.actions.init = callable;
    };
    
    this.addSuccessAction = function (callable)
    {
        this.actions.success = callable;
    };

    this.addFailedAction = function (callable)
    {
        this.actions.failed = callable;
    };
}

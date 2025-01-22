function ResellersCenter_Datatable_Column(name, orderable, sortable, targets)
{
    //Variables
    this.data       = name;
    this.orderable  = (typeof orderable !== "undefined") ? orderable : true;
    this.sortable   = (typeof sortable  !== "undefined") ? sortable  : true;
    this.targets    = (typeof targets   !== "undefined") ? targets   : 0;
}

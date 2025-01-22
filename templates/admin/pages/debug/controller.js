{literal}
var mg_debbugger =
    {
        table: null,
        init: function () {
            $().ready(function () {
                mg_debbugger.loadTable();
            });
        },
        loadTable: function () {
            mg_debbugger.table = $("#debugList").DataTable({
                bProcessing: true,
                bServerSide: true,
                searching: false,
                sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=debug&mg-action=getDataForTable&json=1&datatable=1",
                columns: [
                    {data: "clientName", orderable: false, sortable: false, targets: 0},
                    {data: "resellerName", orderable: false, sortable: false, targets: 0},
                    {data: "foundBy", orderable: false, sortable: false, targets: 0},
                    {data: "type", orderable: false, sortable: false, targets: 0},
                    {data: "sid", orderable: false, sortable: false, targets: 0},
                    {data: "actions", orderable: false, sortable: false, targets: 0},
                ],
                bPaginate: false,
                sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-4 text-center"i><"col-sm-4"p>>>',
                oLanguage: {
                    sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                    sInfo: "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                    sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                    sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                    sProcessing: "",
                    sLengthMenu: "{/literal}{$MGLANG->absoluteT('datatable','lengthMenu')}{literal}",
                    oPaginate: {
                        sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                        sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                    }
                },
                fnDrawCallback: function () {
                    mg_debbugger.registerFixService();
                    mg_debbugger.registerShowService();
                }
            });
        },
        registerFixService: function () {
            $('.fixService').unbind('click');
            $('.fixService').click(function (e) {
                let rid = $(e.target).attr('data-rid');
                let sid = $(e.target).attr('data-sid');
                let type = $(e.target).attr('data-type');

                JSONParser.request("fixservice", {rid: rid, sid: sid, type: type}, () => mg_debbugger.table.ajax.reload(null, false));
            });

        },
        registerShowService() {
            $('.goToService').unbind('click');
            $('.goToService').click(function (e) {
                let cid = $(e.target).attr('data-cid');
                let sid = $(e.target).attr('data-sid');
                let type = $(e.target).attr('data-type');

                let page = 'index.php';
                switch (type) {
                    case 'hosting':
                    case 'addon':
                        page = 'clientsservices.php';
                        break;
                    case 'domain':
                        page = 'clientsdomains.php';
                        break;
                }

                let url = `${page}?userid=${cid}&id=${sid}`;

                window.open(url,'_blank');
            });
        }
    }
mg_debbugger.init();
{/literal}
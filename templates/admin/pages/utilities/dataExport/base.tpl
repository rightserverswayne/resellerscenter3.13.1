<div class="row-fluid">
    <div id="exportData" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-suitcase font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('exportData', 'title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table id='exportDataList' class="table table-hover" width="100%">
                        <thead class='row'>
                            <th>{$MGLANG->T('table', 'exportData', 'data')}&nbsp;&nbsp;</th>
                            <th class='col-sm-1'>{$MGLANG->T('table', 'exportData', 'actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{include file='dataExport/chooseReseller.tpl'}
<script type='text/javascript'>
    {include file='dataExport/controller.js'}
</script>


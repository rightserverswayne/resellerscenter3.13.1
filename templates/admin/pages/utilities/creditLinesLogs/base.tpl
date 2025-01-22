<div id="creditLinesLogs" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-list font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('creditLinesLogsTable','title')}
            </span>
        </div>

        <div class="rc-actions pull-right" style="display: inline-flex;">
            <div id="logsSearch" class="input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="logsListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div>
            <a href="javascript:;" onclick="RC_CreditLinesLogs.openSearchContainer();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class='row'>
            <div class='col-md-12'>
                <table id="logstable" class="table table-hover" width="100%">
                    <thead>
                        <th>{$MGLANG->T('creditLinesLogsTable','#ID')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','client')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','creditLineId')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','balance')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','amount')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','invoiceItemId')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','invoiceId')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','invoiceType')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('creditLinesLogsTable','date')}&nbsp;&nbsp;</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {include file='creditLinesLogs/controller.js'}
</script>
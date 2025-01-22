<div id="monthly" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-calendar font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('monthly','title')}
            </span>
        </div>
            
        <div class="rc-actions pull-right">
            <a href="javascript:;" onclick="RC_Statistics_Monthly.toggleView();" data-toggle="tooltip" title="{$MGLANG->T('button','showTableTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-table"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        
        <div class="graphView row">
            <div class="col-md-12">
                <div id="monthly-income-chart">
                    <canvas height='450'></canvas>
                </div>
            </div>
        </div>
        
        <div class="tableView row">
            <div class="col-md-12">
                <table class="table table-striped table-hover align-vertical">
                    <thead>
                        <th>{$MGLANG->T('monthly','table','month')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('monthly','table','totalsale')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('monthly','table','resellersincome')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('monthly','table','income')}&nbsp;&nbsp;</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
                    
    </div>
</div>
                    
<table id="monthlyTablePrototype" style="display: none;">                
    <tr>
        <td>+month+</td>
        <td>+totalsale+</td>
        <td>+resellersincome+</td>
        <td>+income+</td>
    </tr>
</table>
                    
<script type="text/javascript">
    {include file='monthly/controller.js'}
</script>
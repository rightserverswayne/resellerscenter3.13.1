{**********************************************************************
* ResellersCenter product developed. (2016-07-21)
*
*
*  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
*  CONTACT                        ->       contact@modulesgarden.com
*
*
* This software is furnished under a license and may be used and copied
* only  in  accordance  with  the  terms  of such  license and with the
* inclusion of the above copyright notice.  This software  or any other
* copies thereof may not be provided or otherwise made available to any
* other person.  No title to and  ownership of the  software is  hereby
* transferred.
*
*
**********************************************************************}

{**
* @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
*}
<div id="statistics" class="box light">
    <input hidden value="{$reseller->id}" name="resellerid">

    <div class="box-title">
        <div class="caption">
            <i class="fa fa-bar-chart-o font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('statistics','title')}
            </span>
        </div>
            
        <div class='rc-actions pull-right' style="display: inline-flex">

            <div id='startDate' class="input-group" style="width: 200px;">
                <input type='text' class="form-control input-sm" />
                <span class="input-group-addon">
                    <span class="font-red bold icon-calendar"></span>
                </span>
            </div> 
            
            
            <div style="line-height: 32px;">
                <i class="fa fa-arrows-h" style="color: #AAA; font-size: 20px !important; margin-left: 10px; margin-right: 10px"></i>
            </div>

            <div id='endDate' class="input-group" style="width: 200px; margin-right: 20px;">
                <input type='text' class="form-control input-sm" />
                <span class="input-group-addon">
                    <span class="font-red bold icon-calendar"></span>
                </span>
            </div>
            
{*            <a href="javascript:;" onclick="RC_ResellersStatistics.toggleView();" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-table"></i>
            </a>*}
            
        </div>
    </div>
    <div class="box-body">
        
        <div class="graphView">
            <div id="sales-chart-line">
                <canvas height="450"></canvas>
            </div>
        </div>

        {*<div class="tableView">
            
            <div class="row">
                <div class="col-md-6">
                    <table id="incomeTable" class="table table-striped table-hover align-vertical">
                        <thead>
                            <th>{$MGLANG->T('statistics','table','date')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('statistics','table','totalsale')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('statistics','table','resellerincome')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('statistics','table','income')}&nbsp;&nbsp;</th>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped table-hover align-vertical">
                        <thead>
                            
                        </thead>
                    </table>
                </div>
            </div>
        </div>*}
        
    </div>
</div>
            
<script type="text/javascript">
    {include file='details/statistics/controller.js'}
</script>
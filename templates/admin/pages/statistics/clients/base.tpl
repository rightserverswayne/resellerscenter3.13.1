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

<div id="clientsChart" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-users font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('clients', 'title')}
            </span>
        </div>
    </div>
    <div class="box-body" style="min-height: 150px;">
        <div class="row">
            <div class="col-md-3" style='overflow-y: scroll; max-height: 450px'>
                <table class="table table-striped table-hover align-vertical">
                    <thead>
                        <tr>
                            <th>{$MGLANG->T('clients','table','reseller')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('clients','table','clients')}&nbsp;&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

            <div class="col-md-9">            
                <canvas id="clients-chart-bar" height="450"></canvas>
            </div>   
        </div>
    </div>
</div>
                    
<script type="text/javascript">
    {include file='clients/controller.js'}
</script>
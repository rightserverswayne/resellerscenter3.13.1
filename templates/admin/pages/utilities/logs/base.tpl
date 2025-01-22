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
 <div id="logs" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-exclamation font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('logs','title')}
            </span>
        </div>

        <div class="rc-actions pull-right" style="display: inline-flex;">
            <div id="logsSearch" class="input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="logsListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div>
            <a href="javascript:;" onclick="RC_Logs.openSearchContainer();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
        </div>
            
{*        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#logsInfo0" data-toggle="tab">{$MGLANG->T('info','title')}</a>
            </li>
            <li>
                <a href="#logsErrors" data-toggle="tab">{$MGLANG->T('error','title')}</a>
            </li>
        </ul>*}

    </div>
    <div class="box-body">
        <div class='row'>
            <div class='col-md-12'>
                <table id="logstable" class="table table-hover" width="100%">
                    <thead>
                        <th>{$MGLANG->T('logsTable','#ID')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','admin')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','reseller')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','client')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','message')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','type')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('logsTable','createdat')}&nbsp;&nbsp;</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
                    
<script type="text/javascript">
    {include file='logs/controller.js'}
</script>
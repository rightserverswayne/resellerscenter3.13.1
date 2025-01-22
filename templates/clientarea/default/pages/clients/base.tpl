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


<div id="RCClients">
    <div class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-users font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
            <div class='rc-actions pull-right' style="display: inline-flex">
                <div class="clientsListSearch input-group" style="width: 200px; display: none;">
                    <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                    <input placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
                </div>
                <a href="javascript:;" onclick="ResellersCenter_Clients.export();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-download"></i>
                </a>
                <a href="javascript:;" onclick="ResellersCenter_Clients.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-search"></i>
                </a>
                <a href="javascript:;" onclick="ResellersCenter_Clients.openAddModal();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-hover">
                        <thead>
                            <th>{$MGLANG->T('table','id')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','firstname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','lastname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','companyname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','income')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','creditLine')}  </th>
                            <th>{$MGLANG->T('table','createdat')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{* Add From *}
{include file='add.tpl'}

{* Delete From *}
{include file='delete.tpl'}

<script type="text/javascript">
    {include file='controller.js'}
</script>
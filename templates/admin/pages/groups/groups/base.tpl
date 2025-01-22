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
<div id="groupsBox" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-group font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('groups','title')}
            </span>
            {*<span class="caption-helper">{$MGLANG->T('something')}</span>*}
        </div>

        <div class="rc-actions pull-right" style="display: flex">
            <div class="groupListSearch input-group" style="display: none; margin-left: -200px; position: absolute; top: 17px; width: 200px">
                <span class="input-group-addon" style="background: #FFF;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="groupListFilter" placeholder="" class="form-control" style="border-color: #e5e5e5;" />
            </div>
            <a href="javascript:;" onclick="RC_ConfigurationGroups.openSearchContainer();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
              <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" onclick="RC_ConfigurationGroups.openCreateFormHandler();" data-toggle="tooltip" title="{$MGLANG->T('button','addGroupTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
              <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>
    <div class="box-body" style="height: 300px; overflow: auto;">
        <table id='groupList' class="table group-list-table">
        </table>
    </div>
</div>
        
{* Create Group Form Modal *}
{include file='groups/add.tpl'}

{* Create Group Form Modal *}
{include file='groups/edit.tpl'}

{* Create Remove Form Modal *}
{include file='groups/delete.tpl'}


{* Group Controller *}
<script type="text/javascript">
    {include file='groups/controller.js'}
</script>
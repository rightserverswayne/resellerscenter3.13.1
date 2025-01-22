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
 <div id="docsPdf" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-book font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('resellerDocumentation', 'list', 'title')}
            </span>
        </div>
            
        <div class="rc-actions pull-right" style="display: inline-flex;">
            <div id="DocumentationListSearch" class="input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="DocumentationListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="RC_ResellerDocumentation.openSearchContainer();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
              <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" onclick="RC_ResellerDocumentation.openAddForm();" data-toggle="tooltip" title="{$MGLANG->T('button','addDocumentationTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
              <i class="fa fa-plus"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <table id='DocumentationList' class="table table-hover" width="100%">
                <thead>
                    <th>{$MGLANG->T('resellerDocumentation', 'table', 'id')}&nbsp;&nbsp;</th>
                    <th>{$MGLANG->T('resellerDocumentation', 'table', 'name')}&nbsp;&nbsp;</th>
                    <th>{$MGLANG->T('resellerDocumentation', 'table', 'created_at')}&nbsp;&nbsp;</th>
                    <th>{$MGLANG->T('resellerDocumentation', 'table', 'updated_at')}&nbsp;&nbsp;</th>
                    <th>{$MGLANG->T('resellerDocumentation', 'table', 'actions')}&nbsp;&nbsp;</th>
                </thead>
            </table>
        </div>
    </div>
</div>

{* Include add form *}
{include file='resellerDocumentation/add.tpl'}

{* Include delete modal *}
{include file='resellerDocumentation/delete.tpl'}
            
<script type='text/javascript'>
    {include file='resellerDocumentation/controller.js'}
</script>

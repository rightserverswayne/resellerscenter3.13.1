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
<div class="row-fluid">
    <div id="resellers" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-suitcase font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>

            <div class="rc-actions pull-right" style="display: inline-flex;">
                <div id="resellerSearch" class="input-group" style="width: 200px; display: none;">
                    <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                    <input id="resellerListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
                </div> 
                <a href="javascript:;" onclick="RC_Resellers.openSearchContainer();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                  <i class="fa fa-search"></i>
                </a>
                <a href="javascript:;" onclick="RC_Resellers.openAddForm();" data-toggle="tooltip" title="{$MGLANG->T('button','addTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                  <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table id='resellerList' class="table table-hover" width="100%">
                        <thead>
                            <th>{$MGLANG->T('table', 'groupname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'firstname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'lastname')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'company')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'totalsale')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'monthsale')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'creditline')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'status')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'createdat')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('table', 'actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
            
{* Add Form *}
{include file='add.tpl'}

{* Delete Form *}
{include file='delete.tpl'}

<script type='text/javascript'>
    {include file='controller.js'}
</script>
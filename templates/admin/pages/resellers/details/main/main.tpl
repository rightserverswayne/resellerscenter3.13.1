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
<div id="mainDetails" class="box light" style="height:96%">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-info font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('details','title')}
            </span>
        </div>
        <div class='rc-actions pull-right'>
            <a href="javascript:;" onclick="RC_ResellersMainDetails.openDeleteModal();" data-toggle="tooltip" title="{$MGLANG->T('button','deleteResellerTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-danger btn-icon-only" style='margin-right: 32px !important;'>
                <i class="fa fa-trash-o"></i>
            </a>
        </div>
    </div>
    <div class="box-body" style="display: flex">
        <div class="scroller">
            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','group')}</label>
                </div>
                <div id="changeGroup" data-groupid="{$reseller->group->id}" class="col-md-6 col-sm-6 col-xs-10">
                    <a href="addonmodules.php?module=ResellersCenter&mg-page=groups&gid={$reseller->group_id}"> 
                        {$reseller->group->name}
                    </a>
                    <form style="display: none;">
                        <input hidden name="resellerid" value="{$reseller->id}">
                        <select class='form-control' name='groupid' onchange="RC_ResellersMainDetails.showConfirmModal();" style="width: 75%; display: inline;">
                            {foreach from=$groups item=group}
                                <option value="{$group->id}">{$group->name}</option>
                            {/foreach}
                        </select>
                        
                        <button class="btn btn-sm btn-icon-only btn-danger btn-inverse" data-toggle="tooltip" title="{$MGLANG->T('button','cancelEditGroupTooltip')}" onclick="RC_ResellersMainDetails.hideGroupEdit(this); return false;" style="margin-bottom: 5px !important;"><i class="fa fa-close"></i></button>
                    </form>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-2">
                    <div class='rc-actions'>
                        <a href="javascript:;" onclick="RC_ResellersMainDetails.showGroupEdit(this);" data-toggle="tooltip" title="{$MGLANG->T('button','editGroupTooltip')}" class="groupEditBtn btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only pull-right">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','name')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    <a href="clientssummary.php?userid={$reseller->client->id}">
                        #{$reseller->client->id} {$reseller->client->firstname} {$reseller->client->lastname}
                    </a>
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','companyname')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    {$reseller->client->companyname}
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','email')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    {$reseller->client->email}
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','totalsale')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    {$currency.prefix}{$totalsale}{$currency.suffix}
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','lastmonth')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    {$currency.prefix}{$monthlysale}{$currency.suffix}
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','since')}</label>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-10">
                    {$reseller->created_at}
                </div>
            </div>

            <div class="maindetails_flex col-lg-12 col-md-6 col-sm-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <label>{$MGLANG->T('details','main','creditLine')}</label>
                </div>
                {if is_null($creditline->limit) || $creditline->limit == '0.00' }
                    <div class="col-md-6 col-sm-6 col-xs-10">
                        -
                    </div>
                {else}
                    <div class="col-md-6 col-sm-6 col-xs-10">
                        {$currency.prefix}{$creditline->usage}{$currency.suffix}  /  {$currency.prefix}{$creditline->limit}{$currency.suffix}
                    </div>
                {/if}

            </div>
        </div>
    </div>
</div>
            
{* Delete Reseller Modal *}
{include file='details/main/delete.tpl'}

{* Change Grooup Confirm Modal *}
{include file='details/main/changegroup.tpl'}

<script type="text/javascript">            
    {include file='details/main/controller.js'}
</script>
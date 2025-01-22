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
<div class="panel contentListPanel">
    
    {if !$tlds}
        <div class="alert alert-warning">
            {$MGLANG->T('settings','domains','empty_warning')}
        </div>
    {/if}
    
    <div class="box-title" >
        <div class="caption" style="line-height: 26px;">
            <i class="fa fa-globe font-red-thunderbird"></i>
            <strong class="font-red-thunderbird uppercase">
                {$MGLANG->T('settings','domains','title')}
            </strong>
            <div class="rc-actions pull-right" style="display: inline-flex">
                <div class="contentsListSearch input-group" style="width: 200px; display: none;">
                    <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                    <input id="domainsListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
                </div> 
                <a href="javascript:;" onclick="RC_ConfigurationSettings.showSearchInput(this);" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                    <i class="fa fa-search"></i>
                </a>
                <a href="javascript:;" onclick="RC_SettingsDomains.openAddForm();" data-toggle="tooltip" title="{$MGLANG->T('button','addPricingTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                    <i class="fa fa-plus"></i>
                </a>
                <a href="javascript:;" onclick="RC_SettingsDomains.openMassiveAddForm();" data-toggle="tooltip" title="{$MGLANG->T('button','addMassiveTld')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                    <i class="fa fa-tasks"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row-fluid">
            <div class="col-md-12">
                <table id="domainsTable" class="table table-hover" width="100%">
                    <thead>
                        <th>{$MGLANG->T('settings','domains','table','tld_extension')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('settings','domains','table','type')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('settings','domains','table','counting_type')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('settings','domains','table','profit_percent')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('settings','domains','table','profit_value')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('settings','domains','table','actions')}&nbsp;&nbsp;</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
                                        
{* Add Addon Form Modal *}
{include file='contents/domains/add.tpl'}

{* Add All TLDs Form Modal *}
{include file='contents/domains/massive-add.tpl'}

{* Edit Pricing Form Modal *}
{include file='contents/domains/pricing.tpl'}

{* Edit Configuration Form Modal *}
{include file='contents/domains/config.tpl'}

{* Confirm Delete Form Modal *}
{include file='contents/domains/delete.tpl'}

{* Controller *}
<script type="text/javascript">
    {include file='contents/domains/controller.js'}
</script>
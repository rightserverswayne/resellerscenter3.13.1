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
<div id="services" class="box light">
    <div class="box-title tabbable-line">
        <ul class="nav nav-tabs nav-left">
            <li class="active">
                <a href="#servicesTabHosting" data-toggle="tab">{$MGLANG->T('hosting','title')}</a>
            </li>
            <li onclick="RC_ResellersServices.loadAddonsTable()">
                <a href="#servicesTabAddons" data-toggle="tab">{$MGLANG->T('addons','title')}</a>
            </li>
            <li onclick="RC_ResellersServices.loadDomainsTable()">
                <a href="#servicesTabDomains" data-toggle="tab">{$MGLANG->T('domains','title')}</a>
            </li>
        </ul>
            
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="servicesListSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="servicesListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="RC_ResellersServices.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" onclick="RC_ResellersServices.openAddModal();" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-plus"></i>
            </a>
            <a href="javascript:;" class="btn btn-circle btn-outline btn-inverse btn-default btn-icon-only" data-toggle="tooltip"  title="{$MGLANG->T('services','help')}" >
                <i class="fa fa-question"></i>
            </a>
        </div>    
    </div>
    <div class="box-body">
        <div class="tab-content">
            <div class="tab-pane active" id="servicesTabHosting">
                <div class="scroller">
                    <table id='hostingList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                        <thead>
                            <th>{$MGLANG->T('hosting','table','#ID')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','product')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','domain')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','client')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','price')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','billingcycle')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('hosting','table','actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="servicesTabAddons">
                <div class="scroller">
                    <table id='addonList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                        <thead>
                            <th>{$MGLANG->T('addons','table','#ID')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','addon')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','hosting')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','client')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','price')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','billingcycle')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('addons','table','actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="servicesTabDomains">
                <div class="scroller">
                    <table id='domainList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                        <thead>
                            <th>{$MGLANG->T('domains','table','#ID')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('domains','table','domain')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('domains','table','client')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('domains','table','recurringamount')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('domains','table','registrationperiod')}&nbsp;&nbsp;</th>
                            <th>{$MGLANG->T('domains','table','actions')}&nbsp;&nbsp;</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
            
{* Add Form *}
{include file='details/services/add.tpl'}

{* Reasign Form *}
{include file='details/services/reassign.tpl'}

{* Config Form *}
{include file='details/services/config.tpl'}

{* Delete Modal *}
{include file='details/services/delete.tpl'}

<script type='text/javascript'>
    {include file='details/services/controller.js'}
</script>
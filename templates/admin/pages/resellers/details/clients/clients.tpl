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

<div id="clients" class="box light">
    <div class="box-title tabbable-line">
        <ul class="nav nav-tabs nav-left">
            <li class="active">
                <a href="#clientsTab" data-toggle="tab">{$MGLANG->T('clients','title')}</a>
            </li>
        </ul>
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="clientListSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input id="clientListFilter" placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="RC_ResellersClients.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="javascript:;" onclick="RC_ResellersClients.openAddModal();" data-toggle="tooltip" title="{$MGLANG->T('button','addClientTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-plus"></i>
            </a>
            <a href="javascript:;" class="btn btn-circle btn-outline btn-inverse btn-default btn-icon-only" data-toggle="tooltip"  title="{$MGLANG->T('clients','help')}" >
                <i class="fa fa-question"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
         
        <div class="tab-content">
             <div class="tab-pane active" id="clientsTab">
                <table id='clientsList' class="table table-hover" data-resellerid="{$reseller->id}" width="100%">
                    <thead>
                        <th>{$MGLANG->T('clients','table','#ID')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','firstname')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','lastname')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','company')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','income')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','createdat')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('clients','table','actions')}&nbsp;&nbsp;</th>
                    </thead>
                </table>
             </div>
         </div>
        
    </div>
</div>
            
{* Add Form *}
{include file='details/clients/add.tpl'}

{* Delete Modal *}
{include file='details/clients/delete.tpl'}

<script type='text/javascript'>
    {include file='details/clients/controller.js'}
</script>
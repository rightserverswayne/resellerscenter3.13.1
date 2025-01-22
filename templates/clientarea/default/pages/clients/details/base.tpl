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
<div id="RCClientDetails" class="row-fluid">
    <div class="box light">
        <div class="box-title tabbable-line">
            <div class="caption">
                <i class="fa fa-info font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('details','title')}
                </span>
            </div>
                
            <div class="rc-actions with-tabs">
                <a href="javascript:;" onclick="ResellersCenter_ClientsDetails.goBack();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                  <i class="fa fa-reply"></i>
                </a>
            </div>
                
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#RCClientsInfo" aria-controls="RCClientsInfo" role="tab" data-toggle="tab">{$MGLANG->T('details','info','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCClientsOrders" aria-controls="RCClientsOrders" role="tab" data-toggle="tab">{$MGLANG->T('details','orders','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCClientsInvoices" aria-controls="RCClientsInvoices" role="tab" data-toggle="tab">{$MGLANG->T('details','invoices','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCClientsServices" aria-controls="RCClientsServices" role="tab" data-toggle="tab">{$MGLANG->T('details','services','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCClientsAddons" aria-controls="RCClientsAddons" role="tab" data-toggle="tab">{$MGLANG->T('details','addons','title')}</a>
                </li>
                <li role="presentation">
                    <a href="#RCClientsDomains" aria-controls="RCClientsDomains" role="tab" data-toggle="tab">{$MGLANG->T('details','domains','title')}</a>
                </li>
            </ul>
        </div>
        <div class="box-body">
            
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="RCClientsInfo">
                    {include file='details/info/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCClientsServices">
                    {include file='details/services/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCClientsAddons">
                    {include file='details/addons/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCClientsDomains">
                    {include file='details/domains/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCClientsInvoices">
                    {include file='details/invoices/base.tpl'}
                </div>
                <div role="tabpanel" class="tab-pane" id="RCClientsOrders">
                    {include file='details/orders/base.tpl'}
                </div>
            </div>
            

        </div>
    </div>
</div>
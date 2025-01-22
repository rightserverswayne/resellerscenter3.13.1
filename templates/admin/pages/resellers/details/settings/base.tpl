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
<div id="settings" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-cogs font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('settings', 'title')}
            </span>
        </div>
            
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#generalTab" data-toggle="tab" >
                    {$MGLANG->T('general', 'title')}
                </a>
            </li>
            <li>
                <a href="#billingTab" data-toggle="tab">
                    {$MGLANG->T('billing', 'title')}
                </a>
            </li>
            <li>
                <a href="#miscTab" data-toggle="tab">
                    {$MGLANG->T('misc', 'title')}
                </a>
            </li>
            <li>
                <a href="#emailTemplatesTab" data-toggle="tab">
                    {$MGLANG->T('emailtemplates', 'title')}
                </a>
            </li>
        </ul>
    </div>
    <div class="box-body" style='height: 500px; overflow-y: auto'>
        <form id="resellerConfigurationForm" action=''>
            <input hidden value="{$reseller->id}" name="resellerid">
            <div class="tab-content" style="width: 98%;">
                <div class="tab-pane active" id="generalTab">
                    <div class="scroller">
                        {include file='details/settings/general.tpl'}
                    </div>
                </div>
                <div class="tab-pane" id="billingTab">
                    <div class="scroller">
                        {include file='details/settings/billing.tpl'}
                    </div>
                </div>
                <div class="tab-pane" id="miscTab">
                    <div class="scroller">
                        {include file='details/settings/misc.tpl'}
                    </div>
                </div>
                <div class="tab-pane" id="emailTemplatesTab">
                    <div class="scroller">
                        {include file='details/settings/emailtemplates.tpl'}
                    </div>
                </div>
            </div>
                    
            <div class='col-md-12'>
                <div class='rc-actions pull-right'>
                    <button class='saveConfigBtn btn btn-success btn-inverse'>{$MGLANG->T('general','save')}</button>
                </div>
            </div>
        </form>
    </div>
</div>
                
<script type='text/javascript'>
    {include file='details/settings/controller.js'}
</script>
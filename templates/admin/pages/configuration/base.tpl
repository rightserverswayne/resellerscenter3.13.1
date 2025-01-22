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

<form id="configurationForm">
    
    {* This are default settings *}
    <input hidden name="resellerid" value="0" />
    
    <div id="general" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-cogs font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('general','title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            {include file='general.tpl'}
        </div>
    </div>
    <div id="billing" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-dollar font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('billing','title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            {include file='billing.tpl'}
        </div>
    </div>

    <div id="emailTemplates" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-envelope font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('emailtemplates','title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            {include file='emailtemplates.tpl'}
        </div>
    </div>

    <div id="whmcsAPI" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-envelope font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('whmcsapi','title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            {include file='whmcsapi.tpl'}
        </div>
    </div>
</form>

<div class='row'>
    <div class='col-md-12'>
        <div class='rc-actions text-center'>
            <button class='saveConfigBtn btn btn-lg btn-success btn-inverse' onclick="RC_Configuration.submitConfigurationForm();">{$MGLANG->T('general','save')}</button>
        </div>
    </div>
</div>  

            
<script type="text/javascript">
    {include file='controller.js'}
</script>
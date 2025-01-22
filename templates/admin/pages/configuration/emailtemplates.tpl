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
<div class="row">
    <div class='col-md-12'>
        <div class="help-block">{$MGLANG->T('emailtemplates', 'help')}</div>
    </div>
</div>

<div class="row">
    <div class="box light" style="padding-top: 0px; margin-bottom: 0px;">
        <div class="box-title tabbable-line">
            <ul class="nav nav-tabs nav-left">
                <li class="active">
                    <a href="#emailTabsGeneral" data-toggle="tab">{$MGLANG->T('emailtemplates','general','label')}</a>
                </li>
                <li>
                    <a href="#emailTabsProduct" data-toggle="tab">{$MGLANG->T('emailtemplates','product','label')}</a>
                </li>
                <li>
                    <a href="#emailTabsDomain" data-toggle="tab">{$MGLANG->T('emailtemplates','domain','label')}</a>
                </li>
                <li>
                    <a href="#emailTabsSupport" data-toggle="tab">{$MGLANG->T('emailtemplates','support','label')}</a>
                </li>
                <li>
                    <a href="#emailTabsInvoice" data-toggle="tab">{$MGLANG->T('emailtemplates','invoice','label')}</a>
                </li>
                {if $isWhmcs8}
                    <li>
                        <a href="#emailTabsInvite" data-toggle="tab">{$MGLANG->T('emailtemplates','invite','label')}</a>
                    </li>
                    <li>
                        <a href="#emailTabsUser" data-toggle="tab">{$MGLANG->T('emailtemplates','user','label')}</a>
                    </li>
                {/if}
            </ul>
        </div>
        <div class="box-body">
            <div class="tab-content">
                {assign var=templateTypes value=['general', 'product', 'domain', 'support', 'invoice', 'invite', 'user']}
                {foreach from=$templateTypes key=index item=type}
                    <div class="tab-pane {if $index eq 0}active{/if}" id="emailTabs{$type|ucfirst}">
                        <div class="scroller">
                            <div class="col-md-6">
                                {assign var=splited value=false}
                                {foreach from=$emailTemplates.$type key=index item=template}
                                    {assign var=templateName value=$template->name}
                                        <div class='row'>
                                            <div class="col-md-6 col-sm-6 col-xs-6">
                                                <label class="checkbox-switch-label"><a href="configemailtemplates.php?action=edit&id={$template->id}">{$template->name}</a></label>
                                            </div>
                                            {if empty($settings.emailTemplates)}
                                                {$settings.emailTemplates = []}
                                            {/if}
                                            <div class='col-md-6 col-sm-6 col-xs-6'>
                                                <div class="checkbox-container">
                                                    <input  class="checkbox-switch" 
                                                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                                                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                                                            data-on-color="success" 
                                                            data-off-color="default"  
                                                            data-size="mini" 
                                                            data-label-width="15" 
                                                            type="checkbox"  
                                                            name="settings[emailTemplates][{$template->name}]"
                                                            {if $template->name|array_key_exists:$settings.emailTemplates}checked{/if}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        {if $index >= ($emailTemplates.$type|@count / 2  - 1) && !$splited}
                                            {assign var=splited value=true}
                                            </div><div class="col-md-6">
                                        {/if}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
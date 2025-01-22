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
                        {foreach from=$emailTemplates.$type key=index item=template}
                            <div class='row'>
                                <div class='col-md-12'>
                                    <div class="col-md-10 col-sm-10 col-xs-10">
                                        <label class="checkbox-switch-label"><a href="configemailtemplates.php?action=edit&id={$template->id}">{$template->name}</a></label>
                                    </div>
                                    <div class='col-md-2 col-sm-2 col-xs-2'>
                                        <div class="checkbox-container pull-right">
                                            <input  class="checkbox-switch"
                                                data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                                                data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                                                data-on-color="success"
                                                data-off-color="default"
                                                data-size="mini"
                                                data-label-width="15"
                                                type="checkbox"
                                                name="settings[emailTemplates][{$template->name}]"
                                                {if $settings->emailTemplates}
                                                    {if $template->name|array_key_exists:$settings->emailTemplates}
                                                        checked
                                                    {/if}
                                                {/if}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
<div id="RCEmailTemplateEdit" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-edit font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('emails','details','title')}
            </span>
            <span class="caption-helper">{$templates[0]->name}</span>
        </div>
            
        <div class="rc-actions with-tabs">
            <a href="javascript:;" onclick="ResellersCenter_EmailTemplates.addLanguageHandler();" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
              <i class="fa fa-plus"></i>
            </a>
        </div>
        
        <ul class="nav nav-tabs">
            {foreach from=$templates key=index item=template}
                {if $template->language}
                    {assign var=language value=$template->language}
                {else}
                    {assign var=language value=default}
                {/if}
                
                <li class="{if $index eq 0}active{/if}">
                    <a href="#langTab_{$language}" data-toggle="tab" style="display: inline; line-height: 44px">
                        {$language|ucfirst}
                    </a>
                    
                    {if $language neq 'default'}<button type="button" class="close deleteLanguage" data-language="{$language}" style="line-height: 44px"><span aria-hidden="true">×</span><span class="sr-only"></span></button>{/if}
                </li>
            {/foreach}
        </ul>
        
    </div>
    <div class="box-body" style='min-height: 320px;'>
        <form id="editTemplateForm">
            <input hidden name="name" value="{$template->name}">
            <div class="tab-content">
                {foreach from=$templates key=index item=template}
                    {if $template->language}
                        {assign var=language value=$template->language}
                    {else}
                        {assign var=language value=default}
                    {/if}

                    <div id="langTab_{$language}" class="tab-pane {if $index eq 0}active{/if}">

                        <div class="row">
                            <div class="col-md-1">
                                <label class="pull-right">{$MGLANG->T('emails','subject')}</label>
                            </div>

                            <div class="col-md-11">
                                <input class="form-control" type="text" value="{$template->subject}" name="templates[{$language}][subject]" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <textarea class="form-control tinyMCE" name="templates[{$language}][message]">
                                    {$template->message}
                                </textarea>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </form>

        <div class="row">
            <div class="col-md-12">
                <center>
                    <button class="btn btn-success btn-inverse" onclick="ResellersCenter_EmailTemplates.saveChanges();">{$MGLANG->T('emails','save')}</button>
                    <a class="btn btn-default" href="index.php?m=ResellersCenter&mg-page=configuration#RCConfigEmailTemplates">{$MGLANG->T('emails','goback')}</a>
                </center>
            </div>
        </div>
                    
        {include file='emailTemplates/mergefields.tpl'}

    </div>
</div>
            
{include file='emailTemplates/addLang.tpl'}
        
<script type="text/javascript">
    {include file='emailTemplates/controller.js'}
</script>
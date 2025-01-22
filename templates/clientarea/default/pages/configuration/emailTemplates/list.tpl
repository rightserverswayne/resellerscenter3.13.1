{if $globalSettings->allowEmailGlobalsEdit}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emails','emailGlobalCSS','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <textarea class="form-control input-sm" name="settings[emailGlobalCSS]" style="min-height: 100px">{$settings->emailGlobalCSS}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emails','emailGlobalHeader','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <textarea class="form-control input-sm" name="settings[emailGlobalHeader]" style="min-height: 100px">{$settings->emailGlobalHeader}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emails','emailGlobalFooter','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <textarea class="form-control input-sm" name="settings[emailGlobalFooter]" style="min-height: 100px">{$settings->emailGlobalFooter}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
{/if}


<div class="row">
    <div class="col-md-6">
        
        {* GENERAL *}
        <h4>
            <strong>{$MGLANG->T('emails','general','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.general}
                {foreach from=$emailTemplates.general item=template}
                    {if $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>
        
        {* SUPPORT *}
        <h4>
            <strong>{$MGLANG->T('emails','support','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.support}
                {foreach from=$emailTemplates.support item=template}
                    {if $template->type eq 'support' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}            
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>

        {* USERS *}
        <h4>
            <strong>{$MGLANG->T('emails','user','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.user}
                {foreach from=$emailTemplates.user item=template}
                    {if $template->type eq 'user' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>
    </div>
    
    <div class="col-md-6">
        {* PRODUCTS / SERVICES *}
        <h4>
            <strong>{$MGLANG->T('emails','product','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.product}
                {foreach from=$emailTemplates.product item=template}
                    {if $template->type eq 'product' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>
        
        {* DOMAINS *}
        <h4>
            <strong>{$MGLANG->T('emails','domain','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.domain}
                {foreach from=$emailTemplates.domain item=template}
                    {if $template->type eq 'domain' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>
        
        
        {* INVOICES *}
        <h4>
            <strong>{$MGLANG->T('emails','invoice','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.invoice}
                {foreach from=$emailTemplates.invoice item=template}
                    {if $template->type eq 'invoice' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>


        {* INVITES *}
        <h4>
            <strong>{$MGLANG->T('emails','invite','title')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {if $emailTemplates.invite}
                {foreach from=$emailTemplates.invite item=template}
                    {if $template->type eq 'invite' && $template->name|array_key_exists:$availableEmailTemplates}
                        <tr>
                            <td>{$template->name}</td>
                            <td style="width: 40px;">
                                <a href="?m=ResellersCenter&mg-page=configuration&mg-action=editTemplate&name={$template->name}" class="btn btn-xs btn-primary btn-inverse icon-only">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            {else}
                <tr><td><center>{$MGLANG->T('emails','empty')}</center></td></tr>
            {/if}
        </table>
    </div>
</div>

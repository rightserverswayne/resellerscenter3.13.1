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
<div class='row'>
    <div class='col-md-6 col-sm-12'>
        
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','status','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input  class="checkbox-switch"
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[status]"  
                            {if $settings->status}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','status','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','branding','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input  class="checkbox-switch"
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                            data-on-color="success"
                            data-off-color="default"
                            data-size="mini"
                            data-label-width="15"
                            type="checkbox"
                            name="settings[branding]"
                            {if $settings->branding}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','branding','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','products','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"
                            name="settings[products]"
                            {if $settings->products}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','products','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','domains','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[domains]"
                            {if $settings->domains}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','domains','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','cname','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[cname]"
                            {if $settings->cname}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','cname','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','login','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[login]"
                           {if $settings->login}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','login','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','order','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[order]"
                           {if $settings->order}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','order','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','suspend','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch"
                           data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}"
                           data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}"
                           data-on-color="success"
                           data-off-color="default"
                           data-size="mini"
                           data-label-width="15"
                           type="checkbox"
                           name="settings[suspend]"
                           {if $settings->suspend}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','suspend','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','emailredirect','label')}</label>
            </div>
            <div class='col-md-3 col-sm-3'>
                <div class="checkbox-container">
                    <input class="checkbox-switch" 
                            data-on-text="{$MGLANG->absoluteT('bootstrapswitch','enabled')}" 
                            data-off-text="{$MGLANG->absoluteT('bootstrapswitch','disabled')}" 
                            data-on-color="success" 
                            data-off-color="default"  
                            data-size="mini" 
                            data-label-width="15" 
                            type="checkbox"  
                            name="settings[emailredirect]"
                            {if $settings->emailredirect}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','emailredirect','help')}</div>
            </div>
        </div>    
            
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('general','documentation','label')}</label>
            </div>
            <div class='col-md-12'>
                <select class="form-control select2" name="settings[documentation]">
                    {foreach from=$documentations item=documentation}
                        <option value="{$documentation->id}" {if $documentation->id eq $settings->documentation}selected{/if}>
                            {$documentation->name}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
            
    </div>
    <div class='col-md-6 col-sm-12'>
            
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('general','ticketdepartment','label')}</label>
            </div>
            <div class='col-md-12'>
                <select multiple class="form-control select2" name="settings[ticketDeptids][]">
                    {foreach from=$ticketDepts item=department}
                        <option value="{$department->id}" {if $settings->ticketDeptids && $department->id|in_array:$settings->ticketDeptids}selected{/if}>
                            {$department->name}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('general','ticketdepartment','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('general','whmcstemplate','label')}</label>
            </div>
            <div class='col-md-12'>
                    <select multiple class="form-control select2" name="settings[whmcsTemplates][]">
                        {foreach from=$whmcsTemplates item=template}
                            <option value="{$template}" {if $settings->whmcsTemplates && $template|in_array:$settings->whmcsTemplates}selected{/if}>
                                {$template|ucfirst}
                            </option>
                        {/foreach}
                    </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('general','whmcstemplate','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('general','ordertemplate','label')}</label>
            </div>
            <div class='col-md-12'>
                    <select multiple class="form-control select2" name="settings[orderTemplates][]">
                        {foreach from=$orderTemplates item=template}
                            <option value="{$template}" {if $settings->orderTemplates && $template|in_array:$settings->orderTemplates}selected{/if}>
                                {$template|ucfirst}
                            </option>
                        {/foreach}
                    </select>
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('general','ordertemplate','help')}</div>
            </div>
        </div>
    </div>
</div>
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
    <div class='col-md-6'>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','disallowMainStore','label')}</label>
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
                            name="settings[disallowMainStore]"
                            {if $settings->disallowMainStore}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','disallowMainStore','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','disableKb','label')}</label>
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
                            name="settings[disableKb]"
                            {if $settings->disableKb}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','disableKb','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','hideClientLogin','label')}</label>
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
                            name="settings[hideClientLogin]"
                            {if $settings->hideClientLogin}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','hideClientLogin','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','hideDelete','label')}</label>
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
                            name="settings[hideDelete]"
                            {if $settings->hideDelete}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','hideDelete','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','customMailSettings','label')}</label>
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
                            name="settings[customMailSettings]"
                            {if $settings->customMailSettings}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','customMailSettings','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','customDateFormat','label')}</label>
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
                            name="settings[customDateFormat]"
                            {if $settings->customDateFormat}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','customDateFormat','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','hideSSO','label')}</label>
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
                            name="settings[hideSSO]"
                            {if $settings->hideSSO}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','hideSSO','help')}</div>
            </div>
        </div>

    </div>
            
    <div class='col-md-6'>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('general','promotions','label')}</label>
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
                           name="settings[promotions]"
                           {if $settings->promotions}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('general','promotions','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','allowEmailGlobalsEdit','label')}</label>
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
                            name="settings[allowEmailGlobalsEdit]"
                            {if $settings->allowEmailGlobalsEdit}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','allowEmailGlobalsEdit','help')}</div>
            </div>
        </div>
            
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','hideClientDetails','label')}</label>
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
                            name="settings[hideClientDetails]"
                            {if $settings->hideClientDetails}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','hideClientDetails','help')}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-3">
                <label class="checkbox-switch-label">{$MGLANG->T('misc','sendDefaultEmails','label')}</label>
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
                           name="settings[sendDefaultEmails]"
                           {if $settings->sendDefaultEmails}checked{/if}
                    />
                </div>
            </div>
            <div class='col-md-6 col-sm-6'>
                <div class='help-block'>{$MGLANG->T('misc','sendDefaultEmails','help')}</div>
            </div>
        </div>
    </div>
</div>       

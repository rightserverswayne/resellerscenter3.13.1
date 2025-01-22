<form action="">

    {* Show documentation and dashboard status*}
    <input hidden name="settings[docsDoNotShowAgain]" value="{$settings->docsDoNotShowAgain}" />
    <input hidden name="settings[skipResellerDashboard]" value="{$settings->skipResellerDashboard}" />

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('serviceTerms','gracePeriod','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input" name="settings[gracePeriod]" type="number" value="{if $settings->gracePeriod neq ''}{$settings->gracePeriod}{else}0{/if}" />
                        <div class="help-block">{$MGLANG->T('serviceTerms','gracePeriod','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('serviceTerms','holdPeriod','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input" name="settings[holdPeriod]" type="number" value="{if $settings->holdPeriod neq ''}{$settings->holdPeriod}{else}0{/if}" />
                        <div class="help-block">{$MGLANG->T('serviceTerms','holdPeriod','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('serviceTerms','terminatePeriod','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input" name="settings[terminatePeriod]" type="number" value="{if $settings->terminatePeriod neq ''}{$settings->terminatePeriod}{else}0{/if}" />
                        <div class="help-block">{$MGLANG->T('serviceTerms','terminatePeriod','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('serviceTerms','blockType','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <select class="form-control input" name="settings[blockType]">
                            <option value="">
                                {$MGLANG->T('serviceTerms','blockType','type','none')}
                            </option>
                            <option value="userAccount" {if $settings->blockType eq "userAccount"}selected{/if}>
                                {$MGLANG->T('serviceTerms','blockType','type','blockUserAccount')}
                            </option>
                            <option value="unpaidServices" {if $settings->blockType eq "unpaidServices"}selected{/if}>
                                {$MGLANG->T('serviceTerms','blockType','type','blockUnpaidServices')}
                            </option>
                        </select> <div class="help-block">{$MGLANG->T('serviceTerms','blockType','help')}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-4" style="padding-left:25px;">
                        <label>{$MGLANG->T('serviceTerms','reminders','label')}</label>
                        <div class="help-block">{$MGLANG->T('serviceTerms','reminders','help')}</div>
                    </div>
                    <div class="col-md-8" id="remindersGroup">
                        {if empty($reminders)}
                            <span class="noRemindersMessage">{$MGLANG->T('serviceTerms','reminders','noReminders')}</span>
                        {else}
                            {foreach from=$reminders key=dataKey item=reminder}
                                <div class="row">
                                    <div class="col-md-9">
                                        <select class="form-control input" name="reminders[name][]">
                                            {foreach from=$remindersTemplates item=template}
                                                <option value="{$template}" {if $reminder->name eq $template}selected{/if}>{$template}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input class="form-control input" name="reminders[days][]" type="number" value="{if $reminder->days neq ''}{$reminder->days}{else}0{/if}" />
                                    </div>
                                    <div class="col-md-1 removeReminderBtnContainer">
                                        <i class="fas fa-minus-circle removeReminder"></i>
                                    </div>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                </div>
                <div class="row text-right">
                    <i class="fas fa-plus-circle addReminder"></i>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="row inputsForClone">
    <div class="col-md-9">
        <select disabled class="form-control input" name="reminders[name][]">
            {foreach from=$remindersTemplates item=template}
                <option value="{$template}">{$template}</option>
            {/foreach}
        </select>
    </div>
    <div class="col-md-2">
        <input disabled class="form-control input" name="reminders[days][]" type="number" value="0" />
    </div>
    <div class="col-md-1 removeReminderBtnContainer">
        <i class="fas fa-minus-circle removeReminder"></i>
    </div>
</div>

<script type="text/javascript">
    {include file='serviceTerms/controller.js'}
</script>


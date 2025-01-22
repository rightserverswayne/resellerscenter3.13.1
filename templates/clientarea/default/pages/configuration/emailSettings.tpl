<form action="">

    <div class="row">
        <div class="col-md-6">

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emailSettings','mailHostName','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input-sm" name="settings[mailHostName]" value="{$settings->mailHostName}" />
                        <div class="help-block">{$MGLANG->T('emailSettings','mailHostName','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emailSettings','mailPort','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input-sm" name="settings[mailPort]" value="{$settings->mailPort}" />
                        <div class="help-block">{$MGLANG->T('emailSettings','mailPort','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emailSettings','smtpSslType','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <select class="form-control input-sm" name="settings[smtpSslType]">
                            {foreach from=$availableSecureTypes key=typeKey item=type}
                                <option value="{$typeKey}" {if $settings->smtpSslType eq $typeKey}selected{/if}>{$MGLANG->T('emailSettings','secureTypeSelect',{$type})}</option>
                            {/foreach}
                        </select>
                        <div class="help-block">{$MGLANG->T('emailSettings','smtpSslType','help')}</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-6">

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emailSettings','mailUserName','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input-sm" name="settings[mailUserName]" value="{$settings->mailUserName}" />
                        <div class="help-block">{$MGLANG->T('emailSettings','mailUserName','help')}</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{$MGLANG->T('emailSettings','mailPassword','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input-sm" name="settings[mailPassword]" type="password" value="{$settings->mailPassword}" />
                        <div class="help-block">{$MGLANG->T('emailSettings','mailPassword','help')}</div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 text-right">
                                <button class="btn btn-success btn-inverse" onclick="ResellersCenter_Configuration.testConnection(event);">{$MGLANG->T('emailSettings','testConnection','testConnection')}</button>
                                <div id="testConnectionMessage">
                                    <span class = 'text-success' style="display: none">{$MGLANG->absoluteT('testConnectionMessages', 'connectionSuccess')}</span>
                                    <span class = 'text-danger' style="display: none">{$MGLANG->absoluteT('testConnectionMessages', 'connectionError')}</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-left">
                                <button class="btn btn-primary btn-inverse" onclick="ResellersCenter_Configuration.testMail(event);">{$MGLANG->T('emailSettings','testMail','testMail')}</button>
                                <div id="testMailMessage">
                                    <span class = 'text-success' style="display: none">{$MGLANG->absoluteT('testConnectionMessages', 'sendTestEmailSuccess')}</span>
                                    <span class = 'text-danger' style="display: none">{$MGLANG->absoluteT('testConnectionMessages', 'sendTestEmailFailed')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

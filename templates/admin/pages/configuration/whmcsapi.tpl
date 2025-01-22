<div class="row">
    <div class='col-md-12'>
        <div class="help-block">{$MGLANG->T('whmcsapi', 'help')}</div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('whmcsapi','identifier','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       name="whmcsApiKeys[identifier]"
                       value="{$whmcsApiKeys.identifier}"
                       placeholder="{$MGLANG->T('whmcsapi','identifier','placeholder')}"
                />
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('whmcsapi','identifier', 'part1', 'help')}
                    <a href="configapicredentials.php" target="_blank">{$MGLANG->T('whmcsapi','identifier', 'part2', 'help')}</a>
                    {$MGLANG->T('whmcsapi','identifier', 'part3', 'help')}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('whmcsapi','secret','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       type="password"
                       name="whmcsApiKeys[secret]"
                       value="{$whmcsApiKeys.secret}"
                       placeholder="{$MGLANG->T('whmcsapi','secret','placeholder')}"
                />
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('whmcsapi','secret', 'part1', 'help')}
                    <a href="configapicredentials.php" target="_blank">{$MGLANG->T('whmcsapi','secret', 'part2', 'help')}</a>
                    {$MGLANG->T('whmcsapi','secret', 'part3', 'help')}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('whmcsapi','authLogin','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       name="whmcsApiKeys[authLogin]"
                       value="{$whmcsApiKeys.authLogin}"
                       placeholder="{$MGLANG->T('whmcsapi','authLogin','placeholder')}"
                />
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('whmcsapi','authLogin','help')}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <label>{$MGLANG->T('whmcsapi','authPassword','label')}</label>
            </div>
            <div class='col-md-12'>
                <input class="form-control"
                       type="password"
                       name="whmcsApiKeys[authPassword]"
                       value="{$whmcsApiKeys.authPassword}"
                       placeholder="{$MGLANG->T('whmcsapi','authPassword','placeholder')}"
                />
            </div>
            <div class='col-md-12'>
                <div class='help-block'>{$MGLANG->T('whmcsapi','authPassword','help')}</div>
            </div>
        </div>
    </div>
</div>
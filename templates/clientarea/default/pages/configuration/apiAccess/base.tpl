<form action="">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                            <label>{$MGLANG->T('apiAccess','key','label')}</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control input-sm" name="settings[apikey]" value="{$settings->apikey}" />
                        <div class="help-block">{$MGLANG->T('apiAccess','key','help')}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


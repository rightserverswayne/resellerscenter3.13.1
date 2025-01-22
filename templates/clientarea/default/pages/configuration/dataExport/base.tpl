<div class="row">
    <div class="col-md-12">
        <h4>
            <strong>{$MGLANG->T('dataExport','general','csvDownload')}</strong>
        </h4>
        <table class="table table-hover table-striped">
            {foreach from=$dataExportTypes item=type}
                <tr>
                    <td>
                        {$MGLANG->T('dataExport','type', $type)}
                    </td>
                    <td style="width: 40px;">
                        <a href="?m=ResellersCenter&mg-page=export&mg-action=processExportData&dataType={$type}" class="btn btn-xs btn-primary btn-inverse icon-only">
                            <i class="fa fa-download"></i>
                        </a>
                    </td>
                </tr>
            {/foreach}
        </table>

        <h4>
            <strong>{$MGLANG->T('dataExport','general','apiAccess')}</strong>
        </h4>
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
                                <div id="apiTokenDecription" class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="exportModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('export','modal', 'title')}</h4>
            </div>
            <div class="modal-body">
                <form id='exportForm' action=''>
                    <div class="control-group">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="label-control">{$MGLANG->T('export','modal','resellers','label')}</label>
                            </div>
                            <div class="col-md-10">
                                <select class="form-control resellerId" name="resellerid">     
                                    {foreach from=$resellers item=client}
                                        <option value='{$client['resellerId']}'>{if {$client['resellerId']}}#{$client['resellerId']}{/if} {$client['resellerData']['firstname']} {$client['resellerData']['lastname']}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-inverse" onclick='RC_exportData.processExport();'>{$MGLANG->T('export', 'modal', 'export')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('export', 'modal', 'close')}</button>
            </div>
        </div>
    </div>
</div>


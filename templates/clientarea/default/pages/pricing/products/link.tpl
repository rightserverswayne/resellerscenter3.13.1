<div id="RCPricingProductLink" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{$MGLANG->T('generateLink','title')}</h4>
            </div>
            <div class="modal-body">
                <form action=''>
                    <div class="control-group">
                        <div class="row-fluid productLinkContainer">
                            <div class="help-block">{$MGLANG->T('generateLink','product')}</div>
                            <div class="row-fluid">
                                <input readonly="readonly" id="inputProductLink" class="border p-1 col-10 generatedLinkField" value="{$MGLANG->T('generateLink','noLinkGenerated')}"/>
                                <div class="copyCartUrlBtn">
                                    <p role="button" data-clipboard-target="#inputProductLink" class="btn-primary btn btn-inverse btn-sm only-icon col-1 copyCartUrl copy-to-clipboard" title="{$MGLANG->absoluteT('form','button','copy')}">
                                        <i class="icon-in-button fa fa-clipboard"></i>
                                    </p>
                                    <span>Copied!</span>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid productGroupLinkContainer">
                            <div class="help-block">{$MGLANG->T('generateLink','productGroup')}</div>
                            <div class="row-fluid">
                                <input readonly="readonly" id="inputGroupLink" class="border p-1 col-10 generatedLinkField" value="{$MGLANG->T('generateLink','noLinkGenerated')}"/>
                                <div class="copyCartUrlBtn">
                                    <p role="button" data-clipboard-target="#inputGroupLink" class="btn-primary btn btn-inverse btn-sm only-icon col-1 copyCartUrl copy-to-clipboard" title="{$MGLANG->absoluteT('form','button','copy')}">
                                        <i class="icon-in-button fa fa-clipboard"></i>
                                    </p>
                                    <span>Copied!</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>
        </div>
    </div>
</div>
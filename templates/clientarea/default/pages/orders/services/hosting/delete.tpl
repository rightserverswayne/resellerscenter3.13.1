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
<div id="RCServicesTabDelete" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <input hidden name="relid" value="">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title">{$MGLANG->T('hosting','delete','title')}</h4>
            </div>

            <div class="modal-body">
                <div class="control-group">
                    <div class="row-fluid">
                        <div class="help-block">{$MGLANG->T('hosting','delete','help')}</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-inverse"            data-type="confirm">{$MGLANG->absoluteT('form','button','confirm')}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal"  data-type="cancel">{$MGLANG->absoluteT('form','button','close')}</button>
            </div>

        </div>
    </div>
</div>
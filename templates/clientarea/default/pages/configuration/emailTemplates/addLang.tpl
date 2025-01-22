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
<div id="RCEmailTemplatesAddLang" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action=''>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$MGLANG->T('add','title')}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="label-control" style="padding-top: 7px;">{$MGLANG->T('add','language','label')}</label>
                        </div>
                        <div class="col-md-9">
                            <select class="select2 form-control" name="language">
                                {foreach from=$languages item=lang}
                                    <option value="{$lang}">{$lang}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-inverse" onclick='ResellersCenter_EmailTemplates.addLanguageSubmit();'>{$MGLANG->T('add','save')}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('add','close')}</button>
                </div>
            </div>
        </form>
    </div>
</div>
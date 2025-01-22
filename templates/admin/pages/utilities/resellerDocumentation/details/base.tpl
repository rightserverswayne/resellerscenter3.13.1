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
<div id="documentationDetails" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-edit font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('resellerDocumentation', 'details','title')}
            </span>
            <div class="caption-helper tikcet-subject">
                {$documentation->name}
            </div>
        </div>
            
        <div class="rc-actions with-tabs">
            <a href="javascript:;" onclick="window.history.back();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-reply"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        
            <input hidden="" name="id" value="{$documentation->id}" />
            <div class="row">
                <div class="col-md-12">
                    <h4><strong>{$MGLANG->T('resellerDocumentation', 'resellerselect', 'label')}</strong></h4>
                    <select name="resellers" class="select2 form-control" multiple="">
                        {foreach from=$resellers item=reseller}
                            <option value="{$reseller->id}" {if $reseller->settings.admin.documentation eq $documentation->id}selected=""{/if}>
                                {$reseller->client->firstname} {$reseller->client->lastname}
                            </option>
                        {/foreach}
                    </select>
                    <div class="help-block">{$MGLANG->T('resellerDocumentation', 'resellerselect', 'help')}</div>
                </div>
            </div>
                
            <div class="row">
                <div class="col-md-12">
                    <h4><strong>{$MGLANG->T('resellerDocumentation', 'name', 'label')}</strong></h4>
                    <input name="name" class="form-control" value="{$documentation->name}" />
                </div>
            </div>
                
            <div class="row">
                <div class="col-md-12">
                    <h4><strong>{$MGLANG->T('resellerDocumentation', 'content', 'label')}</strong></h4>
                    <textarea name="content" class="form-control" style="min-height: 300px;">{$documentation->content}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4><strong>{$MGLANG->T('resellerDocumentation', 'pdfversion', 'label')}</strong></h4>
                    
                    <div class='pdfViersionInfo alert alert-info' {if !$documentation->pdfpath}style="display: none;"{/if}>
                        <label>{$MGLANG->T('resellerDocumentation', 'pdfdocs', 'exists')}:</label>
                        <a href="../{$documentation->pdfpath}">{$documentation->pdfpath|basename}</a> 
                        <br />
                        <button class="deletePdf btn btn-danger btn-inverse btn-sm">{$MGLANG->T('resellerDocumentation', 'pdfdocs', 'delete', 'button')}</button>
                    </div>

                    <input type="file" name="pdf" value="" style="display: inline"/>
                    <button class="uploadPdf btn btn-sm btn-info btn-inverse">{$MGLANG->T('resellerDocumentation', 'pdfdocs', 'label')}</button>
                </div>
            </div>
          
        <center><button class="saveDetails btn btn-lg btn-success btn-inverse">{$MGLANG->absoluteT('form', 'button', 'save')}</button></center>
    </div>
</div>
            
<script type='text/javascript'>
    {include file='resellerDocumentation/details/controller.js'}
</script>

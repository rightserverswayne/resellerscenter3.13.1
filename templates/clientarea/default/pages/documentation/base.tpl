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
<div id="RCDocumentation">
    <div class="box light">
        <div class="box-title tabbable-line">
            <div class="caption">
                <i class="fa fa-book font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
                
            <div class='pull-right'>
                {if $documentation->pdfpath}
                    <a href="{$documentation->pdfpath}" target="_blank" class='btn btn-sm btn-info btn-inverse' style='top: 7px;'>{$MGLANG->T('pdfversion')}</a>
                {/if}
            </div>
        </div>
                
        <div class="box-body">
            {$documentation->content|unescape:'html'}
        </div>
    </div>
</div>
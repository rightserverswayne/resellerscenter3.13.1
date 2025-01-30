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
<div class="row">
    <div class="col-md-12">
        <table class="table table-hover table-fixed-cols">
            <thead>
                <th>{$MGLANG->T('products','table','product')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('products','table','group')}</th>
                <th>{$MGLANG->T('products','table','enabledbillingcycles')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('products','table','actions')}&nbsp;&nbsp;</th>
            </thead>
        </table>
    </div>
</div>
            
{* Pricing Form *}
{include file='products/pricing.tpl'}

{* Delete Form *}
{include file='products/delete.tpl'}

{* Show link *}
{include file='products/link.tpl'}

<script type='text/javascript'>
    {include file='products/controller.js'}
</script>
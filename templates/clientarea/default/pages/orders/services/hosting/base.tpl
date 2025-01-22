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
<div id="RCServicesTabFilters" class="row">
    {include file='services/hosting/filters.tpl'}
</div>
{include file='services/hosting/export.tpl'}
<div class="row">
    <div class="col-md-12">
        <table class="table table-hover" width="100%">
            <thead>
                <th>{$MGLANG->T('table','id')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','product')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','domain')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','clientname')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','price')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','billingcycle')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','status')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('table','nextduedate')}&nbsp;&nbsp;</th>
                <th style="min-width: 80px;">{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
            </thead>
        </table>
    </div>
</div>

{include file='services/hosting/delete.tpl'}

{include file='services/hosting/suspend.tpl'}

<script type="text/javascript">
    {include file='services/hosting/controller.js'}
</script>
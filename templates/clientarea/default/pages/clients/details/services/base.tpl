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
    <div class='col-md-12'>
        <table class="table table-hover" width="100%">
            <thead>
                <th>{$MGLANG->T('services','table','id')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','product')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','domain')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','client')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','price')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','billingcycle')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','status')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','nextduedate')}&nbsp;&nbsp;</th>
                <th>{$MGLANG->T('services','table','actions')}&nbsp;&nbsp;</th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

    
{* Add Form *}
{*{include file='details/services/add.tpl'}*}

{* Edit Form *}
{*{include file='details/services/config.tpl'}*}

{* Delete Form *}
{include file='details/services/delete.tpl'}

{include file='details/services/suspend.tpl'}

<script type="text/javascript">
    {include file='details/services/controller.js'}
</script>
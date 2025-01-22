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
<div id="perClient" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-table font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('perClient','title')}
            </span>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-hover align-vertical">
                    <thead>
                        <th>{$MGLANG->T('perClient','id')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('perClient','client')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('perClient','orders')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('perClient','value')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('perClient','income')}&nbsp;&nbsp;</th>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
                        
<script type="text/javascript">
    {include file='perClient/controller.js'}
</script>
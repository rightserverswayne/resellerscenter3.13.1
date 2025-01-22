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
<div class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-bar-chart font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('monthly','title')}
            </span>
        </div>
    </div>
    <div class="box-body">
        <div id="monthly-income-chart">
            <canvas height="450"></canvas>
        </div>
    </div>
</div>
            
<script type="text/javascript">
    {include file='monthly/controller.js'}
</script>
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

<div id="sales" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-line-chart font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('sales','title')}
            </span>
        </div>
            
        <div class='rc-actions pull-right' style="display: inline-flex">

            <div id='startDate' class="input-group" style="width: 200px;">
                <input type='text' class="form-control input-sm" />
                <span class="input-group-addon">
                    <span class="font-red bold icon-calendar"></span>
                </span>
            </div> 
            
            <div style="line-height: 32px;">
                <i class="fa fa-arrows-h" style="color: #AAA; font-size: 20px !important; margin-left: 10px; margin-right: 10px"></i>
            </div>

            <div id='endDate' class="input-group" style="width: 200px;">
                <input type='text' class="form-control input-sm" />
                <span class="input-group-addon">
                    <span class="font-red bold icon-calendar"></span>
                </span>
            </div>
            
        </div>
    </div>
    <div class="box-body">

        <div class="row">
            <div class="col-md-2 col-sm-5">
                <label class="label-control in-line-label pull-right" style="line-height: 20px;">{$MGLANG->T('resellers')}</label>
            </div>
            <div class="col-md-10 col-sm-7">
                <select class="form-control select2" multiple="" name="resellers">
                    {* Filled by AJAX *}
                </select>
            </div>
        </div>


        <div id="sales-chart-line">
            <canvas height="450"></canvas>
        </div>
    </div>
</div>
                        
<script type="text/javascript">
    {include file='sales/controller.js'}
</script>
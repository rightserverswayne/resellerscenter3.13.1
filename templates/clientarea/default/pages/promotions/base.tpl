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

<div id="RCPromotions" class="box light">
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-money font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('title')}
            </span>
        </div>
           
        <div class='rc-actions pull-right' style="display: inline-flex">
            <div class="promotionSearch input-group" style="width: 200px; display: none;">
                <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                <input placeholder="" class="form-control input-sm" style="border-color: #e5e5e5;" />
            </div> 
            <a href="javascript:;" onclick="ResellersCenter_Promotions.showSearch();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                <i class="fa fa-search"></i>
            </a>
            <a href="index.php?m=ResellersCenter&mg-page=promotions&mg-action=details" class="btn btn-circle btn-outline btn-inverse btn-success btn-icon-only">
                <i class="fa fa-plus"></i>
            </a>
        </div>
            
        <ul class="promo-filters nav nav-tabs">
            <li class="active" data-type="active">
                <a href="#RCActivePromotion" data-toggle="tab">
                    {$MGLANG->T('active', 'title')}
                </a>
            </li>
            <li data-type="expired">
                <a href="#RCExpiredPromotion" data-toggle="tab">
                    {$MGLANG->T('expired', 'title')}
                </a>
            </li>
            <li data-type="all">
                <a href="#RCAllPromotion" data-toggle="tab">
                    {$MGLANG->T('all', 'title')}
                </a>
            </li>
        </ul>
                        
    </div>
    <div class="box-body">
        <div class="">
            <div class="">
                <table class="table table-hover" width="100%">
                    <thead>
                        <th>{$MGLANG->T('table','code')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','type')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','value')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','recurring')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','uses')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','startdate')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','expirydate')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','actions')}&nbsp;&nbsp;</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
    
{* Delete Modal *}
{include file='delete.tpl'}
                    
<script type="text/javascript">
    {include file='controller.js'}
</script>
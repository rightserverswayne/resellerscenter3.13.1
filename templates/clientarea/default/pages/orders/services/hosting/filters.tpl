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
<div class="col-md-6 mg-float-left">

    <div class="row">
        <div class="col-md-4">
            <label for="type" class="pull-left">{$MGLANG->absoluteT('datatable','filters','type', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="type" class="form-control select2" data-ajaxload="ProductType">
                {* AJAX *}
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="product" class="pull-left">{$MGLANG->absoluteT('datatable','filters','product', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="product" class="form-control select2" data-ajaxload="Product">
                {* AJAX *}
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="billingcycle" class="pull-left">{$MGLANG->absoluteT('datatable','filters','billingcycle', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="billingcycle" class="form-control select2" data-ajaxload="BillingCycle">
                {* AJAX *}
            </select>
        </div>
    </div>

</div>
<div class="col-md-6 mg-float-left">

    <div class="row">
        <div class="col-md-4">
            <label for="server" class="pull-left">{$MGLANG->absoluteT('datatable','filters','server', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="server" class="form-control select2" data-ajaxload="Server">
                {* AJAX *}
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="paymentmethod" class="pull-left">{$MGLANG->absoluteT('datatable','filters','paymentmethod', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="paymentmethod" class="form-control select2" data-ajaxload="PaymentMethod">
                {* AJAX *}
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="status" class="pull-left">{$MGLANG->absoluteT('datatable','filters','status', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="status" class="form-control select2" data-ajaxload="Status">
                {* AJAX *}
            </select>
        </div>
    </div>

</div>

<div class="col-md-offset-6 col-md-6 mg-float-left">
    <div class="row">
        <div class="col-md-offset-4 col-md-8">
            <input class="form-control input-sm pull-left" placeholder="Search" name="search">
        </div>
    </div>
</div>
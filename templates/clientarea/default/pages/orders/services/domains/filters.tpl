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
            <label for="type" class="pull-pull-left">{$MGLANG->absoluteT('datatable','filters','registrar', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="type" class="form-control select2" data-ajaxload="Registrar">
                {* AJAX *}
            </select>
        </div>
    </div>

</div>
<div class="col-md-6 mg-float-left">

    <div class="row">
        <div class="col-md-4">
            <label for="status" class="pull-pull-left">{$MGLANG->absoluteT('datatable','filters','status', 'label')}</label>
        </div>
        <div class="col-md-8">
            <select name="status" class="form-control select2" data-ajaxload="DomainStatus">
                {* AJAX *}
            </select>
        </div>
    </div>

</div>

<div class="col-md-offset-6 col-md-6 mg-float-left">
    <div class="row">
        <div class="col-md-offset-4 col-md-8">
            <input class="form-control input-sm pull-pull-left" placeholder="Search" name="search">
        </div>
    </div>
</div>
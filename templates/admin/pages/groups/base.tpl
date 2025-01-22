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
<div class="row-fluid">
    <div class="groups col-lg-3 col-md-4 col-xs-12">
        {include file='groups/base.tpl'}
    </div>
    <div class="settings col-lg-9 col-md-8 col-xs-12">
        {include file='contents/base.tpl'}
    </div>
</div>

{* Lets keep both boxes at same height *}
<script type="text/javascript">
    $("#configurationBox .box-body").resize(function(e){
        var height = $(this).height();
        if(height < 100) {
            height = 300;
        }
        $("#groupsBox .box-body").height(height+25);
    });
</script>
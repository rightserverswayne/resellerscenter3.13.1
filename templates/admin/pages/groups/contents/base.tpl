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
<div id="configurationBox" class="box light" style="display: none;" >
    <div class="box-title tabbable-line">
        <div class="caption">
            <i class="fa fa-usd font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('settings','title')}
            </span>
        </div>

        <div class="rc-actions pull-right">
            <a href="javascript:;" onclick="RC_ConfigurationGroups.openDeleteFromHandler();" data-toggle="tooltip" title="{$MGLANG->T('button','deleteGroupTooltip')}" class="btn btn-circle btn-outline btn-inverse btn-danger btn-icon-only">
              <i class="fa fa-trash-o"></i>
            </a>
        </div>

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#productsTab" data-toggle="tab">
                    {$MGLANG->T('settings','products','title')}
                </a>
            </li>
            <li>
                <a href="#addonsTab" data-toggle="tab">
                    {$MGLANG->T('settings','addons','title')}
                </a>
            </li>
            <li>
                <a href="#domainsTab" data-toggle="tab">
                    {$MGLANG->T('settings','domains','title')}
                </a>
            </li>
        </ul>
    </div>
    <div class="box-body">
        <div class="tab-content">

            {* PRODUCTS *}
            <div class="tab-pane active" id="productsTab">
                <div class="scroller">
                    {include file='contents/products/base.tpl'}
                </div>
            </div>

            {* ADDONS *}
            <div class="tab-pane" id="addonsTab">
                <div class="scroller">
                    {include file='contents/addons/base.tpl'}
                </div>
            </div>

            {* DOMAINS *}
            <div class="tab-pane" id="domainsTab">
                <div class="scroller">
                     {include file='contents/domains/base.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>

{* Configuration Controller *}
<script type="text/javascript">
    {include file='contents/controller.js'}
</script>
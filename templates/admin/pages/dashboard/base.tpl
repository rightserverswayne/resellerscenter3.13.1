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
    <div id="resellers" class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-dashboard font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
        </div>
        <div class="box-body">
            <p>
                {$MGLANG->T('about','description1')}
                <ol>
                    <li>{$MGLANG->T('about','list1','part1')} <a href="addonmodules.php?module=ResellersCenter&mg-page=groups" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url1')}</a> {$MGLANG->T('about','list1','part2')}</li>
                    <li>{$MGLANG->T('about','list1','part3')} <a href="addonmodules.php?module=ResellersCenter&mg-page=resellers" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url2')}</a> {$MGLANG->T('about','list1','part4')}</li>
                    <li>{$MGLANG->T('about','list1','part5')} <a href="addonmodules.php?module=ResellersCenter&mg-page=configuration" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url3')}</a>. {$MGLANG->T('about','list1','part6')}</li>
                </ol>
                {$MGLANG->T('about','description2')}
            </p>

            <br>

            <ul>
                <li>{$MGLANG->T('about','list2','part1')} <a href="addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=resellerDocumentation" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url4')}</a>.</li>
                <li>{$MGLANG->T('about','list2','part2')} <a href="addonmodules.php?module=ResellersCenter&mg-page=payouts" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url5')}</a> {$MGLANG->T('about','list2','part3')}</li>
                <li>{$MGLANG->T('about','list2','part4')} <a href="addonmodules.php?module=ResellersCenter&mg-page=statistics" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url6')}</a> {$MGLANG->T('about','list2','part5')}</li>
            </ul>
            <br>

            <p>{$MGLANG->T('about','description3')} <a href="http://www.docs.modulesgarden.com/Resellers_Center_For_WHMCS" style="color: #4169E1;" target="_blank">{$MGLANG->T('about','url7')}</a>.</p>

            <div style='margin-top: 50px;'>
                <center>
                    <button class="btn btn-small btn-inverse btn-primary" onclick="RC_Dashboard.setSkipDashboard();">{$MGLANG->T('confirm','donotshowagain')}</button>
                </center>
            </div>
        </div>
    </div>
</div>
                
<script type='text/javascript'>
    {include file='controller.js'}
</script>
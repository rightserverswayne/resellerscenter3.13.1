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
<div id="RCDashboard">
    <div class="box light">
        <div class="box-title tabbable-line">
            <div class="caption">
                <i class="fa fa-dashboard font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
                
        </div>
                
        <div class="box-body">
            <p>{$MGLANG->T('description1')}</p>
            <ol>
                <li>{$MGLANG->T('list1', 'part1')} <a href="index.php?m=ResellersCenter&mg-page=configuration" style="color: #4169E1;" target="_blank">{$MGLANG->T('url1')}</a>. {$MGLANG->T('list1', 'part2')} </li>
                <li>{$MGLANG->T('list1', 'part3')} <a href="index.php?m=ResellersCenter&mg-page=pricing" style="color: #4169E1;" target="_blank">{$MGLANG->T('url2')}</a> {$MGLANG->T('list1', 'part4')}</li>
                <li>{$MGLANG->T('list1', 'part5')}</li>
            </ol>

            <br>

            <p>{$MGLANG->T('description2')}</p>
            <ul>
                <li style="line-height: 1.2"><a href="index.php?m=ResellersCenter&mg-page=clients" style="color: #4169E1;" target="_blank">{$MGLANG->T('url3')}</a> {$MGLANG->T('list2', 'part2')} <a href="index.php?m=ResellersCenter&mg-page=invoices" style="color: #4169E1;" target="_blank">{$MGLANG->T('url4')}</a> {$MGLANG->T('list2', 'part3')} <a href="index.php?m=ResellersCenter&mg-page=orders" style="color: #4169E1;" target="_blank">{$MGLANG->T('url5')}</a> {$MGLANG->T('list2', 'part4')}</li>
                <li style="line-height: 1.2">{$MGLANG->T('list2', 'part5')} <a href="index.php?m=ResellersCenter&mg-page=tickets" style="color: #4169E1;" target="_blank">{$MGLANG->T('url6')}</a> {$MGLANG->T('list2', 'part6')}</li>
                <li style="line-height: 1.2">{$MGLANG->T('list2', 'part7')} <a href="index.php?m=ResellersCenter&mg-page=statistics" style="color: #4169E1;" target="_blank">{$MGLANG->T('url7')}</a> {$MGLANG->T('list2', 'part8')}</li>
            </ul>
            <br>

            <p><a href="index.php?m=ResellersCenter&mg-page=documentation" style="color: #4169E1;" target="_blank">{$MGLANG->T('url8')}</a> {$MGLANG->T('list2', 'part9')}</p>
            <div style='margin-top: 50px;'>
                <center>
                    <button class="btn btn-small btn-inverse btn-primary" onclick="ResellersCeneter_Dashboard.setSkipDashboard();">{$MGLANG->T('donotshowagain')}</button>
                </center>
            </div>
        </div>
    </div>
</div>
                
<script type='text/javascript'>
    {include file='controller.js'}
</script>                    
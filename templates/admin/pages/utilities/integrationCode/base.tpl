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

<div id="integration" class="box light">
    <div class="box-title">
        <div class="caption">
            <i class="fa fa-code font-red-thunderbird"></i>
            <span class="caption-subject bold font-red-thunderbird uppercase">
                {$MGLANG->T('integrationCode','title')}
            </span>
        </div>
    </div>
    <div class="box-body">
        {* -------------------------- SIX TEMPLATE -------------------------- *}
        <h4><strong>{$MGLANG->T('integrationCodeStoreLogoHeaderSix')}</strong></h4>
        {$MGLANG->T('integrationCodeOriginStoreLogo')}<br />

        {literal}
        <pre>
{if $assetLogoPath}
    &lt;a href="{$WEB_ROOT}/index.php" class="logo"&gt;&lt;img src="{$assetLogoPath}" alt="{$companyname}"&gt;&lt;/a&gt;
{else}
    &lt;a href="{$WEB_ROOT}/index.php" class="logo logo-text"&gt;{$companyname}&lt;/a&gt;
{/if}
        </pre>
        {/literal}
                {$MGLANG->T('integrationCodeReplacementStoreLogo')}<br />
        {literal}
        <pre>
{if $RCLogo}
    &lt;a href="{$WEB_ROOT}/index.php"&gt;&lt;img src="{$RCLogo}" alt="{$companyname}" &gt;&lt;/a&gt;
{else}
    {if $logo}
        &#x3C;p&#x3E;&#x3C;img src=&#x22;{$logo}&#x22; title=&#x22;{$companyname}&#x22; /&#x3E;&#x3C;/p&#x3E;
    {else}
        &#x3C;h2&#x3E;{$companyname}&#x3C;/h2&#x3E;
    {/if}
{/if}
        </pre>
        {/literal}

        {* -------------------------- TWENTY-ONE TEMPLATE -------------------------- *}
        <h4><strong>{$MGLANG->T('integrationCodeStoreLogoHeaderTwentyOne')}</strong></h4>
        {$MGLANG->T('integrationCodeOriginStoreLogo')}<br />

        {literal}
            <pre>
{if $assetLogoPath}
    &#x3C;img src=&#x22;{$assetLogoPath}&#x22; alt=&#x22;{$companyname}&#x22; class=&#x22;logo-img&#x22;&#x3E;
{else}
    {$companyname}
{/if}
            </pre>
        {/literal}
            {$MGLANG->T('integrationCodeReplacementStoreLogo')}<br />
        {literal}
            <pre>
{if $RCLogo}
    &#x3C;img src=&#x22;{$RCLogo}&#x22; alt=&#x22;{$companyname}&#x22; class=&#x22;logo-img&#x22;&#x3E;
{else}
    {if $logo}
        &#x3C;img src=&#x22;{$logo}&#x22; alt=&#x22;{$companyname}&#x22; class=&#x22;logo-img&#x22;&#x3E;
    {else}
        {$companyname}
    {/if}
{/if}
            </pre>
        {/literal}
        <hr>

        <h4><strong>{$MGLANG->T('integrationCodeOriginInvoiceLogoTitle')}</strong></h4>
        {$MGLANG->T('integrationCodeOriginInvoiceLogo')}<br />
        {literal}
            <pre>
{if $logo}
    &#x3C;p&#x3E;&#x3C;img src=&#x22;{$logo}&#x22; title=&#x22;{$companyname}&#x22; /&#x3E;&#x3C;/p&#x3E;
{else}
    &#x3C;h2&#x3E;{$companyname}&#x3C;/h2&#x3E;
{/if}
            </pre>
        {/literal}
                {$MGLANG->T('integrationCodeReplacementInvoiceLogo')}<br />
        {literal}
        <pre>
{if $RCInvoiceLogo}
    &lt;a href="{$WEB_ROOT}/index.php"&gt;&lt;img src="{$RCInvoiceLogo}" alt="{$companyname}" &gt;&lt;/a&gt;
{else}
    {if $logo}
        &#x3C;p&#x3E;&#x3C;img src=&#x22;{$logo}&#x22; title=&#x22;{$companyname}&#x22; /&#x3E;&#x3C;/p&#x3E;
    {else}
        &#x3C;h2&#x3E;{$companyname}&#x3C;/h2&#x3E;
    {/if}
{/if}
        </pre>
        {/literal}
        
        {$MGLANG->T('logo4')}
        <h5><strong>{{$MGLANG->T('sixBased')}}:</strong></h5>
            <pre>{literal}&lt;WHMCS_PATH&gt;/templates/six/invoicepdf_rename.tpl{/literal}</pre>
        <h5><strong>{{$MGLANG->T('twentyOneBased')}}:</strong></h5>
            <pre>{literal}&lt;WHMCS_PATH&gt;/templates/twenty-one/invoicepdf_rename.tpl{/literal}</pre>

        {$MGLANG->T('logo5')}
        <hr>

        {* -------------------------- SIX TEMPLATE -------------------------- *}
        <h4><strong>{$MGLANG->T('resellerdepts1')}</strong></h4>
        {$MGLANG->T('resellerdepts2')}<br>
        {$MGLANG->T('resellerdepts3')}
        <pre>{literal}
&lt;li&gt;
    &lt;a id=&quot;btnGetSupport&quot; href=&quot;submitticket.php&quot;&gt;
        &lt;i class=&quot;fa fa-envelope-o&quot;&gt;&lt;/i&gt;
        &lt;p&gt;
            {$LANG.getsupport} &lt;span&gt;&amp;raquo;&lt;/span&gt;
        &lt;/p&gt;
    &lt;/a&gt;
&lt;/li&gt;
{/literal}</pre>
        {$MGLANG->T('resellerdepts4')}
        <pre>{literal}
{assign var=openTicketVar value='Open Ticket'}
{if $primaryNavbar.$openTicketVar}
    &lt;li&gt;
        &lt;a id=&quot;btnGetSupport&quot; href=&quot;submitticket.php&quot;&gt;
            &lt;i class=&quot;fa fa-envelope-o&quot;&gt;&lt;/i&gt;
            &lt;p&gt;
                {$LANG.getsupport} &lt;span&gt;&amp;raquo;&lt;/span&gt;
            &lt;/p&gt;
        &lt;/a&gt;
    &lt;/li&gt;
{/if}{/literal}</pre>

        {* -------------------------- TWENTY-ONE TEMPLATE -------------------------- *}
        <h4><strong>{$MGLANG->T('resellerdepts1TwentyOne')}</strong></h4>
        {$MGLANG->T('resellerdepts2')}<br>
        {$MGLANG->T('resellerdepts3TwentyOne')}
        <pre>{literal}
&#x3C;div class=&#x22;col-6 offset-3 offset-md-0 col-md-4 col-lg&#x22;&#x3E;
    &#x3C;a href=&#x22;submitticket.php&#x22; class=&#x22;card-accent-green&#x22;&#x3E;
        &#x3C;figure class=&#x22;ico-container&#x22;&#x3E;
            &#x3C;i class=&#x22;fal fa-life-ring&#x22;&#x3E;&#x3C;/i&#x3E;
        &#x3C;/figure&#x3E;
        {lang key=&#x27;homepage.submitTicket&#x27;}
    &#x3C;/a&#x3E;
&#x3C;/div&#x3E;
{/literal}</pre>
        {$MGLANG->T('resellerdepts4')}
        <pre>{literal}
{assign var=openTicketVar value='Open Ticket'}
{if $primaryNavbar.$openTicketVar}
    &#x3C;div class=&#x22;col-6 offset-3 offset-md-0 col-md-4 col-lg&#x22;&#x3E;
        &#x3C;a href=&#x22;submitticket.php&#x22; class=&#x22;card-accent-green&#x22;&#x3E;
            &#x3C;figure class=&#x22;ico-container&#x22;&#x3E;
                &#x3C;i class=&#x22;fal fa-life-ring&#x22;&#x3E;&#x3C;/i&#x3E;
            &#x3C;/figure&#x3E;
            {lang key=&#x27;homepage.submitTicket&#x27;}
        &#x3C;/a&#x3E;
    &#x3C;/div&#x3E;
{/if}{/literal}</pre>
        <h4><strong>{$MGLANG->T('resellerinvoice1')}</strong></h4>
        {$MGLANG->T('resellerinvoice2')}
        <h5><strong>{{$MGLANG->T('sixBased')}}:</strong></h5>
            <pre>{literal}&lt;WHMCS_PATH&gt;/templates/six/rcviewinvoice.tpl
&lt;WHMCS_PATH&gt;/templates/six/rccreditcard.tpl{/literal}</pre>
        <h5><strong>{{$MGLANG->T('twentyOneBased')}}:</strong></h5>
        <pre>{literal}&lt;WHMCS_PATH&gt;/templates/twenty-one/rcviewinvoice.tpl
&lt;WHMCS_PATH&gt;/templates/twenty-one/rccreditcard.tpl{/literal}</pre>
    </div>
</div>
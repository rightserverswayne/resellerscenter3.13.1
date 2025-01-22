{if $class|is_array}
    <td>
        <div class="
        {foreach from=$class item=ifdata}
            {if $originalValue eq $ifdata.0}{$ifdata.1}{/if}
        {/foreach}
        ">
            {$value}
        </div>
    </td>
{else}
    <td>
        <div class="{$class}">{$class} {$value}</div>
    </td>
{/if}
    
<a href="{$link}" class='{$class} btn btn-inverse btn-sm {$type}' {$data} {if $tooltip}data-toggle="tooltip" title="{$tooltip}"{/if}>

    {if $icon}
        <i class='icon-in-button {$icon}'></i>
    {/if}
    {if $text}
        {$text}
    {/if}
    {if $image}
        <img src="{$image}" border="0" align="absmiddle">
    {/if}

</a>
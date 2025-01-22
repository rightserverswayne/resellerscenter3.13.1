<div class="control-group {$style.classes}">
    <div class="row">
        {if $label}
            <div class="col-md-{12 - $style.width}">
                <label class="control-label">
                    {$MGLANG->T('forms', 'labels', $label)}
                </label>
            </div>
        {/if}

        <div class="col-md-{$style.width}">
            <select 
                class="form-control" 
                name="{$name}"
                {if $disabled}disabled="disabled"{/if}
                {if $multiple}multiple="multiple"{/if}  
                {foreach from=$data key=dataKey item=dataValue}
                    data-{$dataKey}="{$dataValue}"
                {/foreach}
                style="{foreach from=$style.custom key=stl item=val}{$stl}:{$val};{/foreach}"  
            >
                {foreach from=$options key=opValue item=option }
                    <option 
                        value="{$opValue}" 
                        {if $multiple}
                            {if in_array($opValue,$value)}selected{/if}
                        {else}
                            {if $opValue==$value}selected{/if}
                        {/if}
                    >
                        {$option}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    {if $description}
        <div class="row">
            <div class="col-md-offset-{12 - $style.width} col-md-{$style.width}">
                <span class="help-block">
                    {$MGLANG->T('forms', 'help', $description)}
                </span>
            </div>
        </div>
    {/if}
</div>
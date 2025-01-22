<div class="control-group {$style.classes}">
    <div class="row">
        {if $label}
            <div class="col-md-{12 - $style.width}">
                <label class="control-label">
                    {$MGLANG->T('forms', 'labels', $label.text)}
                </label>
            </div>
        {/if}

        <div class="col-md-{$style.width}">
            <div class="checkbox">
                <input 
                    type="checkbox" 
                    name="{$name}" 
                    value="{$value}" 
                    {foreach from=$data key=dataKey item=dataValue}
                        data-{$dataKey}="{$dataValue}"
                    {/foreach}
                    {if $checked}checked="checked"{/if} 
                    {if $disabled}disabled="disabled"{/if} 
                    style="{foreach from=$style.custom key=stl item=val}{$stl}:{$val};{/foreach}" 
                />
            </div>
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
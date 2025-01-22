<div class="box light bordered merge-fields">
    <div class="box-title">
        <div class="caption">
            <span class="bold uppercase">
               {$MGLANG->T('emails', 'variables', 'title')}
            </span>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            {foreach from=$mergeFields key=type item=fields}
                {if ($templateType eq 'general' && $type eq 'client_related') || 
                    ($templateType eq 'invoice' && ($type eq 'client_related' || $type eq 'invoice_related')) ||
                    ($templateType eq 'support' && $type eq 'ticket_related') || 
                    ($templateType eq 'product' && $type eq 'product_related') || 
                    ($templateType eq 'domain' && $type eq 'domain_related') || 
                    $type eq 'other'}
                
                    <div class="col-md-6">
                        <h4>
                            <strong>{$MGLANG->absoluteT('mergefields', $type)}</strong>
                        </h4>

                        {foreach from=$fields key=$name item=$var}
                            <dl class="dl-horizontal no-margin-bottom">
                                <dt class="text-align-left">{$MGLANG->absoluteT('mergefields', $name)}</dt>
                                <dd>{$var}</dd>
                            </dl>
                        {/foreach}
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
</div>

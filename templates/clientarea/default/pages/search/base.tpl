<div id="RCSearch">
    <div class="box light">
        <div class="box-title">
            <div class="caption">
                <i class="fa fa-search font-red-thunderbird"></i>
                <span class="caption-subject bold font-red-thunderbird uppercase">
                    {$MGLANG->T('title')}
                </span>
            </div>
            <div class='rc-actions pull-right' style="display: inline-flex">
                <div class="globalSearch input-group" style="width: 200px;">
                    <span class="input-group-addon" style="background: none;"><i class="font-red bold icon-magnifier"></i></span>
                    <input placeholder="{$MGLANG->T('table','search', 'placeholder')}" class="form-control" style="border-color: #e5e5e5;" />
                </div>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-hover">
                        <thead>
                        <th>{$MGLANG->T('table','id')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','type')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','name')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','status')}&nbsp;&nbsp;</th>
                        <th>{$MGLANG->T('table','date')}&nbsp;&nbsp;</th>
                        <th {if $isLagom}style="min-width: 200px;"{else}style="min-width: 150px;"{/if}>
                            {$MGLANG->T('table','actions')}
                        </th>
                        </thead>
                        <tbody>
                            <tr class="odd">
                                <td colspan="6" class="dataTables_initInfo" style="text-align: center !important;">
                                    {$MGLANG->T('table','search','initInfo')}
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{include file='client/delete.tpl'}
{include file='services/hosting/delete.tpl'}
{include file='services/hosting/suspend.tpl'}
{include file='services/addon/delete.tpl'}
{include file='services/domain/delete.tpl'}
{include file='order/details.tpl'}
{include file='order/dialogboxes/accept.tpl'}
{include file='order/dialogboxes/cancel.tpl'}
{include file='order/dialogboxes/delete.tpl'}
{include file='order/dialogboxes/fraud.tpl'}
{include file='invoice/edit/base.tpl'}
{include file='invoice/details.tpl'}

<script type="text/javascript">
    {include file='controller.js'}
    {include file='invoice/edit/controller.js'}
</script>
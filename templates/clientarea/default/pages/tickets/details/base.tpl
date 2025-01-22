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
<div id="RCTicketDetails">
    {if !$TicketNotFound}
        <div class="box light">
            <div class="box-title tabbable-line">
                <div class="caption col-md-6">
                    <i class="fa fa-ticket font-red-thunderbird"></i>
                    <span class="caption-subject bold font-red-thunderbird uppercase">
                        {$MGLANG->T('details','title')}
                    </span>
                    <div class="caption-helper tikcet-subject">
                        #{$ticket->tid} - {$ticket->title}
                    </div>
                </div>

                <div class="rc-actions with-tabs">
                    <a href="javascript:;" onclick="window.history.back();" class="btn btn-circle btn-outline btn-inverse btn-primary btn-icon-only">
                        <i class="fa fa-reply"></i>
                    </a>
                </div>

                <div class="rc-actions with-tabs">
                    {assign var='ticketstatuses' value=['Open', 'Answered', 'Customer-Reply', 'On Hold', 'In Progress', 'Closed']}
                    <select class="form-control ticket-status" data-ticketid="{$smarty.get.tid}">
                        {foreach from=$ticketstatuses item=status}
                            <option class="{$status|replace:" ":""}" {if $ticket->status eq $status}selected{/if}>{$MGLANG->T('details','status', $status)}</option>
                        {/foreach}
                    </select>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#RCTicketDetailsReply" aria-controls="RCTicketDetailsReply" role="tab" data-toggle="tab">{$MGLANG->T('details','reply','title')}</a>
                    </li>
                    <li role="presentation">
                        <a href="#RCTicketDetailsServices" aria-controls="RCTicketDetailsServices" role="tab" data-toggle="tab">{$MGLANG->T('details','services','title')}</a>
                    </li>
                </ul>



            </div>
            <div class="box-body">

                 <div class="tab-content">

                     <div role="tabpanel" class="tab-pane active" id="RCTicketDetailsReply">
                        {* Reply *}
                        <div class="row">
                            <div class="col-md-12">
                                <form class="ticketReply" action="index.php?m=ResellersCenter&mg-page=tickets&mg-action=details&tid={$smarty.get.tid}" method="POST" enctype="multipart/form-data">
                                    <textarea id="replyeditor" class="form-control tinyMCE" name="message"></textarea>

                                    <div class="row-fluid">
                                        {$MGLANG->T('details','attachments')}: <br>
                                        <input type="file" name="attachments[]" accept="{$TicketAllowedFileTypes}"/>

                                        <div class="col-xs-12 ticket-attachments-message text-muted mg-padding-top-5">
                                            Allowed File Extension: {$TicketAllowedFileTypes}
                                        </div>

                                        <button class="addAtachement btn btn-success btn-xs" onclick="return false;" style="margin-left: 12px; margin-top: 5px;">
                                            <i class="fa fa-plus"></i>{$MGLANG->T('details','addmore')}
                                        </button>
                                    </div>


                                    <div class="row text-center mg-display-block">
                                        <button class="addResponse btn btn-primary btn-inverse">{$MGLANG->T('details','addresponse')}</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {* Pervious Messages *}
                        <div class="row">
                            <div class="col-md-12">
                                {foreach from=$ticket->replies->sortByDesc('date') item=reply}
                                    <div class="ticket-reply markdown-content {if $reply->userid eq 0}staff{else}client{/if} mg-ticket-reply">
                                        <div class="date">
                                            {$reply->date}
                                        </div>
                                        <div class="user">
                                            <i class="fa fa-user"></i>
                                            <span class="name">
                                                {if $reply->userid eq 0}
                                                    {$reply->admin}
                                                {else}
                                                    {$reply->client->firstname} {$reply->client->lastname}
                                                {/if}
                                            </span>
                                            <span class="type">
                                                {if $reply->userid eq 0}{$MGLANG->T('details','staff')}{else}{$MGLANG->T('details','client')}{/if}
                                            </span>
                                        </div>

                                        <div class="message">
                                            {$reply->getMarkdownMessage()}

                                            {if $reply->attachment}
                                                <hr>
                                                <strong>{$MGLANG->T('details','attachments')}:</strong><br>
                                                <ul class='list-unstyled'>
                                                    {foreach from='|'|explode:$reply->attachment key=num item=attachment}
                                                        <li><i class="fa fa-file-o"></i> <a href="dl.php?type=ar&id={$reply->id}&i={$num}">{$attachment|regex_replace:"/.*[0-9]_/":""}</a></li>
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </div>
                                    </div>
                                {/foreach}

                                {* First Message *}
                                {if $ticket}
                                    <div class="ticket-reply markdown-content {if $ticket->userid eq 0}staff{else}client{/if} mg-ticket-reply">
                                        <div class="date">
                                            {$ticket->date}
                                        </div>
                                        <div class="user">
                                            <i class="fa fa-user"></i>
                                            <span class="name">
                                                {if $ticket->userid eq 0}
                                                    {$ticket->admin}
                                                {else}
                                                    {$ticket->client->firstname} {$ticket->client->lastname}
                                                {/if}
                                            </span>
                                            <span class="type">
                                                {if $ticket->userid eq 0}{$MGLANG->T('details','staff')}{else}{$MGLANG->T('details','client')}{/if}
                                            </span>
                                        </div>

                                        <div class="message">
                                            {$ticket->getMarkdownMessage()}

                                            {if $ticket->attachment}
                                                <hr>
                                                <strong>{$MGLANG->T('details','attachments')}:</strong><br>
                                                <ul class='list-unstyled'>
                                                    {foreach from='|'|explode:$ticket->attachment key=num item=attachment}
                                                        <li><i class="fa fa-file-o"></i> <a href="dl.php?type=a&id={$ticket->id}&i={$num}">{$attachment|regex_replace:"/.*[1-9]_/":""}</a></li>
                                                    {/foreach}
                                                </ul>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>

                    {* Clients Services *}
                    <div role="tabpanel" class="tab-pane" id="RCTicketDetailsServices">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover">
                                    <thead>
                                        <th>{$MGLANG->T('details', 'servcies', 'name')}&nbsp;&nbsp;</th>
                                        <th>{$MGLANG->T('details', 'servcies', 'amount')}&nbsp;&nbsp;</th>
                                        <th>{$MGLANG->T('details', 'servcies', 'billingcycle')}&nbsp;&nbsp;</th>
                                        <th>{$MGLANG->T('details', 'servcies', 'singupdate')}&nbsp;&nbsp;</th>
                                        <th>{$MGLANG->T('details', 'servcies', 'nextduedate')}&nbsp;&nbsp;</th>
                                        <th>{$MGLANG->T('details', 'servcies', 'status')}&nbsp;&nbsp;</th>
                                    </thead>
                                    <tbody>
                                        {foreach from=$services item=service}
                                            <tr {if $service->relid eq $ticket->service}class="current"{/if}>
                                                <td>{$service->hosting->product->name}</td>
                                                <td>{$service->hosting->amount}</td>
                                                <td>{$service->hosting->billingcycle}</td>
                                                <td>{$service->hosting->regdate}</td>
                                                <td>{$service->hosting->nextduedate}</td>
                                                <td>{$MGLANG->T('details', 'servcies', {$service->hosting->domainstatus})}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>



            </div>
        </div>
    {/if}
</div>
            
<script type="text/javascript">
    {include file='details/controller.js'}
</script>


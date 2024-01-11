<div class='header-background'>
</div>

<div class='header-content'>
    <img src='templates/css/images/southeastern-logo.png'>
    <h2>Fault Summary</h2>
</div>

<div class='fault-summary'>
    <div class='fault-summary-item'><span class='title'>Fault ID:</span><span class='value'>{$template_vars->fault->id}</span></div>
    <div class='fault-summary-item'><span class='title'>Location:</span><span class='value'>{$template_vars->fault->location->name}</span></div>
    <div class='fault-summary-item'><span class='title'>Fault type:</span><span class='value'>{$template_vars->fault->type->name}</span></div>
    <div class='fault-summary-item'><span class='title'>Fault details:</span><span class='value'>{$template_vars->fault->fault_details}</span></div>
    <div class='fault-summary-item'><span class='title'>Darwin connected:</span><span class='value'>{$template_vars->fault->darwin_connected}</span></div>
    <div class='fault-summary-item'><span class='title'>Worldline ref:</span><span class='value'>{if isset($template_vars->fault->worldline_reference)}{$template_vars->fault->worldline_reference}{/if}</span></div>
    <div class='fault-summary-item'><span class='title'>Hackon ref:</span><span class='value'>{if isset($template_vars->fault->hackon_reference)}{$template_vars->fault->hackon_reference}{/if}</span></div>
    <div class='fault-summary-item'><span class='title'>Responsibility:</span><span class='value'>{$template_vars->fault->responsibility}</span></div>
    <div class='fault-summary-item'><span class='title'>Status:</span><span class='value'>{$template_vars->fault->enabled}</span></div>
    <div class='fault-summary-item'><span class='title'>Critical:</span><span class='value'>{$template_vars->fault->is_critical}</span></div>
    <div class='fault-summary-item'><span class='title'>Station contact name:</span><span class='value'>{if isset($template_vars->fault->station_contact_name)}{$template_vars->fault->station_contact_name}{/if}</span></div>
    <div class='fault-summary-item'><span class='title'>Station contact number:</span><span class='value'>{if isset($template_vars->fault->station_contact_number)}{$template_vars->fault->station_contact_number}{/if}</span></div>
    <div class='fault-summary-item'><span class='title'>Updated:</span><span class='value'>{$template_vars->fault->timestamp}</span></div>
</div>

<div class='logs'>
    <h2>Logs</h2>

    {foreach $template_vars->log as $log}   
        <div class='log-entry'>
            <span class='timestamp'>{$log->timestamp}</span>
            <span class='user'>User: {$log->user}</span>
            <span class='action'>{$log->attempted_action}</span>
            {if $log->note == ''}
                <span class='note'>-</span>
            {else}
                <span class='note'>{$log->note}</span>
            {/if}
        </div>
    {/foreach}
</div>

<div class="mailbox">
    <h2 class="mailbox-title">Mailbox</h2>
    {foreach $template_vars->fault->mailbox->merged as $mail}
        <div class="email {$mail->type}">
            <div class="email-header">
                <div class="date">{$mail->timestamp}</div>
                {if $mail->type == 'inbox'}
                    <div class="email-item"><span class='title'>From:</span><span class='value'>{$mail->from_address}</span></div>
                {/if}
                <div class="email-item"><span class='title'>To:</span><span class='value'>{foreach $mail->to as $k => $to}{$to}{if $k != $mail->lastToIndex},{/if} {/foreach}</span></div>
                {if $mail->lastCcIndex != -1}
                    <div class="email-item"><span class='title'>Cc:</span><span class='value'>{foreach $mail->cc_addresses as $k => $cc}{$cc}{if $k != $mail->lastCcIndex},{/if} {/foreach}</span></div>
                {/if}
                <div class="email-item"><span class='title'>Subject:</span><span class='value'>{$mail->subject}</span></div>
            </div>
            <div class="mail-body">{$mail->message}</div>
        </div>
    {/foreach}
</div>
<div class='logs'>
    <h2>{$template_vars->title}</h2>

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
<!-- 

look at old rils pdf template and make it nicer than that

-->
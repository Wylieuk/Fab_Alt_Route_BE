<div style="
    display: inline-block;
    color: #4e5b6b;
    padding: 10px;
    font-family: Segoe UI,Helvetica Neue,Arial,sans-serif;
">
    <div style="padding-bottom: 10px;">
        Offer <a href="{$template_vars->offerUrl}"><strong>{$template_vars->diffs->name|default:{$template_vars->offer->name|default:'unknown'}}</strong></a> has received the following update(s)
    </div>
    <br/>
    <table style="color: #4e5b6b;">
        <tbody>

        {foreach $template_vars->diffs as $key => $diff}
            {if $key != 'images'}
                <tr>
                    <td style="padding-right: 14px;"><strong>{$key}:</strong></td>
                    <td>
                    {if is_array($diff)}
                        {implode(', ', $diff)}
                    {else}
                        {$diff}
                    {/if}
                    </td>
                </tr>
            {/if}
        {/foreach}

        {foreach $template_vars->diffs as $key => $diff}
            {if $key == 'images'}
                {foreach $diff as $key => $image}
                <tr>
                    <td style="padding-right: 14px;"><strong>{$key}:</strong></td>
                    <td>
                    Amended
                    </td>
                </tr>
                {/foreach}
            {/if}
        {/foreach}
            
        </tbody>
    </table>
</div>
<br/>
<div style="margin-left: 10px;">
    <img width="150px" src="{$template_vars->config->siteaddress}/assets/main_logo.svg">
</div>

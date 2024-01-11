<h2>Error report {$template_vars->siteAddress}</h2>
{if isset($template_vars->errorOutput)}
    <div>{$template_vars->errorOutput}</div>
{/if}

*{$template_vars->user}*sdfsdfsdfsd
<hr>
<table>

    {if isset($template_vars->request)}
        {foreach $template_vars->request as $var}
            <tr>
                <td>{$var@key}:</td>
                <td><strong>{$var}</strong></td>
            </tr>
        {/foreach}
    {/if}

    {if isset($template_vars->requestBody)}
            <tr>
                <td>Request body</td>
                <td><strong>{$template_vars->requestBody}</strong></td>
            </tr>
    {/if}

    {if isset($template_vars->user)}
            <tr>
                <td>User</td>
                <td><strong><pre>{$template_vars->user}</pre></strong></td>
            </tr>
    {/if}

</table>

{*add this for ERROREMAILS*}
<hr>
{if isset($template_vars->emailCount)}
    <div>{$template_vars->emailCount}</div>
{/if}
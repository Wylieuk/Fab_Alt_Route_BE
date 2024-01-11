<div>There has been a new vendor sign up</div>
<br>
<table>
{foreach $template_vars->user as $key => $val}
    <tr>
        <td>{$key}</td>
        <td>{$val}</td>
    </tr>
{/foreach}
</table>
<br>
<img width="150px" src="{$config['siteaddress']}/assets/main_logo.svg">
<div>
    Welcome {$template_vars->user->username}
    <br/>
</div>

<div>
    To activate your account please follow this <a href="{$template_vars->config->siteaddress}?page=self_activate_user&action=set_activate_user_self&id={$template_vars->user->id}&checksum={$template_vars->user->checksum}&referrer={$template_vars->referrer}">link</a>
    <br/>
</div>

<div>
    Thank you
    <br/>
</div>

<img width="150px" src="{$config['siteaddress']}/assets/main_logo.svg">
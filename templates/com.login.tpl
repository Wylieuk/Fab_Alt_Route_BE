
<div class="component_title">{'login_title'|lang}</div>
<div class="content">
<form id="login" action="index.php" method="post">
    <input type="hidden" name="securityToken" id="securityToken" value="{$smarty.session.securityToken}">
	<div class="login_field"><input class="field_input greyed" name="username" type="text" default="Username" value="Username"></div>
	<div class="login_field"><input class="field_input greyed" name="password" type="text"  default="Password" value="Password"></div>
    <div class="button_holder"><button id="submit" class="button">{'login'|lang}</button></div>
    {if $template_vars->login_failed}
<div class="warning">Incorrect username or password</div>
{/if}
</form>
</div><!--end content-->

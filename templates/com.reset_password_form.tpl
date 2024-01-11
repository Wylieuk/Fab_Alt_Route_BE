
<div class="content">
<img width="150px" src="{$template_vars->siteaddress}/assets/main_logo.svg">
{if !$template_vars->error && !$template_vars->status}
    <div class="component_title">Reset password for user {$template_vars->username}</div>
    <form id="reset-form" action="index.php" method="post">
        <input type="hidden" name="token" id="token" value="{$template_vars->token}">
        <input type="hidden" name="referrer" id="referrer" value="{$template_vars->referrer}">
        <input type="hidden" name="username" id="username" value="{$template_vars->username}">
        <input type="hidden" name="action" id="action" value="execute">
        <input type="hidden" name="page" id="page" value="reset_password">
        <div class="login_field">
            <input class="field_input" id="password" name="password" type="password"  placeholder="New password" value="">
        </div>
        <div class="login_field">
            <input class="field_input" id="password2" name="password2" type="password"  placeholder="Password again" value="">
        </div>
        <div class="button_holder">
            <button id="x-submit" name="x-submit" type="button" class="button">{'Reset'|lang}</button>
        </div>
    </form>
{else}
    {if $template_vars->error}
        <div class="error">{$template_vars->error}</div>
    {/if}
    {if $template_vars->status}
        <div class="status">{$template_vars->status}</div>
    {/if}
{/if}
</div><!--end content-->


<script nonce="{{$template_vars->CspNonce}}">

    const _bb = document.querySelector('.broswer-back');
    if (_bb){
        _bb.addEventListener('click', function(){
            window.history.back();
        });
    }
    const _bc = document.querySelector('.broswer-close');
    if (_bc){
        _bc.addEventListener('click', function(){
            window.close();
        });
    }

    
    const _s = document.getElementById('x-submit');
    if (_s){
        console.log(_s);
        _s.addEventListener('click', function(){
            if (document.getElementById('password').value !== document.getElementById('password2').value){
                document.getElementById('password2').classList.add("error");
                alert('Passwords do not match');
            } else {
                document.getElementById('password2').classList.remove("error");
                document.getElementById('reset-form').submit();
            }
        });
    }

</script>
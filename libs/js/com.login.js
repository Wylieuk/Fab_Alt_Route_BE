
$(document).ready(function () {

    $('form#login #submit').click(function () {
        $("#login").submit();
    });


    $(document).keypress(function (e) {
        if (e.which == 13) {
            $(document).unbind("keypress");
            $("#login").submit();
            return false;
        }
    });



    $('form#login .login_field input').focus(function () {
        if ($(this).val() == $(this).attr('default')) {
            if ($(this).attr('default') == 'Password') $(this).attr('type', 'password');
            $(this).removeClass('greyed');
            $(this).val("");
            $(this).blur(function () {
                if ($(this).val() == "") {
                    $(this).val($(this).attr('default'));
                    $(this).attr('type', 'text');
                    $(this).addClass('greyed');
                }
            });
        }
    });

});

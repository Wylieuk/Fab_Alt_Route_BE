

/*
* controls debug open and close toggle.
***************************************/
$(document).ready(function () {
    //$(\'.debug_chunk\').toggleClass(\'hidden\'); //start off hidden
    $('.debug_title').on("click", function () {
        $(this).toggleClass('debug_selected');
        $(this).toggleClass('debug_unselected');
        $('.debug_chunk').toggleClass('hidden');
    });
});
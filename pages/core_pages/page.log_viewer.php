<?php
defined("isInSideApplication")?null:die('no access');


if(isset($_REQUEST['clear']) && isset($_REQUEST['file'])){
    clear_Log('logs/' . $_REQUEST['file']);
}

echo '<a href="' . $_SERVER['PHP_SELF'] . '?page=log_viewer">Refesh list</a>';

global $config;
echo '
<style nonce="'.$config['CspNonce'].'">
    th{ border-bottom: 2px solid grey; }
</style>'; 

echo '
<script nonce="' . $config['CspNonce'] . '">

    document.addEventListener("DOMContentLoaded", function () {

        var clearLinks = document.getElementsByClassName("clear");

        for(let i = 0; i < clearLinks.length; i++) {
            clearLinks[i].addEventListener("click", function(e) {
                if (confirm("Clear log?")){
                    //console.log(e.originalTarget.getAttribute("data-href"));
                    window.location.href = e.originalTarget.getAttribute("data-href") ;
                }
            });
        }
    });

</script>;
';
            
echo '<table cellspacing="5px">';
echo '<tr>';
    echo    '<th>Name</th><th>Date</th><th>Size</th><th></th>';
    echo '</tr>';
foreach (files::getDirFiles('logs') as $file){
    echo '<tr>';
    echo    '<td><a href="' . $_SERVER['PHP_SELF'] . '?page=log_viewer&file=' . $file['name']. '">' . $file['name'] . '</a></td><td>' . date('d/m/Y  H:i:s', $file['modified']) . '</td><td>' .ceil($file['size']/1024) . ' KB </td>';
    echo '<td><a class="clear" href="#" data-href="' . $_SERVER['PHP_SELF'] . '?page=log_viewer&clear=true&file=' . $file['name'] . '">Clear</a></td>';
    echo '</tr>';
};

echo '</table>'; 

switch(true){

    case isset($_REQUEST['file']): 
        
        echo '<h3>' . $_REQUEST['file'] . '</h3>';

        $file = explode('.', $_REQUEST['file'])[0];

        $log = nl2br(file_get_contents('logs/' . $file . '.log'));

        echo $log;

        echo '<br>EOF<br><br>';

    break;


}


exit;
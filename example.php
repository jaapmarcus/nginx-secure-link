<?php
    //include wordpress 
    include('../../../wp-load.php');
    $secure = new NginxSecure();
    echo $secure -> secure_url('https://eris.nu','/pathtovideo.mp4');
?>
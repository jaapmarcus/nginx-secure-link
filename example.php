<?php
    //include wordpress 
    include('../../../wp-load.php');
    $secure = new NginxSecure();
    echo $secure -> secure_url('https://domain.nu','/pathtovideo.mp4');
?>

<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

$title = $msg_adheader9;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/purchase.php');
include(PATH . 'templates/footer.php');

?>
<?php

/* Admin - Version Check
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

$title = $msg_versioncheck;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/version-check.php');
include(PATH . 'templates/footer.php');

?>
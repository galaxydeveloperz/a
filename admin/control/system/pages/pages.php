<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

$title           = (isset($_GET['edit']) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1]);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/pages/pages.php');
include(PATH . 'templates/footer.php');

?>
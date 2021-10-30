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

$title = $msadminlang_imap_3_7[0];
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imapban.php');
include(PATH . 'templates/footer.php');

?>
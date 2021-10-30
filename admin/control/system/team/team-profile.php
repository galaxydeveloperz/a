<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || (USER_ADMINISTRATOR == 'no' && $MSTEAM->profile == 'no')) {
  $HEADERS->err403(true);
}

// If administrator, we should be on the main edit screen..
if (USER_ADMINISTRATOR == 'yes') {
  header("Location: index.php?p=team&edit=" . $MSTEAM->id);
  exit;
}

$title = $msg_adheader64;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/team-profile.php');
include(PATH . 'templates/footer.php');

?>
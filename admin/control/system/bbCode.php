<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

$title = $msg_bbcode;

include(PATH . 'templates/system/bbcode-help.php');

?>
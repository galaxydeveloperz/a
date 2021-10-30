<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || !isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Lets check someone isn`t trying to view the admin user..
if ($_GET['id'] == '1' && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$U = mswSQL_table('users', 'id', (int) $_GET['id']);
mswVLQY($U);

$title      = $msg_user86 . ' (' . mswSH($U->name) . ')';
$loadGraph = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/graph.php');
include(PATH . 'templates/footer.php');

?>
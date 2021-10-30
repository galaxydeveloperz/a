<?php

//============================
// TICKET FILTER BY OPTIONS
//============================

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

if (!defined('PARENT')) { exit; }

$filterBy  = '';

if (isset($_GET['priority']) && in_array($_GET['priority'], $levelPrKeys)) {
  $filterBy  .= "AND `priority` = '" . mswSQL($_GET['priority']) . "'";
}
if (isset($_GET['dept'])) {
  if (substr($_GET['dept'], 0, 1) == 'u' && (USER_ADMINISTRATOR == 'yes' || in_array('assign', $userAccess))) {
    $filterBy .= "AND FIND_IN_SET('" . (int) substr($_GET['dept'], 1) . "', `assignedto`) > 0";
  } else {
    $mswDeptFilterAccess  = '';
    $filterBy .= "AND `department` = '" . (int) $_GET['dept'] . "'";
  }
}

?>
<?php if (!defined('PARENT')) { exit; }

/* MENU: TICKET STATUSES
========================================================*/

$statusMenuArr = array('status','statusman');
$mR12          = array_intersect($statusMenuArr, $userAccess);
$mR12_en       = (isset($nMenu['status']['en']) && in_array($nMenu['status']['en'], array('yes','no')) ? $nMenu['status']['en'] : 'yes');

if (!empty($mR12) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['status']          = array($msticketstatuses4_3[0], 'crosshairs', $mR12_en);
  $slidePanelLeftMenu['status']['links'] = array();

  if ($mR12_en == 'yes') {
  
    // Add status..
    if (in_array('status', $mR12) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['status']['links'][] = array(
        'url' => '?p=status',
        'name' => $msticketstatuses4_3[1]
      );
    }

    // Manage statuses..
    if (in_array('statusman', $mR12) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['status']['links'][] = array(
        'url' => '?p=statusman',
        'name' => $msticketstatuses4_3[2]
      );
    }
  
  }

}

?>
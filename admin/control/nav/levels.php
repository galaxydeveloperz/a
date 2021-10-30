<?php if (!defined('PARENT')) { exit; }

/* MENU: PRIORITY LEVELS
========================================================*/

$levelMenuArr = array('levels','levelsman');
$mR7          = array_intersect($levelMenuArr, $userAccess);
$mR7_en       = (isset($nMenu['levels']['en']) && in_array($nMenu['levels']['en'], array('yes','no')) ? $nMenu['levels']['en'] : 'yes');

if (!empty($mR7) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['levels']          = array($msg_adheader52, 'flag-checkered', $mR7_en);
  $slidePanelLeftMenu['levels']['links'] = array();

  if ($mR7_en == 'yes') {
  
    // Add priority level..
    if (in_array('levels', $mR7) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['levels']['links'][] = array(
        'url' => '?p=levels',
        'name' => $msg_adheader50
      );
    }

    // Manage priority levels..
    if (in_array('levelsman', $mR7) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['levels']['links'][] = array(
        'url' => '?p=levelsman',
        'name' => $msg_adheader51
      );
    }
  
  }

}

?>
<?php if (!defined('PARENT')) { exit; }

/* MENU: STANDARD RESPONSES
========================================================*/

$srMenuArr = array('responseman','standard-responses','standard-responses-import');
$mR6       = array_intersect($srMenuArr, $userAccess);
$mR6_en    = (isset($nMenu['stanresp']['en']) && in_array($nMenu['stanresp']['en'], array('yes','no')) ? $nMenu['stanresp']['en'] : 'yes');

if (!empty($mR6) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['stanresp']          = array($msg_adheader13, 'comments-o', $mR6_en);
  $slidePanelLeftMenu['stanresp']['links'] = array();

  if ($mR6_en == 'yes') {
  
    // Standard responses..
    if (in_array('standard-responses', $mR6) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['stanresp']['links'][] = array(
        'url' => '?p=standard-responses',
        'name' => $msg_adheader53
      );
    }

    // Manage responses..
    if (in_array('responseman', $mR6) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['stanresp']['links'][] = array(
        'url' => '?p=responseman',
        'name' => $msg_adheader54
      );
    }

    // Import responses..
    if (in_array('standard-responses-import', $mR6) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['stanresp']['links'][] = array(
        'url' => '?p=standard-responses-import',
        'name' => $msg_adheader60
      );
    }
  
  }

}

?>
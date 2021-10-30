<?php if (!defined('PARENT')) { exit; }

/* MENU: CUSTOM FIELDS
========================================================*/

$fieldMenuArr = array('fieldsman','fields');
$mR5          = array_intersect($fieldMenuArr, $userAccess);
$mR5_en      = (isset($nMenu['fields']['en']) && in_array($nMenu['fields']['en'], array('yes','no')) ? $nMenu['fields']['en'] : 'yes');

if (!empty($mR5) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['fields']          = array($msg_adheader26, 'th-list', $mR5_en);
  $slidePanelLeftMenu['fields']['links'] = array();

  if ($mR5_en == 'yes') {
  
    // Add custom field..
    if (in_array('fields', $mR5) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['fields']['links'][] = array(
        'url' => '?p=fields',
        'name' => $msg_customfields2
      );
    }

    // Manage custom fields..
    if (in_array('fieldsman', $mR5) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['fields']['links'][] = array(
        'url' => '?p=fieldsman',
        'name' => $msg_adheader43
      );
    }
  
  }

}

?>
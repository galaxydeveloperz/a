<?php if (!defined('PARENT')) { exit; }

/* MENU: ADMIN PAGES
========================================================*/

$aPagesMenuArr = array();
if (USER_ADMINISTRATOR == 'no' && !empty($userAccess)) {
  foreach($userAccess AS $usa) {
    if (substr($usa, 0, 6) == 'apages') {
      $aPagesMenuArr[] = $usa;
    }
  }
}
$mR13          = (!empty($aPagesMenuArr) ? $aPagesMenuArr : array());
$mR13_en       = (isset($nMenu['apages']['en']) && in_array($nMenu['apages']['en'], array('yes','no')) ? $nMenu['apages']['en'] : 'yes');
if (!empty($mR13) || USER_ADMINISTRATOR == 'yes') {
  
  $slidePanelLeftMenu['apages']          = array($msadminpages4_3[0], 'files-o', $mR13_en);
  $slidePanelLeftMenu['apages']['links'] = array();

  if ($mR13_en == 'yes') {
  
    // Add admin pages..
    $q_menu_pg = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "admin_pages`
                 WHERE `enPage` = 'yes'
                 ORDER BY `orderBy`
                 ", __file__, __line__);
    while ($MENU_PG = mswSQL_fetchobj($q_menu_pg)) {
      $display_ap = 'no';
      // Account restriction?
      $ap_perms = explode(',', $MENU_PG->accounts);
      if (USER_ADMINISTRATOR == 'yes' || in_array('all', $ap_perms) || in_array($MSTEAM->id, $ap_perms)) {
        $display_ap = 'yes';
      }
      if ($display_ap == 'yes') { 
        if (in_array('apages&view=' . $MENU_PG->id, $mR13) || USER_ADMINISTRATOR == 'yes') {
          $slidePanelLeftMenu['apages']['links'][] = array(
            'url' => '?p=apages&amp;view=' . $MENU_PG->id,
            'name' => mswSH($MENU_PG->title)
          );
        }
      }
    }
  
  }

}

?>
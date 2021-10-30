<?php if (!defined('PARENT')) { exit; }

/* MENU: TICKETS
========================================================*/

$tickMenuArr = array('assign','open','close','disputes','cdisputes','search','search-fields','add','spam');
if (USER_ADMINISTRATOR == 'no' && !empty($userAccess)) {
  foreach($userAccess AS $usa) {
    if (substr($usa, 0, 7) == 'status=') {
      $tickMenuArr[] = $usa;
    }
  }
}

$mR1         = array_intersect($tickMenuArr, $userAccess);
$mR1_en      = (isset($nMenu['tickets']['en']) && in_array($nMenu['tickets']['en'], array('yes','no')) ? $nMenu['tickets']['en'] : 'yes');

if (!empty($mR1) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['tickets']          = array($msg_adheader41, 'pencil', $mR1_en);
  $slidePanelLeftMenu['tickets']['links'] = array();

  if ($mR1_en == 'yes') {
  
    // Add new ticket..
    if (in_array('add', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=add',
        'name' => $msg_open
      );
    }

    // Assign tickets..
    if (in_array('assign', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=assign',
        'name' => $msg_adheader32
      );
    }

    // Open tickets..
    if (in_array('open', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=open',
        'name' => $msg_adheader5
      );
    }

    // Additional statuses..
    $howManyCustomStats = mswSQL_rows('statuses');
    if ($howManyCustomStats > 3) {
      $q_menu_st = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "statuses`
                   WHERE `marker` NOT IN('open','close','closed')
                   ORDER BY `orderBy`
                   ", __file__, __line__);
      while ($MENU_ST = mswSQL_fetchobj($q_menu_st)) {
        if (in_array('status=' . $MENU_ST->id, $mR1) || USER_ADMINISTRATOR == 'yes') {
          $slidePanelLeftMenu['tickets']['links'][] = array(
            'url' => '?t_status=' . $MENU_ST->id,
            'name' => $msg_viewticket7 . ': ' . mswSH($MENU_ST->name)
          );
        }
      }
    }
    
    // Closed tickets..
    if (in_array('close', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=close',
        'name' => $msg_adheader6
      );
    }
    
    // Open disputes..
    if ($SETTINGS->disputes == 'yes') {
      if (in_array('disputes', $mR1) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['tickets']['links'][] = array(
          'url' => '?p=disputes',
          'name' => $msg_adheader28
        );
      }

      // Closed disputes..
      if (in_array('cdisputes', $mR1) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['tickets']['links'][] = array(
          'url' => '?p=cdisputes',
          'name' => $msg_adheader29
        );
      }
    }
    
    // Spam tickets..
    if (in_array('spam', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=spam',
        'name' => $msg_adheader63
      );
    }

    // Search tickets..
    if (in_array('search', $mR1) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['tickets']['links'][] = array(
        'url' => '?p=search',
        'name' => $msg_adheader7
      );
    }

    // Search tickets by custom fields..
    if (mswSQL_rows('ticketfields') > 0) {
      if (in_array('search-fields', $mR1) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['tickets']['links'][] = array(
          'url' => '?p=search-fields',
          'name' => $msg_header18
        );
      }
    }    
  
  }

}

?>
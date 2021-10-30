<?php

/* CLASS FILE
----------------------------------*/

class fields {

  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "cusfields` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  public function rebuildSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "cusfields` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "cusfields` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "cusfields` SET
    `enField`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function addCustomField() {
    // Defaults if not set..
    $acc = (!empty($_POST['acc']) ? mswSQL(implode(',', $_POST['acc'])) : 'all');
    $_POST['fieldType']  = (isset($_POST['fieldType']) && in_array($_POST['fieldType'], array(
      'textarea',
      'input',
      'select',
      'checkbox',
      'calendar'
    )) ? $_POST['fieldType'] : 'input');
    $_POST['fieldReq']   = (isset($_POST['fieldReq']) ? 'yes' : 'no');
    $_POST['repeatPref'] = (isset($_POST['repeatPref']) ? 'yes' : 'no');
    $_POST['enField']    = (isset($_POST['enField']) ? 'yes' : 'no');
    $dept                = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    if (empty($_POST['fieldLoc'])) {
      $_POST['fieldLoc'][] = 'ticket';
    }
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "cusfields` (
    `fieldInstructions`,
    `fieldType`,
    `fieldReq`,
    `fieldOptions`,
    `fieldLoc`,
    `orderBy`,
    `repeatPref`,
    `enField`,
    `departments`,
    `accounts`
    ) VALUES (
    '" . mswSQL($_POST['fieldInstructions']) . "',
    '{$_POST['fieldType']}',
    '{$_POST['fieldReq']}',
    '" . mswSQL($_POST['fieldOptions']) . "',
    '" . mswSQL(implode(',', $_POST['fieldLoc'])) . "',
    '0',
    '{$_POST['repeatPref']}',
    '{$_POST['enField']}',
    '{$dept}',
    '{$acc}'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild sequence..
    fields::rebuildSequence();
    return $last;
  }

  public function editCustomField() {
    // Defaults if not set..
    $acc = (!empty($_POST['acc']) ? mswSQL(implode(',', $_POST['acc'])) : 'all');
    $_POST['fieldType']  = (isset($_POST['fieldType']) && in_array($_POST['fieldType'], array(
      'textarea',
      'input',
      'select',
      'checkbox',
      'calendar'
    )) ? $_POST['fieldType'] : 'input');
    $_POST['fieldReq']   = (isset($_POST['fieldReq']) ? 'yes' : 'no');
    $_POST['repeatPref'] = (isset($_POST['repeatPref']) ? 'yes' : 'no');
    $_POST['enField']    = (isset($_POST['enField']) ? 'yes' : 'no');
    $dept                = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    if (empty($_POST['fieldLoc'])) {
      $_POST['fieldLoc'][] = 'ticket';
    }
    if ((int) $_POST['update'] > 0) {
      $_POST['update'] = (int) $_POST['update'];
      mswSQL_query("UPDATE `" . DB_PREFIX . "cusfields` SET
      `fieldInstructions`  = '" . mswSQL($_POST['fieldInstructions']) . "',
      `fieldType`          = '{$_POST['fieldType']}',
      `fieldReq`           = '{$_POST['fieldReq']}',
      `fieldOptions`       = '" . mswSQL($_POST['fieldOptions']) . "',
      `fieldLoc`           = '" . mswSQL(implode(',', $_POST['fieldLoc'])) . "',
      `repeatPref`         = '{$_POST['repeatPref']}',
      `enField`            = '{$_POST['enField']}',
      `departments`        = '{$dept}',
      `accounts`           = '{$acc}'
      WHERE `id`           = '{$_POST['update']}'
      ", __file__, __line__);
    }
  }

  public function deleteCustomFields() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "cusfields`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields`
      WHERE `fieldID` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      mswSQL_truncate(array('cusfields','ticketfields'));
      // Rebuild sequence..
      fields::rebuildSequence();
      return $rows;
    }
    return '0';
  }
  
  public function fieldAccounts($acc) {
    if (is_null($acc) || $acc == '' || $acc == 'all') {
      return '';
    }
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE `id` IN(" . mswSQL($acc) . ")
           AND `enabled` = 'yes'
           AND `verified` = 'yes'
           ORDER BY `name`, `email`
           ", __file__, __line__);
    while ($A = mswSQL_fetchobj($q)) {
      $ar[] = mswSH($A->name);
    }
    return (!empty($ar) ? implode(', ', $ar) : '');
  }

}

?>
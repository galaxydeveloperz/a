<?php

/* CLASS FILE
----------------------------------*/

class departments {

  // Re-order..
  public function order() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "departments` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  // Add department..
  public function add($userID) {
    // Next order sequence..
    $nextOrder = (mswSQL_rows('departments') + 1);
    $days      = (!empty($_POST['days']) ? implode(',', $_POST['days']) : '');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "departments` (
    `name`,`showDept`,`dept_subject`,`dept_comments`,
    `orderBy`,`manual_assign`,`days`,`dept_priority`,
    `auto_admin`,`auto_response`,`response_sbj`,`response`
    ) VALUES (
    '" . mswSQL($_POST['name']) . "',
    '" . (isset($_POST['showDept']) ? 'yes' : 'no') . "',
    '" . mswSQL($_POST['dept_subject']) . "',
    '" . mswSQL($_POST['dept_comments']) . "',
    '{$nextOrder}',
    '" . (isset($_POST['manual_assign']) ? 'yes' : 'no') . "',
    '{$days}',
    '" . mswSQL($_POST['dept_priority']) . "',
    '" . (isset($_POST['auto_admin']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['auto_response']) ? 'yes' : 'no') . "',
    '" . mswSQL($_POST['response_sbj']) . "',
    '" . mswSQL($_POST['response']) . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // If user isn`t global user, let this user see departments added..
    if ($userID > 1) {
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "userdepts` (
      `userID`,`deptID`
      ) VALUES (
      '{$userID}','{$last}'
      )", __file__, __line__);
    }
    return $last;
  }

  // Update department..
  public function update() {
    $_GET['edit'] = (int) $_POST['update'];
    $days         = (!empty($_POST['days']) ? implode(',', $_POST['days']) : '');
    mswSQL_query("UPDATE `" . DB_PREFIX . "departments` SET
    `name`          = '" . mswSQL($_POST['name']) . "',
    `showDept`      = '" . (isset($_POST['showDept']) ? 'yes' : 'no') . "',
    `dept_subject`  = '" . mswSQL($_POST['dept_subject']) . "',
    `dept_comments` = '" . mswSQL($_POST['dept_comments']) . "',
    `manual_assign` = '" . (isset($_POST['manual_assign']) ? 'yes' : 'no') . "',
    `days`          = '{$days}',
    `dept_priority` = '" . mswSQL($_POST['dept_priority']) . "',
    `auto_admin`    = '" . (isset($_POST['auto_admin']) ? 'yes' : 'no') . "',
    `auto_response` = '" . (isset($_POST['auto_response']) ? 'yes' : 'no') . "',
    `response_sbj`  = '" . mswSQL($_POST['response_sbj']) . "',
    `response`      = '" . mswSQL($_POST['response']) . "'
    WHERE `id`      = '{$_GET['edit']}'
    ", __file__, __line__);
    // If manual assign is not set, remove from any tickets..
    if (isset($_POST['manual_assign']) && $_POST['manual_assign'] == 'no') {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `assignedto`       = ''
      WHERE `department` = '{$_GET['edit']}'
      ", __file__, __line__);
    }
  }

  // Delete department..
  public function delete() {
    if (!empty($_POST['del'])) {
      // Nuke departments..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "departments`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Nuke user department association..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `deptID` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      mswSQL_truncate(array('departments','userdepts'));
      // Rebuild order sequence..
      $seq = 0;
      $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`", __file__, __line__);
      while ($RB = mswSQL_fetchobj($q)) {
        $n = (++$seq);
        mswSQL_query("UPDATE `" . DB_PREFIX . "departments` SET
	      `orderBy`  = '{$n}'
        WHERE `id` = '{$RB->id}'
        ", __file__, __line__);
      }
      return $rows;
    }
    return '0';
  }

}

?>
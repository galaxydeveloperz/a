<?php

/* CLASS FILE
----------------------------------*/

class statuses {

  // Re-order..
  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "statuses` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  // Rebuild sequence..
  public function rebuildSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "statuses` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "statuses` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  // Add status..
  public function addStatus() {
    $colors = (!empty($_POST['colors']) ? serialize($_POST['colors']) : '');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "statuses` (
    `name`, `perms`, `orderBy`, `colors`, `visitor`, `autoclose`
    ) VALUES (
    '" . mswSQL($_POST['name']) . "',
    '" . (isset($_POST['perms']) ? 'yes' : 'no') . "',
    '0',
    '" . mswSQL($colors) . "',
    '" . (isset($_POST['visitor']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['autoclose']) ? 'yes' : 'no') . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild order sequence..
    statuses::rebuildSequence();
    return $last;
  }

  // Update status..
  public function updateStatus() {
    $_GET['edit'] = (int) $_POST['update'];
    $colors = (!empty($_POST['colors']) ? serialize($_POST['colors']) : '');
    mswSQL_query("UPDATE `" . DB_PREFIX . "statuses` SET
    `name`      = '" . mswSQL($_POST['name']) . "',
    `perms`     = '" . (isset($_POST['perms']) ? 'yes' : 'no') . "',
    `colors`    = '" . mswSQL($colors) . "',
    `visitor`   = '" . ($_GET['edit'] > 3 ? (isset($_POST['visitor']) ? 'yes' : 'no') : 'no') . "',
    `autoclose` = '" . ($_GET['edit'] > 3 ? (isset($_POST['autoclose']) ? 'yes' : 'no') : 'no') . "'
    WHERE `id`  = '{$_GET['edit']}'
    ", __file__, __line__);
  }

  // Delete status..
  public function deleteStatuses() {
    if (!empty($_POST['del'])) {
      // Reset tickets back to open
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `ticketStatus` = 'open'
      WHERE `ticketStatus` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "statuses`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    AND `id` NOT IN(1,2,3)
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Rebuild order sequence..
      statuses::rebuildSequence();
      return $rows;
    }
    return '0';
  }

}

?>
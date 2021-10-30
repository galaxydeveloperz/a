<?php

/* CLASS FILE
----------------------------------*/

class levels {

  // Re-order..
  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "levels` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  // Rebuild sequence..
  public function rebuildSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "levels` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "levels` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  // Add level..
  public function addLevel() {
    $colors = (!empty($_POST['colors']) ? serialize($_POST['colors']) : '');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "levels` (
    `name`, `display`, `orderBy`, `colors`
    ) VALUES (
    '" . mswSQL($_POST['name']) . "',
    '" . (isset($_POST['display']) ? 'yes' : 'no') . "',
    '0',
    '" . mswSQL($colors) . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild order sequence..
    levels::rebuildSequence();
    return $last;
  }

  // Update level..
  public function updateLevel() {
    $_GET['edit'] = (int) $_POST['update'];
    $colors = (!empty($_POST['colors']) ? serialize($_POST['colors']) : '');
    mswSQL_query("UPDATE `" . DB_PREFIX . "levels` SET
    `name`     = '" . mswSQL($_POST['name']) . "',
    `display`  = '" . (isset($_POST['display']) ? 'yes' : 'no') . "',
    `colors`   = '" . mswSQL($colors) . "'
    WHERE `id` = '{$_GET['edit']}'
    ", __file__, __line__);
  }

  // Delete level..
  public function deleteLevels() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "levels`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    AND `id`   NOT IN(1,2,3)
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Rebuild order sequence..
      levels::rebuildSequence();
      return $rows;
    }
    return '0';
  }

}

?>
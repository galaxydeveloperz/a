<?php

/* CLASS FILE
----------------------------------*/

class csPages {

  public $settings;

  public function rebuildSequence($t = 'pages') {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . $t . "` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . $t . "` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  public function orderSequence($t = 'pages') {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . $t . "` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  public function enableDisable($t = 'pages') {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . $t . "` SET
    `ts`       = UNIX_TIMESTAMP(),
    `enPage`   = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function addPage() {
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "pages` (
    `ts`,
    `title`,
    `information`,
    `accounts`,
    `enPage`,
    `secure`,
    `orderBy`,
    `tmp`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSQL($_POST['title']) . "',
    '" . mswSQL($_POST['information']) . "',
    '" . (isset($_POST['secure']) ? mswSQL($acc) : '') . "',
    '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['secure']) ? 'yes' : 'no') . "',
    '0',
    '" . mswSQL($_POST['tmp']) . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild sequence..
    csPages::rebuildSequence();
    return $last;
  }

  public function updatePage() {
    $ID   = (int) $_POST['update'];
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mswSQL_query("UPDATE `" . DB_PREFIX . "pages` SET
    `ts`          = UNIX_TIMESTAMP(),
    `title`       = '" . mswSQL($_POST['title']) . "',
    `information` = '" . mswSQL($_POST['information']) . "',
    `accounts`    = '" . (isset($_POST['secure']) ? mswSQL($acc) : '') . "',
    `enPage`      = '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    `secure`      = '" . (isset($_POST['secure']) ? 'yes' : 'no') . "',
    `tmp`       = '" . mswSQL($_POST['tmp']) . "'
    WHERE `id`    = '{$ID}'
    ", __file__, __line__);
  }

  public function deletePages($t = 'pages') {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . $t . "`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_truncate(array($t));
      // Rebuild sequence..
      csPages::rebuildSequence($t);
      return $rows;
    }
    return '0';
  }
  
  public function addAdminPage() {
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "admin_pages` (
    `ts`,
    `title`,
    `information`,
    `accounts`,
    `enPage`,
    `orderBy`,
    `tmp`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSQL($_POST['title']) . "',
    '" . mswSQL($_POST['information']) . "',
    '" . mswSQL($acc) . "',
    '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    '0',
    '" . mswSQL($_POST['tmp']) . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild sequence..
    csPages::rebuildSequence('admin_pages');
    return $last;
  }
  
  public function updateAdminPage() {
    $ID   = (int) $_POST['update'];
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mswSQL_query("UPDATE `" . DB_PREFIX . "admin_pages` SET
    `ts`          = UNIX_TIMESTAMP(),
    `title`       = '" . mswSQL($_POST['title']) . "',
    `information` = '" . mswSQL($_POST['information']) . "',
    `accounts`    = '" . mswSQL($acc) . "',
    `enPage`      = '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    `tmp`       = '" . mswSQL($_POST['tmp']) . "'
    WHERE `id`    = '{$ID}'
    ", __file__, __line__);
  }

}

?>
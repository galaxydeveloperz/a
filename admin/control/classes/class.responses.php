<?php

/* CLASS FILE
----------------------------------*/

class standardResponses {

  public $settings;
  public $ssn;

  public function rebuildSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "responses` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "responses` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "responses` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "responses` SET
    `ts`         = UNIX_TIMESTAMP(),
    `enResponse` = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id`   = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function batchImportSR() {
    $count = 0;
    $dept  = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    // Clear current responses..
    if (isset($_POST['clear'])) {
      $SQL  = '';
      $chop = (empty($_POST['dept']) ? $_POST['deptall'] : $_POST['dept']);
      for ($i = 0; $i < count($chop); $i++) {
        $SQL .= ($i > 0 ? ' OR ' : ' WHERE ') . "FIND_IN_SET(" . mswSQL($chop[$i]) . ",`departments`) > 0";
      }
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "responses`" . $SQL, __file__, __line__);
      mswSQL_truncate(array('responses'));
    }
    // Upload CSV file..
    if ($this->ssn->active('upload_file') == 'yes' && file_exists($this->ssn->get('upload_file'))) {
      $handle = fopen($this->ssn->get('upload_file'), 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Clean array..
          $CSV = array_map('trim', $CSV);
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "responses` (
          `ts`,
          `title`,
          `answer`,
          `departments`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSQL($CSV[0]) . "',
          '" . mswSQL($CSV[1]) . "',
          '" . mswSQL($dept) . "'
          )", __file__, __line__);
          ++$count;
        }
        fclose($handle);
      }
      // Clear session file..
      $this->ssn->delete(array('upload_file'));
      // Rebuild sequence..
      standardResponses::rebuildSequence();
    }
    return $count;
  }

  public function addResponse() {
    $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "responses` (
    `ts`,
    `title`,
    `answer`,
    `departments`,
    `enResponse`,
    `orderBy`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSQL($_POST['title']) . "',
    '" . mswSQL($_POST['answer']) . "',
    '" . mswSQL($dept) . "',
    '" . (isset($_POST['enResponse']) ? 'yes' : 'no') . "',
    '0'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild sequence..
    standardResponses::rebuildSequence();
    return $last;
  }

  public function updateResponse() {
    $ID   = (int) $_POST['update'];
    $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    mswSQL_query("UPDATE `" . DB_PREFIX . "responses` SET
    `ts`          = UNIX_TIMESTAMP(),
    `title`       = '" . mswSQL($_POST['title']) . "',
    `answer`      = '" . mswSQL($_POST['answer']) . "',
    `departments` = '" . mswSQL($dept) . "',
    `enResponse`  = '" . (isset($_POST['enResponse']) ? 'yes' : 'no') . "'
    WHERE `id`    = '{$ID}'
    ", __file__, __line__);
  }

  public function deleteResponses() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "responses`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_truncate(array('responses'));
      // Rebuild sequence..
      standardResponses::rebuildSequence();
      return $rows;
    }
    return '0';
  }

  // Search..
  public function autoSearch() {
    $dp  = (isset($_GET['dept']) ? (int) $_GET['dept'] : '0');
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`title` FROM `" . DB_PREFIX . "responses`
           WHERE LOWER(`title`) LIKE '%" . strtolower(mswSQL($_GET['term'])) . "%'
           AND (`departments` IS NULL OR `departments` = '' OR FIND_IN_SET('{$dp}', `departments`) > 0)
           AND `enResponse` = 'yes'
           ORDER BY `title`
           ", __file__, __line__);
    while ($R = mswSQL_fetchobj($q)) {
      $ar[] = array(
        'value' => $R->id,
        'label' => mswSH($R->title)
      );
    }
    return $ar;
  }

}

?>
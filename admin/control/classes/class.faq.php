<?php

/* CLASS FILE
----------------------------------*/

class faqCentre {

  public $settings;
  public $dt;
  public $ssn;
  public $internal = array('chmod' => 0777, 'chmod-after' => 0644);

  const FAQ_HISTORY_FILENAME = 'history-{faq}-{date}.csv';

  public function exportFAQHistory($dl, $dt) {
    global $msfaq4_3;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $id   = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{faq}',
      '{date}'
    ), array(
      $id,
      $dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), faqCentre::FAQ_HISTORY_FILENAME);
    $csv = array($msfaq4_3[7]);
    $qTH = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "faqhistory`
           WHERE `faqID` = '{$id}'
           ORDER BY `ts` DESC
           ", __file__, __line__);
    while ($HIS = mswSQL_fetchobj($qTH)) {
      $csv[] = mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->dateformat), $sepr) . $sepr . mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->timeformat), $sepr) . $sepr . mswCleanCSV($HIS->action, $sepr) . $sepr . mswCleanCSV($HIS->ip, $sepr);
    }
    if (mswSQL_numrows($qTH) > 0) {
      // Save file to server and download..
      $dl->write($file, implode(mswNL(), $csv));
      if (file_exists($file)) {
        return $file;
      }
    }
    return 'none';
  }
  
  public function deleteFAQHistory($id, $faq) {
    // All or single entry..
    if ($id == 'all') {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqhistory` WHERE `faqID` = '{$faq}'", __file__, __line__);
    } else {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqhistory` WHERE `id` = '{$id}'", __file__, __line__);
    }
    mswSQL_truncate(array('faqhistory'));
  }
  
  // Log
  public function historyLog($que, $action) {
    if ($this->settings->faqHistory == 'yes') {
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqhistory` (
      `ts`,
      `faqID`,
      `action`,
      `ip`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '{$que}',
      '" . mswSQL($action) . "',
      '" . mswSQL(mswIP()) . "'
      )", __file__, __line__);
      $id = mswSQL_insert_id();
      return $id;
    }
    return 0;
  }
  
  // Rebuild attachment order sequence..
  public function rebuildAttSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "faqattach` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($AT = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "faqattach` SET
      `orderBy`  = '{$n}'
      WHERE `id` = '{$AT->id}'
      ", __file__, __line__);
    }
  }

  // Order sequence for attachments..
  public function orderAttSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "faqattach` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  // Enable/disable attachment..
  public function enableDisableAtt() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "faqattach` SET
    `ts`       = UNIX_TIMESTAMP(),
    `enAtt`    = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  // Delete attachment..
  public function deleteAttachments() {
    if (!empty($_POST['del'])) {
      // Remove attachment files..
      $q = mswSQL_query("SELECT `path` FROM `" . DB_PREFIX . "faqattach`
           WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
		       AND `path` != ''
           ORDER BY `id`
		  ", __file__, __line__);
      while ($AT = mswSQL_fetchobj($q)) {
        if (@file_exists($this->settings->attachpathfaq . '/' . $AT->path)) {
          @unlink($this->settings->attachpathfaq . '/' . $AT->path);
        }
      }
      // Delete data..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqattach`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_truncate(array('faqattach'));
    }
    // Rebuild sequence..
    faqCentre::rebuildAttSequence();
    return $rows;
  }

  // Update attachment..
  public function updateAttachment($upl) {
    $ID     = (isset($_POST['update']) ? (int) $_POST['update'] : '0');
    $reload = 'no';
    if ($ID > 0) {
      $display  = $_POST['name'];
      $remote   = (isset($_POST['remote']) ? $_POST['remote'] : '');
      $f_name   = (isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '');
      $f_temp   = (isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : '');
      $f_mime   = (isset($_FILES['file']['type']) ? $_FILES['file']['type'] : $_POST['mimeType']);
      $f_size   = ($f_name && $f_temp ? $_FILES['file']['size'] : $_POST['size']);
      $path     = $_POST['opath'];
      $ext      = substr(strrchr(strtolower($f_name), '.'), 1);
      // Update file..
      if ($remote == '' && $f_size > 0 && $f_name && $f_temp && $upl->isUploaded($f_temp)) {
        // Delete original..
        if (@file_exists($this->settings->attachpathfaq . '/' . $_POST['opath'])) {
          @unlink($this->settings->attachpathfaq . '/' . $_POST['opath']);
        }
        // Does file exist?
        if (@file_exists($this->settings->attachpathfaq . '/' . $f_name)) {
          // Are we renaming attachments..
          if ($this->settings->renamefaq == 'yes') {
            $path = $ID . '-' . $this->dt->mswTimeStamp() . '.' . $ext;
          } else {
            $path = $ID . '_' . mswCleanFile($f_name);
          }
          $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $path);
          // Required by some servers to make image viewable and accessible via FTP..
          $upl->chmodFile($this->settings->attachpathfaq . '/' . $path, $this->internal['chmod-after']);
        } else {
          // Are we renaming attachments..
          if ($this->settings->renamefaq == 'yes') {
            $path = $ID . '.' . $ext;
          } else {
            $path = mswCleanFile($f_name);
          }
          $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $path);
          // Required by some servers to make image viewable and accessible via FTP..
          $upl->chmodFile($this->settings->attachpathfaq . '/' . $path, $this->internal['chmod-after']);
        }
        // Remove temp file if it still exists..
        if (file_exists($f_temp)) {
          @unlink($f_temp);
        }
        $reload = 'yes';
      }
      // Add to database..
      mswSQL_query("UPDATE `" . DB_PREFIX . "faqattach` SET
      `ts`       = UNIX_TIMESTAMP(),
      `name`     = '" . mswSQL($display) . "',
      `remote`   = '" . mswSQL($remote) . "',
      `path`     = '" . mswSQL($path) . "',
      `size`     = '{$f_size}',
      `mimeType` = '{$f_mime}'
      WHERE `id` = '{$ID}'
      ", __file__, __line__);
    }
    return $reload;
  }

  // Remote file size
  public function remoteSize($file) {
    $headers = @get_headers($file, true);
    if (isset($headers['Content-Length'])) {
      return $headers['Content-Length'];
    }
    return '0';
  }

  // Add attachments..
  public function addAttachments($dl, $upl) {
    $count = 0;
    if (!empty($_FILES['file']['tmp_name'])) {
      for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
        if ($upl->isUploaded($_FILES['file']['tmp_name'][$i])) {
          $display = $_FILES['file']['name'][$i];
          $f_name  = $_FILES['file']['name'][$i];
          $f_temp  = $_FILES['file']['tmp_name'][$i];
          $f_mime  = $_FILES['file']['type'][$i];
          $f_size  = ($f_name && $f_temp ? $_FILES['file']['size'][$i] : '0');
          $ext     = substr(strrchr(strtolower($f_name), '.'), 1);
          // Add to database..
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqattach` (
          `ts`,
          `name`,
          `path`,
          `size`,
          `mimeType`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSQL($display) . "',
          '" . mswSQL($f_name) . "',
          '{$f_size}',
          '{$f_mime}'
          )", __file__, __line__);
          $ID = mswSQL_insert_id();
          // Now upload file if applicable..
          if ($ID > 0) {
            if ($f_size > 0) {
              // Does file exist?
              if (@file_exists($this->settings->attachpathfaq . '/' . $f_name)) {
                // Are we renaming attachments..
                if ($this->settings->renamefaq == 'yes') {
                  $new = $ID . '-' . $this->dt->mswTimeStamp() . '.' . $ext;
                } else {
                  $new = $ID . '_' . mswCleanFile($f_name);
                }
                $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $new);
                // Required by some servers to make file viewable and accessible via FTP..
                $upl->chmodFile($this->settings->attachpathfaq . '/' . $new, $this->internal['chmod-after']);
              } else {
                // Are we renaming attachments..
                if ($this->settings->renamefaq == 'yes') {
                  $new = $ID . '.' . $ext;
                } else {
                  $new = mswCleanFile($f_name);
                }
                $upl->moveFile($f_temp, $this->settings->attachpathfaq . '/' . $new);
                // Required by some servers to make file viewable and accessible via FTP..
                $upl->chmodFile($this->settings->attachpathfaq . '/' . $new, $this->internal['chmod-after']);
              }
              // Was file renamed?
              mswSQL_query("UPDATE `" . DB_PREFIX . "faqattach` SET `path` = '{$new}' WHERE `id` = '{$ID}'", __file__, __line__);
            }
            ++$count;
          }
          // Remove temp file if it still exists..
          if (file_exists($f_temp)) {
            @unlink($f_temp);
          }
        }
      }
    }
    // Remote files..
    if (!empty($_POST['remote'])) {
      $mime = $dl->mime_types();
      foreach ($_POST['remote'] AS $rm) {
        if ($rm) {
          // Add to database..
          $display = substr(basename($rm), -250);
          $ext     = substr(strrchr(strtolower($display), '.'), 1);
          $size    = faqCentre::remoteSize($rm);
          $mime    = (isset($mime[$ext]) ? $mime[$ext] : '');
          if ($size > 0) {
            mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqattach` (
            `ts`,
            `name`,
            `remote`,
            `size`,
            `mimeType`
            ) VALUES (
            UNIX_TIMESTAMP(),
            '" . mswSQL($display) . "',
            '" . mswSQL($rm) . "',
            '{$size}',
            '{$mime}'
            )", __file__, __line__);
            ++$count;
          }
        }
      }
    }
    // Rebuild sequence..
    faqCentre::rebuildAttSequence();
    return $count;
  }

  // Enable/disable cats..
  public function enableDisableCats() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
    `enCat`    = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  // Re-order categories..
  public function orderCatSequence() {
    // Parents..
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
	    `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
    // Children..
    if (!empty($_POST['orderSub'])) {
      foreach ($_POST['orderSub'] AS $k => $v) {
        mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
	      `orderBy`  = '{$v}'
        WHERE `id` = '{$k}'
        ", __file__, __line__);
      }
    }
  }

  // Rebuild category sequence..
  public function rebuildCatSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($CT = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$CT->id}'
	    ", __file__, __line__);
      // Subs..
      $seqs = 0;
      $q2   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CT->id}' ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
      while ($SB = mswSQL_fetchobj($q2)) {
        $ns = (++$seqs);
        mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
	      `orderBy`  = '{$ns}'
	      WHERE `id` = '{$SB->id}'
	      ", __file__, __line__);
      }
    }
  }

  // Add category..
  public function addCategory() {
    $_POST['subcat'] = (int) $_POST['subcat'];
    $acc = (!empty($_POST['acc']) ? mswSQL(implode(',', $_POST['acc'])) : 'all');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "categories` (
    `name`,
    `summary`,
    `enCat`,
    `subcat`,
    `private`,
    `accounts`
    ) VALUES (
    '" . mswSQL($_POST['name']) . "',
    '" . mswSQL($_POST['summary']) . "',
    '" . (isset($_POST['enCat']) ? 'yes' : 'no') . "',
    '{$_POST['subcat']}',
    '" . (isset($_POST['private']) && $_POST['subcat'] == '0' ? 'yes' : 'no') . "',
    '" . (isset($_POST['private']) && $_POST['subcat'] == '0' ? $acc : '') . "'
    )", __file__, __line__);
    $last = mswSQL_insert_id();
    // Rebuild order sequence..
    faqCentre::rebuildCatSequence();
    return $last;
  }

  // Update category..
  public function updateCategory() {
    $_GET['edit']    = (int) $_POST['update'];
    $_POST['subcat'] = (int) $_POST['subcat'];
    $private         = (isset($_POST['private']) && $_POST['subcat'] == '0' ? 'yes' : 'no');
    $acc             = (!empty($_POST['acc']) ? mswSQL(implode(',', $_POST['acc'])) : 'all');
    mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
    `name`      = '" . mswSQL($_POST['name']) . "',
    `summary`   = '" . mswSQL($_POST['summary']) . "',
    `enCat`     = '" . (isset($_POST['enCat']) && in_array($_POST['enCat'], array(
      'yes',
      'no'
     )) ? $_POST['enCat'] : 'no') . "',
    `subcat`    = '{$_POST['subcat']}',
    `private`   = '{$private}',
    `accounts`  = '" . (isset($_POST['private']) && $_POST['subcat'] == '0' ? $acc : '') . "'
    WHERE `id`  = '{$_GET['edit']}'
    ", __file__, __line__);
    // Update privacy status for questions in cat and sub cats..
    $catIDs = array($_GET['edit']);
    $q      = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "categories`
              WHERE `subcat` = '{$_GET['edit']}'
              ", __file__, __line__);
    while ($SB = mswSQL_fetchobj($q)) {
      $catIDs[] = $SB->id;
    }
    mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
    `private`   = '{$private}'
    WHERE `id` IN(" . mswSQL(implode(',', $catIDs)) . ")
    ", __file__, __line__);
    // Update all subcats..
    mswSQL_query("UPDATE `" . DB_PREFIX . "categories` SET
    `private`   = '{$private}'
    WHERE `id` IN(" . mswSQL(implode(',', $catIDs)) . ")
    ", __file__, __line__);
  }

  // Delete categories..
  public function deleteCategories() {
    $que = array();
    if (!empty($_POST['del'])) {
      // Clear cats..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "categories`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Clear assigned data..
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
      `cat` = '0'
      WHERE `cat` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      // Table cleanup..
      mswSQL_truncate(array('categories','faq','faqassign'));
      // Rebuild sequence..
      faqCentre::rebuildCatSequence();
      return $rows;
    }
  }

  // Enable/disable questions..
  public function enableDisableQuestions() {
    $_GET['id'] = (int) $_GET['id'];
    $state = ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes');
    mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
    `enFaq`    = '{$state}'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
    return $state;
  }

  // Add question..
  public function addQuestion() {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "faq` (
    `ts`,
    `question`,
    `answer`,
    `featured`,
    `enFaq`,
    `cat`,
    `tmp`,
    `searchkeys`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSQL($_POST['question']) . "',
    '" . mswSQL($_POST['answer']) . "',
    '" . (isset($_POST['featured']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['enFaq']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['cat']) ? (int) $_POST['cat'] : '0') . "',
    '" . mswSQL($_POST['tmp']) . "',
    '" . mswSQL($_POST['searchkeys']) . "'
    )", __file__, __line__);
    $ID = mswSQL_insert_id();
    // Assign attachments..
    if (!empty($_POST['att']) && $ID > 0) {
      foreach ($_POST['att'] AS $aID) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$ID}','{$aID}','attachment'
        )", __file__, __line__);
      }
    }
    // Are any categories private? If so, questions are private..
    faqCentre::rebuildPrivateFAQ();
    // Rebuild sequence..
    faqCentre::rebuildQueSequence();
    return $ID;
  }

  // Rebuild private categories / questions..
  public function rebuildPrivateFAQ() {
    $prcats = array();
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "categories`
         WHERE `private` = 'yes'
         ", __file__, __line__);
    while ($C = mswSQL_fetchobj($q)) {
      $prcats[] = $C->id;
    }
    if (!empty($prcats)) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
      `private` = 'yes'
      WHERE `cat` IN(" . implode(',', $prcats) . ")
      ", __file__, __line__);
    }
    mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
    `private` = 'no'
    WHERE `cat` NOT IN(" . (!empty($prcats) ? implode(',', $prcats) : '0') . ")
    ", __file__, __line__);
  }

  // Update question..
  public function updateQuestion() {
    $_GET['edit'] = (int) $_POST['update'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
    `question`   = '" . mswSQL($_POST['question']) . "',
    `answer`     = '" . mswSQL($_POST['answer']) . "',
    `featured`   = '" . (isset($_POST['featured']) ? 'yes' : 'no') . "',
    `enFaq`      = '" . (isset($_POST['enFaq']) ? 'yes' : 'no') . "',
    `cat`        = '" . (isset($_POST['cat']) ? (int) $_POST['cat'] : '0') . "',
    `tmp`        = '" . mswSQL($_POST['tmp']) . "',
    `searchkeys` = '" . mswSQL($_POST['searchkeys']) . "'
    WHERE `id`   = '{$_GET['edit']}'
    ", __file__, __line__);
    $aff = mswSQL_affrows();
    if ($aff > 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
      `ts` = UNIX_TIMESTAMP()
      WHERE `id` = '{$_GET['edit']}'
      ", __file__, __line__);
    }
    // Update attachments..
    if (!empty($_POST['att'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqassign` WHERE `question` = '{$_GET['edit']}' AND `desc` = 'attachment'", __file__, __line__);
      mswSQL_truncate(array('faqassign'));
      foreach ($_POST['att'] AS $aID) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) VALUES (
        '{$_GET['edit']}','{$aID}','attachment'
        )", __file__, __line__);
      }
    }
    // Are any categories private? If so, questions are private..
    faqCentre::rebuildPrivateFAQ();
    return $aff;
  }

  // Delete question..
  public function deleteQuestions() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faq`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqassign`
      WHERE `question` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqhistory`
      WHERE `faqID` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      mswSQL_truncate(array('faq','faqassign','faqhistory'));
      // Rebuild sequence..
      faqCentre::rebuildQueSequence();
      return $rows;
    }
  }

  // Rebuild question order sequence..
  public function rebuildQueSequence() {
    $seq = 0;
    $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "faq` ORDER BY IF(`orderBy`>0,`orderBy`,9999)", __file__, __line__);
    while ($RB = mswSQL_fetchobj($q)) {
      $n = (++$seq);
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ", __file__, __line__);
    }
  }

  // Order sequence..
  public function orderQueSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ", __file__, __line__);
    }
  }

  // Reset counts..
  public function resetCounts($his) {
    if (!empty($_POST['del'])) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
      `ts`          = UNIX_TIMESTAMP(),
      `kviews`      = '0',
      `kuseful`     = '0',
      `knotuseful`  = '0'
      WHERE `id`   IN(" . mswSQL(implode(',', $_POST['del'])) . ")
      ", __file__, __line__);
      foreach($_POST['del'] AS $rID) {
        faqCentre::historyLog($rID, $his);
      }
    }
  }

  // Batch import..
  public function batchImportQuestions($his) {
    $count = 0;
    $catid = (isset($_POST['cat']) ? (int) $_POST['cat'] : '0');
    if ($catid == 0) {
      return 0;
    }
    // Clear current questions..
    if (isset($_POST['clear']) && $catid > 0) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "faq` WHERE `cat` = '{$catid}'", __file__, __line__);
      mswSQL_truncate(array('faq','faqassign'));
    }
    // Upload CSV file..
    if ($this->ssn->active('upload_file') == 'yes' && file_exists($this->ssn->get('upload_file'))) {
      // If uploaded file exists, read CSV data...
      $handle = fopen($this->ssn->get('upload_file'), 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Clean array..
          $CSV = array_map('trim', $CSV);
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "faq` (
          `ts`,
          `question`,
          `answer`,
          `cat`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSQL($CSV[0]) . "',
          '" . mswSQL($CSV[1]) . "',
          '{$catid}'
          )", __file__, __line__);
          $ID = mswSQL_insert_id();
          if ($ID > 0) {
            faqCentre::historyLog($ID, $his);
            ++$count;
          }
        }
        fclose($handle);
      }
    }
    // Clear session file..
    $this->ssn->delete(array('upload_file'));
    // Are any categories private? If so, questions are private..
    faqCentre::rebuildPrivateFAQ();
    // Rebuild sequence..
    faqCentre::rebuildQueSequence();
    return $count;
  }

}

?>
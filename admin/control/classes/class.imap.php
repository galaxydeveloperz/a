<?php

/* CLASS FILE
----------------------------------*/

class imap {

  // Fulltext search character limit..
  private $ftxt_char_limit = 3;

  public function banFilters() {
    $acc = (isset($_POST['account']) ? 'yes' : 'no');
    $spm = (isset($_POST['spam']) ? 'yes' : 'no');
    mswSQL_truncate(array('imapban'), 'yes');
    if (isset($_POST['filters']) && $_POST['filters']) {
      foreach (explode(mswNL(), $_POST['filters']) AS $bf) {
        if ($bf && strlen($bf) >= $this->ftxt_char_limit) {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "imapban` (
          `filter`,
          `account`,
          `spam`
          ) VALUES (
          '" . mswSQL($bf) . "',
          '{$acc}',
          '{$spm}'
          )", __file__, __line__);
        }
      }
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "imap` SET
    `im_piping` = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id`  = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function addImapAccount() {
    $_POST                  = mswMDAM('mswSQL', $_POST);
    // Defaults if not set..
    $_POST['im_piping']     = (isset($_POST['im_piping']) ? 'yes' : 'no');
    $_POST['im_flags']      = (isset($_POST['im_flags']) ? imap::filterImapFlag($_POST['im_flags']) : '');
    $_POST['im_attach']     = (isset($_POST['im_attach']) ? 'yes' : 'no');
    $_POST['im_ssl']        = (isset($_POST['im_ssl']) ? 'yes' : 'no');
    $_POST['im_port']       = (int) $_POST['im_port'];
    $_POST['im_messages']   = (int) $_POST['im_messages'];
    $_POST['im_move']       = (isset($_POST['im_move']) ? $_POST['im_move'] : '');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "imap` (
    `im_piping`,
    `im_protocol`,
    `im_host`,
    `im_user`,
    `im_pass`,
    `im_port`,
    `im_name`,
    `im_flags`,
    `im_attach`,
    `im_move`,
    `im_messages`,
    `im_ssl`,
    `im_priority`,
    `im_status`,
    `im_dept`,
    `im_email`
    ) VALUES (
    '{$_POST['im_piping']}',
    'imap',
    '{$_POST['im_host']}',
    '{$_POST['im_user']}',
    '{$_POST['im_pass']}',
    '{$_POST['im_port']}',
    '{$_POST['im_name']}',
    '{$_POST['im_flags']}',
    '{$_POST['im_attach']}',
    '{$_POST['im_move']}',
    '{$_POST['im_messages']}',
    '{$_POST['im_ssl']}',
    '{$_POST['im_priority']}',
    '{$_POST['im_status']}',
    '{$_POST['im_dept']}',
    '{$_POST['im_email']}'
    )", __file__, __line__);
    return mswSQL_insert_id();
  }

  public function editImapAccount() {
    $_POST                  = mswMDAM('mswSQL', $_POST);
    // Defaults if not set..
    $_POST['im_piping']     = (isset($_POST['im_piping']) ? 'yes' : 'no');
    $_POST['im_flags']      = (isset($_POST['im_flags']) ? imap::filterImapFlag($_POST['im_flags']) : '');
    $_POST['im_attach']     = (isset($_POST['im_attach']) ? 'yes' : 'no');
    $_POST['im_ssl']        = (isset($_POST['im_ssl']) ? 'yes' : 'no');
    $_POST['im_port']       = (int) $_POST['im_port'];
    $_POST['im_messages']   = (int) $_POST['im_messages'];
    $_POST['im_move']       = (isset($_POST['im_move']) ? $_POST['im_move'] : '');
    $_GET['edit']           = (int) $_POST['update'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "imap` SET
    `im_piping`      = '{$_POST['im_piping']}',
    `im_protocol`    = 'imap',
    `im_host`        = '{$_POST['im_host']}',
    `im_user`        = '{$_POST['im_user']}',
    `im_pass`        = '{$_POST['im_pass']}',
    `im_port`        = '{$_POST['im_port']}',
    `im_name`        = '{$_POST['im_name']}',
    `im_flags`       = '{$_POST['im_flags']}',
    `im_attach`      = '{$_POST['im_attach']}',
    `im_move`        = '{$_POST['im_move']}',
    `im_messages`    = '{$_POST['im_messages']}',
    `im_ssl`         = '{$_POST['im_ssl']}',
    `im_priority`    = '{$_POST['im_priority']}',
    `im_status`      = '{$_POST['im_status']}',
    `im_dept`        = '{$_POST['im_dept']}',
    `im_email`       = '{$_POST['im_email']}'
    WHERE `id`       = '{$_GET['edit']}'
    ", __file__, __line__);
  }

  public function deleteImapAccounts() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "imap`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      $rows = mswSQL_affrows();
      mswSQL_truncate(array('imap'));
      return $rows;
    }
    return '0';
  }

  public function filterImapFlag($path) {
    if (substr($path, 0, 1) != '/') {
      $path = '/' . $path;
    }
    if (substr($path, -1) == '\\') {
      $path = substr_replace($path, '', -2);
    }
    return $path;
  }

}

?>
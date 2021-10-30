<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - TABLES
------------------------------------------------------*/

mswUpLog('Upgrade routine started', 'instruction');

foreach(
  array(
    'imap','imapban','cusfields','ticketfields','disputes','faqassign','faqattach','levels','ban',
    'mailassoc','mailbox','mailfolders','mailreplies','tickethistory','usersaccess','pages','faqdl',
    'social','admin_pages','faqhistory','statuses'
  ) AS $upTables) {
  // Add table..
  $tbdta = str_replace(array('{prefix}', '{engine}'), array(DB_PREFIX, $tableType), mswTmp(PATH . 'control/sql/tables/' . $upTables . '.sql', 'ok'));
  $query = mswSQL_query($tbdta);
  if ($query === 'err') {
    $ERR = mswSQL_error(true);
    mswUpLog(DB_PREFIX . $upTables, $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Table');
  } else {
    // Other ops (if applicable)..
    switch($upTables) {
      case 'imapban':
        // Does anything exist from the original ban filters?
        if (mswCheckTable('imap_b8') == 'yes') {
          $q = mswSQL_query("SELECT `skipFilters` FROM `" . DB_PREFIX . "imap_b8`");
          $F = mswSQL_fetchobj($q);
          if (isset($F->skipFilters)) {
            $fltrs = array_map('trim', explode(',', $F->skipFilters));
            if (!empty($fltrs)) {
              foreach ($fltrs AS $skip) {
                if ($skip) {
                  $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "imapban` (`filter`, `account`) VALUES ('" . mswSQL($skip) . "', 'yes')");
                  if ($query === 'err') {
                    $ERR = mswSQL_error(true);
                    mswUpLog(DB_PREFIX . 'imapban', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Ban Filter');
                  }
                }
              }
            }
          }
        }
        break;
      case 'levels':
        // Add defaults..
        $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "levels` VALUES (1, 'Low', 'yes', 'low', 1, 'a:2:{s:2:\"fg\";s:6:\"000000\";s:2:\"bg\";s:6:\"CCECF2\";}')");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'levels', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Level');
        }
        $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "levels` VALUES (2, 'Medium', 'yes', 'medium', 2, 'a:2:{s:2:\"fg\";s:6:\"FFFFFF\";s:2:\"bg\";s:6:\"B4A7BE\";}')");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'levels', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Level');
        }
        $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "levels` VALUES (3, 'High', 'yes', 'high', 3, 'a:2:{s:2:\"fg\";s:6:\"FFFFFF\";s:2:\"bg\";s:6:\"D42449\";}')");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'levels', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Level');
        }
        // Now convert any new levels..
        if (file_exists(BASE_PATH . 'control/priority-levels.php')) {
          include(BASE_PATH . 'control/priority-levels.php');
          $morelevels = 3;
          if (!empty($priorityLevels)) {
            foreach ($priorityLevels AS $k => $v) {
              $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "levels` (
              `name`, `display`, `marker`, `orderBy`
              ) VALUES (
              '" . mswSQL($v) . "', 'yes', '$k', '" . (++$morelevels) . "'
              )");
            }
            if ($query === 'err') {
              $ERR      = mswSQL_error(true);
              mswUpLog(DB_PREFIX . 'levels', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Old Levels');
            }
          }
        }
        break;
      case 'usersaccess':
        $query = mswSQL_query("update `" . DB_PREFIX . "users` set `pageAccess` = replace(`pageAccess`,'kbase','faq')");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
        }
        $q = mswSQL_query("SELECT `id`,`pageAccess` FROM `" . DB_PREFIX . "users` WHERE `id` > 1 ORDER BY `id`");
        while ($U = mswSQL_fetchobj($q)) {
          $pa = explode('|', $U->pageAccess);
          if (!empty($pa)) {
            foreach ($pa AS $uap) {
              $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "usersaccess` (
              `page`,`userID`,`type`
              ) values (
              '{$uap}','{$U->id}','pages'
              )");
              if ($query === 'err') {
                $ERR      = mswSQL_error(true);
                mswUpLog(DB_PREFIX . 'usersaccess', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Insert');
              }
            }
          }
        }
        break;
      case 'statuses':
        include_once(BASE_PATH . 'content/language/' . $SETTINGS->language . '/lang1.php');
        $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "statuses` (
        `id`, `name`, `perms`, `marker`, `orderby`, `colors`
        ) values (
        1, '" . (isset($msg_viewticket14) ? mswSQL($msg_viewticket14) : 'Open') . "', 'yes', 'open', 2, ''),
        (2, '" . (isset($msg_viewticket15) ? mswSQL($msg_viewticket15) : 'Closed') . "', 'yes', 'close', 1, ''),
        (3, '" . (isset($msg_viewticket16) ? mswSQL($msg_viewticket16) : 'Locked') . "', 'yes', 'closed', 3, ''
        )");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'statuses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Adding Default');
        }
        break;
    }
  }
}

mswUpLog('New tables completed', 'instruction');

?>
<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - OTHER
------------------------------------------------------*/

mswUpLog('Beginning other updates < v3.0', 'instruction');

if (mswCheckColumn('log', 'loginDateTime') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "log` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to log: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "log` drop column `loginDateTime`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from faq: loginDateTime', 'instruction');
  }
  mswSQL_truncate(array('log'));
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Truncation');
  } else {
    mswUpLog('Log table truncated due to upgrade', 'instruction');
  }
}

mswUpLog('Beginning other updates v3.0+', 'instruction');

$query = mswSQL_query("alter table `" . DB_PREFIX . "log` add column `ip` varchar(250) not null default ''");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
} else {
  mswUpLog('Column added to log: ip', 'instruction');
}

$query = mswSQL_query("alter table `" . DB_PREFIX . "log` add column `type` enum('user','acc') not null default 'user'");

if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
} else {
  mswUpLog('Column added to log: type', 'instruction');
}

$query = mswSQL_query("update `" . DB_PREFIX . "mailassoc` set `folder` = 'inbox' where `status` = 'unread'");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'log', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Updates for mailassoc columns', 'instruction');
}

if (mswCheckColumn('levels', 'colors') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "levels` add column `colors` varchar(200) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'levels', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to levels: colors', 'instruction');
  }
}

if (mswCheckColumn('pages', 'tmp') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "pages` add column `tmp` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'pages', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to pages: tmp', 'instruction');
  }
}

/* DELETIONS
   Not required, so the installer will attempt to
   remove them from the installation if permissions allow
-----------------------------------------------------------------*/

$remFiles = array_map('trim', file(PATH . 'control/upgrade/removal.txt'));
if (!empty($remFiles)) {
  mswUpLog('Attempting to remove ' . count($remFiles) . ' obsolete files', 'instruction');
  foreach($remFiles AS $rem_f) {
    if (strpos(basename($rem_f), '.') !== false) {
      $str = 'Attempting to remove file: ' . $rem_f;
      if (file_exists(BASE_PATH . $rem_f)) {
        @unlink(BASE_PATH . $rem_f);
        if (!file_exists(BASE_PATH . $rem_f)) {
          mswUpLog($str . ' (OK)', 'instruction');
        } else {
          mswUpLog($str . '(ERR, possibly permissions issue. Remove manually)', 'instruction');
        }
      } else {
        mswUpLog($rem_f . ' doesn`t exist in installation and was skipped', 'instruction');
      }
    } else {
      $str = 'Attempting to remove directory: ' . $rem_f;
      if (is_dir(BASE_PATH . $rem_f)) {
        @rmdir(BASE_PATH . $rem_f);
        if (!is_dir(BASE_PATH . $rem_f)) {
          mswUpLog($str . ' (OK)', 'instruction');
        } else {
          mswUpLog($str . '(ERR, possibly permissions issue. Remove manually)', 'instruction');
        }
      } else {
        mswUpLog($rem_f . ' doesn`t exist in installation and was skipped', 'instruction');
      }
    }
  }
}

mswUpLog('Other updates completed', 'instruction');

?>
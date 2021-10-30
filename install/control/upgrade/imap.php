<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - IMAP
------------------------------------------------------*/

mswUpLog('Beginning imap updates..', 'instruction');

if (property_exists($SETTINGS, 'im_piping') && $SETTINGS->im_protocol == 'imap' && ($SETTINGS->im_piping == 'yes' || $SETTINGS->im_host)) {
  $query = mswSQL_query("INSERT INTO `" . DB_PREFIX . "imap` (
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
  `im_dept`,
  `im_email`
  ) VALUES (
  '{$SETTINGS->im_piping}',
  '{$SETTINGS->im_protocol}',
  '{$SETTINGS->im_host}',
  '{$SETTINGS->im_user}',
  '{$SETTINGS->im_pass}',
  '{$SETTINGS->im_port}',
  '{$SETTINGS->im_name}',
  '{$SETTINGS->im_flags}',
  '{$SETTINGS->im_attach}',
  '',
  '{$SETTINGS->im_messages}',
  '{$SETTINGS->im_ssl}',
  '{$SETTINGS->im_priority}',
  '{$SETTINGS->im_dept}',
  '{$SETTINGS->im_email}'
  )");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Insert');
  } else {
    mswUpLog('Default data insertion for imap table', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop `im_piping`,
  drop `im_protocol`, drop `im_host`, drop `im_user`, drop `im_pass`,
  drop `im_port`, drop `im_name`, drop `im_flags`, drop `im_attach`,
  drop `im_delete`, drop `im_messages`, drop `im_ssl`,drop `im_priority`,
  drop `im_dept`, drop `im_email`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Multiple Column Drops');
  } else {
    mswUpLog('Old imap columns dropped from settings', 'instruction');
  }
  mswUpLog('Imap data converted from older versions. Single entry added to new imap table.', 'instruction');
}

if (mswCheckColumn('imap', 'im_spam') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "imap` drop column `im_spam`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from imap: im_spam', 'instruction');
  }
}

if (mswCheckColumn('imap', 'im_spam_purge') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "imap` drop column `im_spam_purge`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from imap: im_spam_purge', 'instruction');
  }
}

if (mswCheckColumn('imap', 'im_score') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "imap` drop column `im_score`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from imap: im_score', 'instruction');
  }
}

$query = mswSQL_query("update `" . DB_PREFIX . "imap` set `im_protocol` = 'imap', `im_piping` = 'no' where `im_protocol` = 'pop3'");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Updates');
} else {
  mswUpLog('Imap columns updated: im_protocol & im_piping', 'instruction');
}

mswUpLog('Beginning 4.0 updates for imap', 'instruction');

if (mswCheckTable('imap_b8_filter') == 'yes') {
  $query = mswSQL_query("drop table `" . DB_PREFIX . "imap_b8_filter`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap_b8_filter', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Drop');
  } else {
    mswUpLog('Table dropped: imap_b8_filter', 'instruction');
  }
}

if (mswCheckTable('imap_b8') == 'yes') {
  $query = mswSQL_query("drop table `" . DB_PREFIX . "imap_b8`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap_b8', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Drop');
  } else {
    mswUpLog('Table dropped: imap_b8', 'instruction');
  }
}

if (mswCheckColumn('imap', 'im_spam') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "imap` drop column `im_spam`, drop column `im_spam_purge`, drop column `im_score`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Multiple Column Drop');
  } else {
    mswUpLog('Imap columns dropped: im_spam, im_spam_purge & im_score', 'instruction');
  }
}

mswUpLog('Beginning imap updates for 4.3', 'instruction');

if (mswCheckColumn('imap', 'im_status') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "imap` add column `im_status` varchar(100) not null default 'open'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'imap', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to imap: im_status', 'instruction');
  }
}

mswUpLog('Imap updates completed', 'instruction');

?>
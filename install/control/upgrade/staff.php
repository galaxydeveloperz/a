<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - STAFF
------------------------------------------------------*/

mswUpLog('Beginning staff updates < v3.0', 'instruction');

if (mswCheckColumn('users', 'emailSigs') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `emailSigs` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: emailSigs', 'instruction');
  }
}

if (mswCheckColumn('users', 'notePadEnable') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `notePadEnable` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: notePadEnable', 'instruction');
  }
}

if (mswCheckColumn('users', 'delPriv') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `delPriv` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: delPriv', 'instruction');
  }
}

if (mswCheckColumn('users', 'nameFrom') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `nameFrom` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: nameFrom', 'instruction');
  }
}

if (mswCheckColumn('users', 'emailFrom') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `emailFrom` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: emailFrom', 'instruction');
  }
}

if (mswCheckColumn('users', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: ts', 'instruction');
  }
}

if (mswCheckColumn('users', 'assigned') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `assigned` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: assigned', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "users` set `assigned` = 'yes',`helplink` = 'yes' where `id` = '1'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in users: assigned', 'instruction');
  }
}

if (mswCheckColumn('users', 'timezone') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `timezone` varchar(50) not null default 'Europe/London'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: timezone', 'instruction');
  }
}

if (mswCheckColumn('users', 'addDate') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "users` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Update');
  } else {
    mswUpLog('Column updated in users: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` drop column `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Drop Column');
  } else {
    mswUpLog('Column dropped from users: addDate', 'instruction');
  }
}

mswUpLog('< v3.0 updates completed...Starting staff updates for v3.0+', 'instruction');

if (mswCheckColumn('users', 'enabled') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `enabled` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: enabled', 'instruction');
  }
}

if (mswCheckColumn('users', 'notes') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `notes` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: notes', 'instruction');
  }
}

if (mswCheckColumn('users', 'email2') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `email2` text default null after `email`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: email2', 'instruction');
  }
}

if (mswCheckColumn('users', 'ticketHistory') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `ticketHistory` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: ticketHistory', 'instruction');
  }
}

if (mswCheckColumnType('users', 'pageAccess', 'text') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` change `pageAccess` `pageAccess` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in users: pageAccess', 'instruction');
  }
}

if (mswCheckColumn('users', 'enableLog') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `enableLog` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: enableLog', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailbox') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailbox` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailbox', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailFolders') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailFolders` int(3) not null default '5'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailFolders', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailDeletion') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailDeletion` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailDetection', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailScreen') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailScreen` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailScreen', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailCopy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailCopy` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailCopy', 'instruction');
  }
}

if (mswCheckColumn('users', 'mailPurge') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mailPurge` int(3) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mailPurge', 'instruction');
  }
}

if (mswCheckColumn('users', 'addpages') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `addpages` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: addpages', 'instruction');
  }
}

if (mswCheckColumn('users', 'mergeperms') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `mergeperms` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: mergeperms', 'instruction');
  }
}

if (mswCheckColumn('users', 'digest') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `digest` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: digest', 'instruction');
  }
}

if (mswCheckColumn('users', 'digestasg') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `digestasg` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: digestasg', 'instruction');
  }
}

if (mswCheckColumn('users', 'profile') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `profile` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: profile', 'instruction');
  }
}


if (mswCheckColumn('users', 'helplink') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `helplink` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: helplink', 'instruction');
  }
}

if (mswCheckColumn('users', 'defDays') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `defDays` int(3) not null default '45'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: defDays', 'instruction');
  }
}

if (mswCheckColumn('users', 'editperms') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `editperms` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: editperms', 'instruction');
  }
}

// Updates for older versions..
$query = mswSQL_query("update `" . DB_PREFIX . "users` set `pageAccess` = replace(`pageAccess`, 'kbase', 'faq')");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in users: pageAccess', 'instruction');
}

$query = mswSQL_query("update `" . DB_PREFIX . "users` set `pageAccess` = replace(`pageAccess`, 'kbase-cat', 'faq-cat')");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in users: pageAccess', 'instruction');
}

$query = mswSQL_query("update `" . DB_PREFIX . "users` set `timezone` = 'Europe/London' where `timezone` = '0'");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in users: timezone', 'instruction');
}

$query = mswSQL_query("update `" . DB_PREFIX . "users` set `email` = LOWER(`email`), `email2` = LOWER(`email2`)");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in users: email', 'instruction');
}

mswUpLog('Beginning 4.0 staff updates', 'instruction');

if (mswCheckColumn('users', 'lock') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `lock` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: lock', 'instruction');
  }
}

if (mswCheckColumn('users', 'close') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `close` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: close', 'instruction');
  }
}

if (mswCheckColumn('users', 'admin') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `admin` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: admin', 'instruction');
  }
}

if (mswCheckColumn('users', 'timer') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `timer` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: timer', 'instruction');
  }
}

if (mswCheckColumn('users', 'startwork') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `startwork` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: startwork', 'instruction');
  }
}

if (mswCheckColumn('users', 'workedit') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `workedit` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: workedit', 'instruction');
  }
}

if (mswCheckColumn('users', 'language') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `language` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: language', 'instruction');
  }
}

if (mswCheckColumnType('users', 'accpass', '32') == 'yes' || mswCheckColumnType('users', 'accpass', '40') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` change column `accpass` `accpass` varchar(250) not null default '' after `email2`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in users: accpass', 'instruction');
  }
}

$query = mswSQL_query("update `" . DB_PREFIX . "users` set `admin` = 'yes' where `id` = '1'");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in users: admin', 'instruction');
}

mswUpLog('Beginning staff updates for 4.3', 'instruction');

if (mswCheckColumn('users', 'spamnotify') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `spamnotify` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: spamnotify', 'instruction');
  }
}

if (mswCheckColumn('users', 'savedstaff') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `savedstaff` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: savedstaff', 'instruction');
  }
}

if (mswCheckColumn('users', 'staffupnotify') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `staffupnotify` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: staffupnotify', 'instruction');
  }
}

if (mswCheckColumn('users', 'faqHistory') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `faqHistory` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: faqHistory', 'instruction');
  }
}

if (mswCheckColumn('users', 'digestops') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `digestops` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: digestops', 'instruction');
  }
}

if (mswCheckColumn('users', 'digestdays') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` add column `digestdays` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to users: digestdays', 'instruction');
  }
}

if (mswCheckColumn('users', 'digestasg') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "users` drop column `digestasg`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'users', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Drop Column');
  } else {
    mswUpLog('Column dropped from users: digestasg', 'instruction');
  }
}

mswUpLog('Staff updates completed', 'instruction');

?>
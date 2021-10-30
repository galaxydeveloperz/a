<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - ACCOUNTS
------------------------------------------------------*/

mswUpLog('Beginning account updates < v3.0', 'instruction');

if (mswCheckColumn('portal', 'enabled') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `enabled` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: enabled', 'instruction');
  }
}

if (mswCheckColumn('portal', 'timezone') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `timezone` varchar(50) not null default 'Europe/London'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: timezone', 'instruction');
  }
}

if (mswCheckColumn('portal', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: ts', 'instruction');
  }
}

if (mswCheckColumn('portal', 'addDate') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "portal` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Update Column');
  } else {
    mswUpLog('Column updated: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` drop column `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Drop Column');
  } else {
    mswUpLog('Column dropped from portal: addDate', 'instruction');
  }
}

$query = mswSQL_query("update `" . DB_PREFIX . "portal` set `email` = LOWER(`email`)");
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
} else {
  mswUpLog('Column updated in portal: email', 'instruction');
}

mswUpLog('< v3.0 updates completed...Starting account updates for v3.0+', 'instruction');

if (mswCheckColumn('faq', 'featured') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `ip` varchar(200) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: ip', 'instruction');
  }
}

if (mswCheckColumn('faq', 'featured') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `notes` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: notes', 'instruction');
  }
}

if (mswCheckColumn('portal', 'featured') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `reason` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: reason', 'instruction');
  }
}

if (mswCheckColumn('portal', 'verified') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `verified` enum('yes','no') not null default 'no' after `enabled`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: verified', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "portal` set `verified` = 'yes' where `enabled` = 'yes' and date(from_unixtime(`ts`)) < '2014-01-01'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in portal: verified', 'instruction');
  }
}

if (mswCheckColumn('portal', 'system1') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `system1` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: system1', 'instruction');
  }
}

if (mswCheckColumn('portal', 'system2') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `system2` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: system2', 'instruction');
  }
}

if (mswCheckColumn('portal', 'language') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `language` varchar(100) not null default 'english'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: language', 'instruction');
  }
}

if (mswCheckColumn('portal', 'enableLog') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `enableLog` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: enableLog', 'instruction');
  }
}

if (mswCheckColumnType('portal', 'ip', 'text') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` change column `ip` `ip` text default null after `timezone`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in portal: ip', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "portal` set `timezone` = 'Europe/London' where `timezone` = '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in portal: timezone', 'instruction');
  }
}

mswUpLog('Beginning account updates for 4.0', 'instruction');

if (mswCheckColumnType('portal', 'userPass', '32') == 'yes' || mswCheckColumnType('portal', 'userPass', '40') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` change column `userPass` `userPass` varchar(250) not null default '' after `email`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Change Column');
  } else {
    mswUpLog('Column changed in portal: userPass', 'instruction');
  }
}

mswUpLog('Account updates completed', 'instruction');

?>
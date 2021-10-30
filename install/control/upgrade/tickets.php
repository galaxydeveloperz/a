<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - TICKETS
------------------------------------------------------*/

mswUpLog('Beginning ticket updates < v3.0', 'instruction');

if (mswCheckColumn('tickets', 'ticketNotes') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `ticketNotes` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: ticketNotes', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'isDisputed') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `isDisputed` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: isDisputed', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'disPostPriv') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `disPostPriv` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: disPostPriv', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'addTime') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `addTime` time not null default '00:00:00' after `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: addTime', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'tickLang') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `tickLang` varchar(100) not null default 'english'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: tickLang', 'instruction');
  }
}

if (mswCheckColumnType('tickets', 'priority', 250) == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` change `priority` `priority` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in tickets: priority', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'assignedto') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `assignedto` varchar(250) not null default '' after `department`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: assignedto', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: ts', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'lastrevision') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `lastrevision` int(30) not null default '0' after `ts`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: lastrevision', 'instruction');
  }
}

// Timestamps..
if (mswCheckColumn('tickets', 'addTime') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "tickets` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' ',addTime))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in tickets: ts', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "tickets` set `lastrevision` = UNIX_TIMESTAMP(CONCAT(lastUpdate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in tickets: lastrevision', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` drop column `ticketStamp`,drop column `addDate`,drop column `addTime`,drop column `lastUpdate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Columns dropped from tickets: ticketStamp, addDate, addTime & lastUpdate', 'instruction');
  }
}

if (mswCheckColumn('replies', 'addTime') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` add column `addTime` time not null default '00:00:00' after `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to replies: addTime', 'instruction');
  }
}

if (mswCheckColumn('replies', 'disputeUser') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` add column `disputeUser` int(6) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to replies: disputeUser', 'instruction');
  }
}

if (mswCheckColumn('replies', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to replies: ts', 'instruction');
  }
}

// Timestamps..
if (mswCheckColumn('replies', 'addTime') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "replies` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' ',addTime))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in replies: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` drop column `replyStamp`,drop column `addDate`,drop column `addTime`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Multiple Column Drops');
  } else {
    mswUpLog('Columns dropped from replies: replyStamp, addDate & addTime', 'instruction');
  }
}

mswUpLog('< v3.0 updates completed...Starting ticket updates for v3.0+', 'instruction');

if (mswCheckColumn('tickets', 'visitorID') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `visitorID` int(8) not null default '0' after `assignedto`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: visitorID', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'name') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "portal` add column `name` varchar(200) not null default '' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to portal: name', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "tickets`,`" . DB_PREFIX . "portal` set
  `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`,
  `" . DB_PREFIX . "portal`.`name` = `" . DB_PREFIX . "tickets`.`name`
  where `" . DB_PREFIX . "portal`.`email` = `" . DB_PREFIX . "tickets`.`email`
  ");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets,portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Updates');
  } else {
    mswUpLog('Columns updated in tickets: visitorID & name', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` drop column `name`, drop column `email`, drop column `tickLang`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Multiple Column Drops');
  } else {
    mswUpLog('Columns dropped from tickets: name, email, tickLang', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'tickLang') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` drop column `tickLang`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from tickets: tickLang', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'source') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `source` varchar(10) not null default 'standard'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: source', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'spamFlag') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `spamFlag` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: spamFlag', 'instruction');
  }
}

if (mswCheckColumnType('tickets', 'ipAddresses', 'text') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` change column `ipAddresses` `ipAddresses` text default null after `ticketStatus`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in tickets: ipAddresses', 'instruction');
  }
}

if (mswSQL_rows('imap') > 0) {
  $query = mswSQL_query("update `" . DB_PREFIX . "tickets` set `source` = 'imap' where locate('.',`ipaddresses`) = 0 and `source` = 'standard'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in tickets: source', 'instruction');
  }
}

if (mswCheckColumnType('replies', 'ipAddresses', 'text') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "replies` change column `ipAddresses` `ipAddresses` text default null after `isMerged`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'replies', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in tickets: ipAddresses', 'instruction');
  }
}

if (mswCheckColumn('disputes', 'visitorID') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "disputes` add column `visitorID` int(8) not null default '0' after `ticketID`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'disputes', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to disputes: visitorID', 'instruction');
  }
}

if (mswCheckColumn('disputes', 'userName') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "disputes`,`" . DB_PREFIX . "portal` set
  `" . DB_PREFIX . "disputes`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
  WHERE `" . DB_PREFIX . "disputes`.`userEmail` = `" . DB_PREFIX . "portal`.`email`
  ");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'disputes,portal', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Columns updated in disputes: visitorID & userEmail', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "disputes` drop column `userName`, drop column `userEmail`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'disputes', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Multiple Column Drops');
  } else {
    mswUpLog('Columns dropped in disputes: userName & userEmail', 'instruction');
  }
}

mswUpLog('beginning ticket updates for 4.0', 'instruction');

if (mswCheckColumn('tickets', 'lockteam') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `lockteam` int(7) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: lockteam', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'lockrelease') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `lockrelease` int(30) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: lockrelease', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'tickno') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `tickno` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: tickno', 'instruction');
  }
}

if (mswCheckColumn('tickets', 'worktime') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` add column `worktime` varchar(50) not null default '00:00:00'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickets: worktime', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "tickets` set
           `worktime` = sec_to_time(timestampdiff(second,from_unixtime(`ts`), from_unixtime(`lastrevision`)))
           where `worktime` = '00:00:00'
           and `ts` > 0
           and `lastrevision` > 0
           and `ts` != `lastrevision`
           and timestampdiff(second,from_unixtime(`ts`), from_unixtime(`lastrevision`)) > 0
           and timestampdiff(second,from_unixtime(`ts`), from_unixtime(`lastrevision`)) < 3016800");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Updates');
  } else {
    mswUpLog('Column updated in tickets: worktime', 'instruction');
  }
}

if (mswCheckColumn('tickethistory', 'ip') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickethistory` add column `ip` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickethistory', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickethistory: ip', 'instruction');
  }
}

if (mswCheckColumn('tickethistory', 'staff') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickethistory` add column `staff` int(7) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickethistory', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to tickethistory: staff', 'instruction');
  }  
}

mswUpLog('Beginning ticket updates for 4.3', 'instruction');

if (mswCheckColumn('tickets', 'replyStatus') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` drop column `replyStatus`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from tickets: replyStatus', 'instruction');
  }
}

if (mswCheckColumnType('tickets', 'ticketStatus', 'enum') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "tickets` change column `ticketStatus` `ticketStatus` varchar(20) not null default '' after `priority`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'tickets', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in tickets: ticketStatus', 'instruction');
  }
}

mswUpLog('Ticket updates completed', 'instruction');

?>
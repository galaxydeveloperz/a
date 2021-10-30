<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - DEPARTMENTS, CATEGORIES, STANDARD RESPONSES
--------------------------------------------------------------------------*/

mswUpLog('Beginning attachments updates < v3.0', 'instruction');

if (mswCheckColumnType('attachments', 'fileName', 250) == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "attachments` change `fileName` `fileName` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'attachments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in attachments: fileName', 'instruction');
  }
}

if (mswCheckColumn('attachments', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "attachments` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'attachments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to attachments: ts', 'instruction');
  }
}

if (mswCheckColumn('attachments', 'addDate') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "attachments` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'attachments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in attachments: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "attachments` drop column `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'attachments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Drop Column');
  } else {
    mswUpLog('Column dropped in attachments: addDate', 'instruction');
  }
}

mswUpLog('< v3.0 updates completed...Starting attachment updates for v3.0+', 'instruction');

if (mswCheckColumn('attachments', 'mimeType') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "attachments` add column `mimeType` varchar(100) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'attachments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to attachments: mimeType', 'instruction');
  }
}

mswUpLog('Beginning department updates < v3.0', 'instruction');

if (mswCheckColumn('departments', 'showDept') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `showDept` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: showDept', 'instruction');
  }
}

if (mswCheckColumn('departments', 'dept_subject') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `dept_subject` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: dept_subject', 'instruction');
  }
}

if (mswCheckColumn('departments', 'dept_comments') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `dept_comments` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: dept_comments', 'instruction');
  }
}

if (mswCheckColumn('departments', 'orderBy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `orderBy` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: orderBy', 'instruction');
  }
}

if (mswCheckColumn('departments', 'manual_assign') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `manual_assign` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: manual_assign', 'instruction');
  }
}

mswUpLog('Beginning category updates < v3.0', 'instruction');

if (mswCheckColumn('categories', 'enCat') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "categories` add column `enCat` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'categories', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to categories: enCat', 'instruction');
  }
}

if (mswCheckColumn('categories', 'orderBy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "categories` add column `orderBy` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'categories', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to categories: orderBy', 'instruction');
  }
}

if (mswCheckColumn('categories', 'subcat') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "categories` add column `subcat` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'categories', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to categories: subcat', 'instruction');
  }
}

mswUpLog('Custom fields updates completed < v3.0', 'instruction');

if (mswCheckColumn('cusfields', 'departments') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "cusfields` add column `departments` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'cusfields', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to cusfields: departments', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "cusfields` set `departments` = 'all'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'cusfields', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in cusfields: departments', 'instruction');
  }
}

mswUpLog('Beginning updates v3.0+', 'instruction');

$allDepts = array();
$q        = mswSQL_query("select `id` from `" . DB_PREFIX . "departments` order by `id`");
while ($D = mswSQL_fetchobj($q)) {
  $allDepts[] = $D->id;
}

if (!empty($allDepts)) {
  $query = mswSQL_query("update `" . DB_PREFIX . "cusfields` set `departments` = '" . mswSQL(implode(',', $allDepts)) . "' where `departments` in('0','','all')");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'cusfields', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in cusfields: departments', 'instruction');
  }
}

mswUpLog('Beginning standard responses updates < v3.0', 'instruction');

if (mswCheckColumn('responses', 'enResponse') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` add column `enResponse` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to responses: enResponse', 'instruction');
  }
}

if (mswCheckColumn('responses', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to responses: ts', 'instruction');
  }
}

if (mswCheckColumn('responses', 'addDate') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "responses` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in responses: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` drop column `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from responses: addDate', 'instruction');
  }
}

mswUpLog('Beginning standard response updates v3.0+', 'instruction');

if (mswCheckColumn('responses', 'orderBy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` add column `orderBy` int(8) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to responses: orderBy', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "responses` set `orderBy` = `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in responses: orderBy', 'instruction');
  }
}

if (mswCheckColumn('responses', 'departments') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` add column `departments` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to responses: departments', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "responses` set `departments` = `department`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in responses: departments', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "responses` drop `department`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from responses: department', 'instruction');
  }
}

if (!empty($allDepts)) {
  $query = mswSQL_query("update `" . DB_PREFIX . "responses` set `departments` = '" . mswSQL(implode(',', $allDepts)) . "' where `departments` in('0','','all')");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'responses', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in responses: departments', 'instruction');
  }
}

if (mswCheckColumn('categories', 'private') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "categories` add column `private` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'categories', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to categories: private', 'instruction');
  }
}

if (mswCheckColumn('departments', 'days') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `days` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: days', 'instruction');
  }
} else {
  $query = mswSQL_query("update `" . DB_PREFIX . "departments` set `days` = replace(`days`,'Thur','Thu')");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in departments: days', 'instruction');
  }
}

mswUpLog('Beginning updates for 4.0', 'instruction');

if (mswCheckColumn('categories', 'accounts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "categories` add column `accounts` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'categories', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to categories: accounts', 'instruction');
  }
}

if (mswCheckColumn('departments', 'dept_priority') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `dept_priority` varchar(50) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: dept_priority', 'instruction');
  }
}

mswUpLog('Beginning account updates for 4.3', 'instruction');

if (mswCheckColumn('departments', 'auto_admin') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `auto_admin` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: auto_admin', 'instruction');
  }
}

if (mswCheckColumn('departments', 'auto_response') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `auto_response` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: auto_response', 'instruction');
  }
}

if (mswCheckColumn('departments', 'response_sbj') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `response_sbj` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: response_sbj', 'instruction');
  }
}

if (mswCheckColumn('departments', 'response') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "departments` add column `response` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to departments: response', 'instruction');
  }
}

if (mswCheckColumn('cusfields', 'accounts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "cusfields` add column `accounts` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'cusfields', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to cusfields: accounts', 'instruction');
  }
}

if (mswCheckColumnType('cusfields', 'fieldType', 'calendar') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "cusfields` change `fieldType` `fieldType` enum('textarea','input','select','checkbox','calendar') not null default 'input' after `fieldInstructions`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'cusfields', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in cusfields: fieldType', 'instruction');
  }
}

mswUpLog('Departments, categories, standard responses upgrades completed', 'instruction');

?>
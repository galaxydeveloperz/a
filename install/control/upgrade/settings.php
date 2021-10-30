<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - SETTINGS
------------------------------------------------------*/

mswUpLog('Beginning settings updates < v3.0', 'instruction');

if (!property_exists($SETTINGS, 'softwareVersion')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `softwareVersion` varchar(10) not null default '" . SCRIPT_VERSION . "'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: softwareVersion', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'enableBBCode')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` change `autolinks` `enableBBCode` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in settings: autolinks', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'apiKey')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `apiKey` varchar(100) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: apiKey', 'instruction');
  }
}

if (property_exists($SETTINGS, 'enCapLogin')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `enCapLogin`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: enCapLogin', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'quePerPage')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `quePerPage` int(3) not null default '10' after `popquestions`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: quePerPage', 'instruction');
  }
}

if (property_exists($SETTINGS, 'enSpamSum')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop `enSpamSum`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: enSpamSum', 'instruction');
  }
}

if (property_exists($SETTINGS, 'recaptchaPublicKey')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `recaptchaPublicKey`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: recaptchaPublicKey', 'instruction');
  }
}

if (property_exists($SETTINGS, 'recaptchaPrivateKey')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `recaptchaPrivateKey`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: recaptchaPrivateKey', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'weekStart')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `weekStart` enum('mon','sun') not null default 'sun' after `dateformat`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: weekStart', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'jsDateFormat')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `jsDateFormat` varchar(15) not null default 'DD/MM/YYYY' after `weekstart`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: jsDateFormat', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'sysstatus')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `sysstatus` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: sysstatus', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'autoenable')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `autoenable` date not null default '1000-01-01'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: autoenable', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'autoCloseMail')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `autoCloseMail` enum('yes','no') not null default 'yes' after `autoClose`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: autoCloseMail', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'timeformat')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `timeformat` varchar(15) not null default 'H:iA' after `dateformat`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: timeformat', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `dateformat` = 'd M Y', `timeformat` = 'H:iA'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Updated column in settings: dateformat', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'timezone')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` change `timeoffset` `timezone` varchar(50) not null default 'Europe/London' after `timeformat`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in settings: timeoffset', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'rename')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `rename` enum('yes','no') not null default 'no' after `attachment`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: rename', 'instruction');
  }
}

if (property_exists($SETTINGS, 'mysqldate')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `mysqldate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: mysqldate', 'instruction');
  }
}

// Older language versions adjustment..
if (substr($SETTINGS->language, -4) == '.php') {
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `language` = 'english'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in settings: language', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'rename')) {
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `rename` = 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in settings: rename', 'instruction');
  }
}

if (property_exists($SETTINGS, 'timeOffset')) {
  $diff = substr($SETTINGS->timeOffset, 0, -6);
  if (isset($timezones_php4)) {
    $flip = array_flip($timezones_php4);
    $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `timezone` = '" . (isset($flip[$diff]) ? $flip[$diff] : 'Europe/London') . "'");
    if ($query === 'err') {
      $ERR      = mswSQL_error(true);
      mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
    } else {
      mswUpLog('Column updated in settings: timezone', 'instruction');
    }
  } else {
    $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `timezone` = 'Europe/London'");
    if ($query === 'err') {
      $ERR      = mswSQL_error(true);
      mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
    } else {
      mswUpLog('Column updated in settings: timezone', 'instruction');
    }
  }
}

// v3.0 Changes..
mswUpLog('< v3.0 updates completed...Starting settings updates for v3.0+', 'instruction');

if (!property_exists($SETTINGS, 'disputes')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `disputes` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: disputes', 'instruction');
  }
  if (mswSQL_rows('tickets WHERE `isDisputed` = \'yes\'') > 0) {
    $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `disputes` = 'yes'");
    if ($query === 'err') {
      $ERR      = mswSQL_error(true);
      mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
    } else {
      mswUpLog('Column updated in settings: disputes', 'instruction');
    }
  }
}

if (property_exists($SETTINGS, 'smtp')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `smtp`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: smtp', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'smtp_security')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `smtp_security` varchar(10) not null default '' after `smtp_port`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: smtp_security', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'smtp_debug')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `smtp_debug` enum('yes','no') not null default 'no' after `smtp_security`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: smtp_debug', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'smtp_html')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `smtp_html` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: featured', 'smtp_html');
  }
}

if (!property_exists($SETTINGS, 'offlineReason')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `offlineReason` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: offlineReason', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'createPref')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `createPref` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: createPref', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'createAcc')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `createAcc` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: createAcc', 'instruction');
  }
}

// HTTP Paths..
$hdeskPath = 'http://www.example.com/helpdesk';
if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF'])) {
  $hdeskPath = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install') - 1);
}
$hdeskPathAtt = $hdeskPath . '/content/attachments';
$hdeskPathFaq = $hdeskPath . '/content/attachments-faq';

// Server Paths..
$attFaqPath   = mswSQL(BASE_PATH . 'content/attachments-faq');

if (!property_exists($SETTINGS, 'attachhref')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `attachhref` varchar(250) not null default ''after `attachpath`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: attachhref', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `attachhref` = '{$hdeskPathAtt}'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in settings: attachhref', 'instruction');
  }
}

if (mswCheckColumnType('settings', 'maxsize', 15) == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` change column `maxsize` `maxsize` int(15) not null default '1048576' after `filetypes`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in settings: maxsize', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'attachpathfaq')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `attachpathfaq` varchar(250) not null default '' after `attachhref`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: attachpathfaq', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `attachpathfaq` = '{$attFaqPath}'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in settings: attachpathfaq', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'attachhreffaq')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `attachhreffaq` varchar(250) not null default '' after `attachpathfaq`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: attachhreffaq', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "settings` set `attachhreffaq` = '{$hdeskPathFaq}'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in settings: attachhreffaq', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'renamefaq')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `renamefaq` enum('yes','no') not null default 'no' after `cookiedays`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: renamefaq', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'loginLimit')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `loginLimit` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: loginlimit', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'banTime')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `banTime` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: banTime', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'ticketHistory')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `ticketHistory` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: ticketHistory', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'backupEmails')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `backupEmails` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: backupEmails', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'closenotify')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `closenotify` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: closenotify', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'replyto')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `replyto` varchar(250) not null default '' after `email`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: replyto', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'langSets')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `langSets` text default null after language");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: langSets', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'minPassValue')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `minPassValue` int(3) not null default '8'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: minPassValue', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'accProfNotify')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `accProfNotify` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: accProfNotify', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'newAccNotify')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `newAccNotify` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: newAccNotify', 'instruction');
  }
}

if (property_exists($SETTINGS, 'recaptchaTheme')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `recaptchaTheme`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: recaptchaTheme', 'instruction');
  }
}

if (property_exists($SETTINGS, 'recaptchaLang')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `recaptchaLang`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: recaptchaLang', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'enableLog')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `enableLog` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: enableLog', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'defKeepLogs')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `defKeepLogs` varchar(100) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: defKeepLogs', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'minTickDigits')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `minTickDigits` int(2) not null default '5'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: minTickDigits', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'enableMail')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `enableMail` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: enableMail', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_debug')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_debug` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_debug', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_param')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_param` varchar(10) not null default 'pipe'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_param', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_memory')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_memory` varchar(3) not null default '10'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_memory', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_timeout')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_timeout` varchar(3) not null default '120'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_timeout', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'apiHandlers')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `apiHandlers` varchar(100) not null default 'xml' after `apiKey`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: apiHandlers', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'apiLog')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `apiLog` enum('yes','no') not null default 'no' after `apiKey`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: apiLog', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'disputeAdminStop')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `disputeAdminStop` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: disputeAdminStop', 'instruction');
  }
}

if (property_exists($SETTINGS, 'portalpages')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` drop column `portalpages`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from settings: portalpages', 'instruction');
  }
}

if (mswCheckColumnType('settings', 'language', 250) == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` change `language` `language` varchar(250) not null default 'english' after `attachhreffaq`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in settings: language', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'faqcounts')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `faqcounts` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: faqcounts', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_attach')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_attach` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_attach', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_notify')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_notify` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_notify', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'closeadmin')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `closeadmin` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: closeadmin', 'instruction');
  }
}

mswUpLog('Beginning 4.0 updates for settings', 'instruction');

if (!property_exists($SETTINGS, 'adminlock')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `adminlock` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: adminlock', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'locktime')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `locktime` int(7) not null default '5'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: locktime', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_clean')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_clean` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_clean', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'tawk')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `tawk` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: tawk', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'tawk_home')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `tawk_home` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: tawk_home', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'defdept')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `defdept` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: defdept', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'defprty')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `defprty` varchar(50) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: defprty', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'rantick')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `rantick` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: rantick', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'imap_open')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imap_open` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imap_open', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'autospam')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `autospam` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: autospam', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'wordwrap')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `wordwrap` varchar(200) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: wordwrap', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'timetrack')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `timetrack` enum('yes', 'no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: timetrack', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'selfsign')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `selfsign` enum('yes', 'no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: selfsign', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'openlimit')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `openlimit` enum('yes', 'no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: openlimit', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'mail')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `mail` enum('smtp','mail') not null default 'smtp'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: mail', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'accautodel')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `accautodel` int(5) not null default '7'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: accautodel', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'visclose')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `visclose` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: visclose', 'instruction');
  }
}

mswUpLog('Beginning settings updates for 4.3', 'instruction');

if (!property_exists($SETTINGS, 'imapspamcloseacc')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `imapspamcloseacc` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: imapspamcloseacc', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'navmenu')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `navmenu` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: navmenu', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'faqHistory')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `faqHistory` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: faqHistory', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'spam_score_header')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `spam_score_header` varchar(100) not null default 'X-Spam-Score'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: spam_score_header', 'instruction');
  }
}

if (!property_exists($SETTINGS, 'spam_score_value')) {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "settings` add column `spam_score_value` varchar(100) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to settings: spam_score_value', 'instruction');
  }
}

mswUpLog('Settings updates completed', 'instruction');

?>
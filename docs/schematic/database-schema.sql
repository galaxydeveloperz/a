-- --------------------------------------------------------
-- MySQL Dump: Maian Support
-- --------------------------------------------------------

-- Dumping structure for table msp_admin_pages
DROP TABLE IF EXISTS `msp_admin_pages`;
CREATE TABLE IF NOT EXISTS `msp_admin_pages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text default null,
  `information` text default null,
  `enPage` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `accounts` text default null,
  `tmp` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_attachments
DROP TABLE IF EXISTS `msp_attachments`;
CREATE TABLE IF NOT EXISTS `msp_attachments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` varchar(20) NOT NULL DEFAULT '',
  `replyID` int(11) NOT NULL DEFAULT '0',
  `department` int(5) NOT NULL DEFAULT '0',
  `fileName` varchar(250) NOT NULL DEFAULT '',
  `fileSize` varchar(20) NOT NULL DEFAULT '',
  `mimeType` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_ban
DROP TABLE IF EXISTS `msp_ban`;
CREATE TABLE IF NOT EXISTS `msp_ban` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(250) NOT NULL DEFAULT '',
  `count` int(5) NOT NULL DEFAULT '0',
  `banstamp` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_categories
DROP TABLE IF EXISTS `msp_categories`;
CREATE TABLE IF NOT EXISTS `msp_categories` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `summary` varchar(250) NOT NULL DEFAULT '',
  `enCat` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `subcat` int(5) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  `accounts` text default null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_categories: 2 rows
INSERT INTO `msp_categories` (`id`, `name`, `summary`, `enCat`, `orderBy`, `subcat`, `private`, `accounts`) VALUES
	(1, 'Software Questions', 'This category relates to our software', 'yes', 1, 0, 'no', NULL);
INSERT INTO `msp_categories` (`id`, `name`, `summary`, `enCat`, `orderBy`, `subcat`, `private`, `accounts`) VALUES
	(2, 'Company Questions', 'This category relates to our company', 'yes', 2, 0, 'no', NULL);

-- Dumping structure for table msp_cusfields
DROP TABLE IF EXISTS `msp_cusfields`;
CREATE TABLE IF NOT EXISTS `msp_cusfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldInstructions` varchar(250) NOT NULL DEFAULT '',
  `fieldType` enum('textarea','input','select','checkbox','calendar') NOT NULL DEFAULT 'input',
  `fieldReq` enum('yes','no') NOT NULL DEFAULT 'no',
  `fieldOptions` text default null,
  `fieldLoc` varchar(25) NOT NULL DEFAULT '',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `repeatPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enField` enum('yes','no') NOT NULL DEFAULT 'yes',
  `departments` text default null,
  `accounts` text default null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_departments
DROP TABLE IF EXISTS `msp_departments`;
CREATE TABLE IF NOT EXISTS `msp_departments` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `showDept` enum('yes','no') NOT NULL DEFAULT 'no',
  `dept_subject` text default null,
  `dept_comments` text default null,
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `manual_assign` enum('yes','no') NOT NULL DEFAULT 'no',
  `days` text default null,
  `dept_priority` varchar(50) NOT NULL DEFAULT '',
  `auto_admin` enum('yes','no') NOT NULL DEFAULT 'yes',
  `auto_response` enum('yes','no') NOT NULL DEFAULT 'no',
  `response` text default null,
  `response_sbj` text default null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_departments: 3 rows
INSERT INTO `msp_departments` (`id`, `name`, `showDept`, `dept_subject`, `dept_comments`, `orderBy`, `manual_assign`, `days`, `dept_priority`, `auto_admin`, `auto_response`, `response`, `response_sbj`) VALUES
	(1, 'General Support', 'yes', '', '', 1, 'no', NULL, '', 'yes', 'no', NULL, NULL);
INSERT INTO `msp_departments` (`id`, `name`, `showDept`, `dept_subject`, `dept_comments`, `orderBy`, `manual_assign`, `days`, `dept_priority`, `auto_admin`, `auto_response`, `response`, `response_sbj`) VALUES
	(2, 'Sales and Billing', 'yes', '', '', 2, 'no', NULL, '', 'yes', 'no', NULL, NULL);
INSERT INTO `msp_departments` (`id`, `name`, `showDept`, `dept_subject`, `dept_comments`, `orderBy`, `manual_assign`, `days`, `dept_priority`, `auto_admin`, `auto_response`, `response`, `response_sbj`) VALUES
	(3, 'Technical Support', 'yes', '', '', 3, 'no', NULL, '', 'yes', 'no', NULL, NULL);

-- Dumping structure for table msp_disputes
DROP TABLE IF EXISTS `msp_disputes`;
CREATE TABLE IF NOT EXISTS `msp_disputes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `postPrivileges` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `vis_index` (`visitorID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_faq
DROP TABLE IF EXISTS `msp_faq`;
CREATE TABLE IF NOT EXISTS `msp_faq` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `question` text default null,
  `answer` text default null,
  `kviews` int(10) NOT NULL DEFAULT '0',
  `kuseful` int(10) NOT NULL DEFAULT '0',
  `knotuseful` int(10) NOT NULL DEFAULT '0',
  `enFaq` enum('yes','no') NOT NULL DEFAULT 'yes',
  `featured` enum('yes','no') NOT NULL DEFAULT 'no',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `cat` int(7) NOT NULL DEFAULT '0',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  `tmp` varchar(250) NOT NULL DEFAULT '',
  `searchkeys` text default null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_faq: 2 rows
INSERT INTO `msp_faq` (`id`, `ts`, `question`, `answer`, `kviews`, `kuseful`, `knotuseful`, `enFaq`, `featured`, `orderBy`, `cat`, `private`, `tmp`, `searchkeys`) VALUES
	(1, UNIX_TIMESTAMP(), 'Why is our software the best?', 'This is a test question created when installing Maian Support\n\nPlease update or remove via your admin control panel', 0, 0, 0, 'yes', 'no', 1, 1, 'no', '', NULL);
INSERT INTO `msp_faq` (`id`, `ts`, `question`, `answer`, `kviews`, `kuseful`, `knotuseful`, `enFaq`, `featured`, `orderBy`, `cat`, `private`, `tmp`, `searchkeys`) VALUES
	(2, UNIX_TIMESTAMP(), 'Why is our company the best?', 'This is a test question created when installing Maian Support\n\nPlease update or remove via your admin control panel', 0, 0, 0, 'yes', 'no', 2, 2, 'no', '', NULL);

-- Dumping structure for table msp_faqassign
DROP TABLE IF EXISTS `msp_faqassign`;
CREATE TABLE IF NOT EXISTS `msp_faqassign` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `question` int(7) NOT NULL DEFAULT '0',
  `itemID` int(7) NOT NULL DEFAULT '0',
  `desc` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `que_index` (`question`),
  KEY `att_index` (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_faqattach
DROP TABLE IF EXISTS `msp_faqattach`;
CREATE TABLE IF NOT EXISTS `msp_faqattach` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',
  `remote` varchar(250) NOT NULL DEFAULT '',
  `path` varchar(250) NOT NULL DEFAULT '',
  `size` varchar(30) NOT NULL DEFAULT '',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `enAtt` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mimeType` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_faqdl
DROP TABLE IF EXISTS `msp_faqdl`;
CREATE TABLE IF NOT EXISTS `msp_faqdl` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `question` int(7) NOT NULL DEFAULT '0',
  `token` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_faqhistory
DROP TABLE IF EXISTS `msp_faqhistory`;
CREATE TABLE IF NOT EXISTS `msp_faqhistory` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `faqID` int(11) NOT NULL DEFAULT '0',
  `action` text default null,
  `ip` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `faq_index` (`faqID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_imap
DROP TABLE IF EXISTS `msp_imap`;
CREATE TABLE IF NOT EXISTS `msp_imap` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `im_piping` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_protocol` enum('pop3','imap') NOT NULL DEFAULT 'pop3',
  `im_host` varchar(100) NOT NULL DEFAULT '',
  `im_user` varchar(250) NOT NULL DEFAULT '',
  `im_pass` varchar(100) NOT NULL DEFAULT '',
  `im_port` int(5) NOT NULL DEFAULT '110',
  `im_name` varchar(50) NOT NULL DEFAULT '',
  `im_flags` varchar(250) NOT NULL DEFAULT '',
  `im_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_move` varchar(50) NOT NULL DEFAULT '',
  `im_messages` int(3) NOT NULL DEFAULT '20',
  `im_ssl` enum('yes','no') NOT NULL DEFAULT 'no',
  `im_priority` varchar(250) NOT NULL DEFAULT '',
  `im_status` varchar(100) NOT NULL DEFAULT '',
  `im_dept` int(5) NOT NULL DEFAULT '0',
  `im_email` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_imapban
DROP TABLE IF EXISTS `msp_imapban`;
CREATE TABLE IF NOT EXISTS `msp_imapban` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `filter` text default null,
  `account` enum('yes','no') NOT NULL DEFAULT 'yes',
  `spam` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `filter` (`filter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_imapban: 1 rows
INSERT INTO `msp_imapban` (`id`, `filter`, `account`, `spam`) VALUES
	(1, 'mailer-daemon', 'yes', 'no');

-- Dumping structure for table msp_levels
DROP TABLE IF EXISTS `msp_levels`;
CREATE TABLE IF NOT EXISTS `msp_levels` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `display` enum('yes','no') NOT NULL DEFAULT 'no',
  `marker` varchar(100) NOT NULL DEFAULT '',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `colors` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_levels: 3 rows
INSERT INTO `msp_levels` (`id`, `name`, `display`, `marker`, `orderBy`, `colors`) VALUES
	(1, 'Low', 'yes', 'low', 1, 'a:2:{s:2:"fg";s:6:"000000";s:2:"bg";s:6:"CCECF2";}');
INSERT INTO `msp_levels` (`id`, `name`, `display`, `marker`, `orderBy`, `colors`) VALUES
	(2, 'Medium', 'yes', 'medium', 2, 'a:2:{s:2:"fg";s:6:"FFFFFF";s:2:"bg";s:6:"B4A7BE";}');
INSERT INTO `msp_levels` (`id`, `name`, `display`, `marker`, `orderBy`, `colors`) VALUES
	(3, 'High', 'yes', 'high', 3, 'a:2:{s:2:"fg";s:6:"FFFFFF";s:2:"bg";s:6:"D42449";}');

-- Dumping structure for table msp_log
DROP TABLE IF EXISTS `msp_log`;
CREATE TABLE IF NOT EXISTS `msp_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `userID` int(5) NOT NULL DEFAULT '0',
  `ip` varchar(250) NOT NULL DEFAULT '',
  `type` enum('user','acc') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  KEY `useid_index` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_mailassoc
DROP TABLE IF EXISTS `msp_mailassoc`;
CREATE TABLE IF NOT EXISTS `msp_mailassoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(10) NOT NULL DEFAULT 'inbox',
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `lastUpdate` int(30) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`),
  KEY `mail_index` (`mailID`),
  KEY `status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_mailbox
DROP TABLE IF EXISTS `msp_mailbox`;
CREATE TABLE IF NOT EXISTS `msp_mailbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `message` text default null,
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_mailfolders
DROP TABLE IF EXISTS `msp_mailfolders`;
CREATE TABLE IF NOT EXISTS `msp_mailfolders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staffID` int(8) NOT NULL DEFAULT '0',
  `folder` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_mailreplies
DROP TABLE IF EXISTS `msp_mailreplies`;
CREATE TABLE IF NOT EXISTS `msp_mailreplies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `mailID` int(8) NOT NULL DEFAULT '0',
  `staffID` int(8) NOT NULL DEFAULT '0',
  `message` text default null,
  PRIMARY KEY (`id`),
  KEY `mail_index` (`mailID`),
  KEY `staff_index` (`staffID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_pages
DROP TABLE IF EXISTS `msp_pages`;
CREATE TABLE IF NOT EXISTS `msp_pages` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text default null,
  `information` text default null,
  `enPage` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `secure` enum('yes','no') NOT NULL DEFAULT 'no',
  `accounts` text default null,
  `tmp` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_portal
DROP TABLE IF EXISTS `msp_portal`;
CREATE TABLE IF NOT EXISTS `msp_portal` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `ts` int(30) NOT NULL DEFAULT '0',
  `email` varchar(250) NOT NULL DEFAULT '',
  `userPass` varchar(250) NOT NULL DEFAULT '',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `verified` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0',
  `ip` text default null,
  `notes` text default null,
  `reason` text default null,
  `system1` varchar(250) NOT NULL DEFAULT '',
  `system2` varchar(250) NOT NULL DEFAULT '',
  `language` varchar(100) NOT NULL DEFAULT '',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `em_index` (`email`),
  KEY `nm_index` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_replies
DROP TABLE IF EXISTS `msp_replies`;
CREATE TABLE IF NOT EXISTS `msp_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(15) NOT NULL DEFAULT '0',
  `comments` text default null,
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '',
  `replyType` enum('none','visitor','admin') NOT NULL DEFAULT 'none',
  `replyUser` int(8) NOT NULL DEFAULT '0',
  `isMerged` enum('yes','no') NOT NULL DEFAULT 'no',
  `ipAddresses` text default null,
  `disputeUser` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `repuse_index` (`replyUser`),
  KEY `disuse_index` (`disputeUser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_responses
DROP TABLE IF EXISTS `msp_responses`;
CREATE TABLE IF NOT EXISTS `msp_responses` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `title` text default null,
  `answer` text default null,
  `enResponse` enum('yes','no') NOT NULL DEFAULT 'yes',
  `orderBy` int(8) NOT NULL DEFAULT '0',
  `departments` text default null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_settings
DROP TABLE IF EXISTS `msp_settings`;
CREATE TABLE IF NOT EXISTS `msp_settings` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `website` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `replyto` varchar(250) NOT NULL DEFAULT '',
  `scriptpath` varchar(250) NOT NULL DEFAULT '',
  `attachpath` varchar(250) NOT NULL DEFAULT '',
  `attachhref` varchar(250) NOT NULL DEFAULT '',
  `attachpathfaq` varchar(250) NOT NULL DEFAULT '',
  `attachhreffaq` varchar(250) NOT NULL DEFAULT '',
  `language` varchar(250) NOT NULL DEFAULT 'english',
  `langSets` text default null,
  `dateformat` varchar(20) NOT NULL DEFAULT 'D j M Y, G:ia',
  `timeformat` varchar(15) NOT NULL DEFAULT 'H:iA',
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/London',
  `weekStart` enum('mon','sun') NOT NULL DEFAULT 'sun',
  `jsDateFormat` varchar(15) NOT NULL DEFAULT 'DD/MM/YYYY',
  `kbase` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableVotes` enum('yes','no') NOT NULL DEFAULT 'yes',
  `multiplevotes` enum('yes','no') NOT NULL DEFAULT 'no',
  `popquestions` int(5) NOT NULL DEFAULT '0',
  `quePerPage` int(3) NOT NULL DEFAULT '10',
  `cookiedays` int(5) NOT NULL DEFAULT '0',
  `renamefaq` enum('yes','no') NOT NULL DEFAULT 'no',
  `attachment` enum('yes','no') NOT NULL DEFAULT 'no',
  `rename` enum('yes','no') NOT NULL DEFAULT 'no',
  `attachboxes` int(3) NOT NULL DEFAULT '2',
  `filetypes` text default null,
  `maxsize` int(15) NOT NULL DEFAULT '200',
  `enableBBCode` enum('yes','no') NOT NULL DEFAULT 'yes',
  `afolder` varchar(50) NOT NULL DEFAULT '',
  `autoClose` int(5) NOT NULL DEFAULT '0',
  `autoCloseMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `smtp_host` varchar(100) NOT NULL DEFAULT 'localhost',
  `smtp_user` varchar(100) NOT NULL DEFAULT '',
  `smtp_pass` varchar(100) NOT NULL DEFAULT '',
  `smtp_port` int(4) NOT NULL DEFAULT '25',
  `smtp_security` varchar(10) NOT NULL DEFAULT '',
  `smtp_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `smtp_html` enum('yes','no') NOT NULL DEFAULT 'yes',
  `prodKey` char(60) NOT NULL DEFAULT '',
  `publicFooter` text default null,
  `adminFooter` text default null,
  `encoderVersion` varchar(5) NOT NULL DEFAULT '',
  `softwareVersion` varchar(10) NOT NULL DEFAULT '',
  `apiKey` varchar(100) NOT NULL DEFAULT '',
  `apiLog` enum('yes','no') NOT NULL DEFAULT 'no',
  `apiHandlers` varchar(100) NOT NULL DEFAULT '',
  `sysstatus` enum('yes','no') NOT NULL DEFAULT 'yes',
  `autoenable` date NOT NULL DEFAULT '0000-00-00',
  `disputes` enum('yes','no') NOT NULL DEFAULT 'no',
  `offlineReason` text default null,
  `createPref` enum('yes','no') NOT NULL DEFAULT 'yes',
  `createAcc` enum('yes','no') NOT NULL DEFAULT 'yes',
  `loginLimit` int(5) NOT NULL DEFAULT '0',
  `banTime` int(5) NOT NULL DEFAULT '0',
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `backupEmails` text default null,
  `closenotify` enum('yes','no') NOT NULL DEFAULT 'no',
  `minPassValue` int(3) NOT NULL DEFAULT '8',
  `accProfNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `newAccNotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `defKeepLogs` varchar(100) NOT NULL DEFAULT '',
  `minTickDigits` int(2) NOT NULL DEFAULT '5',
  `enableMail` enum('yes','no') NOT NULL DEFAULT 'yes',
  `imap_debug` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_param` varchar(10) NOT NULL DEFAULT '',
  `imap_memory` varchar(3) NOT NULL DEFAULT '10',
  `imap_timeout` varchar(3) NOT NULL DEFAULT '120',
  `imap_attach` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_notify` enum('yes','no') NOT NULL DEFAULT 'no',
  `disputeAdminStop` enum('yes','no') NOT NULL DEFAULT 'no',
  `faqcounts` enum('yes','no') NOT NULL DEFAULT 'no',
  `closeadmin` enum('yes','no') NOT NULL DEFAULT 'no',
  `adminlock` enum('yes','no') NOT NULL DEFAULT 'no',
  `locktime` int(7) NOT NULL DEFAULT '0',
  `imap_clean` enum('yes','no') NOT NULL DEFAULT 'yes',
  `tawk` text default null,
  `tawk_home` enum('yes','no') NOT NULL DEFAULT 'no',
  `defdept` int(5) NOT NULL DEFAULT '0',
  `defprty` varchar(50) NOT NULL DEFAULT '',
  `rantick` enum('yes','no') NOT NULL DEFAULT 'no',
  `imap_open` enum('yes','no') NOT NULL DEFAULT 'no',
  `autospam` int(5) NOT NULL DEFAULT '0',
  `wordwrap` varchar(200) NOT NULL DEFAULT '',
  `timetrack` enum('yes','no') NOT NULL DEFAULT 'yes',
  `selfsign` enum('yes','no') NOT NULL DEFAULT 'no',
  `openlimit` enum('yes','no') NOT NULL DEFAULT 'no',
  `mail` enum('smtp','mail') NOT NULL DEFAULT 'smtp',
  `accautodel` int(5) NOT NULL DEFAULT '0',
  `visclose` enum('yes','no') NOT NULL DEFAULT 'no',
  `imapspamcloseacc` enum('yes','no') NOT NULL DEFAULT 'no',
  `navmenu` text default null,
  `faqHistory` enum('yes','no') NOT NULL DEFAULT 'no',
  `spam_score_header` varchar(100) NOT NULL DEFAULT '',
  `spam_score_value` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_settings: 1 rows
INSERT IGNORE INTO `msp_settings` (`id`, `website`, `email`, `replyto`, `scriptpath`, `attachpath`, `attachhref`, `attachpathfaq`, `attachhreffaq`, `language`, `langSets`, `dateformat`, `timeformat`, `timezone`, `weekStart`, `jsDateFormat`, `kbase`, `enableVotes`, `multiplevotes`, `popquestions`, `quePerPage`, `cookiedays`, `renamefaq`, `attachment`, `rename`, `attachboxes`, `filetypes`, `maxsize`, `enableBBCode`, `afolder`, `autoClose`, `autoCloseMail`, `smtp_host`, `smtp_user`, `smtp_pass`, `smtp_port`, `smtp_security`, `smtp_debug`, `smtp_html`, `prodKey`, `publicFooter`, `adminFooter`, `encoderVersion`, `softwareVersion`, `apiKey`, `apiLog`, `apiHandlers`, `sysstatus`, `autoenable`, `disputes`, `offlineReason`, `createPref`, `createAcc`, `loginLimit`, `banTime`, `ticketHistory`, `backupEmails`, `closenotify`, `minPassValue`, `accProfNotify`, `newAccNotify`, `enableLog`, `defKeepLogs`, `minTickDigits`, `enableMail`, `imap_debug`, `imap_param`, `imap_memory`, `imap_timeout`, `imap_attach`, `imap_notify`, `disputeAdminStop`, `faqcounts`, `closeadmin`, `adminlock`, `locktime`, `imap_clean`, `tawk`, `tawk_home`, `defdept`, `defprty`, `rantick`, `imap_open`, `autospam`, `wordwrap`, `timetrack`, `selfsign`, `openlimit`, `mail`, `accautodel`, `visclose`, `imapspamcloseacc`, `navmenu`, `faqHistory`, `spam_score_header`, `spam_score_value`) VALUES
	(1, '', 'admin@example.com', '', '', '', '', '', '', 'english', 'a:1:{s:7:"english";s:12:"_default_set";}', 'd M Y', 'H:iA', 'Europe/London', 'sun', 'DD-MM-YYYY', 'yes', 'yes', 'yes', 10, 10, 360, 'no', 'yes', 'yes', 5, '.jpg|.zip|.gif|.rar|.png|.pdf', 1048576, 'yes', 'admin', 0, 'yes', '', '', '', 587, '', 'no', 'yes', '', '', '', 'msw', '4.3', '9FB106E878-77HGF44EDAY', 'yes', 'json,xml', 'yes', '1970-01-01', 'no', '', 'no', 'yes', 5, 5, 'yes', '', 'no', 8, 'yes', 'yes', 'yes', 'a:2:{s:4:"user";s:2:"50";s:3:"acc";s:2:"50";}', 5, 'yes', 'yes', 'pipe', '0', '0', 'no', 'no', 'no', 'no', 'no', 'no', 0, 'yes', NULL, 'no', 0, '', 'no', 'no', 0, '', 'no', 'no', 'no', 'smtp', 0, 'no', 'no', NULL, 'no', '', '0');

-- Dumping structure for table msp_social
DROP TABLE IF EXISTS `msp_social`;
CREATE TABLE IF NOT EXISTS `msp_social` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `desc` varchar(50) NOT NULL DEFAULT '',
  `param` text default null,
  `value` text default null,
  PRIMARY KEY (`id`),
  KEY `descK` (`desc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_statuses
DROP TABLE IF EXISTS `msp_statuses`;
CREATE TABLE IF NOT EXISTS `msp_statuses` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `perms` enum('yes','no') NOT NULL DEFAULT 'no',
  `marker` varchar(100) NOT NULL DEFAULT '',
  `orderBy` int(5) NOT NULL DEFAULT '0',
  `colors` varchar(200) NOT NULL DEFAULT '',
  `visitor` enum('yes','no') NOT NULL DEFAULT 'yes',
  `autoclose` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_statuses: 3 rows
INSERT INTO `msp_statuses` (`id`, `name`, `perms`, `marker`, `orderBy`, `colors`, `visitor`, `autoclose`) VALUES
	(1, 'Open', 'yes', 'open', 2, '', 'yes', 'no');
INSERT INTO `msp_statuses` (`id`, `name`, `perms`, `marker`, `orderBy`, `colors`, `visitor`, `autoclose`) VALUES
	(2, 'Closed', 'yes', 'close', 1, '', 'yes', 'no');
INSERT INTO `msp_statuses` (`id`, `name`, `perms`, `marker`, `orderBy`, `colors`, `visitor`, `autoclose`) VALUES
	(3, 'Locked', 'yes', 'closed', 3, '', 'yes', 'no');

-- Dumping structure for table msp_ticketfields
DROP TABLE IF EXISTS `msp_ticketfields`;
CREATE TABLE IF NOT EXISTS `msp_ticketfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticketID` varchar(20) NOT NULL DEFAULT '',
  `fieldID` int(15) NOT NULL DEFAULT '0',
  `replyID` int(15) NOT NULL DEFAULT '0',
  `fieldData` text default null,
  PRIMARY KEY (`id`),
  KEY `tickid_index` (`ticketID`),
  KEY `fldid_index` (`fieldID`),
  KEY `repid_index` (`replyID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_tickethistory
DROP TABLE IF EXISTS `msp_tickethistory`;
CREATE TABLE IF NOT EXISTS `msp_tickethistory` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `ticketID` int(11) NOT NULL DEFAULT '0',
  `action` text default null,
  `ip` varchar(250) NOT NULL DEFAULT '',
  `staff` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_index` (`ticketID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_tickets
DROP TABLE IF EXISTS `msp_tickets`;
CREATE TABLE IF NOT EXISTS `msp_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tickno` varchar(250) NOT NULL DEFAULT '',
  `ts` int(30) NOT NULL DEFAULT '0',
  `lastrevision` int(30) NOT NULL DEFAULT '0',
  `department` int(8) NOT NULL DEFAULT '0',
  `assignedto` varchar(250) NOT NULL DEFAULT '',
  `visitorID` int(8) NOT NULL DEFAULT '0',
  `subject` varchar(250) NOT NULL DEFAULT '',
  `mailBodyFilter` varchar(30) NOT NULL DEFAULT '',
  `comments` text default null,
  `priority` varchar(250) NOT NULL DEFAULT '',
  `ticketStatus` varchar(20) NOT NULL DEFAULT '',
  `ipAddresses` varchar(250) NOT NULL DEFAULT '',
  `ticketNotes` text default null,
  `isDisputed` enum('yes','no') NOT NULL DEFAULT 'no',
  `disPostPriv` enum('yes','no') NOT NULL DEFAULT 'yes',
  `source` varchar(10) NOT NULL DEFAULT 'standard',
  `spamFlag` enum('yes','no') NOT NULL DEFAULT 'no',
  `lockteam` int(7) NOT NULL DEFAULT '0',
  `lockrelease` int(30) NOT NULL DEFAULT '0',
  `worktime` varchar(50) NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (`id`),
  KEY `depid_index` (`department`),
  KEY `pry_index` (`priority`),
  KEY `isdis_index` (`isDisputed`),
  KEY `ts_index` (`ts`),
  KEY `vis_index` (`visitorID`),
  KEY `lockteam` (`lockteam`),
  KEY `ticketStatus` (`ticketStatus`),
  KEY `tickno` (`tickno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_userdepts
DROP TABLE IF EXISTS `msp_userdepts`;
CREATE TABLE IF NOT EXISTS `msp_userdepts` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL DEFAULT '0',
  `deptID` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid_index` (`userID`),
  KEY `depid_index` (`deptID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table msp_users
DROP TABLE IF EXISTS `msp_users`;
CREATE TABLE IF NOT EXISTS `msp_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `ts` int(30) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(250) NOT NULL DEFAULT '',
  `email2` text default null,
  `accpass` varchar(250) NOT NULL DEFAULT '',
  `signature` text default null,
  `notify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `pageAccess` text default null,
  `emailSigs` enum('yes','no') NOT NULL DEFAULT 'no',
  `notePadEnable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `delPriv` enum('yes','no') NOT NULL DEFAULT 'no',
  `nameFrom` varchar(250) NOT NULL DEFAULT '',
  `emailFrom` varchar(250) NOT NULL DEFAULT '',
  `assigned` enum('yes','no') NOT NULL DEFAULT 'no',
  `timezone` varchar(50) NOT NULL DEFAULT '0',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `notes` text default null,
  `ticketHistory` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enableLog` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailbox` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailFolders` int(3) NOT NULL DEFAULT '5',
  `mailDeletion` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailScreen` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailCopy` enum('yes','no') NOT NULL DEFAULT 'yes',
  `mailPurge` int(3) NOT NULL DEFAULT '0',
  `addpages` text default null,
  `mergeperms` enum('yes','no') NOT NULL DEFAULT 'yes',
  `digest` enum('yes','no') NOT NULL DEFAULT 'yes',
  `profile` enum('yes','no') NOT NULL DEFAULT 'yes',
  `helplink` enum('yes','no') NOT NULL DEFAULT 'no',
  `defDays` int(3) NOT NULL DEFAULT '45',
  `editperms` text default null,
  `lock` enum('yes','no') NOT NULL DEFAULT 'yes',
  `close` enum('yes','no') NOT NULL DEFAULT 'yes',
  `admin` enum('yes','no') NOT NULL DEFAULT 'no',
  `timer` enum('yes','no') NOT NULL DEFAULT 'yes',
  `startwork` enum('yes','no') NOT NULL DEFAULT 'yes',
  `workedit` enum('yes','no') NOT NULL DEFAULT 'yes',
  `language` varchar(250) NOT NULL DEFAULT 'english',
  `spamnotify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `savedstaff` text default null,
  `staffupnotify` enum('yes','no') NOT NULL DEFAULT 'no',
  `faqHistory` enum('yes','no') NOT NULL DEFAULT 'no',
  `digestops` text default null,
  `digestdays` text default null,
  PRIMARY KEY (`id`),
  KEY `email_index` (`email`),
  KEY `nty_index` (`notify`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Dumping data for table msp_users: 1 rows
INSERT INTO `msp_users` (`id`, `ts`, `name`, `email`, `email2`, `accpass`, `signature`, `notify`, `pageAccess`, `emailSigs`, `notePadEnable`, `delPriv`, `nameFrom`, `emailFrom`, `assigned`, `timezone`, `enabled`, `notes`, `ticketHistory`, `enableLog`, `mailbox`, `mailFolders`, `mailDeletion`, `mailScreen`, `mailCopy`, `mailPurge`, `addpages`, `mergeperms`, `digest`, `profile`, `helplink`, `defDays`, `editperms`, `lock`, `close`, `admin`, `timer`, `startwork`, `workedit`, `language`, `spamnotify`, `savedstaff`, `staffupnotify`, `faqHistory`, `digestops`, `digestdays`) VALUES
	(1, UNIX_TIMESTAMP(), 'admin', 'admin@example.com', NULL, '', '', 'yes', '', 'no', 'yes', 'yes', 'admin', 'admin@example.com', 'no', 'Europe/London', 'yes', NULL, 'yes', 'yes', 'yes', 5, 'yes', 'yes', 'yes', 0, NULL, 'yes', 'yes', 'yes', 'yes', 45, NULL, 'yes', 'yes', 'yes', 'no', 'yes', 'yes', 'english', 'yes', NULL, 'no', 'no', NULL, NULL);

-- Dumping structure for table msp_usersaccess
DROP TABLE IF EXISTS `msp_usersaccess`;
CREATE TABLE IF NOT EXISTS `msp_usersaccess` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL DEFAULT '',
  `userID` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_index` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
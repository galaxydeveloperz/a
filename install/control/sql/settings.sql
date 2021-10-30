INSERT IGNORE INTO `{prefix}settings` (
`id`, `website`, `email`, `replyto`, `scriptpath`, `attachpath`, `attachhref`, `attachpathfaq`, `attachhreffaq`,
`language`, `langSets`, `dateformat`, `timeformat`, `timezone`, `weekStart`, `jsDateFormat`, `kbase`, `enableVotes`,
`multiplevotes`, `popquestions`, `quePerPage`, `cookiedays`, `renamefaq`, `attachment`, `rename`, `attachboxes`,
`filetypes`, `maxsize`, `enableBBCode`, `afolder`, `autoClose`, `autoCloseMail`, `smtp_host`, `smtp_user`, `smtp_pass`,
`smtp_port`, `smtp_security`, `smtp_debug`, `prodKey`, `publicFooter`, `adminFooter`, `encoderVersion`, `softwareVersion`,
`apiKey`, `apiLog`, `apiHandlers`, `sysstatus`, `autoenable`,
`disputes`, `offlineReason`, `createPref`, `createAcc`, `loginLimit`, `banTime`, `ticketHistory`, `backupEmails`,
`closenotify`, `minPassValue`, `accProfNotify`, `newAccNotify`, `enableLog`,
`defKeepLogs`, `minTickDigits`, `enableMail`, `imap_debug`, `imap_param`, `imap_memory`, `imap_timeout`,
`disputeAdminStop`, `timetrack`
) VALUES (
1, '{name}', '{email}', '', '{path}', '{attpath-server}', '{attpath}', '{attfaqpath-server}', '{attfaqpath}',
'english', '{langsets}', 'd M Y', 'H:iA', '{zone}', 'sun', 'DD-MM-YYYY', 'yes',
'yes', 'yes', 10, 10, 360, 'no', 'yes', 'yes', 5, '.jpg|.zip|.gif|.rar|.png|.pdf', 1048576, 'yes',
'admin', 0, 'yes', '', '', '', 587, '', 'no', '{key}', '', '', 'msw',
'{version}', '{apikey}', 'yes', 'json,xml', 'yes', '1970-01-01', 'no', '', 'no', 'yes', 5, 5, 'yes', '', 'no', 8,
'yes', 'yes', 'yes', '{deflogs}', 5, 'yes', 'yes', 'pipe', '0', '0', 'no', 'no')
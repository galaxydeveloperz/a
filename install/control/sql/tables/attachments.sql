create table `{prefix}attachments` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `ticketID` varchar(20) not null default '',
  `replyID` int(11) not null default '0',
  `department` int(5) not null default '0',
  `fileName` varchar(250) not null default '',
  `fileSize` varchar(20) not null default '',
  `mimeType` varchar(100) not null default '',
  primary key (`id`),
  key `tickid_index` (`ticketID`),
  key `repid_index` (`replyID`)
) {engine}
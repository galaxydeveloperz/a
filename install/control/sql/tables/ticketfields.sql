create table `{prefix}ticketfields` (
  `id` int(10) unsigned not null auto_increment,
  `ticketID` varchar(20) not null default '',
  `fieldID` int(15) not null default '0',
  `replyID` int(15) not null default '0',
  `fieldData` text default null,
  primary key (`id`),
  key `tickid_index` (`ticketID`),
  key `fldid_index` (`fieldID`),
  key `repid_index` (`replyID`)
) {engine}
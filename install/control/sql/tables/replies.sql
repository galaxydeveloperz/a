create table `{prefix}replies` (
  `id` int(10) unsigned not null auto_increment,
  `ts` int(30) not null default '0',
  `ticketID` int(15) not null default '0',
  `comments` text default null,
  `mailBodyFilter` varchar(30) not null default '',
  `replyType` enum('none','visitor','admin') not null default 'none',
  `replyUser` int(8) not null default '0',
  `isMerged` enum('yes','no') not null default 'no',
  `ipAddresses` text default null,
  `disputeUser` int(6) not null default '0',
  primary key (`id`),
  key `tickid_index` (`ticketID`),
  key `repuse_index` (`replyUser`),
  key `disuse_index` (`disputeUser`)
) {engine}
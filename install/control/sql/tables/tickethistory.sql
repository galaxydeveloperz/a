create table `{prefix}tickethistory` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `ticketID` int(11) not null default '0',
  `action` text default null,
  `ip` varchar(250) not null default '',
  `staff` int(5) not null default '0',
  primary key (`id`),
  key `ticket_index` (`ticketID`)
) {engine}
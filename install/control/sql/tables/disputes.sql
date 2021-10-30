create table `{prefix}disputes` (
  `id` int(10) unsigned not null auto_increment,
  `ticketID` int(15) not null default '0',
  `visitorID` int(8) not null default '0',
  `postPrivileges` enum('yes','no') not null default 'yes',
  primary key (`id`),
  key `tickid_index` (`ticketID`),
  key `vis_index` (`visitorID`)
) {engine}
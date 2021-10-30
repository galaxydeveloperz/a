create table `{prefix}mailbox` (
  `id` int(10) unsigned not null auto_increment,
  `ts` int(30) not null default '0',
  `staffID` int(8) not null default '0',
  `subject` varchar(250) not null default '',
  `message` text default null,
  primary key (`id`),
  key `staff_index` (`staffID`)
) {engine}
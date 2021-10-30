create table `{prefix}mailreplies` (
  `id` int(10) unsigned not null auto_increment,
  `ts` int(30) not null default '0',
  `mailID` int(8) not null default '0',
  `staffID` int(8) not null default '0',
  `message` text default null,
  primary key (`id`),
  key `mail_index` (`mailID`),
  key `staff_index` (`staffID`)
) {engine}
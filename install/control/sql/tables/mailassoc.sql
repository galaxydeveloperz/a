create table `{prefix}mailassoc` (
  `id` int(10) unsigned not null auto_increment,
  `staffID` int(8) not null default '0',
  `mailID` int(8) not null default '0',
  `folder` varchar(10) not null default 'inbox',
  `status` enum('read','unread') not null default 'unread',
  `lastUpdate` int(30) not null default '0',
  primary key (`id`),
  key `staff_index` (`staffID`),
  key `mail_index` (`mailID`),
  key `status_index` (`status`)
) {engine}
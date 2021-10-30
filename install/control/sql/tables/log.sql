create table `{prefix}log` (
  `id` int(10) unsigned not null auto_increment,
  `ts` int(30) not null default '0',
  `userID` int(5) not null default '0',
  `ip` varchar(250) not null default '',
  `type` enum('user','acc') not null default 'user',
  primary key (`id`),
  key `useid_index` (`userID`)
) {engine}
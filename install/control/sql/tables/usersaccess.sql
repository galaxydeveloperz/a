create table `{prefix}usersaccess` (
  `id` int(5) not null auto_increment,
  `page` varchar(100) not null default '',
  `userID` varchar(250) not null default '',
  `type` varchar(32) not null default '',
  primary key (`id`),
  key `user_index` (`userID`)
) {engine}
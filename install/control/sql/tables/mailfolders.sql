create table `{prefix}mailfolders` (
  `id` int(10) unsigned not null auto_increment,
  `staffID` int(8) not null default '0',
  `folder` varchar(50) not null default '',
  primary key (`id`),
  key `staff_index` (`staffID`)
) {engine}
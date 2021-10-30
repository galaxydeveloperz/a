create table `{prefix}userdepts` (
  `id` int(5) not null auto_increment,
  `userID` int(5) not null default '0',
  `deptID` int(5) not null default '0',
  primary key (`id`),
  key `userid_index` (`userID`),
  key `depid_index` (`deptID`)
) {engine}
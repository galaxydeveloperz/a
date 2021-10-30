create table `{prefix}social` (
  `id` int(5) not null auto_increment,
  `desc` varchar(50) not null default '',
  `param` text default null,
  `value` text default null,
  primary key (`id`),
  key `descK` (`desc`)
) {engine}
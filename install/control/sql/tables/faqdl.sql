create table `{prefix}faqdl` (
  `id` int(5) not null auto_increment,
  `question` int(7) not null default '0',
  `token` varchar(200) not null default '',
  primary key (`id`)
) {engine}
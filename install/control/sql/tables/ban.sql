create table `{prefix}ban` (
  `id` int(5) not null auto_increment,
  `type` varchar(100) not null default '',
  `ip` varchar(250) not null default '',
  `count` int(5) not null default '0',
  `banstamp` varchar(250) not null default '',
  primary key (`id`)
) {engine}
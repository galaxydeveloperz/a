create table `{prefix}levels` (
  `id` int(5) not null auto_increment,
  `name` varchar(100) not null default '',
  `display` enum('yes','no') not null default 'no',
  `marker` varchar(100) not null default '',
  `orderBy` int(5) not null default '0',
  `colors` varchar(200) not null default '',
  primary key (`id`)
) {engine}
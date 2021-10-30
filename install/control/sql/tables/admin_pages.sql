create table `{prefix}admin_pages` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `title` text default null,
  `information` text default null,
  `enPage` enum('yes','no') not null default 'yes',
  `orderBy` int(8) not null default '0',
  `accounts` text default null,
  `tmp` varchar(250) not null default '',
  primary key (`id`)
) {engine}
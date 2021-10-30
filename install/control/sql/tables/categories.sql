create table `{prefix}categories` (
  `id` int(5) not null auto_increment,
  `name` varchar(100) not null default '',
  `summary` varchar(250) not null default '',
  `enCat` enum('yes','no') not null default 'yes',
  `orderBy` int(5) not null default '0',
  `subcat` int(5) not null default '0',
  `private` enum('yes','no') not null default 'no',
  `accounts` text default null,
  primary key (`id`)
) {engine}
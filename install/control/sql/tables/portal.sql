create table `{prefix}portal` (
  `id` int(5) not null auto_increment,
  `name` varchar(200) not null default '',
  `ts` int(30) not null default '0',
  `email` varchar(250) not null default '',
  `userPass` varchar(250) not null default '',
  `enabled` enum('yes','no') not null default 'yes',
  `verified` enum('yes','no') not null default 'no',
  `timezone` varchar(50) not null default '0',
  `ip` text default null,
  `notes` text default null,
  `reason` text default null,
  `system1` varchar(250) not null default '',
  `system2` varchar(250) not null default '',
  `language` varchar(100) not null default '',
  `enableLog` enum('yes','no') not null default 'yes',
  primary key (`id`),
  key `em_index` (`email`),
  key `nm_index` (`name`)
) {engine}
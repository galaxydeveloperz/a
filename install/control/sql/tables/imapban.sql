create table `{prefix}imapban` (
  `id` int(8) not null auto_increment,
  `filter` text default null,
  `account` enum('yes','no') not null default 'yes',
  `spam` enum('yes','no') not null default 'no',
  primary key (`id`),
  FULLTEXT key `filter` (`filter`)
) {engine}
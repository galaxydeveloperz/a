create table `{prefix}responses` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `title` text default null,
  `answer` text default null,
  `enResponse` enum('yes','no') not null default 'yes',
  `orderBy` int(8) not null default '0',
  `departments` text default null,
  primary key (`id`)
) {engine}
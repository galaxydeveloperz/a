create table `{prefix}faq` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `question` text default null,
  `answer` text default null,
  `kviews` int(10) not null default '0',
  `kuseful` int(10) not null default '0',
  `knotuseful` int(10) not null default '0',
  `enFaq` enum('yes','no') not null default 'yes',
  `featured` enum('yes','no') not null default 'no',
  `orderBy` int(5) not null default '0',
  `cat` int(7) not null default '0',
  `private` enum('yes','no') not null default 'no',
  `tmp` varchar(250) not null default '',
  `searchkeys` text default null,
  primary key (`id`)
) {engine}
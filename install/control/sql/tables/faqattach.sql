create table `{prefix}faqattach` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `name` varchar(250) not null default '',
  `remote` varchar(250) not null default '',
  `path` varchar(250) not null default '',
  `size` varchar(30) not null default '',
  `orderBy` int(8) not null default '0',
  `enAtt` enum('yes','no') not null default 'yes',
  `mimeType` varchar(100) not null default '',
  primary key (`id`)
) {engine}
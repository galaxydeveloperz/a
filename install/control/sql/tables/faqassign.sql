create table `{prefix}faqassign` (
  `id` int(5) not null auto_increment,
  `question` int(7) not null default '0',
  `itemID` int(7) not null default '0',
  `desc` varchar(20) not null default '',
  primary key (`id`),
  key `que_index` (`question`),
  key `att_index` (`itemID`)
) {engine}
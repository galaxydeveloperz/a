create table `{prefix}faqhistory` (
  `id` int(5) not null auto_increment,
  `ts` int(30) not null default '0',
  `faqID` int(11) not null default '0',
  `action` text default null,
  `ip` varchar(250) not null default '',
  primary key (`id`),
  key `faq_index` (`faqID`)
) {engine}
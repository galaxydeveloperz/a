create table `{prefix}departments` (
  `id` int(5) not null auto_increment,
  `name` varchar(100) not null default '',
  `showDept` enum('yes','no') not null default 'no',
  `dept_subject` text default null,
  `dept_comments` text default null,
  `orderBy` int(5) not null default '0',
  `manual_assign` enum('yes','no') not null default 'no',
  `days` text default null,
  `dept_priority` varchar(50) not null default '',
  `auto_admin` enum('yes','no') not null default 'yes',
  `auto_response` enum('yes','no') not null default 'no',
  `response` text default null,
  `response_sbj` text default null,
  primary key (`id`)
) {engine}
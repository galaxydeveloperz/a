create table `{prefix}cusfields` (
  `id` int(10) unsigned not null auto_increment,
  `fieldInstructions` varchar(250) not null default '',
  `fieldType` enum('textarea','input','select','checkbox','calendar') not null default 'input',
  `fieldReq` enum('yes','no') not null default 'no',
  `fieldOptions` text default null,
  `fieldLoc` varchar(25) not null default '',
  `orderBy` int(5) not null default '0',
  `repeatPref` enum('yes','no') not null default 'yes',
  `enField` enum('yes','no') not null default 'yes',
  `departments` text default null,
  `accounts` text default null,
  primary key (`id`)
) {engine}
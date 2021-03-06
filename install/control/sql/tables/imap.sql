create table `{prefix}imap` (
  `id` int(8) unsigned not null auto_increment,
  `im_piping` enum('yes','no') not null default 'no',
  `im_protocol` enum('pop3','imap') not null default 'pop3',
  `im_host` varchar(100) not null default '',
  `im_user` varchar(250) not null default '',
  `im_pass` varchar(100) not null default '',
  `im_port` int(5) not null default '110',
  `im_name` varchar(50) not null default '',
  `im_flags` varchar(250) not null default '',
  `im_attach` enum('yes','no') not null default 'no',
  `im_move` varchar(50) not null default '',
  `im_messages` int(3) not null default '20',
  `im_ssl` enum('yes','no') not null default 'no',
  `im_priority` varchar(250) not null default '',
  `im_status` varchar(100) not null default '',
  `im_dept` int(5) not null default '0',
  `im_email` varchar(250) not null default '',
  primary key (`id`)
) {engine}
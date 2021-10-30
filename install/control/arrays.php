<?php

/* MODULES
--------------------------------------------------*/

$modules = array(
  array(
    'MYSQLI',
    'mysqli_connect',
    'function',
    'https://php.net/manual/en/book.mysqli.php'
  ),
  array(
    'CURL',
    'curl_init',
    'function',
    'https://php.net/manual/en/book.curl.php'
  ),
  array(
    'IMAP',
    'imap_open',
    'function',
    'https://php.net/manual/en/book.imap.php'
  ),
  array(
    'JSON',
    'json_encode',
    'function',
    'https://php.net/manual/en/book.json.php'
  ),
  array(
    'SIMPLE XML',
    'simplexml_load_string',
    'function',
    'https://php.net/manual/en/book.simplexml.php'
  ),
  array(
    'PASSWORD HASH API',
    'password_hash',
    'function',
    'https://php.net/manual/en/book.password.php'
  )
);

switch(MSW_PHP) {
  case 'old':
    $modules[] = array(
      'MCRYPT',
      'mcrypt_decrypt',
      'function',
      'http://php.net/manual/en/book.mcrypt.php'
    );
    break;
  case 'new':
    $modules[] = array(
      'OPENSSL',
      'openssl_encrypt',
      'function',
      'http://php.net/manual/en/ref.openssl.php'
    );
    break;
}

/* PERMISSIONS
---------------------------------------------------*/

$permissions = array(
 'admin/export',
 'backups',
 'content/attachments',
 'content/attachments-faq',
 'logs'
);

/* CHARACTER SETS
---------------------------------------------------*/

$cSets = mswSQL_charsets();

/* UPGRADE OPS
---------------------------------------------------*/

$ops   = array();
$ops[] = 'Add New Database Tables';
$ops[] = 'Updating Imap Settings';
$ops[] = 'Updating Settings';
$ops[] = 'Updating Tickets and Replies';
$ops[] = 'Updating Accounts';
$ops[] = 'Updating Staff';
$ops[] = 'Updating F.A.Q';
$ops[] = 'Updating Departments, Categories and Standard Responses';
$ops[] = 'Updating Indexes';
$ops[] = 'Clean up and Finish';

?>

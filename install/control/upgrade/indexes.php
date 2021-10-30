<?php

/* Version Update
-------------------------------------------*/

mswUpLog('Beginning index checks. Drop unused indexes.', 'instruction');

if (mswCheckIndex('faq', 'question') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop index `question`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Index Drop');
  } else {
    mswUpLog('Index dropped from faq: question', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop index `question_2`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Index Drop');
  } else {
    mswUpLog('Index dropped from faq: question_2', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop index `answer`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Index Drop');
  } else {
    mswUpLog('Index dropped from faq: answer', 'instruction');
  }
}

if (mswCheckIndex('faq', 'catid_index') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop index `catid_index`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Index Drop');
  } else {
    mswUpLog('Index dropped from faq: catid_index', 'instruction');
  }
}

mswUpLog('Check all live indexes are set', 'instruction');

foreach(array(
  /* Attachments
  --------------------------------------------------------------*/
  '01_attachments' => array('ticketID','tickid_index'),
  '02_attachments' => array('replyID','repid_index'),
  
  /* Disputes
  --------------------------------------------------------------*/
  '01_disputes' => array('ticketID','tickid_index'),
  '02_disputes' => array('visitorID','vis_index'),
  
  /* FAQ
  --------------------------------------------------------------*/
  '01_faqassign' => array('question','que_index'),
  '02_faqassign' => array('itemID','att_index'),
  '03_faqhistory' => array('faqID','faq_index'),
  
  /* Imap
  --------------------------------------------------------------*/
  '01_imapban' => array('filter','filter','fulltext'),
  
  /* Log
  --------------------------------------------------------------*/
  '01_log' => array('userID','useid_index'),
  
  /* Mailbox
  --------------------------------------------------------------*/
  '01_mailassoc' => array('staffID','staff_index'),
  '02_mailassoc' => array('mailID','mail_index'),
  '03_mailassoc' => array('status','status_index'),
  '04_mailbox' => array('staffID','staff_index'),
  '05_mailfolders' => array('staffID','staff_index'),
  '06_mailreplies' => array('mailID','mail_index'),
  '07_mailreplies' => array('staffID','staff_index'),
  
  /* Accounts
  --------------------------------------------------------------*/
  '01_portal' => array('email','em_index'),
  '02_portal' => array('name','nm_index'),
  
  /* Replies
  --------------------------------------------------------------*/
  '01_replies' => array('ticketID','tickid_index'),
  '02_replies' => array('replyUser','repuse_index'),
  '03_replies' => array('disputeUser','disuse_index'),
  
  /* Social
  --------------------------------------------------------------*/
  '01_social' => array('desc','descK'),
  
  /* Tickets
  --------------------------------------------------------------*/
  '01_ticketfields' => array('ticketID','tickid_index'),
  '02_ticketfields' => array('fieldID','fldid_index'),
  '03_ticketfields' => array('replyID','repid_index'),
  '04_tickethistory' => array('ticketID','ticket_index'),
  '05_tickets' => array('department','depid_index'),
  '06_tickets' => array('priority','pry_index'),
  '07_tickets' => array('isDisputed','isdis_index'),
  '08_tickets' => array('ts','ts_index'),
  '09_tickets' => array('visitorID','vis_index'),
  '10_tickets' => array('lockteam','lockteam'),
  '11_tickets' => array('ticketStatus','ticketStatus'),
  '12_tickets' => array('tickno','tickno'),
  
  /* Users
  --------------------------------------------------------------*/
  '01_userdepts' => array('userID','userid_index'),
  '02_userdepts' => array('deptID','depid_index'),
  '03_users' => array('email','email_index'),
  '04_users' => array('notify','nty_index'),
  '05_usersaccess' => array('userID','user_index')
) AS $k => $v) {
  $table = substr($k, 3);
  if (!isset($v[2])) {
    $v[2] = 'def';
  }
  if (mswCheckIndex($table, $v[1]) == 'no') {
    $query = mswSQL_query("alter table `" . DB_PREFIX . $table . "` add " . ($v[2] == 'fulltext' ? 'fulltext' : 'index') . " `" . $v[1] . "` (`" . $v[0] . "`)");
    if ($query === 'err') {
      $ERR  = mswSQL_error(true);
      mswUpLog(DB_PREFIX . $table, $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add ' . ($v[2] == 'fulltext' ? 'Fulltext Index' : 'Index'));
    } else {
      mswUpLog(($v[2] == 'fulltext' ? 'Fulltext Index' : 'Index') . ' added to ' . $table . ': ' . $v[1], 'instruction');
    }
  } else {
    mswUpLog(($v[2] == 'fulltext' ? 'Fulltext Index' : 'Index') . ' ' . $v[1] . ' exists in ' . $table . ' and will be ignored', 'instruction');
  }
}

mswUpLog('Index updates completed', 'instruction');

?>
<?php

//============================
// TICKET ORDER BY OPTIONS
//============================

if (!defined('PARENT')) { exit; }

// Set the default..
// Must be a key loaded below ($msg_script44)..
if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = ORDER_TICKET_SCREENS;
}

$orderBy = '';

if (isset($_GET['orderby']) && in_array($_GET['orderby'],array_keys($msg_script44))) {
  switch ($_GET['orderby']) {
    // Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `ticketName`';
	    break;
	  // Name (descending)..
    case 'name_desc':
	    $orderBy = 'ORDER BY `ticketName` desc';
	    break;
	  // Subject (ascending)..
    case 'subject_asc':
	    $orderBy = 'ORDER BY `subject`';
	    break;
	  // Subject (descending)..
    case 'subject_desc':
	    $orderBy = 'ORDER BY `subject` desc';
	    break;
	  // TicketID (ascending)..
    case 'id_asc':
	    $orderBy = 'ORDER BY `ticketID`';
	    break;
	  // TicketID (descending)..
    case 'id_desc':
	    $orderBy = 'ORDER BY `ticketID` desc';
	    break;
	  // Priority (ascending)..
    case 'pr_asc':
	    $orderBy = 'ORDER BY `levelName`';
	    break;
	  // Priority (descending)..
    case 'pr_desc':
	    $orderBy = 'ORDER BY `levelName` desc';
	    break;
	  // Department (ascending)..
    case 'dept_asc':
	    $orderBy = 'ORDER BY `deptName`';
	    break;
	  // Department (descending)..
    case 'dept_desc':
	    $orderBy = 'ORDER BY `deptName` desc';
	    break;
    // Least replies (ascending)..
    case 'rep_asc':
	    $orderBy = 'ORDER BY `replyCount`';
	    break;
	  // Most replies (descending)..
    case 'rep_desc':
	    $orderBy = 'ORDER BY `replyCount` desc';
	    break;
	  // Date Updated (Newest)..
    case 'rev_desc':
	    $orderBy = 'ORDER BY `lastrevision`';
	    break;
	  // Date Updated (Oldest)..
    case 'rev_asc':
	    $orderBy = 'ORDER BY `lastrevision` desc';
	    break;
	  // Date Added (Newest)..
    case 'date_desc':
	    $orderBy = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts`';
	    break;
	  // Date Added (Oldest)..
    case 'date_asc':
	    $orderBy = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts` desc';
	    break;
  }
}

?>
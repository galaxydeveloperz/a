<?php

//------------------------------------------------------------------------------
// LANGUAGE FILE
// Edit with care. Make a backup first before making changes.
//
// [1] Apostrophes should be escaped. eg: it\'s christmas.
// [2] Take care when editing arrays as they are spread across multiple lines
//
// If you make a mistake and you see a parse error, revert to backup file
//------------------------------------------------------------------------------

$msadminlang4_3 = array(
  'Enable Notification for Spam Tickets',
  'Enabled for Admin Tickets',
  'When Moving Tickets to Spam, Also Disable Account',
  'Show All',
  'Nav Menu Options',
  'Hide',
  'Reset Menu',
  'Apply Menu Changes - Only Set if Checked',
  'Send Ticket Assign Notification to Visitors',
  'Enable F.A.Q History',
  'Default Status',
  'Include Options',
  'Restrict to Certain Days',
  '<a href="?p=pageman">{pages} Other Pages</a> &amp; <a href="?p=apages">{apages} Admin Pages</a>',
  'Nav Menu Reset',
  'There are no account restrictions set',
  'Protocol',
  '(IMAP ONLY) Spam Score Header Name (To honour server restriction. eg: X-Spam-Score)',
  '(IMAP ONLY) Spam Ticket Detected When Spam Score Header is Greater Than or Equal To (0 to disable)'
);

$mssuptickets4_3 = array(
  'Dispute Users Updated',
  'Staff Notifications',
  'Notify Support Team Members of this Update (Optional)',
  'Save Selections',
  'Can Send Update Notifications to Other Staff When Replying to Ticket',
  'Can View F.A.Q History',
  'Last draft saved<br>{date} @ {time}'
);

$mscsfields4_3 = array(
  'Calendar',
  'Accounts',
  'Search Accounts',
  'Accounts: <span class="highlight">{accounts}</span>'
);

$msadminpages4_3 = array(
  'Admin Pages',
  'Edit Page',
  'Support Team',
  'Restrict to Support Team - Type Keyword to Locate Account',
  'Team Restriction: {count}',
  'There are currently 0 pages to display.',
  'Page Added',
  'Page Updated'
);

$msticketstatuses4_3 = array(
  'Ticket Statuses',
  'Add Status',
  'Manage Statuses',
  'Update Status',
  'Status Info',
  'Status Display Name',
  'Status Added',
  'Status Updated',
  'Please enter ticket status name',
  'There are currently no ticket statuses',
  'Please enter valid status',
  '<a href="?p=statusman">{statuses} Ticket Statuses</a>',
  'Other Status',
  'Ticket Locked to Visitors',
  'Ticket Ignored by Auto Close Option'
);

$msdept4_3 = array(
  'Enable Custom Email Response for New Tickets',
  'Enter Custom Response Subject',
  'Enter Custom Response Message',
  'Name of visitor who opens ticket',
  'Email of visitor who opens ticket',
  'Subject of ticket',
  'Ticket Number/ID',
  'Department name',
  'Priority level of ticket',
  'Status of ticket',
  'Comments entered by visitor',
  'Attachment information (if applicable)',
  'Custom field information (if applicable)',
  'Database ID of visitor who opened ticket'
);

$msfaq4_3 = array(
  'History',
  'There is currently no history for this question',
  'Question added by {staff}',
  'Question updated by {staff}',
  'Question enabled by {staff}',
  'Question disabled by {staff}',
  'Question count reset by {staff}',
  'Date,Time,Action,IP'
);

$msmessageslang4_3 = array(
  'Ban Filters Updated'
);

$mspubliclang4_3 = array(
  'The status of this ticket does not permit further replies until reviewed by our staff. Please be patient.'
);

$msgloballang4_3 = array(
  'OK',
  'Cancel',
  'Are you sure?',
  'Manage',
  'Refresh (Edit)',
  'Refresh',
  'Other',
  'Changelog',
  'Cancel &amp; Reload',
  'Unknown',
  'seconds',
  'Powered by',
  'Passwords Updated'
);

$emailSubjects['admin-team-notification-update'] = '[#{ticket}] Update Notification: {staff} replied to ticket';

$msg_ticket_history['ticket-attachment-deletion'] = 'Attachment ID {aid} ({attachment}) deleted from ticket by {user}';
$msg_ticket_history['ticket-attachment-reply-deletion'] = 'Attachment ID {aid} ({attachment}) deleted from reply {reply} by {user}';
$msg_ticket_history['ticket-field-deletion'] = 'Custom field "{field}" deleted from ticket by {user}';
$msg_ticket_history['ticket-field-reply-deletion'] = 'Custom field "{field}" deleted from reply {reply} by {user}';
$msg_ticket_history['new-ticket-admin-closed'] = 'Ticket locked by {user}';
$msg_ticket_history['admin-custom-status-change'] = 'Status changed to "{status}" by {user}';

$msg_ticket_actioned['status-changed'] = 'Ticket status changed: {status}';

// DO NOT change keys
$msemail_digest = array(
  'tba' => 'Tickets To Be Assigned',
  'tfs' => 'Tickets Flagged as Spam and Moved to Spam Tickets',
  'ope' => 'New Open Tickets - No Replies',
  'ots' => 'Open Tickets - Waiting for Staff Reply',
  'otv' => 'Open Tickets - Waiting for Visitor Reply',
  'odp' => 'New Open Disputes - No Replies',
  'ods' => 'Open Disputes - Waiting for Staff Reply',
  'odv' => 'Open Disputes - Waiting for Visitor Reply'
);

$msg_edigest3 = '[#{ticket} / {priority} / {status}] {subject}';

?>
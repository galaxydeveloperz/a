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

$msg_cal_mobile = '["Ja","Fe","Ma","Ap","Ma","Ju","Ju","Au","Se","Oc","No","De"]';

$mspubliclang3_7 = array(
  'Account Menu',
  'Private F.A.Q Categories',
  'Private Pages',
  'Related Categories',
  'There are currently 0 related categories',
  'Search F.A.Q',
  'An error occurred downloading this file, please try again later',
  'Search Results'
);

$msadminlang3_7 = array(
  '<b>Send Email to Staff</b> - If checked, email notification is sent with new login details',
  'Print Ticket',
  'Add Entry',
  'Add History Entry',
  'System',
  '<span class="hidden-xs">Ticket </span>History (<span class="mswlastyear">{lastyear}</span> / <span class="mswthisyear">{year}</span>)',
  '<span class="hidden-xs">Responses </span>History (<span class="mswlastyear">{lastyear}</span> / <span class="mswthisyear">{year}</span>)',
  'Move to Spam',
  'Moved to Spam',
  'Operation completed',
  'Software Version',
  'Add Imap Account',
  'Search'
);

$msadminlang_tickets_3_7 = array(
  'Accept - Send No Emails',
  'Accept - Send Notification Emails',
  'Please Select',
  'Delete Ticket',
  'Move to Open Tickets',
  'TICKET LOCKED! The status of this ticket is readonly as it is currently being reviewed by <b>{staff}</b>',
  'Edit',
  'Notes',
  'Open',
  'Close',
  'Lock',
  'Print',
  '{count} spam tickets have been deleted',
  'Replies: <b>{count}</b>',
  'Current Work Time',
  'Start',
  'Stop',
  'Reset',
  'Total Work Time',
  'h,m,s', // abbreviations..hours, minutes, seconds
  'Pause',
  'Reply cannot be added.<br><br>This ticket is locked and currently being reviewed by <b>{name}</b>'
);

$msadminlang_user_3_7 = array(
  'Total Replies',
  'This Year',
  'This Month',
  'Last 3 Months',
  'Last 6 Months',
  'Date Added',
  'Last Reply',
  'Average Replies Per Month',
  'Average Replies Per Year',
  'Overall Response Rate',
  'Last {count} Replies',
  'Can Close Tickets',
  'Can Lock Tickets',
  'Administrator',
  'With Merge Privileges Enabled',
  'With Close Privileges Enabled',
  'With Lock Privileges Enabled',
  'Show Administrators Only',
  'With Mailbox Enabled',
  'CSV Report',
  'Name,Total Replies,This Year,This Month,Last 3 Months,Last 6 Months,Last Reply,Average Replies Per Month,Average Replies Per Year,Overall Response Rate (%)', // For csv, must be comma delimited
  'Export to CSV',
  'Email Settings',
  'For {name} ONLY',
  'Start Work Timer As Soon As Ticket is Viewed',
  'Can View Work Timer Controls',
  'Can Edit Ticket Work Time',
  'Language',
  'Default System Language'
);

$msadminlang_settings_3_7 = array(
  'Always Close Ticket On Admin Reply',
  'CleanTalk API &amp; Spam',
  'CleanTalk API Key',
  'Enable CleanTalk For Standard Tickets',
  'Enable CleanTalk Log',
  'Enable CleanTalk For Imap Tickets',
  'For Imap Tickets Disable if Account is Already Active',
  'If Spam Ticket Detected, Add to Spam Tickets',
  'Lock Open Tickets When Viewed by Support Team - Helps Prevent Simultaneous Replies',
  'Release Lock After XX Minutes',
  'Attempt to Clean Quoted Data From Email Message Body',
  'Tawk.to Live Support',
  'Enter Tawk.to API Code',
  'Enable on Helpdesk Homescreen Only',
  'Default Department (Optional)',
  'Random Ticket Numbers',
  'Tickets Cannot Be ReOpened by Email',
  'The following must be completed to prevent email delivery issues:<br><br>Other Options > SMTP > Email Notification "From" Address',
  'Auto Delete Spam Tickets When Ticket is XX Days Old',
  'Wordwrap Setting for Desktops / Laptops - Wrap Ticket Message Body at XX Chars',
  'Wordwrap Setting for Mobile Phones - Wrap Ticket Message Body at XX Chars',
  'Wordwrap Setting for Tablet Computers - Wrap Ticket Message Body at XX Chars',
  'Enable Work Time Tracking For Tickets',
  'SSL/TLS Only - Do Not Verify Certificates',
  'Prevent Tickets From Being Opened If At Least One Ticket Is Already Open',
  'Mail Protocol',
  'PHP Mail Function',
  'Mail Settings',
  'Auto Delete Unverified Accounts After XX Days',
  'Enable CleanTalk For Account Sign Up'
);

$msadminlang_imap_3_7 = array(
  'Ban Filters',
  'Update Filters',
  'Ban Filters (One Entry Per Line)',
  'Disable if Active Account Exists For Email Address',
  'Move to Spam Tickets Instead of Deleting Ticket',
  'Please enter Imap Host or IP address'
);

$msadminlang_accounts_3_7 = array(
  'Show Unverified Only',
  '<b>{count}</b> unverified accounts were auto deleted. They have reached their <b>{days}</b> day limit.'
);

$msadminlang_responses_3_7 = array(
  'Please enter title and answer'
);

$msadminlang_dashboard_3_7 = array(
  'Open Tickets - All Staff',
  'Open Tickets - Assigned to {name}',
  'Open Disputes - All Staff',
  'Open Disputes - Assigned to {name}',
  'Open Tickets - Assigned to Other Staff',
  'Open Disputes - Assigned to Other Staff',
  'System Overview',
  'Add Ticket',
  'There are currently no tickets',
  'Clear Staff Ticket Locks',
  'Locked Staff Tickets',
  'Unlock',
  'There are currently no staff locked tickets'
);

$msadminlang_faq_3_7 = array(
  'Private',
  'Restrict to Accounts',
  'Private categories are for parent categories only',
  'Private category restricted to one or more accounts',
  'Account Restrictions',
  'Question(s) Imported',
  'This category already exists, please try again',
  'This question already exists, please try again',
  'Please enter a question, answer and category',
  'Please enter category name'
);

$msadminlang_dept_3_7 = array(
  'Default Priority (Optional)',
  'Please enter department name'
);

$msadminlang_reports_3_7 = array(
  'Work Time'
);

$msadminlang_public_3_7 = array(
  'Your message has been flagged as possible spam and has been rejected by our system.',
  'Your application has been flagged as possible spam and has been rejected by our system.'
);

$msadminlang3_7createticket = array(
  'You appear to already have at least 1 open ticket in progress.<br><br>No further tickets can be opened until these have been processed.'
);

$msadminlang_pages_3_7 = array(
  'Please enter page title',
  'OR Load From Custom Template'
);

$msadminlang3_7fields = array(
  'Please enter custom field instruction and select box type'
);

$msadminlang3_7prlevels = array(
  'Options',
  'Admin Highlighter - Foreground Colour',
  'Admin Highlighter - Background Colour',
  'ID',
  'Please enter priority level name'
);

$emailSubjects['reopen-not-allowed'] = 'Ticket Reply Not Permitted, Please Read';
$emailSubjects['multiple-open-disallowed'] = 'Your Support Ticket Was Not Accepted';

$msg_ticket_history['ticket-status-spam'] = 'Ticket moved to spam tickets by {user}';
$msg_ticket_history['ticket-status-spam-open'] = 'Ticket moved from spam tickets to open tickets by {user}';
$msg_ticket_actioned['spam'] = 'Ticket Moved to Spam Tickets';
$msg_ticket_actioned['reopen'] = 'Ticket Opened';

$msg_adheader24 = 'Tickets by Email';

$imap_cron_output = array(
  'Mailbox Host:',
  'Email(s) Found.',
  'Waiting to be Assignment.',
  'Spam or Banned - Moved to Spam Tickets',
  'Spam or Banned - Auto Deleted.',
  'New Ticket(s) Opened.',
  'Replies Added.',
  'Attachment(s) Saved.',
  'Memory Used:',
  'Peak Memory Used:',
  'Duration:',
  'Mailbox:',
  'Reply Added'
);

$imap_cron_output_err = 'Cron failed, check your cronjob/crontab parameters.';
$imap_cron_output_err2 = 'Cron failed, language file not found: {file}';

?>
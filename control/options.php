<?php

/* ADDITIONAL OPTIONS
   These options are mainly admin related and are provided as an easy
   option to change additional settings in the system. Most are
   detailed on the Q&A page in the docs.

   IMPORTANT!! Edit values on right, DO NOT change values in capitals
   
   To prevent selections being overwritten in future versions
   see the Q&A in the docs and the following question:
   
   "How Can I Retain Options (control/options.php) in Future Updates?"
===========================================================================*/

/* ADMIN FOLDER NAME
   If you rename the admin folder, set the new name here
-------------------------------------------------------------------------*/
define('ADMIN_FLDR', 'admin');


/* DEFAULT DATA TO SHOW PER PAGE
-------------------------------------------------------------------------*/
define('DEFAULT_DATA_PER_PAGE', 25);


/* DEFAULT OFF CANVAS PANEL SECTION
   On load this panel will be expanded. Can be any of the following:

   tickets, staff, apages, accounts, dept, fields, stanresp,
   pages, levels, imap, faq, settings, status
-------------------------------------------------------------------------*/
define('DEF_OPEN_MENU_PANEL', 'tickets');


/* ENABLE ONE CLICK IMAGE VIEWER
   If enabled, shows View option for image ticket and
   faq attachments. This will reveal the full http path to the file.
   
   Admin ONLY
-------------------------------------------------------------------------*/
define('ONE_CLICK_IMG_VIEWER', 1);


/* RESTRICT SUBJECT TEXT
   Restricts subject text to certain amount of characters
   Stops display stretching etc
   
   0 = Disable
-------------------------------------------------------------------------*/
define('TICK_SUBJECT_TXT', 50);


/* LAST REPLIES LIMIT FOR STAFF
   On staff performance page
-------------------------------------------------------------------------*/
define('TEAM_USER_REPLIES', 20);


/* ON ADD TICKET SCREEN, EMAIL NOTIFICATION CHECKED
   
   1 = Yes, 0 = No
-------------------------------------------------------------------------*/
define('ADD_TICKET_MAIL_NOTIFY', 1);


/* SHOW CONFIRMATION DIALOG
   Hidden in previous versions.
   
   0 = Hide, 1 = Show
-------------------------------------------------------------------------*/
define('CONF_DIALOG', 1);


/* SHOW ALL TICKET ASSIGNMENT FOR OPEN TICKETS
   On admin homescreen, do you want administrators
   to see all current open ticket assignment.
   
   1 = Enabled, 0 = Disabled
-------------------------------------------------------------------------*/
define('SHOW_ALL_ASSIGNMENT', 1);


/* REDIRECT TO TICKET ON LOGIN
   If a support team member clicks on an email ticket link and is
   directed to the admin log in page, do you want them to be directed
   to the ticket after login? Can save time locating
   tickets and be a big time saver.
   
   1 = Enabled, 0 = Disabled
-------------------------------------------------------------------------*/
define('REDIRECT_TO_TICKET_ON_LOGIN', 1);


/* TICKET SEARCH AUTO CHECK OPTIONS
   Which ticket type checkboxes should be auto checked on search tickets page
-------------------------------------------------------------------------*/
define('SEARCH_AUTO_CHECK_TICKETS', 'yes');
define('SEARCH_AUTO_CHECK_DISPUTES', 'yes');
define('SEARCH_AUTO_CHECK_RESPONSES', 'no');


/* IBOX WINDOW SIZES
   Set sizes for ibox pop up windows (admin)
-------------------------------------------------------------------------*/
define('IBOX_NOTES_WIDTH', 800);
define('IBOX_NOTES_HEIGHT', 400);
define('IBOX_RESPONSE_WIDTH', 800);
define('IBOX_RESPONSE_HEIGHT', 500);
define('IBOX_FAQ_WIDTH', 900);
define('IBOX_FAQ_HEIGHT', 500);
define('IBOX_QVIEW_WIDTH', 800);
define('IBOX_QVIEW_HEIGHT', 500);
define('IBOX_PAGE_WIDTH', 800);
define('IBOX_PAGE_HEIGHT', 500);
define('IBOX_ASTFF_WIDTH', 500);
define('IBOX_ASTFF_HEIGHT', 400);
define('IBOX_DISUSRS_WIDTH', 500);
define('IBOX_DISUSRS_HEIGHT', 400);
define('IBOX_SYSOVV_WIDTH', 900);
define('IBOX_SYSOVV_HEIGHT', 500);
define('IBOX_FLTRS_WIDTH', 450);
define('IBOX_FLTRS_HEIGHT', 400);
define('IBOX_TAGS_WIDTH', 600);
define('IBOX_TAGS_HEIGHT', 450);
define('IBOX_FQACC_WIDTH', 600);
define('IBOX_FQACC_HEIGHT', 400);
define('IBOX_IMAP_WIDTH', 800);
define('IBOX_IMAP_HEIGHT', 500);
define('IBOX_SYSLOCKS_WIDTH', 650);
define('IBOX_SYSLOCKS_HEIGHT', 500);
define('IBOX_STATUSES_WIDTH', 450);
define('IBOX_STATUSES_HEIGHT', 300);

/* AUTO CREATE API KEY - KEY LENGTH
   Max 100 characters
-------------------------------------------------------------------------*/
define('API_KEY_LENGTH', 30);


/* ENABLE SOFTWARE VERSION CHECK
   Displays on the top bar and is an easy check option to see if new
   versions have been release. You may wish to disable this for clients.
   
   0 = Disabled, 1 = Enabled
-------------------------------------------------------------------------*/
define('DISPLAY_SOFTWARE_VERSION_CHECK', 1);


/* REPORTS
   Default previous range for initial reports screen. Supports strtotime
-------------------------------------------------------------------------*/
define('REP_DEF_RANGE_OLD', '-6 months');


/* SHOW ADMIN DASHBOARD GRAPH
   Do you want to show the admin dashboard graph?
   
   1 = Yes, 0 = No
-------------------------------------------------------------------------*/
define('SHOW_ADMIN_DASHBOARD_GRAPH', 1);


/* MAILBOX COUNT REFRESH TIME (in milliseconds)
   The amount of time the system checks for unread mailbox messages.
   
   Set to 0 to disable.
-------------------------------------------------------------------------*/
define('MAILBOX_UNREAD_REFRESH_TIME', 30000);


/* DEFAULT ORDER BY FOR TICKETS
   For homescreen, any valid database field name and asc or desc. Field/column between backticks.
   For ticket screens, any of the following:

   name_asc = name ascending
   name_desc = name descending
	 subject_asc =  subject ascending
	 subject_desc = subject descending
	 id_asc = id ascending
   id_desc = id descending
   pr_asc = priority ascending
   pr_desc = priority descending
   dept_asc = department ascending
   dept_desc = department descending
   rep_asc = least replies ascending
   rep_desc = most replies descending
   rev_desc = latest updated
   rev_asc = oldest updated
   date_desc = date newest
   date_asc = date oldest
-------------------------------------------------------------------------*/
define('ORDER_HOMESCREEN_TICKET', '`id` DESC');
define('ORDER_TICKET_SCREENS', 'id_desc');


/* REPLY TEXT CUT OFF LIMIT
   For user replies screen. Displays x chars for comments. 0 to disable
-------------------------------------------------------------------------*/
define('TEAM_REPLY_COMM_LIMIT', 0);


/* CSV UPLOAD PREFERENCES
   Set default options for file import CSVs
-------------------------------------------------------------------------*/
define('CSV_IMPORT_DELIMITER', ',');
define('CSV_IMPORT_ENCLOSURE', '"');
define('CSV_MAX_LINES_TO_READ', 999999);
// Set max to read at 5mb
define('CSV_COUNT_MAX_LINES_SIZE', (1024 * 1024 * 5));


/* CATEGORIES SUMMARY TEXT DISPLAY LIMIT
   Restrict display for category summary in admin
-------------------------------------------------------------------------*/
define('CATEGORIES_SUMMARY_TEXT_LIMIT', 115);


/* ADMIN MERGE REDIRECT TIME
   Time in seconds before screen redirects if tickets are merged
-------------------------------------------------------------------------*/
define('TICK_MERGE_RDR_TIME', 3);


/* IP LOOKUP
   Service for url lookup. Use {ip} where IP address must be in url
-------------------------------------------------------------------------*/
define('IP_LOOKUP', 'http://whatismyipaddress.com/ip/{ip}');


/* SET MEMORY ALLOCATION LIMIT FOR INTENSIVE OPS
   If you find that your system is timing out due to
   memory allocation, increase this limit. Applies to
   admin ops/cron jobs only.
   
   Set allocation to blank to totally disable
   Set time limit to 0 for no time out or value in
   seconds.
-------------------------------------------------------------------------*/
define('MS_SET_MEM_ALLOCATION_LIMIT', '300M');
define('MS_SET_TIME_OUT_LIMIT', 0);


/* SUB LINK SEPARATOR
   For ticket view. Spacer between custom field link
   and attachments link
-------------------------------------------------------------------------*/
define('SUBLINK_SEPARATOR', '&nbsp;&nbsp;');


/* DISPLAY LOGIN MESSAGE
   For visitor logins.
----------------------------------------------------*/
define('DISPLAY_LOGIN_MSG', 1);

/* SAVE DRAFTS
   When loading ticket reply area / add ticket
----------------------------------------------------*/
define('SAVE_DRAFTS', 1);
define('DRAFT_TIMEOUT', 400);
define('DRAFT_MSG_TIMEOUT', 3000);

/* TABLE HEAD DECORATION
   Shows two forward slashes before table header
   data. Change it if you don't need it or like it.
----------------------------------------------------*/
define('TABLE_HEAD_DECORATION', '<span class="slant">//</span> ');

?>
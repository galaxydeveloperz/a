<?php if (!defined('PARENT')) { exit; }
$homeViewCount = 0;

        $qTA = mswSQL_query("SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`,
               IF(`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'),
                 `" . DB_PREFIX . "levels`.`id`,
                 `" . DB_PREFIX . "levels`.`marker`
               ) AS `levelMarker`,
               (SELECT `name` FROM `" . DB_PREFIX . "users`
                WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
               ) AS `lockTeamName`,
               (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
               ) AS `replyCount`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority` = 
                 IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
                   `" . DB_PREFIX . "levels`.`id`,
                   `" . DB_PREFIX . "levels`.`marker`
                 )
               )
               WHERE `ticketStatus` NOT IN('close','closed')
	             AND `isDisputed`     = 'no'
               AND FIND_IN_SET(" . $MSTEAM->id . ", `assignedto`) > 0
	             AND `spamFlag`       = 'no'
               ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
               ", __file__, __line__);
        $TARows = mswSQL_numrows($qTA);
        if ($TARows > 0) {
        ++$homeViewCount;
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <span class="pull-right hidden-xs"><i class="fa fa-life-ring fa-fw"></i></span>
            <i class="fa fa-user fa-fw"></i> <?php echo str_replace('{name}', mswSH($MSTEAM->name), $msadminlang_dashboard_3_7[1]); ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              while ($TICKETS = mswSQL_fetchobj($qTA)) {
                $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                $showStatus = '';
                $tLock = 'no';
                switch ($TICKETS->ticketStatus) {
                  case 'open':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                    break;
                  case 'close':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                    break;
                  case 'closed':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                    break;
                  default:
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                    break;
                }
                if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                  $tLock = 'yes';
                }
                if ($howManyCustomStats > 0) {
                  if ($TICKETS->assignedto == 'waiting') {
                    $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                  }
                }
                if ($tkStatus) {
                  $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                }
                ?>
                <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                  <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                  <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                  </td>
                  <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                  <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30) . $showStatus; ?></span>
                  <?php
                  if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                  ?>
                  <span class="tdCellInfo">
                    <?php
                    echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                    ?>
                  </span>
                  <?php
                  }
                  ?>
                  </td>
                  <td><?php echo mswSH($TICKETS->ticketName); ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                  </td>
                  <td>
                  <?php
                  if (isset($last[0]) && $last[0]!='0') {
                  echo mswCD($last[0]);
                  ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                  <?php
                  } else {
                  echo '- - - -';
                  }
                  $noteIndi = '';
                  if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                    $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                  }
                  ?>
                  </td>
                  <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                </tr>
                <tr>
                 <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                 <?php
                 // Ticket Options..
                 include(PATH . 'templates/system/tickets/global/ticket-options.php');
                 ?>
                 </td>
                </tr>
                <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
        }

        // Show all assignments if administrator..
        if (SHOW_ALL_ASSIGNMENT && USER_ADMINISTRATOR == 'yes') {
          $qTA = mswSQL_query("SELECT *,
                 `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
                 `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
                 `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
                 `" . DB_PREFIX . "departments`.`name` AS `deptName`,
                 `" . DB_PREFIX . "levels`.`name` AS `levelName`,
                 IF(`" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`id`) AS `levelMarker`,
                 (SELECT `name` FROM `" . DB_PREFIX . "users`
                  WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
                 ) AS `lockTeamName`,
                 (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                  WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                 ) AS `replyCount`
                 FROM `" . DB_PREFIX . "tickets`
                 LEFT JOIN `" . DB_PREFIX . "departments`
                 ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
                 LEFT JOIN `" . DB_PREFIX . "portal`
                 ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
                 LEFT JOIN `" . DB_PREFIX . "levels`
                 ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
                  OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
                 WHERE `ticketStatus` NOT IN('close','closed')
	               AND `isDisputed`     = 'no'
                 AND FIND_IN_SET(" . $MSTEAM->id . ", `assignedto`) = 0
                 AND `assignedto` NOT IN('waiting', '')
                 AND `spamFlag`       = 'no'
                 ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
                 ", __file__, __line__);
          $TARows = mswSQL_numrows($qTA);
          if ($TARows > 0) {
          ++$homeViewCount;
          ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <span class="pull-right hidden-xs"><i class="fa fa-life-ring fa-fw"></i></span>
              <i class="fa fa-user fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[4]; ?>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                    <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                 </tr>
                </thead>
                <tbody>
                <?php
                while ($TICKETS = mswSQL_fetchobj($qTA)) {
                  $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                  $tkStatus = '';
                  $showStatus = '';
                  $tLock = 'no';
                  switch ($TICKETS->ticketStatus) {
                    case 'open':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                      break;
                    case 'close':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                      break;
                    case 'closed':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                      break;
                    default:
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                      break;
                  }
                  if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                    $tLock = 'yes';
                  }
                  if ($howManyCustomStats > 0) {
                    if ($TICKETS->assignedto == 'waiting') {
                      $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                    }
                  }
                  if ($tkStatus) {
                    $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                  }
                  ?>
                  <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                    <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                    <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                    </td>
                    <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                    <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30) . $showStatus; ?></span>
                    <?php
                    if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                    ?>
                    <span class="tdCellInfo">
                      <?php
                      echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                      ?>
                    </span>
                    <?php
                    }
                    ?>
                    </td>
                    <td><?php echo mswSH($TICKETS->ticketName); ?>
                    <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                    </td>
                    <td>
                    <?php
                    if (isset($last[0]) && $last[0]!='0') {
                    echo mswCD($last[0]);
                    ?>
                    <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                    <?php
                    } else {
                    echo '- - - -';
                    }
                    $noteIndi = '';
                    if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                      $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                    }
                    ?>
                    </td>
                    <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                  </tr>
                  <tr>
                   <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                   <?php
                   // Ticket Options..
                   include(PATH . 'templates/system/tickets/global/ticket-options.php');
                   ?>
                   </td>
                  </tr>
                  <?php
                }
                ?>
                </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php
        }
        }

        $qT1 = mswSQL_query("SELECT *,
               `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
	             `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	             `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	             `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	             `" . DB_PREFIX . "levels`.`name` AS `levelName`,
               IF(`" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`id`) AS `levelMarker`,
               (SELECT `name` FROM `" . DB_PREFIX . "users`
                WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
               ) AS `lockTeamName`,
               (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
               ) AS `replyCount`
	             FROM `" . DB_PREFIX . "tickets`
               LEFT JOIN `" . DB_PREFIX . "departments`
	             ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
	             LEFT JOIN `" . DB_PREFIX . "portal`
	             ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
	             LEFT JOIN `" . DB_PREFIX . "levels`
	             ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	              OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
               WHERE `ticketStatus` NOT IN('close','closed')
	             AND `isDisputed`     = 'no'
               AND `assignedto`     = ''
	             AND `spamFlag`       = 'no'
               " . (USER_ADMINISTRATOR == 'no' ? mswSQL_deptfilter($ticketFilterAccess) : '') . "
               ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
               ", __file__, __line__);
		    $T1Rows = mswSQL_numrows($qT1);
        if ($T1Rows > 0) {
        ++$homeViewCount;
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <span class="pull-right hidden-xs"><i class="fa fa-life-ring fa-fw"></i></span>
            <i class="fa fa-users fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[0]; ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              while ($TICKETS = mswSQL_fetchobj($qT1)) {
                $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                $tkStatus = '';
                $showStatus = '';
                $tLock = 'no';
                switch ($TICKETS->ticketStatus) {
                  case 'open':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                    break;
                  case 'close':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                    break;
                  case 'closed':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                    break;
                  default:
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                    break;
                }
                if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                  $tLock = 'yes';
                }
                if ($howManyCustomStats > 0) {
                  if ($TICKETS->assignedto == 'waiting') {
                    $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                  }
                }
                if ($tkStatus) {
                  $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                }
                ?>
                <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                  <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                  <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                  </td>
                  <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                  <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30) . $showStatus; ?></span>
                  <?php
                  if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                  ?>
                  <span class="tdCellInfo">
                    <?php
                    echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                    ?>
                  </span>
                  <?php
                  }
                  ?>
                  </td>
                  <td><?php echo mswSH($TICKETS->ticketName); ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                  </td>
                  <td>
                  <?php
                  if (isset($last[0]) && $last[0]!='0') {
                  echo mswCD($last[0]);
                  ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                  <?php
                  } else {
                  echo '- - - -';
                  }
                  $noteIndi = '';
                  if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                    $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                  }
                  ?>
                  </td>
                  <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                </tr>
                <tr>
                 <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                 <?php
                 // Ticket Options..
                 include(PATH . 'templates/system/tickets/global/ticket-options.php');
                 ?>
                 </td>
                </tr>
                <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
        }

        // Are disputes enabled?
        if ($SETTINGS->disputes == 'yes') {
          $qT2 = mswSQL_query("SELECT *,
                 `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
                 `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
                 `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
                 `" . DB_PREFIX . "departments`.`name` AS `deptName`,
                 `" . DB_PREFIX . "levels`.`name` AS `levelName`,
                 IF(`" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`id`) AS `levelMarker`,
                 (SELECT `name` FROM `" . DB_PREFIX . "users`
                  WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
                 ) AS `lockTeamName`,
                 (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                  WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                 ) AS `replyCount`
                 FROM `" . DB_PREFIX . "tickets`
                 LEFT JOIN `" . DB_PREFIX . "departments`
                 ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
                 LEFT JOIN `" . DB_PREFIX . "portal`
                 ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
                 LEFT JOIN `" . DB_PREFIX . "levels`
                 ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
                  OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
                 WHERE `ticketStatus` NOT IN('close','closed')
	               AND `isDisputed`     = 'yes'
                 AND FIND_IN_SET(" . $MSTEAM->id . ", `assignedto`) > 0
                 AND `spamFlag`       = 'no'
                 ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
                 ", __file__, __line__);
          $T2Rows = mswSQL_numrows($qT2);
          if ($T2Rows > 0) {
          ++$homeViewCount;
          ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <span class="pull-right hidden-xs"><i class="fa fa-bullhorn fa-fw"></i></span>
              <i class="fa fa-user fa-fw"></i> <?php echo str_replace('{name}', mswSH($MSTEAM->name), $msadminlang_dashboard_3_7[3]); ?>
            </div>
            <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              while ($TICKETS = mswSQL_fetchobj($qT2)) {
                $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                $tkStatus = '';
                $showStatus = '';
                $tLock = 'no';
                switch ($TICKETS->ticketStatus) {
                  case 'open':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                    break;
                  case 'close':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                    break;
                  case 'closed':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                    break;
                  default:
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                    break;
                }
                if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                  $tLock = 'yes';
                }
                if ($howManyCustomStats > 0) {
                  if ($TICKETS->assignedto == 'waiting') {
                    $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                  }
                }
                if ($tkStatus) {
                  $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                }
                ?>
                <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                  <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                  <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                  </td>
                  <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                  <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30); ?></span>
                  <?php
                  if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                  ?>
                  <span class="tdCellInfo">
                    <?php
                    echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                    ?>
                  </span>
                  <?php
                  }
                  ?>
                  </td>
                  <td><?php echo mswSH($TICKETS->ticketName); ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                  </td>
                  <td>
                  <?php
                  if (isset($last[0]) && $last[0]!='0') {
                  echo mswCD($last[0]);
                  ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                  <?php
                  } else {
                  echo '- - - -';
                  }
                  $noteIndi = '';
                  if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                    $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                  }
                  ?>
                  </td>
                  <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                </tr>
                <tr>
                 <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                 <?php
                 // Ticket Options..
                 include(PATH . 'templates/system/tickets/global/ticket-options.php');
                 ?>
                 </td>
                </tr>
                <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
          </div>
          <?php
          }

          // Show all assignments if administrator..
        if (SHOW_ALL_ASSIGNMENT && USER_ADMINISTRATOR == 'yes') {
          $qTA = mswSQL_query("SELECT *,
                 `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
                 `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
                 `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
                 `" . DB_PREFIX . "departments`.`name` AS `deptName`,
                 `" . DB_PREFIX . "levels`.`name` AS `levelName`,
                 IF(`" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`id`) AS `levelMarker`,
                 (SELECT `name` FROM `" . DB_PREFIX . "users`
                  WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
                 ) AS `lockTeamName`,
                 (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                  WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                 ) AS `replyCount`
                 FROM `" . DB_PREFIX . "tickets`
                 LEFT JOIN `" . DB_PREFIX . "departments`
                 ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
                 LEFT JOIN `" . DB_PREFIX . "portal`
                 ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
                 LEFT JOIN `" . DB_PREFIX . "levels`
                 ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
                  OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
                 WHERE `ticketStatus` NOT IN('close','closed')
	               AND `isDisputed`     = 'yes'
                 AND FIND_IN_SET(" . $MSTEAM->id . ", `assignedto`) = 0
                 AND `assignedto` NOT IN('waiting', '')
                 AND `spamFlag`       = 'no'
                 ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
                 ", __file__, __line__);
          $TARows = mswSQL_numrows($qTA);
          if ($TARows > 0) {
          ++$homeViewCount;
          ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <span class="pull-right hidden-xs"><i class="fa fa-bullhorn fa-fw"></i></span>
              <i class="fa fa-user fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[5]; ?>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                    <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                 </tr>
                </thead>
                <tbody>
                <?php
                while ($TICKETS = mswSQL_fetchobj($qTA)) {
                  $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                  $tkStatus = '';
                  $showStatus = '';
                  $tLock = 'no';
                  switch ($TICKETS->ticketStatus) {
                    case 'open':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                      break;
                    case 'close':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                      break;
                    case 'closed':
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                      break;
                    default:
                      $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                      break;
                  }
                  if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                    $tLock = 'yes';
                  }
                  if ($howManyCustomStats > 0) {
                    if ($TICKETS->assignedto == 'waiting') {
                      $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                    }
                  }
                  if ($tkStatus) {
                    $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                  }
                  ?>
                  <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                    <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                    <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                    </td>
                    <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                    <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30) . $showStatus; ?></span>
                    <?php
                    if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                    ?>
                    <span class="tdCellInfo">
                      <?php
                      echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                      ?>
                    </span>
                    <?php
                    }
                    ?>
                    </td>
                    <td><?php echo mswSH($TICKETS->ticketName); ?>
                    <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                    </td>
                    <td>
                    <?php
                    if (isset($last[0]) && $last[0]!='0') {
                    echo mswCD($last[0]);
                    ?>
                    <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                    <?php
                    } else {
                    echo '- - - -';
                    }
                    $noteIndi = '';
                    if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                      $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                    }
                    ?>
                    </td>
                    <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                  </tr>
                  <tr>
                   <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                   <?php
                   // Ticket Options..
                   include(PATH . 'templates/system/tickets/global/ticket-options.php');
                   ?>
                   </td>
                  </tr>
                  <?php
                }
                ?>
                </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php
          }
          }

          $qT3 = mswSQL_query("SELECT *,
                   `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
                   `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
                   `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
                   `" . DB_PREFIX . "departments`.`name` AS `deptName`,
                   `" . DB_PREFIX . "levels`.`name` AS `levelName`,
                   IF(`" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`marker`, `" . DB_PREFIX . "levels`.`id`) AS `levelMarker`,
                   (SELECT `name` FROM `" . DB_PREFIX . "users`
                    WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
                   ) AS `lockTeamName`,
                   (SELECT count(*) FROM `" . DB_PREFIX . "replies`
                    WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                   ) AS `replyCount`,
                   (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
                    WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                   ) AS `disputeCount`
                   FROM `" . DB_PREFIX . "tickets`
                   LEFT JOIN `" . DB_PREFIX . "departments`
                   ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
                   LEFT JOIN `" . DB_PREFIX . "portal`
                   ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
                   LEFT JOIN `" . DB_PREFIX . "levels`
                   ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
                    OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
                   WHERE `ticketStatus` NOT IN('close','closed')
	                 AND `isDisputed`     = 'yes'
                   AND `assignedto`     = ''
                   AND `spamFlag`       = 'no'
                   " . (USER_ADMINISTRATOR == 'no' ? mswSQL_deptfilter($ticketFilterAccess) : '') . "
                   ORDER BY `" . DB_PREFIX . "tickets`." . ORDER_HOMESCREEN_TICKET . "
                   ", __file__, __line__);
          $T3Rows = mswSQL_numrows($qT3);
          if ($T3Rows > 0) {
          ++$homeViewCount;
          ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <span class="pull-right hidden-xs"><i class="fa fa-bullhorn fa-fw"></i></span>
              <i class="fa fa-users fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[2]; ?>
            </div>
            <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              while ($TICKETS = mswSQL_fetchobj($qT3)) {
                $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
                $tkStatus = '';
                $showStatus = '';
                $tLock = 'no';
                switch ($TICKETS->ticketStatus) {
                  case 'open':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket14);
                    break;
                  case 'close':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket15);
                    break;
                  case 'closed':
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_viewticket16);
                    break;
                  default:
                    $tkStatus = (isset($ticketStatusSel[$TICKETS->ticketStatus][0]) ? $ticketStatusSel[$TICKETS->ticketStatus][0] : $msg_script17);
                    break;
                }
                if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                  $tLock = 'yes';
                }
                if ($howManyCustomStats > 0) {
                  if ($TICKETS->assignedto == 'waiting') {
                    $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
                  }
                }
                if ($tkStatus) {
                  $showStatus = ' | <span id="spanstatus_' . $TICKETS->ticketID . '">' . $tkStatus . '</span>';
                }
                ?>
                <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                  <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                  <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                  </td>
                  <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                  <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30) . $showStatus; ?></span>
                  <?php
                  if ($TICKETS->assignedto && $TICKETS->assignedto != 'waiting') {
                  ?>
                  <span class="tdCellInfo">
                    <?php
                    echo '<i class="fa fa-users"></i> ' . $MSTICKET->assignedTeam($TICKETS->assignedto);
                    ?>
                  </span>
                  <?php
                  }
                  ?>
                  </td>
                  <td><?php echo mswSH($TICKETS->ticketName); ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
                  </td>
                  <td>
                  <?php
                  if (isset($last[0]) && $last[0]!='0') {
                  echo mswCD($last[0]);
                  ?>
                  <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($last[1],$SETTINGS->timeformat); ?></span>
                  <?php
                  } else {
                  echo '- - - -';
                  }
                  $noteIndi = '';
                  if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                    $noteIndi = '<a class="noteindicator" href="#" onclick="iBox.showURL(\'?p=view-' . ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKETS->ticketID . '&amp;editNotes=yes\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false""><i class="fa fa-file-text' . ($TICKETS->ticketNotes ? '' : '-o') . ' fa-fw"></i></a> / ';
                  }
                  ?>
                  </td>
                  <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-<?php echo ($tLock == 'yes' ? 'danger' : 'info'); ?> btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-<?php echo ($tLock == 'yes' ? 'lock' : 'chevron-down'); ?> fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo $noteIndi . str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
                </tr>
                <tr>
                 <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
                 <?php
                 // Ticket Options..
                 include(PATH . 'templates/system/tickets/global/ticket-options.php');
                 ?>
                 </td>
                </tr>
                <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
          </div>
          <?php
        }
        }


        if ($homeViewCount == 0) {
        ?>
        <div class="alert alert-info">
          <i class="fa fa-warning fa-fw ms_red"></i> <?php echo $msadminlang_dashboard_3_7[8]; ?>
        </div>
        <?php
        }

        ?>
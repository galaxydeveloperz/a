<?php if (!defined('PARENT') || !isset($ACC->id)) { exit; }
include(PATH . 'templates/system/tickets/global/order-by.php');
include(PATH . 'templates/system/tickets/global/filter-by.php');
$dis       = array();
$SQL       = '';
$ticketIDs = array();
if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = strtolower($_GET['keys']);
  $SQL           = 'AND (LOWER(`' . DB_PREFIX . 'tickets`.`subject`) LIKE \'%' . mswSQL($_GET['keys']) . '%\' OR LOWER(`' . DB_PREFIX . 'tickets`.`comments`) LIKE \'%' . mswSQL($_GET['keys']) . '%\')';
}
if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
  $from  = $MSDT->mswDatePickerFormat($_GET['from']);
  $to    = $MSDT->mswDatePickerFormat($_GET['to']);
  $SQL  .= " AND (DATE(FROM_UNIXTIME(`" . DB_PREFIX . "tickets`.`ts`)) BETWEEN '{$from}' AND '{$to}')";
}
if (isset($_GET['dept'])) {
  if (substr($_GET['dept'], 0, 1) == 'u' && (USER_ADMINISTRATOR == 'yes' || in_array('assign', $userAccess))) {
    $SQL .= " AND (FIND_IN_SET('" . (int) substr($_GET['dept'], 1) . "', `" . DB_PREFIX . "tickets`.`assignedto`) > 0)";
  } else {
    $_GET['dept'] = (int) $_GET['dept'];
    if ($_GET['dept'] > 0) {
      $SQL .= " AND (`" . DB_PREFIX . "tickets`.`department` = '" . mswSQL($_GET['dept']) . "')";
    }
  }
}
if (isset($_GET['status']) && in_array($_GET['status'], $statusPrKeys)) {
  $SQL .= " AND (`" . DB_PREFIX . "tickets`.`ticketStatus` = '" . mswSQL($_GET['status']) . "')";
}
if (isset($_GET['keys']) && $_GET['keys'] && isset($_GET['replies'])) {
  $q = mswSQL_query("SELECT `ticketID` FROM `" . DB_PREFIX . "replies`
       WHERE LOWER(`comments`) LIKE '%" . mswSQL($_GET['keys']) . "%'
	     GROUP BY `ticketID`
	     ", __file__, __line__);
  while ($RP = mswSQL_fetchobj($q)) {
	  $ticketIDs[] = $RP->ticketID;
	}
}
// Disputes..
if ($SETTINGS->disputes == 'yes' && (isset($_GET['disputes']) || isset($_GET['all']))) {
  // Disputes in other tickets..
  $qD = mswSQL_query("SELECT `ticketID` FROM `" . DB_PREFIX . "disputes`
        WHERE `visitorID` = '{$ACC->id}'
        " . (!empty($ticketIDs) ? 'AND `id` NOT IN(' . mswSQL(implode(',', $ticketIDs)) . ')' : '') . "
	      GROUP BY `ticketID`
	      ", __file__, __line__);
  while ($DP = mswSQL_fetchobj($qD)) {
    $ticketIDs[] = $DP->ticketID;
  }
  // Disputes from started tickets..
  $qD2 = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "tickets`
         WHERE `visitorID` = '{$ACC->id}'
		     AND `isDisputed`  = 'yes'
		     AND `spamFlag`    = 'no'
	       " . (!empty($ticketIDs) ? 'AND `id` NOT IN(' . mswSQL(implode(',', $ticketIDs)) . ')' : '') . "
	      ", __file__, __line__);
  while ($DP2 = mswSQL_fetchobj($qD2)) {
    $ticketIDs[] = $DP2->id;
  }
}
// What to show?
$whatToShow = '';
if (isset($_GET['all'])) {
   $whatToShow  = 'WHERE (`' . DB_PREFIX . 'portal`.`email` = \'' . $ACC->email . '\')';
   if (!empty($ticketIDs)) {
     $whatToShow .= ' OR (`' . DB_PREFIX . 'tickets`.`id` IN(' . mswSQL(implode(',', $ticketIDs)) . '))';
   }
} else {
  if (empty($ticketIDs)) {
    $whatToShow  = 'WHERE (`' . DB_PREFIX . 'portal`.`email` = \'' . $ACC->email . '\')';
  } else {
    $whatToShow .= 'WHERE (`' . DB_PREFIX . 'tickets`.`id` IN(' . mswSQL(implode(',', $ticketIDs)) . '))';
  }
}
$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
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
     (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
      WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `disputeCount`,
     (SELECT count(*) FROM `" . DB_PREFIX . "replies`
      WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `replyCount`
     FROM `" . DB_PREFIX . "tickets`
     LEFT JOIN `" . DB_PREFIX . "departments`
     ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
     LEFT JOIN `" . DB_PREFIX . "portal`
     ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
     LEFT JOIN `" . DB_PREFIX . "levels`
     ON (`" . DB_PREFIX . "tickets`.`priority` = 
       IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
         `" . DB_PREFIX . "levels`.`id`,
         `" . DB_PREFIX . "levels`.`marker`
       )
     )
     $whatToShow
     $SQL
	   AND (`spamFlag` = 'no')
     " . $filterBy . mswSQL_deptfilter($ticketFilterAccess) . "
     " . $orderBy . "
     $sqlLimStr
     ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  =  (isset($c->rows) ? $c->rows : '0');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('accountman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=accountman"><?php echo $msg_adheader40; ?></a></li>
      <?php
      }
      ?>
      <li><a href="index.php?p=accounts&amp;edit=<?php echo $ACC->id; ?>"><?php echo mswSH($ACC->name); ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>"><?php echo $msg_accounts13; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>"><?php echo $msg_accounts13; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Search..
    include(PATH . 'templates/system/bootstrap/search-tickets.php');
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right topfilterarea">
            <?php
            if (USER_ADMINISTRATOR == 'yes' || in_array('accounts', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=accounts')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // For small screen devices, we hide the filters to give us more screen space..
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            include(PATH . 'templates/system/tickets/global/order-filter.php');
            include(PATH . 'templates/system/tickets/global/status-filter.php');
            include(PATH . 'templates/system/tickets/global/dept-filter.php');
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            </div>
            <?php
            }
            ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <?php
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')">
                  </th>
                  <?php
                  } else {
                  ?>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton2','mswCVal2')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_open37; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              if ($sqlQryRows > 0) {
              while ($TICKETS = mswSQL_fetchobj($q)) {
              $last = $MSPTICKETS->getLastReply($TICKETS->ticketID);
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
              $tLock = 'no';
              if ($SETTINGS->adminlock == 'yes' && $TICKETS->lockteam > 0 && $TICKETS->lockteam != $MSTEAM->id) {
                $tLock = 'yes';
              }
              if ($TICKETS->assignedto == 'waiting') {
                $tkStatus = (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '<a href="?p=assign">' : '') . $msadminlang3_1adminviewticket[23] . (in_array('assign', $userAccess) || USER_ADMINISTRATOR == 'yes' ? '</a>' : '');
              }
              ?>
              <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')" name="del[]" value="<?php echo $TICKETS->ticketID; ?>"></td>
                <td><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                </td>
                <td><b><?php echo mswSH($TICKETS->subject); ?></b>
                <?php
                if ($TICKETS->isDisputed=='yes') {
                ?>
                <span class="tdCellInfoDispute">
                 <i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30); ?> | <span id="spanstatus_<?php echo $TICKETS->ticketID; ?>"><?php echo $tkStatus; ?></span>
                </span>
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
                <span class="tdCellInfoDispute">
                 <a href="#" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;dis_users=yes','',{width:<?php echo IBOX_DISUSRS_WIDTH; ?>,height:<?php echo IBOX_DISUSRS_HEIGHT; ?>});return false"><i class="fa fa-bullhorn fa-fw"></i> <?php echo str_replace('{count}',($TICKETS->disputeCount + 1),$msg_showticket30); ?></a>
                </span>
                <?php
                } else {
                ?>
                <span class="tdCellInfo"><i class="fa fa-chevron-right"></i> <?php echo $MSYS->department($TICKETS->department,$msg_script30); ?> | <span id="spanstatus_<?php echo $TICKETS->ticketID; ?>"><?php echo $tkStatus; ?></span></span>
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
              } else {
              ?>
              <tr class="warning nothing_to_see">
                <td colspan="6"><?php echo $msg_open10; ?></td>
              </tr>
              <?php
              }
              ?>
              </tbody>
              </table>
            </div>
          </div>
          <div class="panel-footer">
           <?php
           if (USER_DEL_PRIV == 'yes' && $sqlQryRows > 0) {
	         ?>
           <button onclick="mswButtonOp('tickdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_open15); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_open15; ?></span> <span id="mswCVal">(0)</span></button>
	         <?php
	         }
           if ($sqlQryRows > 0) {
           ?>
           <button class="btn btn-primary button_margin_right20" onclick="mswProcess('tickexp')" type="button" id="delButton2" disabled="disabled"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_search25; ?></span> <span id="mswCVal2">(0)</span></button>
           <?php
           }
           if (in_array('accountman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=accountman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

        <?php
        if ($sqlLimStr && $sqlQryRows > 0 && $sqlQryRows > $limit) {
          define('PER_PAGE', $limit);
          $PGS = new pagination(array($sqlQryRows, $msg_script42, $page),'?p=' . $_GET['p'] . '&amp;next=');
          echo $PGS->display();
        }
        ?>

      </div>
    </div>
    </form>

  </div>
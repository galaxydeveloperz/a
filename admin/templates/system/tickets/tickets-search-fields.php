<?php if (!defined('PARENT')) { exit; }
$filters       = array();
$searchParams  = '';
$s             = '';
$sqlQryRows   = 0;
$area          = (empty($_GET['area']) ? array('tickets', 'disputes') : $_GET['area']);
include(PATH . 'templates/system/tickets/global/order-by.php');
if (isset($_GET['keys'])) {
  // Filters..
  if ($_GET['keys']) {
    $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
    // Search custom field data for ticket IDs
    $csFieldSearch = 'WHERE LOWER(`fieldData`) LIKE \'%' . $_GET['keys'] . '%\' ';
    if (isset($_GET['field'])) {
      if ($_GET['field'] > 0) {
        $_GET['field'] = (int) $_GET['field'];
        $csFieldSearch .= 'AND `fieldID` = \'' . mswSQL($_GET['field']) . '\'';
      }
    }
    $ticketIDs = array();
	  $q         = mswSQL_query("SELECT `ticketID` FROM `" . DB_PREFIX . "ticketfields`
	               $csFieldSearch
		             GROUP BY `ticketID`
		             ", __file__, __line__);
    while ($CFD = mswSQL_fetchobj($q)) {
	    $ticketIDs[] = $CFD->ticketID;
	  }
    $ticketIDs = (!empty($ticketIDs) ? $ticketIDs : array(0));
	  $filters[] = '`' . DB_PREFIX . 'tickets`.`id` IN(' . mswSQL(implode(',', $ticketIDs)) . ')';
	}
  if ($_GET['keys']) {
    if (isset($_GET['priority']) && in_array($_GET['priority'], $levelPrKeys)) {
      $filters[]  = "`priority` = '" . mswSQL($_GET['priority']) . "'";
    }
    if (isset($_GET['dept'])) {
      if (substr($_GET['dept'], 0, 1) == 'u' && (USER_ADMINISTRATOR == 'yes' || in_array('assign', $userAccess))) {
        $filters[] = "FIND_IN_SET('" . (int) substr($_GET['dept'], 1) . "', `assignedto`) > 0";
      } else {
        $_GET['dept'] = (int) $_GET['dept'];
        if ($_GET['dept'] > 0) {
          $filters[] = "`department` = '" . mswSQL($_GET['dept']) . "'";
        }
      }
    }
    if (isset($_GET['assign'])) {
      if ($_GET['assign'] > 0) {
        $_GET['assign'] = (int) $_GET['assign'];
        $filters[]      = "FIND_IN_SET('" . mswSQL($_GET['assign']) . "',`assignedto`) > 0";
      }
    }
    if (isset($_GET['status']) && in_array($_GET['status'], $statusPrKeys)) {
      $filters[] = "`ticketStatus` = '" . mswSQL($_GET['status']) . "'";
    }
    if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
      $from      = $MSDT->mswDatePickerFormat($_GET['from']);
      $to        = $MSDT->mswDatePickerFormat($_GET['to']);
      $filters[] = "DATE(FROM_UNIXTIME(`" . DB_PREFIX . "tickets`.`ts`)) BETWEEN '{$from}' AND '{$to}'";
    }
    if (count($area) > 1) {
      $filters[] = "`isDisputed` IN('yes','no')";
    } else {
      if (in_array('tickets', $area)) {
        $filters[] = "`isDisputed` = 'no'";
      } else {
        $filters[] = "`isDisputed` = 'yes'";
      }
    }
    // Build search string..
    if (!empty($filters)) {
      for ($i=0; $i<count($filters); $i++) {
        $searchParams .= ($i ? ' AND (' : 'WHERE (') . $filters[$i] . ')';
      }
    }
    // Count for pages..
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
         (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
          WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
         ) AS `disputeCount`,
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
         " . ($searchParams ? $searchParams . ' AND `assignedTo` != \'waiting\' AND `spamFlag` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess) : 'WHERE `spamFlag` = \'no\' AND `assignedTo` != \'waiting\'') . "
         $orderBy
         $sqlLimStr
         ", __file__, __line__);
    $c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
    $sqlQryRows  =  (isset($c->rows) ? $c->rows : '0');
  }
}
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys']) && isset($q)) {
      ?>
      <li><a href="index.php?p=search-fields"><?php echo $msg_header18; ?></a></li>
      <li class="active"><?php echo $msg_search6.' ('.mswNFM($sqlQryRows).')'; ?></li>
      <?php
      } else {
      ?>
      <li class="active"><?php echo $msg_header18; ?></li>
      <?php
      }
      ?>
    </ol>

    <form method="get" action="#">
    <div class="row searcharea"<?php echo (isset($_GET['keys']) && $_GET['keys'] ? ' style="display:none"' : ''); ?>>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-calendar fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search19; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-filter fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search20; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[30]; ?></label>
                  <input type="text" class="form-control" name="keys" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($_GET['keys']) ? mswCD($_GET['keys']) : ''); ?>">
                </div>
                <?php
                if ($SETTINGS->disputes == 'yes') {
                  if (in_array('open', $userAccess) || in_array('close', $userAccess) || USER_ADMINISTRATOR == 'yes') {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="area[]" value="tickets"<?php echo (!empty($_GET['area']) && in_array('tickets',$_GET['area']) ? ' checked="checked"' : (empty($_GET['area']) && SEARCH_AUTO_CHECK_TICKETS == 'yes' ? ' checked="checked"' : '')); ?>> <?php echo $msg_search12; ?></label>
                    </div>
                  </div>
                  <?php
                  }
                  if (in_array('disputes', $userAccess) || in_array('cdisputes', $userAccess) || USER_ADMINISTRATOR == 'yes') {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label><input type="checkbox" name="area[]" value="disputes"<?php echo (!empty($_GET['area']) && in_array('disputes',$_GET['area']) ? ' checked="checked"' : (empty($_GET['area']) && SEARCH_AUTO_CHECK_DISPUTES == 'yes' ? ' checked="checked"' : '')); ?>> <?php echo $msg_search13; ?></label>
                    </div>
                  </div>
                  <?php
                  }
		            }
                ?>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_search7; ?></label>
                  <input type="text" class="form-control" id="from" tabindex="<?php echo (++$tabIndex); ?>" name="from" value="<?php echo (isset($_GET['from']) ? mswCD($_GET['from']) : ''); ?>">
                  <input type="text" class="form-control" id="to" tabindex="<?php echo (++$tabIndex); ?>" name="to" value="<?php echo (isset($_GET['to']) ? mswCD($_GET['to']) : ''); ?>" style="margin-top:10px">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <label><?php echo $msg_search4; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_dept = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
                  while ($DEPT = mswSQL_fetchobj($q_dept)) {
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem('dept',$DEPT->id,true); ?>><?php echo mswSH($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_search5; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo mswSelectedItem('priority',$k,true); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_search8; ?></label>
                  <select name="status" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  foreach ($ticketStatusSel AS $sk => $sv) {
                  ?>
                  <option value="<?php echo $sk; ?>"<?php echo mswSelectedItem('status',$sk,true); ?>><?php echo $sv[0]; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_open31; ?></label>
                  <select name="assign" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_users  = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
                  while ($U = mswSQL_fetchobj($q_users)) {
                  ?>
                  <option value="<?php echo $U->id; ?>"<?php echo mswSelectedItem('assign',$U->id,true); ?>><?php echo mswCD($U->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1[29]; ?></label>
                  <select name="field" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - -</option>
                  <?php
                  $q_fld  = mswSQL_query("SELECT `id`,`fieldInstructions` FROM `" . DB_PREFIX . "cusfields` ORDER BY `fieldInstructions`", __file__, __line__);
                  while ($F = mswSQL_fetchobj($q_fld)) {
                  ?>
                  <option value="<?php echo $F->id; ?>"<?php echo mswSelectedItem('field',$F->id,true); ?>><?php echo mswCD($F->fieldInstructions); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="p" value="search-fields">
           <?php
           if ($SETTINGS->disputes == 'no') {
           ?>
           <input type="hidden" name="area[]" value="tickets">
           <?php
           }
           ?>
            <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_search2; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

    <?php
    // Search results.
    if (isset($_GET['keys']) && $_GET['keys'] && isset($q)) {
    ?>
    <form method="post" action="#">
    <div class="row resultsarea">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right topfilterarea">
            <?php
            define('SKIP_SEARCH_BOX', 1);
            if (USER_ADMINISTRATOR == 'yes' || in_array('add', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=add')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // For small screen devices, we hide the filters to give us more screen space..
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswSearchReload('search-fields')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            include(PATH . 'templates/system/tickets/global/order-filter.php');
            include(PATH . 'templates/system/tickets/global/status-filter.php');
            include(PATH . 'templates/system/tickets/global/dept-filter.php');
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            if (!in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-info btn-sm" type="button" onclick="mswSearchReload('search-fields')"><i class="fa fa-search fa-fw"></i></button>
            <?php
            }
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
                <td class="tdticketno"><a href="?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
                <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
                </td>
                <?php
                if ($TICKETS->isDisputed == 'yes') {
                ?>
                <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
                <span class="tdCellInfo">
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
                </td>
                <?php
                } else {
                ?>
                <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
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
                ?>
                </td>
                <?php
                }
                ?>
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

          <?php
          if ($sqlQryRows > 0) {
          ?>
          <div class="panel-footer">
           <input type="hidden" name="orderbyexp" value="<?php echo mswSH($orderBy); ?>">
           <?php
           if (USER_DEL_PRIV == 'yes') {
	         ?>
           <button onclick="mswButtonOp('tickdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_open15); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_open15; ?></span> <span id="mswCVal">(0)</span></button>
	         <?php
	         }
           ?>
           <button class="btn btn-primary" onclick="mswProcess('tickexp')" type="button" id="delButton2" disabled="disabled"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_search25; ?></span> <span id="mswCVal2">(0)</span></button>
          </div>
          <?php
          }
          ?>
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
    <?php
    }
    ?>

  </div>
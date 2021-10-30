<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; }
mswVLQY($SUPTICK);
$countOfEnFlds     = mswSQL_rows('cusfields WHERE `enField` = \'yes\'');
$tickID            = (int) $_GET['id'];
$aCount            = mswSQL_rows('attachments WHERE `ticketID` = \'' . $tickID . '\' AND `replyID` = \'0\'');
$uCount            = mswSQL_rows('users WHERE `id` > 0');
// Fields..
$qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
      WHERE FIND_IN_SET('ticket',`fieldLoc`)       > 0
      AND `enField`                                = 'yes'
			AND FIND_IN_SET('{$SUPTICK->department}', `departments`) > 0
      ORDER BY `orderBy`
      ", __file__, __line__);
$countOfCusFields = mswSQL_numrows($qF);
$tickDept         = mswSQL_table('departments','id', $SUPTICK->department);
if ($countOfCusFields > 0) {
  define('LOAD_DATE_PICKERS', 1);
  define('LOAD_CAL_INPUT_FUNCTION', 'two');
}
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      // Status of ticket for link
      $link = getTicketLink(array(
        't' => $SUPTICK,
        'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
        's' => $ticketStatusSel
      ));
      if ($link[0]) {
      ?>
      <li><a href="<?php echo $link[0]; ?>"><?php echo $link[1]; ?></a></li>
      <?php
      }
      ?>
      <li><a href="?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $tickID; ?>"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_portal35 : $msg_portal8); ?></a></li>
      <li class="active"><?php echo str_replace('{ticket}', mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno), $msg_viewticket20); ?></li>
    </ol>
    
    <?php
    if (isset($_GET['showAdd'])) {
    ?>
    <div class="alert alert-warning alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
      <b><i class="fa fa-check fa-fw"></i></b> <?php echo $msg_newticket42; ?>
    </div>
    <?php
    }
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-ticket fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add; ?></span></a></li>
          <?php
          if ($uCount > 0 && $tickDept->manual_assign == 'yes' && $SUPTICK->assignedto != 'waiting' && $SUPTICK->spamFlag == 'no') {
          ?>
          <li id="liusr"><a href="#four" data-toggle="tab"><i class="fa fa-users fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1adminticketedit[1]; ?></span></a></li>
          <?php
          }
          if ($countOfEnFlds > 0) {
          ?>
          <li id="licus"<?php echo ($countOfCusFields == 0 ? ' style="display:none"' : ''); ?>><a href="#two" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
          <?php
          }
          if ($SETTINGS->attachment == 'yes' && $aCount > 0) {
          ?>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments; ?></span></a></li>
          <?php
          }
          ?>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_newticket15; ?></label>
                  <input type="text" class="form-control" name="subject" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo mswSH($SUPTICK->subject); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket6; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"<?php echo ($countOfCusFields > 0 ? ' onchange="mswDeptLoader(\'two\',\'ticket\',\'0\',\'ticket\')"' : ''); ?>>
                  <?php
                  $q_dept = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
                  while ($DEPT = mswSQL_fetchobj($q_dept)) {
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem($DEPT->id,$SUPTICK->department); ?>><?php echo mswCD($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket8; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo mswSelectedItem($k,$SUPTICK->priority); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

		            <div class="form-group">
                  <?php
		              // BBCode..
		              include(PATH . 'templates/system/bbcode-buttons.php');
		              ?>
		              <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSH($SUPTICK->comments); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_viewticket17; ?></label>
			            <select name="status" class="form-control">
			              <?php
			              // Check team perms..
			              if (USER_CLOSE_PRIV == 'no') {
											unset($ticketStatusSel['close']);
										}
										if (USER_LOCK_PRIV == 'no') {
											unset($ticketStatusSel['closed']);
										}
			              foreach ($ticketStatusSel AS $sk => $sv) {
                    ?>
                    <option value="<?php echo $sk; ?>"<?php echo mswSelectedItem($sk,$SUPTICK->ticketStatus); ?>><?php echo $sv[0]; ?></option>
                    <?php
                    }
                    ?>
                  </select>
                </div>

                <?php
                if ($MSTEAM->workedit == 'yes' || USER_ADMINISTRATOR == 'yes') {
                ?>
                <div class="form-group">
                  <label><?php echo $msadminlang_tickets_3_7[18]; ?></label>
                  <input type="text" class="form-control" name="worktime" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo ($SUPTICK->worktime ? mswSH($SUPTICK->worktime) : '00:00:00'); ?>">
                </div>
                <?php
                }
                ?>

              </div>
              <?php
              if ($uCount > 0 && $SUPTICK->assignedto != 'waiting' && $SUPTICK->spamFlag == 'no') {
              ?>
              <div class="tab-pane fade" id="four">

                <div class="table-responsive">
                 <table class="table table-striped table-hover">
                 <tbody>
                 <?php
                 $boomUsers = explode(',', $SUPTICK->assignedto);
                 $q_users   = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
                 while ($USERS = mswSQL_fetchobj($q_users)) {
                   ?>
                   <tr>
                   <td><input type="checkbox" name="assigned[]" value="<?php echo $USERS->id; ?>"<?php echo (in_array($USERS->id,$boomUsers) ? ' checked="checked"' : ''); ?>></td>
                   <td><?php echo mswSH($USERS->name); ?></td>
                   <td><?php echo mswSH($USERS->email); ?></td>
                   <?php
                   if (in_array('users',$userAccess) || USER_ADMINISTRATOR == 'yes') {
                   // Only show global edit id for global user..
                     if ($USERS->id == '1' && $MSTEAM->id == '1') {
                     ?>
                     <td><a href="?p=team&amp;edit=<?php echo $USERS->id; ?>" title="<?php echo mswSH($msg_user14); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                     <?php
                     } else {
                       if ($USERS->id > '1') {
                       ?>
                       <td><a href="?p=team&amp;edit=<?php echo $USERS->id; ?>" title="<?php echo mswSH($msg_user14); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                       <?php
                       }
                     }
                   }
                   ?>
                   </tr>
                 <?php
                 }
                 ?>
                 </tbody>
                 </table>
               </div>

              </div>
              <?php
              }
              if ($countOfEnFlds > 0) {
              ?>
              <div class="tab-pane fade" id="two">

                <?php
                if ($countOfCusFields > 0) {
                  while ($FIELDS = mswSQL_fetchobj($qF)) {
                    $TF = mswSQL_table('ticketfields','ticketID',(int) $tickID,' AND `replyID` = \'0\' AND `fieldID` = \'' . $FIELDS->id . '\'');
                    switch ($FIELDS->fieldType) {
                      case 'textarea':
                        echo $MSFM->buildTextArea(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'input':
                        echo $MSFM->buildInputBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'calendar':
                        echo $MSFM->buildCalBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'select':
                        echo $MSFM->buildSelect(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'checkbox':
                        echo $MSFM->buildCheckBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                    }
                  }
                } else {
                  echo '<i class="fa fa-warning fa-fw ms_red"></i> ' . $msadminlang3_1adminticketedit[0];
                }
                ?>

              </div>
              <?php
              }
              if ($SETTINGS->attachment == 'yes' && $aCount > 0) {
              ?>
              <div class="tab-pane fade" id="three">

               <div class="table-responsive">
                 <table class="table table-striped table-hover">
                 <tbody>
                 <?php
                 $qA = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                       WHERE `ticketID` = '{$tickID}'
                       AND `replyID` = '0'
                       ORDER BY `fileName`
                       ", __file__, __line__);
                 while ($ATT = mswSQL_fetchobj($qA)) {
                   $ext     = strrchr($ATT->fileName, '.');
                   $split   = explode('-', $ATT->addDate);
                   $folder  = '';
                   $base   = $SETTINGS->attachpath . '/';
                   // Check for newer folder structure..
                   if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
                     $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
                   }
                   ?>
                   <tr id="attrow<?php echo $ATT->id; ?>">
                   <?php
                   $oneclick = '';
                   if (@file_exists($base . $ATT->fileName)) {
                   if (ONE_CLICK_IMG_VIEWER && substr($ATT->mimeType, 0, 6) == 'image/') {
                     $split  = explode('-', $ATT->addDate);
                     $folder = '';
                     // Check for newer folder structure..
                     if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
                       $folder = $split[0] . '/' . $split[1] . '/';
                     }
                     $oneclick = ' (<a href="' . $SETTINGS->attachhref . '/' . $folder . $ATT->fileName . '" onclick="iBox.showURL(this.href,\'\');return false">' . $msg_script10 . '</a>)';
                   }
                   ?>
                   <td><b>[<?php echo substr(strtoupper($ext), 1); ?>]</b> <?php echo substr($ATT->fileName, 0, strpos($ATT->fileName, '.')); ?></td>
                   <?php
                   } else {
                   ?>
                   <td>[<?php echo substr(strtoupper($ext), 1); ?>] <?php echo substr($ATT->fileName, 0, strpos($ATT->fileName, '.')); ?>
                   <span class="does_not_exist"><i class="fa fa-warning fa-fw ms_red"></i> <?php echo $msadminlang3_1adminviewticket[17]; ?></span>
                   </td>
                   <?php
                   }
                   ?>
                   <td<?php echo (USER_DEL_PRIV == 'no' ? ' class="text-right"' : ''); ?>><?php echo mswFSC($ATT->fileSize) . $oneclick; ?></td>
                   <td class="text-right">
                   <?php
                   if (file_exists($base . $ATT->fileName)) {
                   ?>
                   <a href="#" onclick="mswDL('<?php echo $ATT->id; ?>','dla');return false" title="<?php echo mswSH($msg_viewticket50); ?>"><i class="fa fa-download fa-fw"></i></a>
                   <?php
                   } else {
                   ?>
                   <i class="fa fa-download fa-fw fadeddownload"></i>
                   <?php
                   }
                   if (USER_DEL_PRIV == 'yes' && !defined('TICKET_TEAM_LOCK')) {
                   ?>
                   <a href="#" onclick="mswRowForDel('<?php echo $ATT->id; ?>','attachment');return false"><i class="fa fa-times fa-fw ms_red"></i></a>
                   <?php
                   }
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
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="odeptid" value="<?php echo $SUPTICK->department; ?>">
            <input type="hidden" name="id" value="<?php echo $tickID; ?>">
            <input type="hidden" name="area" value="ticket">
            <input type="hidden" name="wtime" value="<?php echo ($SUPTICK->worktime ? mswSH($SUPTICK->worktime) : '00:00:00'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('tickedit')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket21; ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=view-ticket&amp;id=<?php echo $tickID; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo $msg_levels11; ?></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
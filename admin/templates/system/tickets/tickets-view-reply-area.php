<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
$savedstaff  = ($MSTEAM->savedstaff ? unserialize($MSTEAM->savedstaff) : array());
// Ticket Reply Area..
if (!defined('TICKET_TEAM_LOCK') && !in_array($SUPTICK->ticketStatus, array('closed')) && $SUPTICK->spamFlag == 'no' && $SUPTICK->assignedto != 'waiting') {
include(BASE_PATH . 'control/classes/class.upload.php');
$MSUPL     = new msUpload();
$aMaxFiles = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : '9999999');
$mSize     = $MSUPL->getMaxSize();
$mswUploadDropzone2 = array(
  'ajax' => 'tickreply',
  'multiple' => ($aMaxFiles > 1 ? 'true' : 'false'),
  'max-files' => $aMaxFiles,
  'max-size' => $mSize,
  'drag' => 'false',
  'div' => 'four'
);
// Custom  fields..
$qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
      WHERE FIND_IN_SET('admin', `fieldLoc`) > 0
      AND `enField`                         = 'yes'
      ORDER BY `orderBy`
      ", __file__, __line__);
$cusFieldRows = mswSQL_numrows($qF);
// Standard responses..
$numResp = mswSQL_rows('responses WHERE `enResponse` = \'yes\' AND FIND_IN_SET(\'' . $SUPTICK->department . '\',`departments`) > 0');
define('JS_LOADER', 'ticket-reply.php');
if ($cusFieldRows > 0) {
  define('LOAD_DATE_PICKERS', 1);
  define('LOAD_CAL_INPUT_FUNCTION', 'three');
}
?>
<div class="row" id="replyArea">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
    <ul class="nav nav-tabs">
      <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-quote-left fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1adminviewticket[14]; ?></span></a></li>
      <li><a href="#two" data-toggle="tab"><i class="fa fa-commenting-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket12; ?></span></a></li>
      <?php
      if ($cusFieldRows > 0) {
      ?>
      <li><a href="#three" data-toggle="tab"><i class="fa fa-list fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
      <?php
      }
      ?>
      <li><a href="#four" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_attachments; ?></span></a></li>
      <li><a href="#five" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_accounts8; ?></span></a></li>
      <?php
      $otherTeamMems = 0;
      if (USER_ADMINISTRATOR == 'yes' || $MSTEAM->staffupnotify == 'yes') {
        $qNU = mswSQL_query("SELECT `id`, `name` FROM `" . DB_PREFIX . "users`
               WHERE `id` != '{$MSTEAM->id}'
               AND `notify` = 'yes'
               ORDER BY `name`
               ", __file__, __line__);
        $otherTeamMems = mswSQL_numrows($qNU);
        if ($otherTeamMems > 0) {
        ?>
        <li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-arrow-down fa-fw" title="<?php echo mswSH($msgloballang4_3[6]); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msgloballang4_3[6]; ?></span> <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#six" data-toggle="tab"><?php echo $mssuptickets4_3[1]; ?></a></li>
          </ul>
				</li>
				<?php
				}
			}
			?>
    </ul>
  </div>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10 repar">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="tab-content">
          <div class="tab-pane active in" id="one">

            <div class="form-group">
              <?php
              include(PATH . 'templates/system/bbcode-buttons.php');
              ?>
              <textarea name="comments" rows="15" cols="40" id="comments" class="form-control" tabindex="<?php echo (++$tabIndex); ?>"></textarea>
              <input type="hidden" name="worktime" value="00:00:00">
            </div>

            <?php
            if ($SETTINGS->timetrack == 'yes') {
            ?>
            <div class="timerdivarea">
              <label><?php echo $msadminlang_tickets_3_7[14]; ?></label>
              <div class="timerdiv">00:00:00</div>
              <?php
              // Show buttons and controls?
              if ($MSTEAM->timer == 'yes' || $MSTEAM->id == '1' || USER_ADMINISTRATOR == 'yes') {
              ?>
              <div class="timerbuttons">
                <button class="btn btn-xs btn-success bstart" type="button" title="<?php echo mswSH($msadminlang_tickets_3_7[15]); ?>"><i class="fa fa-play fa-fw"></i></button>
                <button class="btn btn-xs btn-primary bpause" type="button" title="<?php echo mswSH($msadminlang_tickets_3_7[20]); ?>"><i class="fa fa-pause fa-fw"></i></button>
                <button class="btn btn-xs btn-primary bstop" type="button" title="<?php echo mswSH($msadminlang_tickets_3_7[16]); ?>"><i class="fa fa-stop fa-fw"></i></button>
                <button class="btn btn-xs btn-info breset" type="button" title="<?php echo mswSH($msadminlang_tickets_3_7[17]); ?>"><i class="fa fa-refresh fa-fw"></i></button>
              </div>
              <?php
              }
              ?>
            </div>
            <?php
            }
            ?>

          </div>
          <div class="tab-pane fade" id="two">

            <div class="form-group">
              <label><?php echo $msadminlang3_1adminviewticket[12]; ?></label>
              <input type="text" name="sresp" value="" class="form-control" tabindex="<?php echo (++$tabIndex); ?>">
            </div>

            <?php
            if (in_array('standard-responses', $userAccess) || USER_ADMINISTRATOR == 'yes') {
            ?>
            <div class="form-group">
              <label><?php echo $msadminlang3_1adminviewticket[13]; ?></label>
              <input type="text" class="form-control" name="response" value="" tabindex="<?php echo (++$tabIndex); ?>">
              <input type="hidden" name="dept[]" value="<?php echo $SUPTICK->department; ?>">
            </div>
            <?php
            }
            ?>

          </div>
          <?php
          if ($cusFieldRows > 0) {
          ?>
          <div class="tab-pane fade" id="three">

            <?php
            while ($FIELDS = mswSQL_fetchobj($qF)) {
              switch ($FIELDS->fieldType) {
                case 'textarea':
                  echo $MSFM->buildTextArea(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                  break;
                case 'input':
                  echo $MSFM->buildInputBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                  break;
                case 'select':
                  echo $MSFM->buildSelect(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(++$tabIndex));
                  break;
                case 'calendar':
                  echo $MSFM->buildCalBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                  break;
                case 'checkbox':
                  echo $MSFM->buildCheckBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions);
                  break;
              }
            }
            ?>

          </div>
          <?php
          }
          ?>
          <div class="tab-pane fade" id="four">

            <div id="dropzone" class="dropzone">
              <div class="droparea">
                <?php echo str_replace('{max}', mswFSC($mSize), $msadminlang3_1uploads[6]); ?>
              </div>
            </div>

          </div>
          <div class="tab-pane fade" id="five">

            <?php
            // Merging only allowed for standard tickets..
            if (TICKET_TYPE == 'ticket' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->mergeperms == 'yes')) {
            ?>
            <div class="form-group">
              <label><?php echo $msg_viewticket102; ?></label>
              <input type="text" class="form-control" value="" name="mergeid_t" onkeyup="mswMergeClear()">
            </div>
            <?php
            }
            ?>

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
                  switch($sk) {
                    case 'open':
                      ?>
                      <option value="open"<?php echo ($SETTINGS->closeadmin == 'no' && $SUPTICK->ticketStatus == $sk ? ' selected="selected"' : ''); ?>><?php echo $sv[0]; ?></option>
                      <?php
                      break;
                    case 'close':
                      ?>
                      <option value="close"<?php echo ($SETTINGS->closeadmin == 'yes' || $SUPTICK->ticketStatus == $sk ? ' selected="selected"' : ''); ?>><?php echo $sv[0]; ?></option>
                      <?php
                      break;
                    case 'closed':
                      ?>
                      <option value="close"<?php echo ($SETTINGS->closeadmin == 'no' && $SUPTICK->ticketStatus == $sk ? ' selected="selected"' : ''); ?>><?php echo $sv[0]; ?></option>
                      <?php
                      break;
                    default:
                      ?>
                      <option value="<?php echo $sk; ?>"<?php echo ($SETTINGS->closeadmin == 'no' && $SUPTICK->ticketStatus == $sk ? ' selected="selected"' : ''); ?>><?php echo $sv[0]; ?></option>
                      <?php
                      break;
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label><?php echo $msg_newticket8; ?></label>
              <select name="priority" class="form-control">
              <?php
              if (!empty($ticketLevelSel)) {
              foreach ($ticketLevelSel AS $k => $v) {
              ?>
              <option value="<?php echo $k; ?>"<?php echo ($SUPTICK->priority == $k ? ' selected="selected"' : ''); ?>><?php echo $v; ?></option>
              <?php
              }
              }
              ?>
              </select>
              <input type="hidden" name="cur_priority" value="<?php echo $SUPTICK->priority; ?>">
            </div>

            <div class="form-group">
              <label><?php echo $msg_viewticket18; ?></label>
              <select name="mail" class="form-control">
              <option value="yes" selected="selected"><?php echo $msg_script4; ?></option>
              <option value="no"><?php echo $msg_script5; ?></option>
              </select>
            </div>

            <?php
            if (USER_ADMINISTRATOR == 'yes') {
            ?>
            <div class="form-group">
              <div class="checkbox">
                <label><input type="checkbox" name="history" value="yes" checked="checked"> <?php echo $msg_viewticket109; ?></label>
              </div>
            </div>
            <?php
            }
            ?>

          </div>
          <?php
          if ($otherTeamMems > 0) {
          ?>
          <div class="tab-pane fade" id="six">
            <div class="form-group">
              <label><?php echo $mssuptickets4_3[2]; ?></label>
              <?php
              while ($STAFF = mswSQL_fetchobj($qNU)) {
              ?>
              <div class="checkbox">
                <label><input type="checkbox" name="staffmail[]" value="<?php echo $STAFF->id; ?>"<?php echo (in_array($STAFF->id, $savedstaff) ? ' checked="checked"' : ''); ?>> <?php echo mswSH($STAFF->name); ?></label>
              </div>
              <?php
              }
              ?>
              <div class="checkbox">
                <label><br><input type="checkbox" name="staffmailsave" value="yes"> <b><?php echo $mssuptickets4_3[3]; ?></b></label>
              </div>
            </div>
          </div>
          <?php
          }
          ?>
        </div>
      </div>
      <?php
      if ($SUPTICK->ticketStatus == 'closed') {
        $msg_viewticket13 = $msadminlang3_1adminviewticket[24];
      }
      ?>
      <div class="panel-footer">
        <?php
        if (SAVE_DRAFTS) {
        define('DRAFT_AREA', '#comments');
        define('DRAFT_ID', $tickID);
        ?>
        <div class="pull-right draftarea" id="draft_<?php echo $tickID; ?>">
          &nbsp;
        </div>
        <?php
        }
        ?>
        <input type="hidden" name="ticketID" value="<?php echo $tickID; ?>">
        <button class="btn btn-primary" type="submit"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket13; ?></span></button>
			</div>
    </div>
  </div>

  <?php
  // History..
  include(PATH . 'templates/system/tickets/tickets-view-history.php');
  ?>

</div>
<?php
} else {
?>
<div class="row">
  <?php
  if (!defined('TICKET_TEAM_LOCK')) {
  ?>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <?php
    $url = (TICKET_TYPE == 'dispute' ? '?p=view-dispute&amp;id=' . $tickID . '&amp;act=reopen' : '?p=view-ticket&amp;id=' . $tickID . '&amp;act=reopen');
    if ($SUPTICK->spamFlag == 'yes') {
      $msg = $msg_spam3;
    } elseif ($SUPTICK->assignedto == 'waiting') {
      $msg = $msadminlang3_1adminviewticket[7];
    } else {
      $msg = str_replace('{url}', $url, $msg_viewticket45);
    }
    ?>
    <div class="alert alert-danger"><i class="fa fa-warning fa-fw ms_red"></i> <?php echo $msg; ?></div>
  </div>
  <?php
  }

  // History..
  include(PATH . 'templates/system/tickets/tickets-view-history.php');
  ?>

</div>
<?php
}
?>
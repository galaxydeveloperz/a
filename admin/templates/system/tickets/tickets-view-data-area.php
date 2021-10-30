<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
           $sublinks = array();

           // Custom Fields..
           $qT = mswSQL_query("SELECT `fieldData`,`fieldType`,`fieldInstructions`,`fieldID`,
                 `" . DB_PREFIX . "ticketfields`.`id` AS `ticketFieldID`
                 FROM `" . DB_PREFIX . "ticketfields`
                 LEFT JOIN `" . DB_PREFIX . "cusfields`
                 ON `" . DB_PREFIX . "ticketfields`.`fieldID`      = `" . DB_PREFIX . "cusfields`.`id`
                 WHERE `" . DB_PREFIX . "ticketfields`.`ticketID`  = '{$tickID}'
                 AND `" . DB_PREFIX . "ticketfields`.`replyID`     = '{$dRepID}'
                 AND `" . DB_PREFIX . "ticketfields`.`fieldData`  != 'nothing-selected'
                 AND `" . DB_PREFIX . "ticketfields`.`fieldData`  != ''
                 AND `" . DB_PREFIX . "cusfields`.`enField`        = 'yes'
                 ORDER BY `" . DB_PREFIX . "cusfields`.`orderBy`
                 ", __file__, __line__);
            $cFCount = mswSQL_numrows($qT);
            if ($cFCount > 0) {
              $sublinks[] = '<button class="btn btn-default btn-sm cs_but" type="button" onclick="mswToggleTicketData(\'' . $toggleID . '\', \'field\')"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs">' . $msg_viewticket97 . '</span> (<span class="cscount">' . $cFCount . '</span>)</button>';
            }

            // Attachments..
            $qA = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                  WHERE `ticketID` = '{$tickID}'
                  AND `replyID` = '{$dRepID}'
                  ORDER BY `fileName`
                  ", __file__, __line__);
            $aCount = mswSQL_numrows($qA);
            if ($aCount > 0) {
              $sublinks[] = '<button class="btn btn-default btn-sm at_but" type="button" onclick="mswToggleTicketData(\'' . $toggleID . '\', \'attach\')"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs">' . $msg_viewticket40 . '</span> (<span class="atcount">' . $aCount . '</span>)</button>';
            }

            // If something is to display, add a horizontal line..
            if ($cFCount > 0 || $aCount > 0) {
            ?>
            <hr>
            <?php
            }

            if ($cFCount > 0) {
            ?>
            <div class="mswcf" style="display:none" id="cs_sublcs_<?php echo $dRepID; ?>">
              <?php
              while ($TS = mswSQL_fetchobj($qT)) {
                ?>
                <input type="hidden" name="cs_subl_<?php echo $TS->ticketFieldID; ?>" value="<?php echo $dRepID; ?>">
                <div class="<?php echo $label; ?>" id="cs_wrap_<?php echo $TS->ticketFieldID; ?>" data-cs="true">
                  <?php
                  switch ($TS->fieldType) {
                    case 'textarea':
                    case 'input':
                    case 'select':
                    case 'calendar':
                      ?>
                      <div class="panel-heading"><?php echo (USER_DEL_PRIV == 'yes' ? '<span class="pull-right"><a href="#" onclick="mswButtonOp(\'tickcsdel\',\'' . $TS->ticketFieldID . '\');return false;"><i class="fa fa-times fa-fw ms_red"></i></a></span>' : ''); ?><i class="fa fa-caret-right fa-fw"></i> <?php echo mswSH($TS->fieldInstructions); ?></div>
                      <div class="panel-body"><?php echo $MSPARSER->mswTxtParsingEngine($TS->fieldData); ?></div>
                      <?php
                      break;
                    case 'checkbox':
                      ?>
                      <div class="panel-heading"><?php echo (USER_DEL_PRIV == 'yes' ? '<span class="pull-right"><a href="#" onclick="mswButtonOp(\'tickcsdel\',\'' . $TS->ticketFieldID . '\');return false;"><i class="fa fa-times fa-fw ms_red"></i></a></span>' : ''); ?><i class="fa fa-caret-right fa-fw"></i> <?php echo mswSH($TS->fieldInstructions); ?></div>
                      <div class="panel-body"><?php echo str_replace('#####', '<br>', mswSH($TS->fieldData)); ?></div>
                      <?php
                      break;
                  }
                  ?>
                </div>
                <?php
              }
              ?>
              <hr>
            </div>
            <?php
            }

            if ($aCount > 0) {
            ?>
            <div class="mswatt" style="display:none" id="at_attwat_<?php echo $dRepID; ?>">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                <tbody data-at="true">
                <?php
                while ($ATT = mswSQL_fetchobj($qA)) {
                  $ext    = strrchr($ATT->fileName, '.');
                  $split  = explode('-', $ATT->addDate);
                  $base   = $SETTINGS->attachpath . '/';
                  // Check for newer folder structure..
                  if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
                    $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
                  }
                  ?>
                  <tr id="datatrat_<?php echo $ATT->id; ?>">
                    <?php
                    $oneclick = '';
                    if (file_exists($base . $ATT->fileName)) {
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
                    <td><?php echo mswFSC($ATT->fileSize) . $oneclick; ?></td>
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
                      <a href="#" onclick="mswButtonOp('tickattdel','<?php echo $ATT->id; ?>');return false;"><i class="fa fa-times fa-fw ms_red"></i></a>
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
              <hr>
            </div>
            <?php
            }

            if (!empty($sublinks)) {
            ?>
            <div class="text-right ticketsublinks" id="sublinks_<?php echo $dRepID; ?>">
              <?php echo implode(SUBLINK_SEPARATOR, $sublinks); ?>
            </div>
            <?php
            }
            ?>

<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }

      // Replies..
      $q_replies = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "replies`
                   WHERE `ticketID` = '{$tickID}'
                   ORDER BY `id`
                   ", __file__, __line__);
      while ($REPLIES = mswSQL_fetchobj($q_replies)) {
        switch ($REPLIES->replyType) {
         case 'admin':
           $USER       = mswSQL_table('users', 'id', $REPLIES->replyUser);
           $replyName  = (isset($USER->name) ? mswSH($USER->name) : $msg_script17);
           $label      = 'panel panel-default';
           $icon       = 'users';
           break;
         case 'visitor':
           if ($REPLIES->disputeUser > 0) {
             $DU         = mswSQL_table('portal', 'id', $REPLIES->disputeUser, '', '`name`');
             $replyName  = (isset($DU->name) ? mswSH($DU->name) : $msg_script17);
           } else {
             $USER       = mswSQL_table('portal', 'id', $REPLIES->replyUser, '', '`name`');
             $replyName  = (isset($USER->name) ? mswSH($USER->name) : $msg_script17);
           }
           $label      = 'panel panel-default';
           $icon       = 'user';
           break;
        }
        ?>
        <div class="<?php echo $label; ?>" id="datarp_<?php echo $REPLIES->id; ?>">
          <div class="panel-heading colorchangeheader left-align">
            <span class="pull-right hidden-xs">(<?php echo $msadminlang3_1adminviewticket[18]; ?>: <?php echo $REPLIES->id; ?>)</span>
            <i class="fa fa-<?php echo $icon; ?> fa-fw"></i> <?php echo $replyName; ?> <span class="mobilebreakpoint"><span class="no_fontweight"><i class="fa fa-chevron-right fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($REPLIES->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REPLIES->ts,$SETTINGS->timeformat); ?></span></span>
          </div>
          <div class="panel-body" id="rp<?php echo $REPLIES->id; ?>">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($REPLIES->comments);
            if ($REPLIES->replyType == 'admin' && $USER->signature) {
            ?>
            <hr>
            <?php
            echo mswNL2BR($MSPARSER->mswAutoLinkParser(mswSH($USER->signature)));
            }
            $dRepID   = $REPLIES->id;
            $toggleID = 'rp' . $REPLIES->id;
            include(PATH . 'templates/system/tickets/tickets-view-data-area.php');
            ?>
          </div>
          <div class="panel-footer <?php echo $REPLIES->replyType; ?>panelfooter">
            <span class="pull-right">
              <?php
              if (!defined('TICKET_TEAM_LOCK')) {
                if (USER_EDIT_R_PRIV == 'yes' || USER_DEL_PRIV == 'yes') {
                  if (USER_EDIT_R_PRIV == 'yes') {
                  ?>
                  <a href="?p=edit-reply&amp;id=<?php echo $REPLIES->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <?php
                  }
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <a href="#" onclick="mswButtonOp('tickrepdel','<?php echo $REPLIES->id; ?>');return false;" title="<?php echo mswSH($msg_script8); ?>"><i class="fa fa-times fa-fw ms_red"></i></a>
                  <?php
                  }
                } else {
                  ?>
                  &nbsp;
                  <?php
                }
              } else {
                ?>
                &nbsp;
                <?php
              }
              ?>
            </span>
            <?php echo loadIPAddresses($REPLIES->ipAddresses); ?>
          </div>
        </div>
        <?php
      }

      ?>
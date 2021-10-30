    <?php if (!defined('PARENT') || !isset($TICKETS->ticketID)) { exit; }
    if (isset($_GET['p']) && $_GET['p'] == 'acchistory') {
      $areaToLoad = 'search';
    } else {
      $areaToLoad = (isset($_GET['p']) ? $_GET['p'] : '');
    }
    if ($areaToLoad == '') {
      if (defined('LOADED_HOME')) {
        $areaToLoad = 'open';
      }
    }
    $thisTicket_ID = $TICKETS->ticketID;
    if ($areaToLoad && $TICKETS->assignedto != 'waiting') {
      switch($areaToLoad) {
        case 'open':
        case 'disputes':
        default:
          $drop_txt = array(
            $msadminlang3_1adminviewticket[1],
            $msadminlang3_1adminviewticket[0]
          );
          if (USER_CLOSE_PRIV == 'yes') {
          ?>
          <button class="btn btn-danger btn-xs" type="button" onclick="mswTickAct('close', '<?php echo $thisTicket_ID; ?>', '');return false;" title="<?php echo mswSH($drop_txt[0]); ?>"><i class="fa fa-minus-square fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[9]; ?></span></button>
          <?php
          }
          if (USER_LOCK_PRIV == 'yes') {
          ?>
          <button class="btn btn-danger btn-xs" type="button" onclick="mswTickAct('lock', '<?php echo $thisTicket_ID; ?>', '');return false;" title="<?php echo mswSH($drop_txt[1]); ?>"><i class="fa fa-lock fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[10]; ?></span></button>
          <?php
          }
          if ($howManyCustomStats > 0) {
          ?>
          <button class="btn btn-info btn-xs" type="button" onclick="iBox.showURL('?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;showStatuses=<?php echo (defined('LOADED_HOME') ? 'home' : 'yes'); ?>','',{width:<?php echo IBOX_STATUSES_WIDTH; ?>,height:<?php echo IBOX_STATUSES_HEIGHT; ?>});return false"><i class="fa fa-crosshairs fa-fw"></i><span class="hidden-xs">  <?php echo $msgloballang4_3[6]; ?></span></button>
          <?php
          }
          break;
        case 'close':
        case 'cdisputes':
          $drop_txt = array(
            $msadminlang3_1adminviewticket[5]
          );
          ?>
          <button class="btn btn-info btn-xs" type="button" onclick="mswTickAct('open', '<?php echo $thisTicket_ID; ?>', '');return false;" title="<?php echo mswSH($drop_txt[0]); ?>"><i class="fa fa-unlock fa-fw"></i><span class="hidden-xs"> <?php echo $msadminlang_tickets_3_7[8]; ?></span></button>
          <?php
          break;
        case 'spam':
          break;
        case 'assign':
          break;
        case 'search':
        case 'search-fields':
          switch($TICKETS->ticketStatus) {
            case 'open':
            default:
              $drop_txt = array(
                $msadminlang3_1adminviewticket[1],
                $msadminlang3_1adminviewticket[0]
              );
              if (USER_CLOSE_PRIV == 'yes') {
              ?>
              <button class="btn btn-danger btn-xs search_btn_close" type="button" onclick="mswTickAct('close', '<?php echo $thisTicket_ID; ?>', 'search');return false;" title="<?php echo mswSH($drop_txt[0]); ?>"><i class="fa fa-minus-square fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[9]; ?></span></button>
              <?php
              }
              if (USER_LOCK_PRIV == 'yes') {
              ?>
              <button class="btn btn-danger btn-xs search_btn_close" type="button" onclick="mswTickAct('lock', '<?php echo $thisTicket_ID; ?>', 'search');return false;" title="<?php echo mswSH($drop_txt[1]); ?>"><i class="fa fa-lock fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[10]; ?></span></button>
              <?php
              }
              if ($howManyCustomStats > 0) {
              ?>
              <button class="btn btn-info btn-xs search_btn_close" type="button" onclick="iBox.showURL('?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;showStatuses=search','',{width:<?php echo IBOX_STATUSES_WIDTH; ?>,height:<?php echo IBOX_STATUSES_HEIGHT; ?>});return false"><i class="fa fa-crosshairs fa-fw"></i><span class="hidden-xs">  <?php echo $msgloballang4_3[6]; ?></span></button>
              <?php
              }
              break;
            case 'close':
            case 'closed':
              $drop_txt = array(
                $msadminlang3_1adminviewticket[5]
              );
              ?>
              <button class="btn btn-info btn-xs search_btn_open" type="button" onclick="mswTickAct('open', '<?php echo $thisTicket_ID; ?>', 'search');return false;" title="<?php echo mswSH($drop_txt[0]); ?>"><i class="fa fa-unlock fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[8]; ?></span></button>
              <?php
              break;
          }
          break;
      }
    }
    if (in_array($cmd, $userAccess) || USER_ADMINISTRATOR == 'yes') {
    ?>
    <button class="btn btn-danger btn-xs spambutton" type="button" onclick="mswTickAct('spam', '<?php echo $thisTicket_ID; ?>', '');return false;" title="<?php echo mswSH($msadminlang3_7[7]); ?>"><i class="fa fa-cutlery fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang3_7[7]; ?></span></button>
    <?php
    }
    ?>
    <a class="btn btn-default btn-xs printbutton" href="?p=view-ticket&amp;print=<?php echo $TICKETS->ticketID; ?>" onclick="window.open(this);return false" style="padding:2px 5px 2px 5px" title="<?php echo mswSH($msadminlang3_7[1]); ?>"><i class="fa fa-print fa-fw"></i><span class="hidden-xs"> <?php echo $msadminlang_tickets_3_7[11]; ?></span></a>
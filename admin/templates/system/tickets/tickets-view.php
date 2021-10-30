<?php if (!defined('PARENT') || !isset($_GET['id'])) { exit; }
define('TICKET_LOADER',1);
define('TICKET_TYPE','ticket');
$tickID = (int) $_GET['id'];
if ($tickID == 0) { exit; }
mswVLQY($SUPTICK);
$tickDept = mswSQL_table('departments','id', $SUPTICK->department);
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
      <li class="active"><?php echo $title; ?></li>
    </ol>

    <?php
    if (defined('TICKET_TEAM_LOCK')) {
      $LK_STF = mswSQL_table('users', 'id', $SUPTICK->lockteam);
      if (isset($LK_STF->name)) {
        ?>
        <div class="alert alert-danger alert-dismissable border_2x">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
          <b><i class="fa fa-lock fa-fw"></i></b> <?php echo str_replace('{staff}', mswSH($LK_STF->name), $msadminlang_tickets_3_7[5]); ?>
        </div>
        <?php
      }
    }

    if (isset($actionMsg)) {
    ?>
    <div class="alert alert-warning alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
      <b><i class="fa fa-check fa-fw"></i></b> <?php echo $actionMsg; ?>
    </div>
    <?php
    }

    if (!defined('TICKET_TEAM_LOCK')) {
    ?>
    <div class="row ticketbuttonarea">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10 text-right">
        <?php
        // Is notepad available..
        if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
        ?>
        <button class="btn btn-success btn-sm" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $tickID; ?>&amp;editNotes=yes','',{width:<?php echo IBOX_NOTES_WIDTH; ?>,height:<?php echo IBOX_NOTES_HEIGHT; ?>});return false"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket54; ?></span></button>
        <?php
        }
        // For small screen devices, we hide the filters to give us more screen space..
        if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
        ?>
        <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
        <div class="hidetkfltrs" style="display:none">
        <hr>
        <?php
        }
        // Assigned..
        if ($tickDept->manual_assign == 'yes' && $SUPTICK->assignedto != 'waiting') {
          $atlinks = array();
          foreach (explode(',',$SUPTICK->assignedto) AS $assToK) {
            $thisAssignPerson = mswSQL_table('users','id', (int) $assToK);
            if (isset($thisAssignPerson->name)) {
              $atlinks[] = array(
                'link' => (in_array('teamman',$userAccess) || USER_ADMINISTRATOR == 'yes' ? '?p=team&amp;edit=' . $assToK : '#'),
                'icon' => '<i class="fa fa-check fa-fw highlight_icon"></i> ',
                'name' => $thisAssignPerson->name
              );
            }
          }
          if (empty($atlinks)) {
            $atlinks[] = array(
              'link' => '#',
              'name' => $msadminlang3_1adminviewticket[23]
            );
          }
          if (USER_EDIT_T_PRIV == 'yes') {
            $atlinks[] = array(
              'link' => 'sep',
              'name' => ''
            );
            $atlinks[] = array(
              'link' => '?p=edit-ticket&amp;id=' . $tickID,
              'icon' => '<i class="fa fa-users fa-fw highlight_icon"></i> ',
              'name' => $msadminlang3_1adminviewticket[21]
            );
          }
          echo $MSBOOTSTRAP->button(array(
            'text' => $msadminlang3_1adminviewticket[22],
            'links' => $atlinks,
            'orientation' => ' dropdown-menu-right',
            'centered' => 'yes',
            'area' => 'admin',
            'icon' => 'users'
          ));
        }
        // Actions..
        if (in_array($SUPTICK->ticketStatus, array('close','closed'))) {
          $links = array(
            array(
              'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=reopen',
              'name' => $msadminlang3_1adminviewticket[5],
              'icon' => '<i class="fa fa-unlock fa-fw highlight_icon"></i> ',
              'extra' => 'onclick="mswLinkOp(this.href);return false;"'
            )
          );
          $links[] = array(
            'link' => '?p=view-ticket&amp;print=' . $tickID,
            'name' => $msadminlang3_7[1],
            'icon' => '<i class="fa fa-print fa-fw highlight_icon"></i> ',
            'extra' => 'onclick="window.open(this);return false"'
          );
        } else {
          if ($SUPTICK->spamFlag == 'no') {
            $links = array(
              array(
                'link' => '#',
                'name' => $msg_viewticket75,
                'extra' => 'onclick="mswScrollToArea(\'replyArea\', \'0\', \'0\');return false"',
                'icon' => '<i class="fa fa-plus fa-fw highlight_icon"></i> '
              )
            );
          }
          if (USER_EDIT_T_PRIV == 'yes') {
            $links[] = array(
              'link' => '?p=edit-ticket&amp;id=' . $tickID,
              'icon' => '<i class="fa fa-pencil fa-fw highlight_icon"></i> ',
              'name' => $msg_viewticket120
            );
          }
          $links[] = array(
            'link' => '?p=view-ticket&amp;print=' . $tickID,
            'name' => $msadminlang3_7[1],
            'icon' => '<i class="fa fa-print fa-fw highlight_icon"></i> ',
            'extra' => 'onclick="window.open(this);return false"'
          );
          if ($SUPTICK->spamFlag == 'no') {
            if (USER_CLOSE_PRIV == 'yes' || USER_LOCK_PRIV == 'lock') {
              $links[] = array(
                'link' => 'sep'
              );
              if (USER_CLOSE_PRIV == 'yes') {
                $links[] = array(
                  'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=close',
                  'name' => $msadminlang3_1adminviewticket[1],
                  'icon' => '<i class="fa fa-minus-square fa-fw highlight_icon"></i> ',
                  'extra' => 'onclick="mswLinkOp(this.href);return false;"'
                );
              }
              if (USER_LOCK_PRIV == 'yes') {
                $links[] = array(
                  'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=lock',
                  'name' => '&nbsp;' . $msadminlang3_1adminviewticket[0],
                  'icon' => '<i class="fa fa-lock fa-fw highlight_icon"></i>',
                  'extra' => 'onclick="mswLinkOp(this.href);return false;"'
                );
              }
              // Other statuses?
              if ($howManyCustomStats > 3) {
                $links[] = array(
                  'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=lock',
                  'name' => '&nbsp;' . $msticketstatuses4_3[12],
                  'icon' => '<i class="fa fa-crosshairs fa-fw highlight_icon"></i>',
                  'extra' => 'onclick="iBox.showURL(\'?p=view-ticket&amp;id=' . $tickID. ' &amp;showStatuses=yes\',\'\',{width:' . IBOX_STATUSES_WIDTH . ',height:' . IBOX_STATUSES_HEIGHT . '});return false"'
                );
              }
              if (in_array('spam', $userAccess) || USER_ADMINISTRATOR == 'yes') {
                $links[] = array(
                  'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=to-spam',
                  'name' => $msadminlang3_7[7],
                  'icon' => '<i class="fa fa-cutlery fa-fw highlight_icon"></i> ',
                  'extra' => 'onclick="mswLinkOp(this.href);return false;"'
                );
              }
            }
          } else {
            if (USER_DEL_PRIV == 'yes') {
              $links[] = array(
                'link' => 'sep'
              );
              $links[] = array(
                'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=spam-del',
                'name' => $msadminlang_tickets_3_7[3],
                'icon' => '<i class="fa fa-times fa-fw ms_red"></i> ',
                'extra' => 'onclick="mswLinkOp(this.href);return false;"'
              );
            }
          }
          if ($SUPTICK->spamFlag == 'no') {
            if ($SETTINGS->disputes == 'yes') {
              $links[] = array('link' => 'sep');
              $links[] = array(
                'link' => '?p=view-ticket&amp;id=' . $tickID . '&amp;act=dispute',
                'name' => $msg_disputes3,
                'icon' => '<i class="fa fa-bullhorn fa-fw highlight_icon"></i> ',
                'extra' => 'onclick="mswLinkOp(this.href);return false;"'
              );
            }
          }
        }
        echo $MSBOOTSTRAP->button(array(
          'text' => $msg_script43,
          'links' => $links,
          'orientation' => ' dropdown-menu-right',
          'centered' => 'no',
          'area' => 'admin',
          'icon' => 'cog',
          'id' => ' id="msw_admin_add_tick_menu"'
        ));
        if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
        ?>
        </div>
        <?php
        }
        ?>
      </div>
    </div>
    <?php
    }
    ?>

    <form method="post" action="index.php?ajax=tickreply" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading colorchangeheader left-align startheader">
            <i class="fa fa-user fa-fw"></i> <?php echo mswSH($SUPTICK->name); ?>
            <p class="tdatebar">
              <?php echo mswSH($SUPTICK->email); ?><br>
              <?php echo $MSDT->mswDateTimeDisplay($SUPTICK->ts,$SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($SUPTICK->ts,$SETTINGS->timeformat); ?>
            </p>
            <?php
            if ($SETTINGS->timetrack == 'yes') {
            ?>
            <span class="worktime">
              <i class="fa fa-clock-o fa-fw"></i> <?php echo $msadminlang_tickets_3_7[18] . ': ' . $MSDT->worktime(($SUPTICK->worktime ? $SUPTICK->worktime : '00:00:00'), $msadminlang_tickets_3_7[19]); ?>
            </span>
            <?php
            }
            ?>
          </div>
          <div class="panel-body margin_top_10" id="tk<?php echo $SUPTICK->id; ?>">
            <span class="ticketsubject"><i class="fa fa-commenting-o fa-fw"></i> <?php echo mswSH($SUPTICK->subject); ?></span>
            <hr>
            <?php
            echo $MSPARSER->mswTxtParsingEngine($SUPTICK->comments);
            $dRepID   = 0;
            $toggleID = 'tk' . $SUPTICK->id;
            $label    = 'panel panel-default';
            include(PATH . 'templates/system/tickets/tickets-view-data-area.php');
            ?>
          </div>
          <div class="panel-footer">
           <span class="pull-right">
             <?php echo loadIPAddresses($SUPTICK->ipAddresses); ?>
           </span>
           <?php echo $MSYS->levels($SUPTICK->priority); ?> <i class="fa fa-angle-right fa-fw hidden-xs"></i>
           <span class="mobilebreakpoint"><?php echo $MSYS->department($SUPTICK->department,$msg_script30); ?></span>
          </div>
        </div>

        <?php
        include(PATH . 'templates/system/tickets/tickets-view-replies.php');
        ?>

      </div>
    </div>

    <?php
    include(PATH . 'templates/system/tickets/tickets-view-reply-area.php');
    ?>

    <input type="hidden" name="isDisputed" value="no">
    <?php
    if (USER_ADMINISTRATOR != 'no') {
    ?>
    <input type="hidden" name="history" value="yes">
    <?php
    }
    ?>
    </form>

  </div>
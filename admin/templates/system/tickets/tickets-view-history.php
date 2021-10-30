<?php if (!defined('TICKET_LOADER') || !isset($tickID)) { exit; }
    // Show ticket history..
	  if ($SETTINGS->ticketHistory == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->ticketHistory == 'yes')) {
	  $qTH = mswSQL_query("SELECT *,
           `" . DB_PREFIX . "tickethistory`.`id` AS `historyID`,
           `" . DB_PREFIX . "tickethistory`.`ts` AS `historyTS`
           FROM `" . DB_PREFIX . "tickethistory`
           LEFT JOIN `" . DB_PREFIX . "users`
           ON `" . DB_PREFIX . "tickethistory`.`staff` = `" . DB_PREFIX . "users`.`id`
           WHERE `" . DB_PREFIX . "tickethistory`.`ticketID` = '{$tickID}'
           ORDER BY `" . DB_PREFIX . "tickethistory`.`ts` DESC
           ", __file__, __line__);
	  $historyRows = mswSQL_numrows($qTH);
    ?>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
      <div class="panel panel-default history-panel">
        <div class="panel-heading colorchangeheader text-right right-align">
          <span class="pull-left margin_top_7">
           <i class="fa fa-clock-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo strtoupper($msg_viewticket110) . '</span> ( #' . mswTicketNumber($tickID, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?> )
          </span>
          <?php
          $links = array();
          if (!defined('TICKET_TEAM_LOCK')) {
            $links[] = array(
              'link' => '#',
              'name' => $msadminlang3_7[2],
              'icon' => '<i class="fa fa-plus fa-fw highlight_icon"></i> ',
              'extra' => 'onclick="iBox.showURL(\'?p=view-ticket&amp;addHis=' . $tickID . '\',\'\',{width:' . IBOX_NOTES_WIDTH . ',height:' . IBOX_NOTES_HEIGHT . '});return false"'
            );
          }
          $links[] = array(
            'link' => '#',
            'name' => $msg_viewticket112,
            'icon' => '<i class="fa fa-save fa-fw highlight_icon"></i> ',
            'extra' => 'onclick="mswProcess(\'tickhisexp\',\'' . $tickID . '\');return false"'
          );
          if (USER_DEL_PRIV == 'yes' && !defined('TICKET_TEAM_LOCK')) {
            $links[] = array(
              'link' => 'sep'
            );
            $links[] = array(
              'link' => '?p=view-ticket&amp;exportHistory=' . $tickID,
              'name' => $msg_viewticket118,
              'icon' => '<i class="fa fa-times fa-fw ms_red"></i> ',
              'extra' => 'onclick="mswRemoveHistory(\'all\', \'' . $tickID . '\');return false"'
            );
          }
          echo $MSBOOTSTRAP->button(array(
            'text' => $msg_script43,
            'links' => $links,
            'orientation' => ' dropdown-menu-right',
            'centered' => 'no',
            'area' => 'admin',
            'icon' => 'cog',
            'no-mobile' => 'yes'
          ));
          ?>
        </div>
        <div class="panel-body historybody">

          <?php
          if ($historyRows > 0) {
          ?>
          <div class="table-responsive historyarea">
            <table class="table table-striped table-hover">
            <tbody>
              <?php
              while ($HIS = mswSQL_fetchobj($qTH)) {
              ?>
              <tr id="hdata_<?php echo $HIS->historyID; ?>">
                <td><?php echo $MSDT->mswDateTimeDisplay($HIS->historyTS, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($HIS->historyTS, $SETTINGS->timeformat) . '<span class="tdCellInfo">' . ($HIS->staff > 0 && $HIS->name ? $HIS->name : $msadminlang3_7[4]) . ($HIS->ip ? ' / ' . loadIPAddresses($HIS->ip) . '' : ''); ?></span></td>
                <td><?php echo mswSH($HIS->action); ?></td>
                <?php
                if (USER_DEL_PRIV == 'yes' && !defined('TICKET_TEAM_LOCK')) {
                ?>
                <td class="text-right"><i class="fa fa-times fa-fw ms_red cursor_pointer" onclick="mswRemoveHistory('<?php echo $HIS->historyID; ?>', '<?php echo $tickID; ?>')" title="<?php echo mswSH($msg_public_history12); ?>"></i></td>
                <?php
                }
                ?>
              </tr>
              <?php
              }
              ?>
            </tbody>
            </table>
          </div>
          <?php
          } else {
          ?>
          <div class="nothing_to_see"><?php echo $msg_viewticket111; ?></div>
          <?php
          }
          ?>

        </div>
      </div>
    </div>
    <?php
    }
    ?>



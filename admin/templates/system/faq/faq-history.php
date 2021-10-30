<?php if (!defined('FAQ_HIS_LOADER')) { exit; }
    // Show faq history..
	  if ($SETTINGS->faqHistory == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->faqHistory == 'yes')) {
	  $qTH = mswSQL_query("SELECT *,
           `" . DB_PREFIX . "faqhistory`.`id` AS `historyID`,
           `" . DB_PREFIX . "faqhistory`.`ts` AS `historyTS`
           FROM `" . DB_PREFIX . "faqhistory`
           WHERE `" . DB_PREFIX . "faqhistory`.`faqID` = '{$EDIT->id}'
           ORDER BY `" . DB_PREFIX . "faqhistory`.`ts` DESC
           ", __file__, __line__);
	  $historyRows = mswSQL_numrows($qTH);
    ?>
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default history-panel">
          <div class="panel-heading colorchangeheader text-right right-align">
            <span class="pull-left margin_top_7">
             <i class="fa fa-clock-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo strtoupper($msfaq4_3[0]); ?></span>
            </span>
            <?php
            $links = array();
            $links[] = array(
              'link' => '#',
              'name' => $msg_viewticket112,
              'icon' => '<i class="fa fa-save fa-fw highlight_icon"></i> ',
              'extra' => 'onclick="mswProcess(\'faqhisexp\',\'' . $EDIT->id . '\');return false"'
            );
            if (USER_DEL_PRIV == 'yes') {
              $links[] = array(
                'link' => 'sep'
              );
              $links[] = array(
                'link' => '?p=faq&amp;exportHistory=' . $EDIT->id,
                'name' => $msg_viewticket118,
                'icon' => '<i class="fa fa-times fa-fw ms_red"></i> ',
                'extra' => 'onclick="mswRemoveFAQHistory(\'all\', \'' . $EDIT->id . '\');return false"'
              );
            }
            if ($historyRows > 0) {
              echo $MSBOOTSTRAP->button(array(
                'text' => $msg_script43,
                'links' => $links,
                'orientation' => ' dropdown-menu-right',
                'centered' => 'no',
                'area' => 'admin',
                'icon' => 'cog',
                'no-mobile' => 'yes'
              ));
            } else {
              echo '<p style="height:20px"></p>';
            }
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
                  <td><?php echo $MSDT->mswDateTimeDisplay($HIS->historyTS, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($HIS->historyTS, $SETTINGS->timeformat) . '<span class="tdCellInfo">' . ($HIS->ip ? loadIPAddresses($HIS->ip) . '' : ''); ?></span></td>
                  <td><?php echo mswSH($HIS->action); ?></td>
                  <?php
                  if (USER_DEL_PRIV == 'yes' && !defined('TICKET_TEAM_LOCK')) {
                  ?>
                  <td class="text-right"><i class="fa fa-times fa-fw ms_red cursor_pointer" onclick="mswRemoveFAQHistory('<?php echo $HIS->historyID; ?>', '<?php echo $EDIT->id; ?>')" title="<?php echo mswSH($msg_public_history12); ?>"></i></td>
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
            <div class="nothing_to_see"><?php echo $msfaq4_3[1]; ?></div>
            <?php
            }
            ?>

          </div>
        </div>
      </div>
    </div>
    <?php
    }
    ?>



<?php if (!defined('PARENT')) { exit; } ?>
        <div class="windowpanelarea">
        <div class="panel panel-default">
          <div class="panel-heading left-align">
            <i class="fa fa-lock fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[10]; ?>
          </div>
          <div class="panel-body text_height_25 ticketlockarea">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <tbody>
                <?php
                $q = mswSQL_query("SELECT *,
                     (SELECT `name` FROM `" . DB_PREFIX . "users`
                      WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
                     ) AS `lockTeamName`
                     FROM `" . DB_PREFIX . "tickets`
                     WHERE `lockteam` > 0
                     ORDER BY `ts` DESC
                     ", __file__, __line__);
                if (mswSQL_numrows($q) > 0) {
                while ($TICKETS = mswSQL_fetchobj($q)) {
                ?>
                <tr id="tlk_<?php echo $TICKETS->id; ?>">
                  <td><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->id; ?>" title="<?php echo mswSH($msg_open7); ?>">#<?php echo mswTicketNumber($TICKETS->id, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a></td>
                  <td><?php echo ($TICKETS->lockTeamName ? mswSH($TICKETS->lockTeamName) : $msg_script17); ?></td>
                  <td class="text-right"><a href="#" onclick="mswRelStaffLock('<?php echo $TICKETS->id; ?>')"><?php echo $msadminlang_dashboard_3_7[11]; ?></a></td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr><td class="text-center nothing_to_see"><?php echo $msadminlang_dashboard_3_7[12]; ?></td></tr>
                <?php
                }
                ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
        </div>
<?php if (!defined('PARENT')) { exit; } ?>

      <div class="fluid-container windowpanelarea">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-tags fa-fw"></i> <?php echo strtoupper($msg_tools22); ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <tbody>
                <?php
                foreach (array(
                  '{ACC_NAME}' => $msdept4_3[3],
                  '{ACC_EMAIL}' => $msdept4_3[4],
                  '{SUBJECT}' => $msdept4_3[5],
                  '{TICKET}' => $msdept4_3[6],
                  '{DEPT}' => $msdept4_3[7],
                  '{PRIORITY}' => $msdept4_3[8],
                  '{STATUS}' => $msdept4_3[9],
                  '{COMMENTS}' => $msdept4_3[10],
                  '{ATTACHMENTS}' => $msdept4_3[11],
                  '{CUSTOM}' => $msdept4_3[12],
                  '{ID}' => $msdept4_3[13]
                ) AS $k => $v) {
                  ?>
                  <tr>
                    <td><?php echo $k; ?></td>
                    <td class="text-right"><?php echo $v; ?></td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
		  </div>

<?php if (!defined('PARENT') || !isset($_GET['pr_acc'])) { exit; } ?>

      <div class="fluid-container">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-user fa-fw"></i> <?php echo strtoupper($msadminlang_faq_3_7[4]); ?>
          </div>
          <div class="panel-body">
            <div class="catrestrictions">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                <tbody>
                  <?php
                  $CT = mswSQL_table('categories', 'id', (int) $_GET['pr_acc']);
                  if (isset($CT->accounts)) {
                    $q = mswSQL_query("SELECT `name`, `email` FROM `" . DB_PREFIX . "portal`
                         WHERE `id` IN(" . mswSQL(($CT->accounts ? $CT->accounts : '0'))  . ")
                         ORDER BY `name`
                         ", __file__, __line__);
                    if (mswSQL_numrows($q) > 0) {
                      while ($ACC = mswSQL_fetchobj($q)) {
                        ?>
                        <tr>
                          <td><b><?php echo mswSH($ACC->name); ?></b></td>
                          <td class="text-right"><?php echo mswSH($ACC->email); ?></td>
                        </tr>
                        <?php
                      }
                    } else {
                      ?>
                      <tr>
                        <td style="text-align:center" class="ms_red"><?php echo $msadminlang4_3[15]; ?></td>
                      </tr>
                      <?php
                    }
                  } else {
                    ?>
                    <tr>
                      <td><i class="fa fa-warning fa-fw"></i> <?php echo $msadminlang4_3[15]; ?></td>
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
		  </div>

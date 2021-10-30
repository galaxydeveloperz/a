<?php if (!defined('PATH')) { exit; } ?>

    <nav class="pushy pushy-left">
      <div class="pushy-content">

        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
          <?php

          /* ACCOUNT OPTIONS IF LOGGED IN
          -------------------------------------------------------*/

          if ($this->LOGGED_IN == 'yes') {
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading_mn1">
              <h4 class="panel-title">
                <a<?php echo ($this->OFF_CANVAS_PANEL_STATE != 'mn1' ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_mn1" aria-expanded="<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'mn1' ? 'true' : 'false'); ?>" onclick="mswPanel('mn1')" aria-controls="collapse_mn1" title="<?php echo mswSH($this->PB_LNG[0][0]); ?>">
                  <i class="fa fa-address-card-o fa-fw"></i> <?php echo $this->PB_LNG[0][0]; ?>
                </a>
              </h4>
            </div>
            <div id="collapse_mn1" class="panel-collapse collapse<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'mn1' ? ' in' : ' '); ?>" role="tabpanel" aria-labelledby="heading_mn1">
              <div class="panel-body linkbodyarea">
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open"><i class="fa fa-pencil fa-fw"></i> <?php echo $this->PB_TXT_LNG[0]; ?></a></div>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=profile"><i class="fa fa-user fa-fw"></i> <?php echo $this->PB_TXT_LNG[3]; ?></a></div>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=history"><i class="fa fa-calendar fa-fw"></i> <?php echo $this->PB_TXT_LNG[1]; ?></a></div>
                <?php
                // Is the dispute system enabled?
                if ($this->SETTINGS->disputes == 'yes') {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=disputes"><i class="fa fa-bullhorn fa-fw"></i> <?php echo $this->PB_TXT_LNG[4]; ?></a></div>
                <?php
                }
                // Can visitor close account?
                if ($this->SETTINGS->visclose == 'yes') {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=close"><i class="fa fa-times fa-fw"></i> <?php echo $this->PB_TXT_LNG[5]; ?></a></div>
                <?php
                }
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?lo=1"><i class="fa fa-unlock fa-fw"></i> <?php echo $this->PB_TXT_LNG[2]; ?></a></div>
              </div>
            </div>
          </div>
          <?php
          }

          /* FAQ CATEGORIES
          ------------------------------------------------------------*/

          if ($this->CATEGORIES_MENU) {
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading_ct1">
              <h4 class="panel-title">
                <a<?php echo ($this->OFF_CANVAS_PANEL_STATE != 'ct1' ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_ct1" aria-expanded="<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'ct1' ? 'true' : 'false'); ?>" onclick="mswPanel('ct1')" aria-controls="collapse_ct1" title="<?php echo mswSH($this->PB_LNG[2]); ?>">
                  <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->PB_LNG[2]; ?>
                </a>
              </h4>
            </div>
            <div id="collapse_ct1" class="panel-collapse collapse<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'ct1' ? ' in' : ' '); ?>" role="tabpanel" aria-labelledby="heading_ct1">
              <div class="panel-body linkbodyarea">
                <?php
                foreach ($this->CATEGORIES_MENU AS $cmnu) {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $cmnu['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $cmnu['name']; ?></a><?php echo $cmnu['count']; ?></div>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <?php
          }

          /* FAQ PRIVATE CATEGORIES
          ------------------------------------------------------------*/

          if ($this->LOGGED_IN == 'yes' && $this->PRIVATE_CATEGORIES_MENU) {
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading_pct1">
              <h4 class="panel-title">
                <a<?php echo ($this->OFF_CANVAS_PANEL_STATE != 'pct1' ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_pct1" aria-expanded="<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pct1' ? 'true' : 'false'); ?>" onclick="mswPanel('pct1')" aria-controls="collapse_pct1" title="<?php echo mswSH($this->PB_LNG[0][1]); ?>">
                  <i class="fa fa-folder fa-fw"></i> <?php echo $this->PB_LNG[0][1]; ?>
                </a>
              </h4>
            </div>
            <div id="collapse_pct1" class="panel-collapse collapse<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pct1' ? ' in' : ' '); ?>" role="tabpanel" aria-labelledby="heading_pct1">
              <div class="panel-body linkbodyarea">
                <?php
                foreach ($this->PRIVATE_CATEGORIES_MENU AS $pcmnu) {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $pcmnu['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $pcmnu['name']; ?></a><?php echo $pcmnu['count']; ?></div>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <?php
          }

          /* CUSTOM PAGES
          -------------------------------------------------------------*/

          if (!empty($this->OTHER_PAGES_MENU)) {
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading_pg1">
              <h4 class="panel-title">
                <a<?php echo ($this->OFF_CANVAS_PANEL_STATE != 'pg1' ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_pg1" aria-expanded="<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pg1' ? 'true' : 'false'); ?>" onclick="mswPanel('pg1')" aria-controls="collapse_pg1" title="<?php echo mswSH($this->PB_LNG[1]); ?>">
                  <i class="fa fa-file-text-o fa-fw"></i> <?php echo $this->PB_LNG[1]; ?>
                </a>
              </h4>
            </div>
            <div id="collapse_pg1" class="panel-collapse collapse<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pg1' ? ' in' : ' '); ?>" role="tabpanel" aria-labelledby="heading_pg1">
              <div class="panel-body linkbodyarea">
                <?php
                foreach ($this->OTHER_PAGES_MENU AS $opg) {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?pg=<?php echo $opg['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $opg['name']; ?></a></div>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <?php
          }

          /* CUSTOM PRIVATE PAGES
          -------------------------------------------------------------*/

          if ($this->LOGGED_IN == 'yes' && !empty($this->PRIVATE_PAGES_MENU)) {
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading_pgp1">
              <h4 class="panel-title">
                <a<?php echo ($this->OFF_CANVAS_PANEL_STATE != 'pgp1' ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_pgp1" aria-expanded="<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pgp1' ? 'true' : 'false'); ?>" onclick="mswPanel('pgp1')" aria-controls="collapse_pgp1" title="<?php echo mswSH($this->PB_LNG[0][2]); ?>">
                  <i class="fa fa-file-text fa-fw"></i> <?php echo $this->PB_LNG[0][2]; ?>
                </a>
              </h4>
            </div>
            <div id="collapse_pgp1" class="panel-collapse collapse<?php echo ($this->OFF_CANVAS_PANEL_STATE == 'pgp1' ? ' in' : ' '); ?>" role="tabpanel" aria-labelledby="heading_pgp1">
              <div class="panel-body linkbodyarea">
                <?php
                foreach ($this->PRIVATE_PAGES_MENU AS $opg) {
                ?>
                <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?pg=<?php echo $opg['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $opg['name']; ?></a></div>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <?php
          }
          ?>
        </div>

      </div>
    </nav>
    <div class="site-overlay"></div>
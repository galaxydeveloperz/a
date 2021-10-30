<?php if (!defined('PATH')) { exit; } ?>
           <div class="btn-group">
              <button class="btn btn-primary btn-sm" type="button"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[10] : $this->TXT[9]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-sort fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
               <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
              <?php
              foreach ($this->DD_ORDER AS $fk1 => $fv1) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;order=<?php echo $fk1 . mswQueryParams(array('p','order','next')); ?>"><?php echo $fv1; ?></a></li>
              <?php
              }
              ?>
              </ul>
            </div>

            <div class="btn-group">
              <button class="btn btn-primary btn-sm"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[11] : $this->TXT[10]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-filter fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam.mswQueryParams(array('p','filter','next')); ?>"><?php echo ($this->IS_DISPUTED=='yes' ? $this->TXT[14] : $this->TXT[13]); ?></a></li>
              <?php
              foreach ($this->DD_FILTERS AS $fk2 => $fv2) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;filter=<?php echo $fk2 . mswQueryParams(array('p','filter','next')); ?>"><?php echo $fv2; ?></a></li>
              <?php
              }
              ?>
              </ul>
            </div>

            <div class="btn-group">
              <button class="btn btn-primary btn-sm"><span class="hidden-xs"><?php echo ($this->IS_DISPUTED=='yes' ? $this->TXT[12] : $this->TXT[11]); ?></span><span class="hidden-sm hidden-md hidden-lg"><i class="fa fa-filter fa-fw"></i></span></button>
              <button class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam.mswQueryParams(array('p','dept','next')); ?>"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[13] : $this->TXT[12]); ?></a></li>
              <?php
              foreach ($this->DD_DEPT AS $fk3 => $fv3) {
              ?>
              <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=<?php echo $pageParam; ?>&amp;dept=<?php echo $fk3 . mswQueryParams(array('p','dept','next')); ?>"><?php echo $fv3; ?></a></li>
              <?php
               }
              ?>
             </ul>
            </div>
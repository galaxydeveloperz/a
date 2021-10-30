<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->TXT[0]; ?></a></li>
      <li class="active"><?php echo mswCD($this->CPAGE->title); ?></li>
    </ol>

    <div class="row">
      <div class="col-lg-8">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            echo $this->CPAGE_TXT;
            ?>
          </div>
        </div>

      </div>
      <div class="col-lg-4">

        <?php
        if (!empty($this->OTHER_PAGES_MENU)) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-file-text-o fa-fw"></i> <?php echo $this->PB_LNG[1]; ?>
          </div>
          <div class="panel-body text_height_25" id="mswfaqcatarea">

            <?php
            foreach ($this->OTHER_PAGES_MENU AS $opg) {
            ?>
            <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?pg=<?php echo $opg['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $opg['name']; ?></a></div>
            <?php
            }
            ?>

          </div>
        </div>
        <?php
        }

        if (!empty($this->PRIVATE_PAGES_MENU)) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-file-text fa-fw"></i> <?php echo $this->PB_LNG[0][2]; ?>
          </div>
          <div class="panel-body text_height_25" id="mswfaqcatarea">

            <?php
            foreach ($this->PRIVATE_PAGES_MENU AS $opg) {
            ?>
            <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?pg=<?php echo $opg['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $opg['name']; ?></a></div>
            <?php
            }
            ?>

          </div>
        </div>
        <?php
        }
        ?>

      </div>
    </div>

  </div>
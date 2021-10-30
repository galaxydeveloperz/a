<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->TXT[0]; ?></a></li>
      <?php
      // Is this a sub category?
      if (isset($this->SUB['id'])) {
      ?>
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->SUB['id']; ?>"><?php echo mswCD($this->SUB['name']); ?></a></li>
      <li class="active"><?php echo mswCD($this->PARENT['name']); ?></li>
      <?php
      } else {
      ?>
      <li class="active"><?php echo mswCD($this->PARENT['name']); ?></li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Show summary..
    if ($this->PARENT['summary']) {
    ?>
    <div class="well well-sm">
      <?php
      echo mswSH($this->PARENT['summary']);
      ?>
    </div>
    <?php
    }
    ?>

    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/" id="sform">
    <div class="panel panel-default">
      <div class="panel-body" style="padding-bottom:0">
        <div class="form-group">
          <div class="form-group input-group">
            <input type="hidden" name="p" value="faq-search">
            <input type="text" placeholder="<?php echo $this->TXT[3]; ?>" name="q" value="" class="form-control">
            <span class="input-group-addon"><i class="fa fa-search fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-file-text-o fa-fw"></i> <?php echo $this->TXT[4]; ?>
          </div>
          <div class="panel-body faqquestionwrapper">
            <?php
            // QUESTIONS FOR THIS CATEGORY
            // html/faq-question-link.htm
            // html/nothing-found.htm
            echo $this->FAQ;
            ?>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-folder-o fa-fw"></i> <?php echo $this->TXT[8]; ?>
          </div>
          <div class="panel-body">
            <?php
            // RELATED CATEGORIES
            if (!empty($this->RELATED_CATEGORIES)) {
              foreach ($this->RELATED_CATEGORIES AS $cmnu) {
              ?>
              <div><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $cmnu['id']; ?>"><i class="fa fa-angle-right fa-fw"></i> <?php echo $cmnu['name']; ?></a><?php echo $cmnu['count']; ?></div>
              <?php
              }
            } else {
              echo $this->TXT[9];
            }
            ?>
          </div>
        </div>
      </div>
    </div>
    </form>

    <?php
	  // PAGE NUMBERS
    // html/pagination/*
	  if ($this->PAGES) {
	    echo $this->PAGES;
    }
    ?>

  </div>
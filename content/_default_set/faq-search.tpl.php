<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->TXT[0]; ?></a></li>
      <?php
      // Is this a sub category?
      if (isset($this->PARENT['id'])) {
        if (isset($this->SUB['id'])) {
        ?>
        <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->SUB['id']; ?>"><?php echo mswCD($this->SUB['name']); ?></a></li>
        <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->PARENT['id']; ?>"><?php echo mswCD($this->PARENT['name']); ?></a></li>
        <?php
        } else {
        ?>
        <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->PARENT['id']; ?>"><?php echo mswCD($this->PARENT['name']); ?></a></li>
        <?php
        }
      }
      ?>
      <li class="active"><?php echo $this->TXT[4]; ?></li>
    </ol>

    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/" id="sform">
    <div class="panel panel-default">
      <div class="panel-body" style="padding-bottom:0">
        <div class="form-group">
          <div class="form-group input-group">
            <input type="hidden" name="p" value="faq-search">
            <input type="text" placeholder="<?php echo $this->TXT[3]; ?>" name="q" value="<?php echo mswSH($_GET['q']); ?>" class="form-control">
            <span class="input-group-addon"><i class="fa fa-chevron-right fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-file-text-o fa-fw"></i> <?php echo $this->TXT[5]; ?>
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
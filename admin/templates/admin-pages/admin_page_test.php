<?php if (!defined('PARENT')) { exit; }

/* CUSTOM ADMIN PAGE TEMPLATE - EXAMPLE
   You can use any element from the page array:

   print_r($PG)

   Or manually add your own code/info.
-----------------------------------------------*/

?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('apages', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=apages"><?php echo $msadminlang3_1cspages[2]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo mswSH($PG->title); ?></li>
    </ol>
    
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading">
            <?php
            echo mswSH($PG->title);
            ?>
          </div>
          <div class="panel-body">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($PG->information);
            ?>
          </div>
        </div>
      </div>
    </div>

  </div>
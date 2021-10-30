<?php if (!defined('PARENT')) { exit; } ?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li><a href="index.php?p=mailbox"><?php echo $msg_mailbox; ?></a></li>
      <li class="active"><?php echo $msg_adheader61; ?> (<?php echo $msg_mailbox4; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <?php
      include(PATH . 'templates/system/mailbox/mailbox-nav.php');
	    ?>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_mailbox10; ?></label>
                  <input type="text" class="form-control" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" name="subject">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_mailbox7; ?></label>
                  <textarea name="message" rows="5" cols="20" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_mailbox11; ?></label>
                  <div class="checkbox">
                    <label><input type="checkbox" onclick="mswCheckBoxes(this.checked,'.mailStaff');mswCheckCount('mailbox','sendbutton','mswCVal')"> <b><?php echo $msgadminlang3_1mailbox[0]; ?></b></label>
                  </div>
                  <div class="mailStaff">
                  <?php
                  $q = mswSQL_query("SELECT `id`,`name`
                       FROM `" . DB_PREFIX . "users`
                       WHERE `id` != '{$MSTEAM->id}'
                       ORDER BY `name`
                       ", __file__, __line__);
                  while ($STAFF = mswSQL_fetchobj($q)) {
                  ?>
                  <div class="checkbox">
                    <label><input type="checkbox" name="staff[]" value="<?php echo $STAFF->id; ?>" onclick="mswCheckCount('mailbox','sendbutton','mswCVal')"> <?php echo mswSH($STAFF->name); ?></label>
                  </div>
                  <?php
                  }
                  ?>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" onclick="mswProcess('mbcompose')" disabled="disabled" id="sendbutton"><i class="fa fa-envelope fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_mailbox8; ?></span> <span id="mswCVal">(0)</span></button>
          </div>
        </div>
      </div>
    </div>
    </form>

  </div>
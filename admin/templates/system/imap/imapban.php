<?php if (!defined('PARENT')) { exit; }
$filters = '';
$account = 'yes';
$spam = 'no';
$fltrs = array();
$q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "imapban` ORDER BY `id`");
while ($F = mswSQL_fetchobj($q)) {
  if ($F->filter) {
    $fltrs[] = $F->filter;
    $account = $F->account;
    $spam = $F->spam;
  }
}
if (!empty($fltrs)) {
  $filters = implode(mswNL(), $fltrs);
}
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('imapman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=imapman"><?php echo $msadminlang3_1[4]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msadminlang_imap_3_7[0]; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-ban fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang_imap_3_7[0]; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msadminlang_imap_3_7[2]; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="filters" tabindex="<?php echo (++$tabIndex); ?>"><?php echo mswSH(trim($filters)); ?></textarea>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="account" value="yes"<?php echo ($account == 'yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang_imap_3_7[3]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="spam" value="yes"<?php echo ($spam == 'yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang_imap_3_7[4]; ?>
                    </label>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <button class="btn btn-primary" type="button" onclick="mswProcess('imapban')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msadminlang_imap_3_7[1]; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int) $_GET['edit'];
  $EDIT         = mswSQL_table('categories','id',$_GET['edit']);
  mswVLQY($EDIT);
}
define('JS_LOADER', 'faq-cat.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('faq-catman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=faq-catman"><?php echo $msg_adheader45; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_kbasecats5 : $msg_kbase16); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-edit fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_kbase59; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-lock fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang_faq_3_7[0]; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enCat" value="yes"<?php echo (isset($EDIT->enCat) && $EDIT->enCat=='yes' ? ' checked="checked"' : (!isset($EDIT->enCat) ? ' checked="checked"' : '')); ?>> <?php echo $msg_kbase24; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase17; ?></label>
                  <input class="form-control" type="text" name="name" tabindex="<?php echo (++$tabIndex); ?>" maxlength="100" value="<?php echo (isset($EDIT->name) ? mswSH($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase15; ?></label>
                  <input class="form-control" type="text" name="summary" tabindex="<?php echo (++$tabIndex); ?>" maxlength="250" value="<?php echo (isset($EDIT->summary) ? mswSH($EDIT->summary) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase38; ?></label>
                  <select name="subcat" class="form-control" onchange="mswRemPrFlags(this.value)">
                    <option value="0"><?php echo $msg_kbase36; ?></option>
                    <?php
                    $q_cat = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY `name`", __file__, __line__);
                    if (mswSQL_numrows($q_cat)>0) {
                    ?>
                    <optgroup label="<?php echo mswSH($msg_kbase37); ?>">
                    <?php
                    while ($CAT = mswSQL_fetchobj($q_cat)) {
                    ?>
                    <option<?php echo (isset($EDIT->id) ? mswSelectedItem($EDIT->subcat,$CAT->id) : ''); ?> value="<?php echo $CAT->id; ?>"><?php echo mswCD($CAT->name); ?></option>
                    <?php
                    }
                    }
                    ?>
                    </optgroup>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" onclick="mswSecureFlag(this.checked)" name="private" value="yes"<?php echo (isset($EDIT->private) && $EDIT->private=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1faq[2]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang_faq_3_7[1]; ?></label>
                  <?php
                  $dis = 'disabled="disabled"';
                  if (isset($EDIT->private) && $EDIT->private == 'yes' && $EDIT->subcat == 0) {
                    $dis = '';
                  }
                  ?>
                  <input type="text" class="form-control" name="search" <?php echo $dis; ?> tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <?php
                $html = '';
                if (isset($EDIT->accounts) && !in_array($EDIT->accounts, array('','all',null))) {
                  $qA = mswSQL_query("SELECT `id`, `name`, `email` FROM `" . DB_PREFIX . "portal`
                        WHERE `id` IN(" . mswSQL($EDIT->accounts) . ")
                        ORDER BY `name`
                        ", __file__, __line__);
                  while ($ACC = mswSQL_fetchobj($qA)) {
                    $rembox = '<a href="#" onclick="mswRemFltrBox(\'' . $ACC->id . '\');return false"><i class="fa fa-times fa-fw ms_red"></i></a>';
                    $html  .= '<p id="acf_' . $ACC->id . '">' . $rembox . ' <input type="hidden" name="acc[]" value="' . $ACC->id . '">' . mswSH($ACC->name) . ' (' . mswSH($ACC->email) .  ')</p>';
                  }
                }
                ?>
                <div class="accFilterArea"><?php echo $html; ?></div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('faqcat')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_kbasecats5 : $msg_kbase16); ?></span></button>
            <?php
            if (in_array('faq-catman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faq-catman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
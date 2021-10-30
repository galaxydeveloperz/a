<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswSQL_table('levels','id',$_GET['edit']);
  mswVLQY($EDIT);
  $colors = ($EDIT->colors ? unserialize($EDIT->colors) : array());
}

define('JS_LOADER', 'levels.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('levelsman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=levelsman"><?php echo $msg_adheader51; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_levels5 : $msg_adheader50); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-level-up fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_levels25; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_7prlevels[0]; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_levels18; ?></label>
                  <input type="text" class="form-control" maxlength="100" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSH($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="display" value="yes"<?php echo (isset($EDIT->display) && $EDIT->display=='yes' ? ' checked="checked"' : (!isset($EDIT->display) ? ' checked="checked"' : '')); ?>> <?php echo $msg_levels15; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msadminlang3_7prlevels[1]; ?></label>
                  <input type="text" class="form-control colorpicker" name="colors[fg]" maxlength="6" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) && isset($colors['fg']) ? mswSH($colors['fg']) : ''); ?>"<?php echo (isset($EDIT->id) && isset($colors['fg']) ? ' style="background:#' . mswSH($colors['fg']) . '"' : ''); ?>>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_7prlevels[2]; ?></label>
                  <input type="text" class="form-control colorpicker" name="colors[bg]" maxlength="6" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) && isset($colors['bg']) ? mswSH($colors['bg']) : ''); ?>"<?php echo (isset($EDIT->id) && isset($colors['bg']) ? ' style="background:#' . mswSH($colors['bg']) . '"' : ''); ?>>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('levels')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_levels10 : $msg_levels2); ?></span></button>
           <?php
           if (in_array('levelsman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=levelsman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
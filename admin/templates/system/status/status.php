<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswSQL_table('statuses','id',$_GET['edit']);
  mswVLQY($EDIT);
  $colors = ($EDIT->colors ? unserialize($EDIT->colors) : array());
}

define('JS_LOADER', 'levels.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('statusman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=statusman"><?php echo $msticketstatuses4_3[2]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msticketstatuses4_3[2] : $msticketstatuses4_3[1]); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-info fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msticketstatuses4_3[4]; ?></span></a></li>
          <!--<li><a href="#two" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_7prlevels[0]; ?></span></a></li>-->
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msticketstatuses4_3[5]; ?></label>
                  <input type="text" class="form-control" maxlength="100" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSH($EDIT->name) : ''); ?>">
                </div>
                
                <?php
                if (!isset($EDIT->id) || (isset($EDIT->id) && !in_array($EDIT->id, array(1,2,3)))) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="visitor" value="yes"<?php echo (isset($EDIT->visitor) && $EDIT->visitor=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msticketstatuses4_3[13]; ?>
                    </label>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="autoclose" value="yes"<?php echo (isset($EDIT->autoclose) && $EDIT->autoclose=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msticketstatuses4_3[14]; ?>
                    </label>
                  </div>
                </div>
                <?php
                }
                ?>

              </div>
              <!--
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
              -->
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('status')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msticketstatuses4_3[3] : $msticketstatuses4_3[1]); ?></span></button>
           <?php
           if (in_array('statusman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=statusman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
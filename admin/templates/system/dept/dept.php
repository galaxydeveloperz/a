<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int)$_GET['edit'];
  $EDIT         = mswSQL_table('departments','id',$_GET['edit']);
  mswVLQY($EDIT);
  $dayEn        = ($EDIT->days ? explode(',', $EDIT->days) : array());
}
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('deptman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=deptman"><?php echo $msg_dept9; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_dept5 : $msg_dept2); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_dept24; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-sign-in fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_dept25; ?></span></a></li>
	        <li><a href="#three" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1dept[1]; ?></span></a></li>
	      </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_dept19; ?></label>
                  <input type="text" class="form-control" maxlength="100" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSH($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input<?php echo (isset($EDIT->id) && $EDIT->manual_assign=='yes' ? ' onclick="if(!this.checked){mswAlert(\'' . mswJSClean($msg_script_action5) . '\',\'\',\'alert\')}" ' : ' '); ?>type="checkbox" name="manual_assign" value="yes"<?php echo (isset($EDIT->id) && $EDIT->manual_assign=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_dept22; ?></label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group"><label><?php echo $msg_dept17; ?></label>
                  <input type="text" class="form-control" name="dept_subject" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->dept_subject) ? mswSH($EDIT->dept_subject) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_dept18; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="dept_comments" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->dept_comments) ? mswSH($EDIT->dept_comments) : ''); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang_dept_3_7[0]; ?></label>
                  <select name="dept_priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - - - -</option>
                  <?php
                  if (!empty($ticketLevelSel)) {
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo (isset($EDIT->dept_priority) && $EDIT->dept_priority == $k ? ' selected="selected"' : ''); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  }
                  ?>
                  </select>
                </div>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="auto_admin" value="yes"<?php echo (isset($EDIT->auto_admin) && $EDIT->auto_admin=='yes' ? ' checked="checked"' : (!isset($EDIT->auto_admin) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang4_3[1]; ?>
                    </label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="showDept" value="yes"<?php echo (isset($EDIT->id) && $EDIT->showDept=='yes' ? ' checked="checked"' : (!isset($EDIT->id) ? ' checked="checked"' : '')); ?>> <?php echo $msg_dept15; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1dept[0]; ?></label>
                  <div style="margin-top:10px">
                  <?php
                  $dep_days_mon = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
                  $dep_days_sun = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
                  for ($i=0; $i<7; $i++) {
                    $dy_arr = ($SETTINGS->weekStart == 'sun' ? $msg_script29 : $msg_script28);
                    $dy_db = ($SETTINGS->weekStart == 'sun' ? $dep_days_sun : $dep_days_mon);
                    ?>
                    <input type="checkbox" name="days[]" value="<?php echo $dy_db[$i]; ?>"<?php echo (isset($dayEn) && in_array($dy_db[$i], $dayEn) ? ' checked="checked"' : (!isset($dayEn) ? ' checked="checked"' : '')); ?>> <?php echo $dy_arr[$i]; ?>&nbsp;&nbsp;
                    <?php
                  }
                  ?>
                  </div>
                </div>
                
                <hr>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="auto_response" value="yes"<?php echo (isset($EDIT->auto_response) && $EDIT->auto_response=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msdept4_3[0]; ?>
                    </label>
                  </div>
                </div>
                
                <div class="form-group">
                  <label><?php echo $msdept4_3[1]; ?></label>
                  <input type="text" class="form-control" name="response_sbj" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->response_sbj) ? mswSH($EDIT->response_sbj) : ''); ?>">
                </div>
                
                <div class="form-group">
                  <label><?php echo $msdept4_3[2]; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="response" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->response) ? mswSH($EDIT->response) : ''); ?></textarea>
                  <span class="help-block"><a href="?p=dept&amp;mtags=yes" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_TAGS_WIDTH; ?>,height:<?php echo IBOX_TAGS_HEIGHT; ?>});return false" title="<?php echo mswSH($msg_tools22); ?>"><i class="fa fa-tags fa-fw"></i> <?php echo $msg_tools22; ?></a></span>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('dept')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_dept5 : $msg_dept2); ?></span></button>
            <?php
            if (in_array('deptman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=deptman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
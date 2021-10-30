<?php if (!defined('PARENT')) { exit; }
$_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
?>
      <form method="get" action="index.php">
      <div class="row searchboxarea" style="display:none">
        <input type="hidden" name="p" value="<?php echo $_GET['p']; ?>">
        <div class="col-lg-12">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="form-group searchbox">
                <div class="form-group">
                  <select name="dept" class="form-control">
                    <option value="0"><?php echo $msg_tools10; ?></option>
                    <?php
                    $q_dept = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
                    while ($DEPT = mswSQL_fetchobj($q_dept)) {
                    ?>
                    <option value="<?php echo $DEPT->id; ?>"<?php echo mswSelectedItem('dept',$DEPT->id,true); ?>><?php echo mswSH($DEPT->name); ?></option>
                    <?php
                    }
                    // For administrators, show all assigned users in filter..
                    if (USER_ADMINISTRATOR == 'yes') {
                    $q_users     = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
                    if (mswSQL_numrows($q_users)>0) {
                    ?>
                    <option value="0" disabled="disabled">- - - - - -</option>
                    <?php
                    while ($U = mswSQL_fetchobj($q_users)) {
                    ?>
                    <option value="u<?php echo $U->id; ?>"<?php echo mswSelectedItem('dept','u'.$U->id,true); ?>><?php echo $msg_open31 . ' ' . mswSH($U->name); ?></option>
                    <?php
                    }
                    }
                    }
                    ?>
                  </select>
                </div>

                <div class="form-group">
                  <select name="view" class="form-control">
                    <option value="day"<?php echo mswSelectedItem('view','day',true); ?>><?php echo $msg_reports4; ?></option>
                    <option value="month"<?php echo mswSelectedItem('view','month',true); ?>><?php echo $msg_reports5; ?></option>
                  </select>
                </div>

                <div class="form-group">
                  <input type="text" placeholder="<?php echo mswSH($msg_reports2); ?>" class="form-control" id="from" name="from" value="<?php echo mswSH($from); ?>">
                </div>

                <div class="form-group">
                  <input type="text" placeholder="<?php echo mswSH($msg_reports3); ?>" class="form-control" id="to" name="to" value="<?php echo mswSH($to); ?>">
                </div>

                <div class="text-center" style="margin-top:5px">
                  <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i> <?php echo $msadminlang3_7[12]; ?></button>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
      </form>
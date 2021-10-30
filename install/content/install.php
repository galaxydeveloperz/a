<?php if (!defined('PARENT')) { exit; } ?>

<div class="container mainmswarea">

  <div class="row">

    <div class="col-lg-12">

      <h1><i class="fa fa-cog fa-fw"></i><span class="hidden-xs"> <?php echo SCRIPT_NAME; ?></span> Installer</h1>

      <hr>

    </div>

  </div>

</div>

<div id="formarea">

  <form method="post" action="#">
  <div class="container ops">

    <div class="row">

      <div class="col-lg-12">

        <?php
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME') || !defined('DB_PREFIX')
           || !defined('DB_CHAR_SET') || !defined('DB_LOCALE') || !defined('SECRET_KEY') || !defined('COOKIE_NAME') || !defined('COOKIE_EXPIRY_DAYS')
           || !defined('COOKIE_SSL') || !defined('ENABLE_MYSQL_ERRORS') || !defined('MYSQL_DEFAULT_ERROR')) {
        ?>
        <div class="panel panel-danger">
          <div class="panel-heading">
            <i class="fa fa-warning fa-fw"></i> Database Connection File - Fatal Error
          </div>
          <div class="panel-body">
          One or more constants have been edited incorrectly in the following file:<br><br>
          <b>control/connect.php</b><br><br>
          Please try again using the notes in that file as a reference. Once you have corrected the errors, refresh page.
          </div>
        </div>
        <?php
        } elseif (phpVersion() < MSW_PHP_MIN_VER) {
        ?>
        <div class="panel panel-danger">
          <div class="panel-heading">
            <i class="fa fa-warning fa-fw"></i> PHP Version Error
          </div>
          <div class="panel-body">
          Your PHP version is too old and <?php echo SCRIPT_NAME; ?> cannot run on this server.<br><br>
          The required minimum version is <b>PHP<?php echo MSW_PHP_MIN_VER; ?></b>, your version is <b>PHP<?php echo phpVersion(); ?></b><br><br>
          Please update your PHP installation to continue.<br><br>
          Thank you.
          </div>
        </div>
        <?php
        } elseif (!defined('LIC_DEV') && SECRET_KEY == 'secret-key-name123') {
        ?>
        <div class="panel panel-danger">
          <div class="panel-heading">
            <i class="fa fa-warning fa-fw"></i> Security Error
          </div>
          <div class="panel-body">
          This is important!!<br><br>
          As per the installation instructions, you <b>MUST</b> rename the <b>SECRET_KEY</b> value in the 'control/connect.php' file for security.<br><br>
          This is used as a security key / salt to help protect your system. Try and make the key as difficult as possible for better security.<br><br>
          Once you are done, refresh this page.<br><br>
          Thank you.
          </div>
        </div>
        <?php
        } else {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Database Connection
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>DB Host</th>
                  <th>DB User</th>
                  <th>DB Pass</th>
                  <th>DB Name</th>
                  <th>Table Prefix</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo DB_HOST; ?></td>
                  <td><?php echo DB_USER; ?></td>
                  <td><?php echo DB_PASS; ?></td>
                  <td><?php echo DB_NAME; ?></td>
                  <td><?php echo DB_PREFIX; ?></td>
                </tr>
              </tbody>
              </table>
            </div>
            Connection File: <b>control/connect.php</b>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Required Modules
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <tbody>
                <?php
                for ($i=0; $i<count($modules); $i++) {
                ?>
                <tr>
                  <td><?php echo $modules[$i][0]; ?></td>
                  <td><a href="<?php echo $modules[$i][3]; ?>" onclick="window.open(this);return false"><i class="fa fa-info-circle fa-fw"></i></a></td>
                  <td class="text-right">
                  <?php
                  switch($modules[$i][2]) {
                    case 'function':
                      if (function_exists($modules[$i][1])) {
                        echo '<i class="fa fa-check-circle fa-fw msw_green"></i>';
                      } else {
                        echo '<i class="fa fa-times-circle fa-fw msw_red"></i>';
                        ++$count;
                      }
                      break;
                    case 'class':
                      if (class_exists($modules[$i][1])) {
                        echo '<i class="fa fa-check-circle fa-fw msw_green"></i>';
                      } else {
                        echo '<i class="fa fa-times-circle fa-fw msw_red"></i>';
                        ++$count;
                      }
                      break;
                  }
                  ?>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
              </table>
            </div>
            Missing modules must be installed before you can continue.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Permissions
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover perms">
              <tbody>
                <?php
                for ($i=0; $i<count($permissions); $i++) {
                ?>
                <tr>
                  <td><i class="fa fa-folder fa-fw"></i> <?php echo $permissions[$i]; ?></td>
                  <td class="text-right">
                  <?php
                  if (is_writeable(BASE_PATH . $permissions[$i])) {
                    echo '<i class="fa fa-check-circle fa-fw msw_green"></i>';
                  } else {
                    echo '<i class="fa fa-times-circle fa-fw msw_red"></i>';
                    ++$count;
                  }
                  ?>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
              </table>
            </div>
            Above directories must have read/write permissions. Example: 0755 or 0777.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Database Preferences
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Character Set / Collation</label>
              <select name="charset" class="form-control">
              <?php
              if (!empty($cSets)) {
                foreach ($cSets AS $set) {
                ?>
                <option value="<?php echo $set; ?>"<?php echo ($set == $defChar ? ' selected="selected"' : ''); ?>><?php echo $set; ?></option>
                <?php
                }
              } else {
                ?>
                <option value="<?php echo $defChar; ?>" selected="selected"><?php echo $defChar; ?></option>
                <?php
              }
              ?>
              </select>
            </div>
            <div class="form-group">
              <label>Database Engine</label>
              <select name="engine" class="form-control">
                <option value="MyISAM" selected="selected">MyISAM</option>
                <option value="InnoDB">InnoDB</option>
              </select>
            </div>
            MySQL Version: <b><?php echo $sqlVer; ?></b> / If you aren`t sure of this, leave as default.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Helpdesk Settings
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Helpdesk Name</label>
              <div class="form-group input-group">
                <span class="input-group-addon"><i class="fa fa-life-ring fa-fw"></i></span>
                <input type="text" name="nm" value="My Helpdesk" class="form-control">
              </div>
            </div>
            <div class="form-group">
              <label>Timezone</label>
              <select name="timezone" class="form-control">
              <?php
              if (!empty($timezones)) {
                foreach ($timezones AS $tzk => $tz) {
                ?>
                <option value="<?php echo $tzk; ?>"<?php echo (function_exists('date_default_timezone_get') && @date_default_timezone_get() == $tzk ? ' selected="selected"' : ($tzk == 'Europe/London' ? ' selected="selected"' : '')); ?>><?php echo $tz; ?></option>
                <?php
                }
              }
              ?>
              </select>
            </div>
            Can be changed later via admin control panel.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Administration Access
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Email Address</label>
              <div class="form-group input-group">
                <span class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></span>
                <input type="text" name="em" value="" class="form-control">
              </div>
            </div>
            <div class="form-group">
              <label>Password</label>
              <div class="form-group input-group">
                <span class="input-group-addon"><i class="fa fa-lock fa-fw"></i></span>
                <input type="password" name="pw" value="" class="form-control">
              </div>
            </div>
            Details can be changed later via admin control panel.
          </div>
        </div>

        <div class="text-center buttonarea">
          <button class="btn btn-success" type="button" onclick="mswIns('install')"><i class="fa fa-check-circle fa-fw"></i> Install</button>
        </div>
        <?php
        }
        ?>

      </div>

    </div>

  </div>
  </form>

</div>
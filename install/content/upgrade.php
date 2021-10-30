<?php if (!defined('PARENT')) { exit; } ?>

<div class="container mainmswarea">

  <div class="row">

    <div class="col-lg-12">

      <h1><i class="fa fa-cog fa-fw<?php echo (isset($_GET['run']) ? ' fa-spin' : ''); ?>"></i><span class="hidden-xs"> <?php echo SCRIPT_NAME; ?></span> - Upgrade</h1>

      <hr>

    </div>

  </div>

</div>

<div id="formarea">

  <form method="post" action="<?php echo (isset($_GET['run']) ? '#' : 'upgrade.php?run=yes'); ?>" onsubmit="return mswConf()">
  <div class="container ops">

    <div class="row">

      <div class="col-lg-12">

        <?php
        if (isset($_GET['done'])) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-check-circle fa-fw"></i> Upgrade Completed
          </div>
          <div class="panel-body">
           The upgrade of <?php echo SCRIPT_NAME; ?> is now completed. You are successfully running <b>v<?php echo SCRIPT_VERSION; ?></b>.<br><br>
           <span style="border:1px solid #555;color:red;display:block;padding:10px;background:#ff9999;color:#fff"><i class="fa fa-warning fa-fw"></i> For security, DELETE or rename the 'install' folder in your helpdesk directory NOW!!</b></span><br>
           I really hope you are liking <?php echo SCRIPT_NAME; ?> and thanks for upgrading.<br><br>
           As always, feedback and comments are most welcome.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-question-circle fa-fw"></i> Whats New in <?php echo SCRIPT_VERSION; ?>?
          </div>
          <div class="panel-body">
           To see what changes have been made in the latest release, please see the <a style="text-decoration:underline;font-weight:bold" href="https://www.maiansupport.com/changelog.html" onclick="window.open(this);return false"><?php echo SCRIPT_NAME; ?> changelog</a>.
          </div>
        </div>

        <div class="row">

          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <button onclick="window.location='../index.php'" class="btn btn-primary" type="button"><i class="fa fa-search fa-fw"></i><span class="hidden-xs"> View Help Desk</span></button>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-right">
            <button onclick="window.location='../<?php echo ADMIN_FLDR; ?>/index.php'" class="btn btn-success" type="button"><i class="fa fa-lock fa-fw"></i><span class="hidden-xs"> View Control Panel</span></button>
          </div>

        </div>
        <?php
        } else {
        if (phpVersion() < MSW_PHP_MIN_VER) {
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
        } else {
        if (!isset($_GET['run'])) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Version Check
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Installed Version</th>
                  <th class="text-right">Upgrade Version</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-size: 20px"><?php echo $SETTINGS->softwareVersion; ?></td>
                  <td class="text-right" style="font-size: 20px"><?php echo SCRIPT_VERSION; ?></td>
                </tr>
              </tbody>
              </table>
            </div>
            <?php
            if ($SETTINGS->softwareVersion >= SCRIPT_VERSION) {
              echo '<i class="fa fa-check-circle fa-fw msw_green"></i> Upgrade Not Required, You appear to be running the latest version.';
            }
            ?>
          </div>
        </div>
        <?php
        }

        if ($SETTINGS->softwareVersion < SCRIPT_VERSION) {
        if (isset($_GET['run'])) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> <?php echo count($ops); ?> Upgrade Operations
          </div>
          <div class="panel-body">
            This may take several minutes depending on the size of your database. Please <b>DO NOT</b> refresh your browser.
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Operation Detail</th>
                  <th class="text-right">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if (!empty($ops)) {
                for ($i = 0; $i < count($ops); $i++) {
                ?>
                <tr>
                  <td id="td1_<?php echo $i; ?>"<?php echo ($i == 0 ? ' class="msw_bold"' : ''); ?>><?php echo $ops[$i]; ?></td>
                  <td class="text-right" id="td2_<?php echo $i; ?>"><?php echo ($i == 0 ? '<i class="fa fa-spinner fa-spin fa-fw"></i> Running..' : 'Waiting..'); ?></td>
                </tr>
                <?php
                }
                }
                ?>
              </tbody>
              </table>
            </div>
          </div>
          <input type="hidden" name="charset" value="<?php echo mswSH($_POST['charset']); ?>">
        </div>
        <?php
        } else {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-chevron-right fa-fw"></i> Database to Upgrade
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
            <i class="fa fa-chevron-right fa-fw"></i> Collation / Character Set - For Any New Tables and / or Columns
          </div>
          <div class="panel-body">
            <div class="form-group">
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
            MySQL Version: <b><?php echo $sqlVer; ?></b> / If you aren`t sure of this, leave as default.
          </div>
        </div>

        <div class="text-center buttonarea">
          <button class="btn btn-success" type="submit"><i class="fa fa-check-circle fa-fw"></i> Upgrade</button>
        </div>
        <?php
        }
        }
        }
        }
        ?>

      </div>

    </div>

  </div>
  </form>

</div>
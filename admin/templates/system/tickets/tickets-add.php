<?php if (!defined('PARENT')) { exit; }
$countOfCusFields  = mswSQL_rows('cusfields WHERE `enField` = \'yes\'');
$countOfOtherUsers = mswSQL_rows('users WHERE `id` > 0');
$dept              = array();
include(BASE_PATH . 'control/classes/class.upload.php');
$MSUPL     = new msUpload();
$aMaxFiles = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : '9999999');
$mSize     = $MSUPL->getMaxSize();
$mswUploadDropzone2 = array(
  'ajax' => 'ticket',
  'multiple' => ($aMaxFiles > 1 ? 'true' : 'false'),
  'max-files' => $aMaxFiles,
  'max-size' => $mSize,
  'drag' => 'false',
  'div' => 'four'
);
define('LOAD_DATE_PICKERS', 1);
define('JS_LOADER', 'add-ticket.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li class="active"><?php echo $msg_open; ?></li>
    </ol>

    <form method="post" action="index.php?ajax=ticket" enctype="multipart/form-data" id="mswform">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add5; ?></span></a></li>
          <?php
          if ($countOfCusFields > 0) {
          ?>
          <li id="licus"><a href="#three" data-toggle="tab"><i class="fa fa-list-alt fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
          <?php
          }
          if ($SETTINGS->attachment == 'yes') {
          ?>
          <li><a href="#four" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_add3; ?></span></a></li>
          <?php
          }
          ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-fw" title="<?php echo mswSH($msg_settings85); ?>"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings85; ?></span> <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
            <?php
            if ($countOfOtherUsers > 0) {
            ?>
            <li id="liusr"><a href="#five" data-toggle="tab"><?php echo $msadminlang3_1adminticketedit[1]; ?></a></li>
            <?php
            }
            ?>
            <li><a href="#six" data-toggle="tab"><?php echo $msg_accounts18; ?></a></li>
          </ul>
          </li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <label><?php echo $msg_newticket15; ?></label>
                  <input type="text" class="form-control" name="subject" tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket6; ?></label>
                  <select name="dept" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"<?php echo ($countOfCusFields > 0 ? ' onchange="mswDeptLoader(\'three\',\'add\',\'0\',\'ticket\')"' : ''); ?>>
                  <?php
                  $q_dept = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
                  while ($DEPT = mswSQL_fetchobj($q_dept)) {
                  $dept[] = $DEPT->id;
                  ?>
                  <option value="<?php echo $DEPT->id; ?>"><?php echo mswCD($DEPT->name); ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_newticket8; ?></label>
                  <select name="priority" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <?php
                  if (!empty($ticketLevelSel)) {
                  foreach ($ticketLevelSel AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php
                  }
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <?php
                  // BBCode..
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_viewticket17; ?> (<?php echo $msg_add13; ?>)</label>
                  <select name="status" class="form-control">
			              <?php
			              // Remove closed status from array
			              // Check team perms..
			              unset($ticketStatusSel['close']);
			              if (USER_LOCK_PRIV == 'no') {
			                unset($ticketStatusSel['closed']);
			              }
                    foreach ($ticketStatusSel AS $sk => $sv) {
                    ?>
                    <option value="<?php echo $sk; ?>"><?php echo $sv[0]; ?></option>
                    <?php
                    }
                    ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msg_viewticket2; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswSearchAccounts('name',0);return false" title="<?php echo mswSH($msg_add6); ?>"><i class="fa fa-search fa-fw"></i> </a></span>
                    <input type="text" class="form-control" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
		            </div>

                <div class="form-group accntn" style="display:none">
                  <select name="accntn" class="form-control" onchange="mswSelectAccount(this.value,'name')"></select>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_viewticket3; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswSearchAccounts('email',0);return false" title="<?php echo mswSH($msg_add6); ?>"><i class="fa fa-search fa-fw"></i> </a></span>
                    <input type="text" class="form-control" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group accnte" style="display:none">
                  <select name="accnte" class="form-control" onchange="mswSelectAccount(this.value,'email')"></select>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="accMail" value="yes"<?php echo (ADD_TICKET_MAIL_NOTIFY ? ' checked="checked"' : ''); ?>> <?php echo $msg_viewticket18; ?></label>
                  </div>
                </div>

              </div>
              <?php
              if ($countOfCusFields > 0 && isset($dept[0])) {
              ?>
              <div class="tab-pane fade" id="three">

                <?php
                // Custom fields..
                $qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
                      WHERE FIND_IN_SET('ticket',`fieldLoc`)   > 0
                      AND `enField`                            = 'yes'
                      AND FIND_IN_SET('{$dept[0]}',`departments`) > 0
                      ORDER BY `orderBy`
                      ", __file__, __line__);
                if (mswSQL_numrows($qF) > 0) {
                  while ($FIELDS = mswSQL_fetchobj($qF)) {
                    switch ($FIELDS->fieldType) {
                      case 'textarea':
                        echo $MSFM->buildTextArea(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                        break;
                      case 'input':
                        echo $MSFM->buildInputBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                        break;
                      case 'select':
                        echo $MSFM->buildSelect(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),$FIELDS->fieldOptions);
                        break;
                      case 'calendar':
                        echo $MSFM->buildCalBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex));
                        break;
                      case 'checkbox':
                        echo $MSFM->buildCheckBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions);
                        break;
                    }
                  }
                } else {
                  echo '<i class="fa fa-warning fa-fw ms_red"></i>' . $msadminlang3_1[6];
                }
                ?>

              </div>
              <?php
              }
              if ($SETTINGS->attachment == 'yes') {
              ?>
              <div class="tab-pane fade" id="four">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo str_replace('{max}', mswFSC($mSize), $msadminlang3_1uploads[6]); ?>
                  </div>
                </div>

              </div>
              <?php
              }
              if ($countOfOtherUsers > 0) {
              ?>
              <div class="tab-pane fade" id="five">

                <?php
                $q_users  = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
                while ($USERS = mswSQL_fetchobj($q_users)) {
                ?>
                <div class="form-group">
                    <div class="checkbox">
                    <label><input type="checkbox" name="assigned[]" value="<?php echo $USERS->id; ?>" onclick="if(this.checked){mswUncheckAssigned('box')}"> <?php echo mswCD($USERS->name); ?></label>
                  </div>
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="waiting" value="yes" onclick="if(this.checked){mswUncheckAssigned('wait')}"> <?php echo $msg_add10; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="assignMail" value="yes" checked="checked"> <?php echo $msg_viewticket18 . ' ' . $msg_add12; ?></label>
                  </div>
                </div>

              </div>
              <?php
              }
              ?>
              <div class="tab-pane fade" id="six">

                <div class="form-group">
                  <textarea name="notes" rows="15" cols="40" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <?php
            if (SAVE_DRAFTS) {
            define('DRAFT_AREA', '#comments');
            define('DRAFT_ID', 'add');
            ?>
            <div class="pull-right draftarea" id="draft_add">
              &nbsp;
            </div>
            <?php
            }
            ?>
            <input type="hidden" name="process" value="1">
           <button class="btn btn-primary" type="submit"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_open; ?></span></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
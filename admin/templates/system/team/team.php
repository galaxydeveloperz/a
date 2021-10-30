<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']         = (int)$_GET['edit'];
  $EDIT                 = mswSQL_table('users','id',$_GET['edit']);
  mswVLQY($EDIT);
  $ePageAccess          = mswGetUserPageAccess($_GET['edit']);
  $eDeptAccess          = mswGetDepartmentAccess($_GET['edit']);
  $mswDeptFilterAccess  = mswDeptFilterAccess($MSTEAM,$eDeptAccess,'department');
  $ePerms               = ($EDIT->editperms ? unserialize($EDIT->editperms) : array());
  $eDigest              = ($EDIT->digestops ? unserialize($EDIT->digestops) : array());
  $eDigDays             = ($EDIT->digestdays ? unserialize($EDIT->digestdays) : array());
}
define('JS_LOADER', 'team.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('teamman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=teamman"><?php echo $msg_adheader58; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_user14 : $msg_adheader57); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user73; ?></span></a></li>
          <li class="admin_hideperms"><a href="#two" data-toggle="tab"><i class="fa fa-lock fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user74; ?></span></a></li>
          <li class="admin_hideperms"><a href="#three" data-toggle="tab"><i class="fa fa-folder-open fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user75; ?></span></a></li>
          <li class="admin_hideperms"><a href="#four" data-toggle="tab"><i class="fa fa-cog fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_user76; ?></span></a></li>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_settings85; ?></span><b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="#seven" data-toggle="tab"><?php echo $msg_adheader61; ?></a></li>
              <li><a href="#eight" data-toggle="tab"><?php echo $msg_user104; ?></a></li>
              <li><a href="#five" data-toggle="tab"><?php echo $msadminlang_user_3_7[22]; ?></a></li>
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
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enabled" value="yes"<?php echo (isset($EDIT->enabled) && $EDIT->enabled=='yes' ? ' checked="checked"' : (!isset($EDIT->enabled) ? ' checked="checked"' : '')); ?>> <?php echo $msg_accounts19; ?>
                    </label>
                  </div>
                </div>

                <?php
                if ($MSTEAM->id == '1') {
                  if (isset($EDIT->id) && $EDIT->id == '1') {
                  ?>
                  <input type="hidden" name="admin" value="yes">
                  <?php
                  } else {
                  ?>
                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" onclick="if(this.checked){mswTeamPerms('hide')}else{mswTeamPerms('show')}" name="admin" value="yes"<?php echo (isset($EDIT->admin) && ($EDIT->admin=='yes' || $EDIT->id=='1') ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang_user_3_7[13]; ?>
                      </label>
                    </div>
                  </div>
                  <?php
                  }
                } else {
                  if (isset($EDIT->admin) && $EDIT->admin == 'yes') {
                  ?>
                  <input type="hidden" name="admin" value="yes">
                  <?php
                  }
                }

                if (!isset($EDIT->id)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="welcome" value="yes" checked="checked"> <?php echo $msg_accounts23; ?>
                    </label>
                  </div>
                </div>
                <?php
                }
                ?>

                <div class="form-group">
                  <label><?php echo $msg_user; ?></label>
                  <input type="text" class="form-control" name="name" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->name) ? mswSH($EDIT->name) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user4; ?></label>
                  <input type="text" class="form-control" name="email" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->email) ? mswSH($EDIT->email) : ''); ?>">
                </div>

                <div class="form-group">
                  <label id="labelPass"><?php echo $msg_user12; ?></label>
                  <div class="form-group input-group">
                    <span class="input-group-addon"><a href="#" onclick="mswPassGenerator('labelPass','accpass');return false" title="<?php echo mswSH($msg_accounts20); ?>"><i class="fa fa-refresh fa-fw"></i></a></span>
                    <input type="password" class="form-control" name="accpass" tabindex="<?php echo (++$tabIndex); ?>" value="">
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user70; ?></label>
                  <select name="timezone" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value="0">- - - - - - -</option>
                  <?php
                  // TIMEZONES..
                  foreach ($timezones AS $k => $v) {
                  ?>
                  <option value="<?php echo $k; ?>"<?php echo (isset($EDIT->timezone) ? mswSelectedItem($EDIT->timezone,$k) : ''); ?>><?php echo $v; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang_user_3_7[27]; ?></label>
                  <select name="language" tabindex="<?php echo (++$tabIndex); ?>" class="form-control">
                  <option value=""><?php echo $msadminlang_user_3_7[28] . ' (' . mswSH($SETTINGS->language) . ')'; ?></option>
                  <?php
                  if (is_dir(BASE_PATH . 'content/language')) {
                    $showlang = opendir(BASE_PATH . 'content/language');
                    while (false!==($read=readdir($showlang))) {
                      if (is_dir(BASE_PATH . 'content/language/' . $read) && !in_array($read, array('.', '..')) && $read != $SETTINGS->language) {
                      ?>
                      <option<?php echo (isset($EDIT->language) ? mswSelectedItem($read, $EDIT->language) : ''); ?>><?php echo $read; ?></option>
                      <?php
                      }
                    }
                    closedir($showlang);
                  }
                  ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <?php
                if (!empty($slidePanelLeftMenu)) {
                  foreach (array_keys($slidePanelLeftMenu) AS $smk) {
                    if ($slidePanelLeftMenu[$smk][2] == 'yes') {
                    ?>
                    <div class="form-group">
                      <div class="checkbox">
                        <label>
                          <input onclick="mswCheckBoxes(this.checked,'.boxes_<?php echo $smk; ?>')" type="checkbox"> <b><?php echo $slidePanelLeftMenu[$smk][0]; ?></b>
                        </label>
                      </div>
                    </div>

                    <div class="boxes_<?php echo $smk; ?>">
                    <?php
                    for ($i=0; $i<count($slidePanelLeftMenu[$smk]['links']); $i++) {
                    switch($smk) {
                      case 'apages':
                        $k = str_replace('&amp;', '&', substr($slidePanelLeftMenu[$smk]['links'][$i]['url'], 3));
                        break;
                      default:
                        $k = substr($slidePanelLeftMenu[$smk]['links'][$i]['url'], 3);
                        break;
                    }
                    ?>
                    <div class="form-group">
                      <div class="checkbox indent_10">
                        <label>
                          <input type="checkbox" name="accessPages[]" value="<?php echo $k; ?>"<?php echo (isset($EDIT->id) && in_array($k,$ePageAccess) ? ' checked="checked"' : ''); ?>> <?php echo $slidePanelLeftMenu[$smk]['links'][$i]['name']; ?>
                        </label>
                      </div>
                    </div>
                    <?php
                    }
                    ?>
                    </div>
                    <?php
                    }
                  }
                }
                ?>

                <div class="form-group">
                  <label><?php echo $msg_user100; ?></label>
                  <input type="text" class="form-control" name="addpages" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->addpages) ? mswSH($EDIT->addpages) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input onclick="mswCheckBoxes(this.checked,'#three_ck')" type="checkbox" name="all" value="all"<?php echo (isset($eDeptAccess) && mswSQL_rows('departments')==count($eDeptAccess) ? ' checked="checked"' : ''); ?>> <b><?php echo $msg_user56; ?></b>
                    </label>
                  </div>
                </div>

                <div id="three_ck">
                <?php
                // If global log in no filter necessary..
                $q_dept = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
                while ($DEPT = mswSQL_fetchobj($q_dept)) {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="dept[]" value="<?php echo $DEPT->id; ?>"<?php echo (isset($EDIT->id) && in_array($DEPT->id,$eDeptAccess) ? ' checked="checked"' : ''); ?>> <?php echo mswSH($DEPT->name); ?>
                    </label>
                  </div>
                </div>
                <?php
                }
                ?>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="assigned" value="yes"<?php echo (isset($EDIT->assigned) && $EDIT->assigned=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_user69; ?>
                    </label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="four">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="notePadEnable" value="yes"<?php echo (isset($EDIT->notePadEnable) && $EDIT->notePadEnable=='yes' ? ' checked="checked"' : (!isset($EDIT->notePadEnable) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user54; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="enableLog" value="yes"<?php echo (isset($EDIT->enableLog) && $EDIT->enableLog=='yes' ? ' checked="checked"' : (!isset($EDIT->enableLog) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user91; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="mergeperms" value="yes"<?php echo (isset($EDIT->mergeperms) && $EDIT->mergeperms=='yes' ? ' checked="checked"' : (!isset($EDIT->mergeperms) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user101; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="profile" value="yes"<?php echo (isset($EDIT->profile) && $EDIT->profile=='yes' ? ' checked="checked"' : (!isset($EDIT->profile) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user107; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="helplink" value="yes"<?php echo (isset($EDIT->helplink) && $EDIT->helplink=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_user108; ?>
                    </label>
                  </div>
                </div>

                <?php
                if ($SETTINGS->ticketHistory=='yes') {
                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="ticketHistory" value="yes"<?php echo (isset($EDIT->ticketHistory) && $EDIT->ticketHistory=='yes' ? ' checked="checked"' : (!isset($EDIT->ticketHistory) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user90; ?>
                    </label>
                  </div>
                </div>
                <?php
                }

                ?>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="delPriv" value="yes"<?php echo (isset($EDIT->delPriv) && $EDIT->delPriv=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_user64; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="close" value="yes"<?php echo (isset($EDIT->close) && $EDIT->close=='yes' ? ' checked="checked"' : (!isset($EDIT->close) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang_user_3_7[11]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="lock" value="yes"<?php echo (isset($EDIT->lock) && $EDIT->lock=='yes' ? ' checked="checked"' : (!isset($EDIT->lock) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang_user_3_7[12]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="editperms[]" value="ticket"<?php echo (isset($ePerms) && in_array('ticket', $ePerms) ? ' checked="checked"' : ''); ?>> <?php echo $msgadminlang3_1staff[0]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="editperms[]" value="reply"<?php echo (isset($ePerms) && in_array('reply', $ePerms) ? ' checked="checked"' : ''); ?>> <?php echo $msgadminlang3_1staff[1]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="timer" value="yes"<?php echo (isset($EDIT->timer) && $EDIT->timer=='yes' ? ' checked="checked"' : (!isset($EDIT->timer) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang_user_3_7[25]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="startwork" value="yes"<?php echo (isset($EDIT->startwork) && $EDIT->startwork=='yes' ? ' checked="checked"' : (!isset($EDIT->startwork) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang_user_3_7[24]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="workedit" value="yes"<?php echo (isset($EDIT->workedit) && $EDIT->workedit=='yes' ? ' checked="checked"' : (!isset($EDIT->workedit) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang_user_3_7[26]; ?>
                    </label>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="staffupnotify" value="yes"<?php echo (isset($EDIT->staffupnotify) && $EDIT->staffupnotify=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $mssuptickets4_3[4]; ?>
                    </label>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="faqHistory" value="yes"<?php echo (isset($EDIT->faqHistory) && $EDIT->faqHistory=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $mssuptickets4_3[5]; ?>
                    </label>
                  </div>
                </div>

              </div>
              <div class="tab-pane fade" id="five">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="notify" value="yes"<?php echo (isset($EDIT->notify) && $EDIT->notify=='yes' ? ' checked="checked"' : (!isset($EDIT->notify) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user18; ?>
                    </label>
                  </div>
                </div>
                
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="spamnotify" value="yes"<?php echo (isset($EDIT->spamnotify) && $EDIT->spamnotify=='yes' ? ' checked="checked"' : (!isset($EDIT->spamnotify) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang4_3[0]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="emailSigs" value="yes"<?php echo (isset($EDIT->emailSigs) && $EDIT->emailSigs=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msg_user45; ?>
		                </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user17; ?></label>
                  <textarea class="form-control" rows="8" cols="40" name="signature" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->signature) ? mswSH($EDIT->signature) : ''); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user65; ?></label>
                  <input type="text" class="form-control" name="nameFrom" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->nameFrom) ? mswSH($EDIT->nameFrom) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user66; ?></label>
                  <input type="text" class="form-control" name="emailFrom" maxlength="250" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->emailFrom) ? mswSH($EDIT->emailFrom) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user85; ?></label>
                  <input type="text" class="form-control" name="email2" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->email2) ? mswSH($EDIT->email2) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="six">

                <div class="form-group">
                  <textarea class="form-control" rows="5" cols="50" name="notes"><?php echo (isset($EDIT->notes) ? mswSH($EDIT->notes) : ''); ?></textarea>
                </div>

              </div>
              <div class="tab-pane fade" id="seven">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="mailbox" value="yes"<?php echo (isset($EDIT->mailbox) && $EDIT->mailbox=='yes' ? ' checked="checked"' : (!isset($EDIT->mailbox) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user95; ?>
		                </label>
                  </div>
		            </div>

                <div class="form-group admin_hideperms">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="mailDeletion" value="yes"<?php echo (isset($EDIT->mailDeletion) && $EDIT->mailDeletion=='yes' ? ' checked="checked"' : (!isset($EDIT->mailDeletion) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user96; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="mailScreen" value="yes"<?php echo (isset($EDIT->mailScreen) && $EDIT->mailScreen=='yes' ? ' checked="checked"' : (!isset($EDIT->mailScreen) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user97; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="mailCopy" value="yes"<?php echo (isset($EDIT->mailCopy) && $EDIT->mailCopy=='yes' ? ' checked="checked"' : (!isset($EDIT->mailCopy) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user98; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user99; ?></label>
                  <input type="text" class="form-control" name="mailFolders" maxlength="3" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->mailFolders) ? (int)$EDIT->mailFolders : 5); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_user106; ?></label>
                  <input type="text" class="form-control" name="mailPurge" maxlength="3" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->mailPurge) ? (int)$EDIT->mailPurge : 0); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="eight">

                <div class="form-group">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="digest" value="yes"<?php echo (isset($EDIT->digest) && $EDIT->digest=='yes' ? ' checked="checked"' : (!isset($EDIT->digest) ? ' checked="checked"' : '')); ?>> <?php echo $msg_user102; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang4_3[11]; ?></label>
                  <?php
                  // Are disputes enabled?
                  if ($SETTINGS->disputes == 'no') {
                    unset($msemail_digest['odp']);
                    unset($msemail_digest['ods']);
                    unset($msemail_digest['odv']);
                  }
                  foreach($msemail_digest AS $edk => $edv) {
                  ?>
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="digops[]" value="<?php echo $edk; ?>"<?php echo (!empty($eDigest) && in_array($edk, $eDigest) ? ' checked="checked"' : ''); ?>> <?php echo $edv; ?>
                    </label>
                  </div>
                  <?php
                  }
                  ?>
                </div>
                
                <div class="form-group">
                  <label><?php echo $msadminlang4_3[12]; ?></label>
                  <div>
                    <?php
                    foreach(($SETTINGS->weekStart == 'sun' ? $msg_script29 : $msg_script28) AS $dk => $dv) {
                    $slot = ($SETTINGS->weekStart == 'sun' ? array(0,1,2,3,4,5,6) : array(1,2,3,4,5,6,0));
                    ?>
                    <input type="checkbox" name="digdays[]" value="<?php echo $slot[$dk]; ?>"<?php echo (!empty($eDigDays) && in_array($slot, $eDigDays) ? ' checked="checked"' : ''); ?>> <?php echo $dv; ?>&nbsp;&nbsp;
                    <?php
                    }
                  ?>
                  </div>
                </div>
                <hr>
                <a href="../control/cron/email-digest.php" onclick="mswWinConf(this.href);return false"><i class="fa fa-play fa-fw"></i> <?php echo $msg_user105; ?></a>
                <?php
                if (isset($EDIT->id) && $EDIT->digest=='yes') {
                ?>
                <br>
                <a href="../control/cron/email-digest.php?id=<?php echo $MSTEAM->id; ?>" onclick="mswWinConf(this.href);return false"><i class="fa fa-play fa-fw"></i> <?php echo $msg_user105 . ' - ' . str_replace('{name}', mswSH($MSTEAM->name), $msadminlang_user_3_7[23]); ?></a>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
           <?php
           if (isset($EDIT->id)) {
           ?>
           <input type="hidden" name="old_pass" value="<?php echo $EDIT->accpass; ?>">
           <input type="hidden" name="currID" value="<?php echo $EDIT->id; ?>">
           <?php
           }
           ?>
           <button class="btn btn-primary" type="button" onclick="mswProcess('team')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_user14 : $msg_adheader57); ?></span></button>
           <?php
           if (in_array('teamman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=teamman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>
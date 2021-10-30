<?php if (!defined('PARENT')) { exit; }
$categories  = array();
$attachments = array();
if (isset($_GET['edit'])) {
  $_GET['edit'] = (int) $_GET['edit'];
  $EDIT         = mswSQL_table('faq','id', $_GET['edit']);
  mswVLQY($EDIT);
  $qAS          = mswSQL_query("SELECT `itemID` FROM `" . DB_PREFIX . "faqassign` WHERE `question` = '{$EDIT->id}' AND `desc` = 'attachment' GROUP BY `itemID`", __file__, __line__);
  while ($AA = mswSQL_fetchobj($qAS)) {
    $attachments[] = $AA->itemID;
  }
}
$tempSets = ($SETTINGS->langSets ? unserialize($SETTINGS->langSets) : array());
$qA  = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "faqattach` WHERE `enAtt` = 'yes' ORDER BY `name`", __file__, __line__);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('faqman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=faqman"><?php echo $msg_adheader47; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($EDIT->id) ? $msg_kbase13 : $msg_kbase3); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_kbase42; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-reorder fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_import10; ?></span></a></li>
          <li><a href="#three" data-toggle="tab"><i class="fa fa-paperclip fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader33; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="enFaq" value="yes"<?php echo (isset($EDIT->enFaq) && $EDIT->enFaq=='yes' ? ' checked="checked"' : (!isset($EDIT->enFaq) ? ' checked="checked"' : '')); ?>> <?php echo $msg_kbase28; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="featured" value="yes"<?php echo (isset($EDIT->featured) && $EDIT->featured=='yes' ? ' checked="checked"' : ''); ?>> <?php echo $msadminlang3_1faq[0]; ?></label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase; ?></label>
                  <input type="text" class="form-control" name="question" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->id) ? mswSH($EDIT->question) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msg_kbase2; ?></label>
                  <?php
                  define('BB_BOX','answer');
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea class="form-control" rows="8" cols="40" name="answer" id="answer" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->id) ? mswSH($EDIT->answer) : ''); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang_pages_3_7[1]; ?></label>
                  <select name="tmp" class="form-control">
                    <option value="">- - - - - - -</option>
                    <?php
                    if (!empty($tempSets)) {
                      $thme = array_values($tempSets);
                      if (isset($thme[0]) && is_dir(BASE_PATH . 'content/' . $thme[0] . '/custom-templates')) {
                        $dir = opendir(BASE_PATH . 'content/' . $thme[0] . '/custom-templates');
                        while (false!==($read=readdir($dir))) {
                          if (substr(strtolower($read), 0, 4) == 'faq_') {
                          ?>
                          <option<?php echo (isset($EDIT->tmp) ? mswSelectedItem($read, $EDIT->tmp) : ''); ?>><?php echo $read; ?></option>
                          <?php
                          }
                        }
                        closedir($dir);
                      }
                    }
                    ?>
                  </select>
                </div>

                <div class="form-group">
                  <label><?php echo $mspubliclang_faq4_2[0]; ?></label>
                  <input type="text" class="form-control" name="searchkeys" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->searchkeys) ? mswSH($EDIT->searchkeys) : ''); ?>">
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <?php
                $fqcc = 0;
                $q1 = mswSQL_query("SELECT `id`, `name`, `private`, `accounts` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' AND `enCat` = 'yes' ORDER BY `name`", __file__, __line__);
                while ($CAT = mswSQL_fetchobj($q1)) {
                ?>
                <div class="form-group">
                  <div class="radio">
                    <label><input type="radio" name="cat" value="<?php echo $CAT->id; ?>"<?php echo (isset($EDIT->id) ? mswSelectedItem($EDIT->cat, $CAT->id, false, true) : (++$fqcc == '1' ? ' checked="checked"' : '')); ?>><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . mswCD($CAT->name); ?></label>
                  </div>
                </div>
                <?php
                $q2 = mswSQL_query("SELECT `id`, `name` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CAT->id}' AND `enCat` = 'yes' ORDER BY `name`", __file__, __line__);
                while ($SUB = mswSQL_fetchobj($q2)) {
                ?>
                <div class="form-group">
                  <div class="radio indent_10">
                    <label><input type="radio" name="cat" value="<?php echo $SUB->id; ?>"<?php echo (isset($EDIT->id) ? mswSelectedItem($EDIT->cat, $SUB->id, false, true) : ''); ?>><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . mswCD($SUB->name); ?></label>
                  </div>
                </div>
                <?php
                }
                }
                ?>

              </div>
              <div class="tab-pane fade" id="three">

                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th style="width:6%">
                        <input type="checkbox" onclick="mswCheckBoxes(this.checked,'#three')">
                      </th>
                      <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_attachments16); ?></th>
                      <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_kbase49); ?></th>
                      <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if (mswSQL_numrows($qA)>0) {
                      while ($ATT = mswSQL_fetchobj($qA)) {
                      $ext  = substr(strrchr(strtolower(($ATT->remote ? $ATT->remote : $ATT->path)),'.'),1);
                      $info = '[' . strtoupper($ext) . '] '.($ATT->size>0 ? mswFSC($ATT->size) : $msg_script17);
                      ?>
                      <tr>
                        <td><input type="checkbox" name="att[]" value="<?php echo $ATT->id; ?>"<?php echo mswCheckedArrItem($attachments,$ATT->id); ?>></td>
                        <td><b><?php echo ($ATT->name ? mswSH($ATT->name) : ($ATT->remote ? $ATT->remote : $ATT->path)); ?></b></td>
                        <td><?php echo $info; ?></td>
                        <td class="text-right">
                          <a href="#" onclick="mswDL('<?php echo $ATT->id; ?>','dl');return false" title="<?php echo mswSH($msg_kbase50); ?>"><i class="fa fa-download fa-fw"></i></a>
                          <?php
                          if (ONE_CLICK_IMG_VIEWER && substr($ATT->mimeType, 0, 6) == 'image/') {
                          ?>
                          <a href="<?php echo ($ATT->remote ? $ATT->remote : $SETTINGS->attachhreffaq . '/' . $ATT->path); ?>" onclick="iBox.showURL(this.href,'');return false"><i class="fa fa-search fa-fw"></i></a>
                          <?php
                          }
                          ?>
                        </td>
                      </tr>
                      <?php
                      }
                    } else {
                    ?>
                    <tr class="warning nothing_to_see">
                      <td colspan="3"><?php echo $msg_attachments9; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <input type="hidden" name="<?php echo (isset($EDIT->id) ? 'update' : 'process'); ?>" value="<?php echo (isset($EDIT->id) ? $EDIT->id : '1'); ?>">
            <button class="btn btn-primary" type="button" onclick="mswProcess('faq')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msg_kbase13 : $msg_kbase3); ?></span></button>
            <?php
            if (in_array('faqman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faqman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>
    
    <?php
    // On edit screen, show history
    if (isset($_GET['edit'], $EDIT->id)) {
      define('FAQ_HIS_LOADER', 1);
      include(PATH . 'templates/system/faq/faq-history.php');
    }
    ?>
    
  </div>
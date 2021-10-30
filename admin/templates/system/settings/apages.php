<?php if (!defined('PARENT')) { exit; }
if (isset($_GET['edit'])) {
  $_GET['edit']  = (int) $_GET['edit'];
  $EDIT          = mswSQL_table('admin_pages', 'id', $_GET['edit']);
  mswVLQY($EDIT);
}
define('JS_LOADER', 'apages.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('apages', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=apages"><?php echo $msadminlang3_1cspages[2]; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo (isset($_GET['edit']) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1]); ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_1cspages[4]; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-users fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminpages4_3[2]; ?></span></a></li>
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
                      <input type="checkbox" name="enPage" value="yes"<?php echo (isset($EDIT->enPage) && $EDIT->enPage=='yes' ? ' checked="checked"' : (!isset($EDIT->enPage) ? ' checked="checked"' : '')); ?>> <?php echo $msadminlang3_1cspages[6]; ?>
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <label><?php echo $msg_response; ?></label>
                  <input type="text" class="form-control" name="title" tabindex="<?php echo (++$tabIndex); ?>" value="<?php echo (isset($EDIT->title) ? mswSH($EDIT->title) : ''); ?>">
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang3_1cspages[8]; ?></label>
                  <?php
                  define('BB_BOX', 'information');
                  include(PATH . 'templates/system/bbcode-buttons.php');?>
                  <textarea class="form-control" rows="8" cols="40" name="information" id="information" tabindex="<?php echo (++$tabIndex); ?>"><?php echo (isset($EDIT->information) ? mswSH($EDIT->information) : ''); ?></textarea>
                </div>

                <div class="form-group">
                  <label><?php echo $msadminlang_pages_3_7[1]; ?></label>
                  <select name="tmp" class="form-control">
                    <option value="">- - - - - - -</option>
                    <?php
                    if (is_dir(PATH . 'templates/admin-pages')) {
                      $dir = opendir(PATH . 'templates/admin-pages');
                      while (false!==($read=readdir($dir))) {
                        if (substr(strtolower($read), -4) == '.php') {
                        ?>
                        <option<?php echo (isset($EDIT->tmp) ? mswSelectedItem($read, $EDIT->tmp) : ''); ?>><?php echo $read; ?></option>
                        <?php
                        }
                      }
                      closedir($dir);
                    }
                    ?>
                  </select>
                </div>

              </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <label><?php echo $msadminpages4_3[3]; ?></label>
                  <input type="text" class="form-control" name="search" tabindex="<?php echo (++$tabIndex); ?>" value="">
                </div>

                <?php
                $html = '';
                if (isset($EDIT->accounts) && !in_array($EDIT->accounts, array('','all',null))) {
                  $qA = mswSQL_query("SELECT `id`, `name`, `email` FROM `" . DB_PREFIX . "users`
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
           <button class="btn btn-primary" type="button" onclick="mswProcess('apages')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo (isset($EDIT->id) ? $msadminlang3_1cspages[3] : $msadminlang3_1cspages[1]); ?></span></button>
           <?php
           if (isset($_GET['edit']) && (in_array('apages', $userAccess)  || USER_ADMINISTRATOR == 'yes')) {
           ?>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=apages')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
           <?php
           }
           ?>
          </div>
        </div>

      </div>
    </div>
    </form>
    
    <?php
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "admin_pages`
         ORDER BY `orderBy`
    		 ", __file__, __line__);
    $totalR = mswSQL_rows('admin_pages');
    ?>
    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-list fa-fw"></i> <?php echo $msadminpages4_3[0]; ?> (<?php echo mswNFM($totalR); ?>)
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <?php
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_response; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($totalR > 0) {
                while ($PG = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $PG->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td>
                <input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $PG->id; ?>">
                </td>
                <?php
                }
                ?>
                <td>
                <select name="order[<?php echo $PG->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($totalR+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>" <?php echo mswSelectedItem($PG->orderBy,$i,false); ?>>
                <?php echo $i; ?>
                </option>
                <?php
                }
                ?>
                </select>
                </td>
                <td><b><?php echo mswSH($PG->title); ?></b>
                <span class="tdCellInfo">
                <?php
                echo str_replace(array('{count}'),array(($PG->accounts == 'all' ? $msadminlang3_1cspages[12] : count(explode(',',$PG->accounts)))),$msadminpages4_3[4]);
                ?>
                </span>
                </td>
                <td class="text-right">
                  <i class="fa fa-<?php echo ($PG->enPage=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($PG->enPage=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'apgstate','<?php echo $PG->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                  <a href="?p=apages&amp;edit=<?php echo $PG->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>">
                <?php echo $msadminpages4_3[5]; ?>
                </td>
                </tr>
                <?php
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>
          
          <?php
	        if ($totalR > 0) {
          ?>
          <div class="panel-footer">
          <?php
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswButtonOp('apgdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('apgseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
          </div>
	        <?php
	        }
          ?>
        </div>
      </div>
    </div>
    
    </form>

  </div>
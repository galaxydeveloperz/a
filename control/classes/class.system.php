<?php

/* CLASS FILE
----------------------------------*/

class msSystem {

  public $settings;
  public $datetime;

  public function languages() {
    $lang = array();
    if (is_dir(PATH . 'content/language')) {
      $d = opendir(PATH . 'content/language');
      while (false !== ($r = readdir($d))) {
        if (!in_array($r, array('.','..')) && is_dir(PATH . 'content/language/' . $r)) {
          $lang[] = $r;
        }
      }
      closedir($d);
    }
    return $lang;
  }

  public function token() {
    $t = substr(md5(uniqid(rand(), 1)), 3, 30);
    return mswEncrypt($t . SECRET_KEY);
  }

  // Assign ticket status based on value..
  public function status($tstatus, $s = array()) {
    global $msg_script17;
    return (isset($s[$tstatus][0]) ? $s[$tstatus][0] : $msg_script17);
  }

  public function department($id, $msg, $object = false) {
    $DEPT = mswSQL_table('departments', 'id', $id);
    if ($object) {
      return $DEPT;
    }
    return (isset($DEPT->name) ? mswSH($DEPT->name) : $msg);
  }

  public function ticketDepartments($dept = '', $arr = false) {
    $html = '';
    $arrD = array();
    $now  = $this->datetime->mswTimeStamp();
    $day  = $this->datetime->mswDateTimeDisplay($now, 'D', $this->settings->timezone);
    $q_dept = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "departments`
              WHERE `showDept` = 'yes'
              AND (`days` IS NULL OR `days` = '' OR FIND_IN_SET('{$day}', `days`) > 0)
              ORDER BY `orderBy`
              ", __file__, __line__);
    if (mswSQL_numrows($q_dept) > 0) {
      while ($DEPT = mswSQL_fetchobj($q_dept)) {
        $html .= str_replace(array(
          '{value}',
          '{selected}',
          '{text}'
        ), array(
          $DEPT->id,
          mswSelectedItem($dept, $DEPT->id),
          mswSH($DEPT->name)
        ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-department.htm'));
        $arrD[$DEPT->id] = mswSH($DEPT->name);
      }
    }
    return ($arr ? $arrD : $html);
  }

  public function customPages($user = 0, $l) {
    $html = '';
    $menu = array();
    $mnu = array();
    // For legacy versions..
    $wrap = (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages.htm') ? mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages.htm') : '');
    $link = (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages-link.htm') ? mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages-link.htm') : '');
    $q    = mswSQL_query("SELECT `id`, `title` FROM `" . DB_PREFIX . "pages`
            WHERE `enPage` = 'yes'
            " . ($user > 0 ? 'AND `secure` = \'yes\' AND (FIND_IN_SET(\'' . $user . '\', `accounts`) > 0 OR `accounts` = \'all\')' : 'AND `secure` = \'no\'') . "
            ORDER BY `orderBy`
            ", __file__, __line__);
    while ($PG = mswSQL_fetchobj($q)) {
      if ($link) {
        $html .= str_replace(array(
          '{id}',
          '{url}',
          '{title}'
        ),array(
          $PG->id,
          $this->settings->scriptpath,
          mswSH($PG->title)
        ),$link);
      }
      // If user is logged in..
      if ($user > 0) {
        $menu[] = array(
          'id' => $PG->id,
          'name' => mswSH($PG->title)
        );
      } else {
        $mnu[] = array(
          'id' => $PG->id,
          'name' => mswSH($PG->title)
        );
      }
    }
    return array(
      ($html && $wrap ? str_replace(array('{pages}', '{text}'), array($html, $l[8]), $wrap) : ''),
      $menu,
      $mnu
    );
  }

  public function levels($level, $arr = false, $keys = false, $filter = false) {
    $level  = ($level ? strtolower($level) : '');
    $levels = array();
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "levels`
         " . ($filter ? 'WHERE `display` = \'yes\'' : '') . "
         ORDER BY `orderBy`
         ", __file__, __line__);
    while ($L = mswSQL_fetchobj($q)) {
      $levels[($L->marker ? $L->marker : $L->id)] = mswSH($L->name);
    }
    return ($keys ? array_keys($levels) : ($arr ? $levels : (isset($levels[$level]) ? $levels[$level] : $levels['low'])));
  }
  
  public function getAutoCloseIgnoreStatuses() {
    $statuses = array();
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "statuses`
         WHERE `id` > 3
         AND `autoclose` = 'yes'
         ", __file__, __line__);
    while ($S = mswSQL_fetchobj($q)) {
      $statuses[] = $S->id;
    }
    return $statuses;
  }
  
  public function statuses($status, $arr = false) {
    $status   = ($status ? strtolower($status) : '');
    $statuses = array();
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "statuses`
         ORDER BY `orderBy`
         ", __file__, __line__);
    while ($S = mswSQL_fetchobj($q)) {
      $statuses[($S->marker ? $S->marker : $S->id)] = array(
        mswSH($S->name),
        $S->visitor
      );
    }
    return ($arr ? $statuses : (isset($statuses[$status]) ? $statuses[$status] : $statuses['open']));
  }

  public function callback($cmd) {
    // FAQ..
    if (isset($_GET['a']) || isset($_GET['c']) || isset($_GET['q']) || isset($_GET['v'])) {
      $cmd       = (isset($_GET['a']) ? 'que' : (isset($_GET['q']) ? 'search' : 'faq'));
      $_GET['p'] = (isset($_GET['a']) ? 'que' : (isset($_GET['q']) ? 'search' : 'faq'));
    }
    // Verification..
    if (isset($_GET['va'])) {
      $cmd = 'create';
    }
    // Ajax..
    if (isset($_GET['ajax'])) {
      $cmd = 'ajax';
    }
    // Logout..
    if (isset($_GET['lo'])) {
      $cmd = 'login';
    }
    // Custom Page..
    if (isset($_GET['pg'])) {
      $cmd = 'custom-page';
    }
    // View ticket..
    if (isset($_GET['t']) || isset($_GET['attachment'])) {
      $cmd = 'ticket';
    }
    // View dispute..
    if (isset($_GET['d']) || isset($_GET['qd'])) {
      $cmd = 'dispute';
    }
    // Search..
    if (isset($_GET['qt'])) {
      $cmd = 'history';
    }
    // Search Disputes..
    if (isset($_GET['qd'])) {
      $cmd = 'disputes';
    }
    // FAQ attachment..
    if (isset($_GET['dl'])) {
      $cmd = 'faq';
    }
    // Imap..
    if (isset($_GET[$this->settings->imap_param])) {
      $cmd = $this->settings->imap_param;
    }
    // BB code..
    if (isset($_GET['bbcode'])) {
      $cmd = 'home';
    }
    // API..
    if (isset($_GET['api']) || isset($_GET['xml'])) {
      $cmd = 'api';
    }
    return $cmd;
  }

  public function jsCSSBlockLoader($ms_js_css_loader = array(), $loc) {
    $html = array();
    $base = $this->settings->scriptpath . '/content/' . MS_TEMPLATE_SET . '/';
    switch($loc) {
      case 'head':
        // For themes older than 4.0
        if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/css/jquery.uploader.css') && array_key_exists('uploader', $ms_js_css_loader)) {
          $html[] = '<link href="' . $base . 'css/jquery.uploader.css" rel="stylesheet" type="text/css">';
        }
        if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/css/jquery.ibox.css') && array_key_exists('ibox', $ms_js_css_loader)) {
          $html[] = '<link href="' . $base . 'css/jquery.ibox.css" rel="stylesheet" type="text/css">';
        }
        if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/css/bbcode.css') && array_key_exists('bbcode', $ms_js_css_loader)) {
          $html[] = '<link rel="stylesheet" href="' . $base . 'css/bbcode.css" type="text/css">';
        }
        break;
      case 'foot':
        if (array_key_exists('ibox', $ms_js_css_loader)) {
          $html[] = '<script src="' . $base . 'js/plugins/jquery.ibox.js"></script>';
        }
        break;
    }
    return (!empty($html) ? implode(mswNL(), $html) : '');
  }

}

?>
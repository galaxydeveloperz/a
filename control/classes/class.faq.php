<?php

/* CLASS FILE
----------------------------------*/

class msFAQ {

  public $settings;
  public $dt;
  public $ssn;

  // Cat permissions..
  public function catrestr($acc = 0) {
    $cats = array();
    $q = mswSQL_query("SELECT `id`, `accounts` FROM `" . DB_PREFIX . "categories`
         WHERE `private` = 'yes'
         ", __file__, __line__);
    while ($C = mswSQL_fetchobj($q)) {
      if ($acc == 0) {
        $cats[] = $C->id;
      } else {
        if ($acc > 0 && !in_array($C->accounts, array(null, '', 'all'))) {
          if (!in_array($acc, explode(',', $C->accounts))) {
            $cats[] = $C->id;
          }
        }
      }
    }
    return $cats;
  }

  // Voting stats..
  public function stats($id) {
    $a    = array('0%', '0%', '0');
    $KB   = mswSQL_table('faq', 'id', $id);
    if (isset($KB->kuseful)) {
      $yes  = ($KB->kviews > 0 ? mswNFM(($KB->kuseful / $KB->kviews) * 100, 2) : '0.00');
      $no   = ($KB->kviews > 0 ? mswNFM(($KB->knotuseful / $KB->kviews) * 100, 2) : '0.00');
      if (substr($yes, -3) == '.00') {
        $yes = substr($yes, 0, -3);
      }
      if (substr($no, -3) == '.00') {
        $no = substr($no, 0, -3);
      }
      return array($yes . '%', $no . '%', mswNFM($KB->kviews));
    }
    return $a;
  }

  // Voting system..
  public function vote() {
    $id    = (int) $_GET['id'];
    $votes = array();
    if ($id > 0 && in_array($_GET['vote'], array('yes','no'))) {
      switch ($_GET['vote']) {
        case 'no':
          $table = '`knotuseful` = (`knotuseful` + 1)';
          break;
        default:
          $table = '`kuseful` = (`kuseful` + 1)';
          break;
      }
      mswSQL_query("UPDATE `" . DB_PREFIX . "faq` SET
      `kviews`   = (`kviews` + 1),
      $table
      WHERE `id` = '{$id}'
      ", __file__, __line__);
      // If multiple votes aren`t allowed, set cookie..
      if ($this->settings->multiplevotes == 'no') {
        // If cookie is set, get array of ids and update with new id..
        // If not set, just add id to array..
        if ($this->ssn->active_c(COOKIE_NAME) == 'yes') {
          $votes   = unserialize($this->ssn->get_c(COOKIE_NAME));
          $votes[] = $id;
          // Clear the cookie..
          $this->ssn->delete_c(array(COOKIE_NAME));
        } else {
          $votes[] = $id;
        }
        // Set cookie..
        $this->ssn->set_c(array(
          array(
            COOKIE_NAME,
            serialize($votes),
            $this->dt->mswTimeStamp() + 60 * 60 * 24 * $this->settings->cookiedays
          )
        ));
      }
      return 'ok';
    }
    return 'fail';
  }

  // Attachments..
  public function attachments() {
    $html = array();
    $id   = (int) $_GET['a'];
    $q = mswSQL_query("SELECT *,
         `" . DB_PREFIX . "faqattach`.`id` AS `attachID`
		     FROM `" . DB_PREFIX . "faqassign`
         LEFT JOIN `" . DB_PREFIX . "faqattach`
         ON `" . DB_PREFIX . "faqassign`.`itemID`      = `" . DB_PREFIX . "faqattach`.`id`
         WHERE `" . DB_PREFIX . "faqassign`.`question` = '{$id}'
		     AND `" . DB_PREFIX . "faqassign`.`desc`       = 'attachment'
         AND `" . DB_PREFIX . "faqattach`.`enAtt`      = 'yes'
         GROUP BY `" . DB_PREFIX . "faqassign`.`itemID`
         ORDER BY `" . DB_PREFIX . "faqattach`.`orderBy`
         ", __file__, __line__);
    while ($ATT = mswSQL_fetchobj($q)) {
      $show = 'yes';
      $ext  = substr(strrchr(($ATT->path ? $ATT->path : $ATT->remote), '.'), 1);
      // Check local file exists..
      if ($ATT->path && !file_exists($this->settings->attachpathfaq . '/' . $ATT->path)) {
        $show = 'no';
      }
      if ($show == 'yes') {
        $html[] = str_replace(array(
          '{url}',
          '{id}',
          '{name}',
          '{name_alt}',
          '{size}',
          '{que}',
          '{filetype}'
        ), array(
          ($ATT->remote ? $ATT->remote : '?dl=' . $ATT->attachID),
          $ATT->attachID,
          ($ATT->name ? mswCD($ATT->name) : ($ATT->remote ? basename($ATT->remote) : $ATT->path)),
          ($ATT->name ? mswSH($ATT->name) : ($ATT->remote ? basename($ATT->remote) : $ATT->path)),
          mswFSC($ATT->size),
          $id,
          strtoupper($ext)
        ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-attachment-link.htm')) . mswNL();
      }
    }
    return (!empty($html) ? implode(mswNL(), $html) : '');
  }

  // FAQ questions..
  public function questions($data = array()) {
    $str = array();
    if ($this->settings->kbase == 'no') {
      return '';
    }
    $qry = '';
    // Restricted cats..
    $restr_cats = msFAQ::catrestr($data['account']);
    if (!empty($restr_cats)) {
      $qry .= mswNL() . 'AND `' . DB_PREFIX . 'faq`.`cat` NOT IN(' . implode(',', $restr_cats) . ')';
    }
    // Search mode..
    if (isset($data['search'][0], $data['search'][1])) {
      $q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
	         `" . DB_PREFIX . "faq`.`id` AS `faqID`,
		       `" . DB_PREFIX . "faq`.`question` AS `faqQuestion`
		       FROM `" . DB_PREFIX . "faq`
           LEFT JOIN `" . DB_PREFIX . "categories`
		       ON `" . DB_PREFIX . "categories`.`id`  = `" . DB_PREFIX . "faq`.`cat`
           " . $data['search'][0] . "
		       AND `" . DB_PREFIX . "faq`.`enFaq` = 'yes'
           AND `" . DB_PREFIX . "categories`.`enCat` = 'yes'
		       " . (!empty($restr_cats) ? 'AND `' . DB_PREFIX . 'categories`.`id` NOT IN(' . implode(',', $restr_cats) . ')' : '') . "
           GROUP BY `" . DB_PREFIX . "faq`.`id`
           ORDER BY `" . DB_PREFIX . "faq`.`orderBy`
		       LIMIT " . $data['limit'] . "," . $this->settings->quePerPage, __file__, __line__);
      if ($data['search'][1] == 'yes') {
        $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
        return (isset($c->rows) ? $c->rows : '0');
      }
    } else {
      $q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
	         `" . DB_PREFIX . "faq`.`id` AS `faqID`,
		       `" . DB_PREFIX . "faq`.`question` AS `faqQuestion`
		       FROM `" . DB_PREFIX . "faq`
	         LEFT JOIN `" . DB_PREFIX . "categories`
		       ON `" . DB_PREFIX . "categories`.`id`  = `" . DB_PREFIX . "faq`.`cat`
           WHERE `" . DB_PREFIX . "faq`.`enFaq` = 'yes'
           AND `" . DB_PREFIX . "categories`.`enCat` = 'yes'
		       " . (isset($data['flag']) ? $data['flag'] : '') . "
           " . ($data['id'] > 0 ? 'AND `' . DB_PREFIX . 'faq`.`cat` = \'' . $data['id'] . '\'' : '') . "
		       " . (!empty($restr_cats) ? 'AND `' . DB_PREFIX . 'categories`.`id` NOT IN(' . implode(',', $restr_cats) . ')' : '') . "
           " . (isset($data['queryadd']) ? $data['queryadd'] : '') . "
		       ORDER BY " . (isset($data['orderor']) ? $data['orderor'] : '`' . DB_PREFIX . 'faq`.`orderBy`') . "
		       LIMIT " . $data['limit'] . "," . (isset($data['show_limit']) && $data['show_limit'] > 0 ? $data['show_limit'] : $this->settings->quePerPage), __file__, __line__);
      if (isset($data['count'])) {
        $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
        return (isset($c->rows) ? $c->rows : '0');
      }
    }
    while ($LINKS = mswSQL_fetchobj($q)) {
      $str[] = str_replace(array(
        '{article}',
        '{url_params}',
        '{question}',
        '{count}'
      ), array(
        $LINKS->faqID,
        mswQueryParams(array(
          'a',
          'p',
          'q'
        )),
        mswCD($LINKS->faqQuestion),
        mswNFM($LINKS->kviews)
      ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-question-link.htm'));
    }
    return (!empty($str) ? implode(mswNL(), $str) : str_replace('{text}', $data['l'][0], mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/nothing-found.htm')));
  }

  // FAQ category links..
  public function menu($data = array()) {
    $str = array();
    $mnu = array();
    if ($this->settings->kbase == 'no') {
      return $str;
    }
    $qry = '';
    // Private categories..
    if ($data['account'] > 0) {
      if (isset($data['private_cats'])) {
        $qry = 'AND (`private` = \'yes\' AND (FIND_IN_SET(\'' . $data['account'] . '\', `accounts`) > 0 OR FIND_IN_SET(\'all\', `accounts`) > 0))';
      } elseif (isset($data['private_cat']) && $data['private_cat'] == 'yes') {
        $qry = 'AND (`private` = \'yes\' AND (FIND_IN_SET(\'' . $data['account'] . '\', `accounts`) > 0 OR FIND_IN_SET(\'all\', `accounts`) > 0))';
      } else {
        $qry = 'AND ((`private` = \'yes\' AND `accounts` IS NULL OR `accounts` IN(\'\',\'all\')) OR `private` = \'no\')';
      }
    } else {
      $qry = 'AND `private` = \'no\'';
    }
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "categories`
         WHERE `enCat`  = 'yes'
         AND `subcat`   = '" . (isset($data['parent']) ? $data['parent'] : '0') . "'
         " . $qry . "
         ORDER BY `orderBy`
         ", __file__, __line__);
    while ($CATS = mswSQL_fetchobj($q)) {
      $count = '';
      if ($this->settings->faqcounts == 'yes') {
        $count = mswSQL_rows('faq WHERE `cat` = \'' . $CATS->id . '\' AND `enFaq` = \'yes\'');
      }
      // For legacy versions..
      if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-cat-menu-link.htm')) {
        $str[] = str_replace(array(
          '{cat}',
          '{url}',
          '{category}',
          '{count}'
        ), array(
          $CATS->id,
          $this->settings->scriptpath,
          mswSH($CATS->name),
          ($count ? ' (' . mswNFM($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : ''))
        ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-cat-menu-link.htm'));
      }
      $mnu[$CATS->id] = array(
        'id' => $CATS->id,
        'name' => mswSH($CATS->name),
        'count' => ($count ? ' (' . mswNFM($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : '')),
        'subs' => array()
      );
      // Sub categories..
      $loadSubCats = 'yes';
      if ($loadSubCats == 'yes' && !isset($data['parent'])) {
        $qS = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "categories`
              WHERE `enCat` = 'yes'
              AND `subcat`  = '{$CATS->id}'
              ORDER BY `orderBy`
              ", __file__, __line__);
        while ($SUBS = mswSQL_fetchobj($qS)) {
          $count = '';
          if ($this->settings->faqcounts == 'yes') {
            $count = mswSQL_rows('faq WHERE `cat` = \'' . $SUBS->id . '\' AND `enFaq` = \'yes\'');
          }
          $mnu[$CATS->id]['subs'][] = array(
            'id' => $SUBS->id,
            'name' => mswSH($SUBS->name),
            'count' => ($count ? ' (' . mswNFM($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : ''))
          );
          // For legacy versions..
          if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-sub-menu-link.htm')) {
            $str[] = str_replace(array(
              '{cat}',
              '{subcat}',
              '{url}',
              '{category}',
              '{category-alt}',
              '{count}'
            ), array(
              $CATS->id,
              $SUBS->id,
              $this->settings->scriptpath,
              mswSH($SUBS->name),
              mswSH($SUBS->name . ' (' . $CATS->name . ')'),
              ($count ? ' (' . mswNFM($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : ''))
            ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-sub-menu-link.htm'));
          }
        }
      }
    }
    return array(
      'string' => (!empty($str) ? implode(mswNL(), $str) : ''),
      'array' => $mnu
    );
  }

  public function token($op, $id) {
    switch($op) {
      case 'create':
        $token = substr(md5(uniqid(rand(),1)), 3, 40) . time();
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "faqdl` (
        `question`,
        `token`
        ) VALUES (
        '{$id}',
        '{$token}'
        )", __file__, __line__);
        return (mswSQL_insert_id() > 0 ? $token : '');
        break;
      case 'clear':
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "faqdl` WHERE `id` = '{$id}'", __file__, __line__);
        mswSQL_truncate(array('faqdl'));
        break;
    }
  }

}

?>
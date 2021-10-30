<?php

/* CLASS FILE
----------------------------------*/

class customFieldManager {

  public $parser;
  public $dt;

  // Mysql..
  public function insert($ticketID, $fieldID, $replyID, $data) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
    `ticketID`,`fieldID`,`replyID`,`fieldData`
    ) VALUES (
    '{$ticketID}','{$fieldID}','{$replyID}','" . mswSQL($data) . "'
    )", __file__, __line__);
  }

  // Display..
  public function display($ticketID, $replyID = 0, $count = 0, $label = 'panel panel-default') {
    $html = '';
    $wrap = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields-wrapper.htm');
    $qT = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "ticketfields`
          LEFT JOIN `" . DB_PREFIX . "cusfields`
          ON `" . DB_PREFIX . "cusfields`.`id` = `" . DB_PREFIX . "ticketfields`.`fieldID`
          WHERE `ticketID`  = '{$ticketID}'
          AND `replyID`     = '{$replyID}'
			    AND `enField`     = 'yes'
          AND `fieldData`  != 'nothing-selected'
          AND `fieldData`  != ''
          ORDER BY `" . DB_PREFIX . "cusfields`.`id`
          ", __file__, __line__);
    if ($count) {
      return mswSQL_numrows($qT);
    }
    while ($TS = mswSQL_fetchobj($qT)) {
      if ($TS->repeatPref == 'no' && strpos($TS->fieldLoc, 'admin') !== false) {
      } else {
        switch ($TS->fieldType) {
          case 'textarea':
          case 'input':
          case 'select':
          case 'calendar':
            $html .= str_replace(array(
              '{head}',
              '{data}',
              '{label}'
            ), array(
              mswCD($TS->fieldInstructions),
              $this->parser->mswTxtParsingEngine($TS->fieldData),
              $label
            ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields.htm'));
            break;
          case 'checkbox':
            $html .= str_replace(array(
              '{head}',
              '{data}',
              '{label}'
            ), array(
              mswCD($TS->fieldInstructions),
              str_replace('#####', '<br>', mswCD($TS->fieldData)),
              $label
            ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-custom-fields.htm'));
            break;
        }
      }
    }
    return ($html ? str_replace('{fields}', trim($html), $wrap) : '');
  }

  // Return data for emails..
  public function email($ticketID, $replyID = 0) {
    global $msg_script17;
    $text = '';
    $qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
          LEFT JOIN `" . DB_PREFIX . "ticketfields`
          ON `" . DB_PREFIX . "cusfields`.`id` = `" . DB_PREFIX . "ticketfields`.`fieldID`
          WHERE `ticketID`  = '{$ticketID}'
          AND `replyID`     = '{$replyID}'
          AND `enField`     = 'yes'
          ORDER BY `" . DB_PREFIX . "cusfields`.`orderBy`
          ", __file__, __line__);
    if (mswSQL_numrows($qF) > 0) {
      while ($FIELDS = mswSQL_fetchobj($qF)) {
        switch ($FIELDS->fieldType) {
          case 'checkbox':
            $text .= mswCD($FIELDS->fieldInstructions) . mswNL();
            $text .= str_replace('#####', mswNL(), mswCD($FIELDS->fieldData)) . mswNL(2);
            break;
          default:
            $text .= mswCD($FIELDS->fieldInstructions) . mswNL();
            $text .= mswCD($FIELDS->fieldData) . mswNL(2);
            break;
        }
      }
    }
    return ($text ? trim($text) : $msg_script17);
  }

  // Insert and return data..
  public function data($area, $ticketID, $replyID = 0, $dept, $acc = 0) {
    global $msg_script17;
    $text = '';
    if ($acc > 0) {
      $sql = 'AND (FIND_IN_SET(\'' . (int) $acc . '\', `accounts`) > 0 OR `accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    } else {
      $sql = 'AND (`accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    }
    $qF  = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
           WHERE FIND_IN_SET('{$area}', `fieldLoc`) > 0
           AND FIND_IN_SET('{$dept}', `departments`) > 0
           $sql
           AND `enField` = 'yes'
           ORDER BY `orderBy`
           ", __file__, __line__);
    if (mswSQL_numrows($qF) > 0) {
      while ($FIELDS = mswSQL_fetchobj($qF)) {
        switch ($FIELDS->fieldType) {
          case 'textarea':
          case 'input':
          case 'calendar':
            if ($_POST['customField'][$FIELDS->id] != '') {
              $text .= mswCD($FIELDS->fieldInstructions) . mswNL();
              $text .= $_POST['customField'][$FIELDS->id] . mswNL(2);
            }
            break;
          case 'select':
            if ($_POST['customField'][$FIELDS->id] != 'nothing-selected') {
              $text .= mswCD($FIELDS->fieldInstructions) . mswNL();
              $text .= $_POST['customField'][$FIELDS->id] . mswNL(2);
            }
            break;
          case 'checkbox':
            if (!empty($_POST['customField'][$FIELDS->id])) {
              $text .= mswCD($FIELDS->fieldInstructions) . mswNL();
              foreach ($_POST['customField'][$FIELDS->id] AS $k => $v) {
                $text .= $v . mswNL();
              }
              $text .= mswNL();
            }
            break;
        }
      }
    }
    return ($text ? trim($text) : $msg_script17);
  }

  // Check required fields..
  public function check($area, $dept, $acc = 0) {
    $e = array();
    if ($acc > 0) {
      $sql = 'AND (FIND_IN_SET(\'' . (int) $acc . '\', `accounts`) > 0 OR `accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    } else {
      $sql = 'AND (`accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    }
    $qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
          WHERE FIND_IN_SET('{$area}',`fieldLoc`) > 0
          AND FIND_IN_SET('{$dept}',`departments`) > 0
          AND `fieldReq`  = 'yes'
          AND `enField`   = 'yes'
          $sql
          ORDER BY `orderBy`
          ", __file__, __line__);
    if (mswSQL_numrows($qF) > 0) {
      while ($FIELDS = mswSQL_fetchobj($qF)) {
        switch ($FIELDS->fieldType) {
          case 'textarea':
          case 'input':
            if (isset($_POST['customField'][$FIELDS->id]) && $_POST['customField'][$FIELDS->id] == '') {
              $e[] = mswSH($FIELDS->fieldInstructions);
            }
            break;
          case 'calendar':
            if (isset($_POST['customField'][$FIELDS->id])) {
              $get = array();
              if ($_POST['customField'][$FIELDS->id]) {
                $get = explode('-', $this->dt->mswDatePickerFormat(substr($_POST['customField'][$FIELDS->id], 0, 10)));
              }
              $date = array(
                'mon' => (isset($get[1]) ? preg_replace('/[^0-9]/', '', $get[1]) : '00'),
                'day' => (isset($get[2]) ? preg_replace('/[^0-9]/', '', $get[2]) : '00'),
                'year' => (isset($get[0]) ? preg_replace('/[^0-9]/', '', $get[0]) : '0000')
              );
              if (!checkdate($date['mon'], $date['day'], $date['year'])) {
                $e[] = mswSH($FIELDS->fieldInstructions);
              }
            }
            break;
          case 'select':
            if (isset($_POST['customField'][$FIELDS->id]) && $_POST['customField'][$FIELDS->id] == 'nothing-selected') {
              $e[] = mswSH($FIELDS->fieldInstructions);
            }
            break;
          case 'checkbox':
            if (empty($_POST['customField'][$FIELDS->id])) {
              $e[] = mswSH($FIELDS->fieldInstructions);
            }
            break;
        }
      }
    }
    return $e;
  }

  // Render new fields..
  public function build($area, $dept, $acc = 0) {
    $html = '';
    $tab  = 6;
    if ($acc > 0) {
      $sql = 'AND (FIND_IN_SET(\'' . (int) $acc . '\', `accounts`) > 0 OR `accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    } else {
      $sql = 'AND (`accounts` IS NULL OR `accounts` = \'\' OR `accounts` = \'all\')';
    }
    $qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
          WHERE FIND_IN_SET('{$area}',`fieldLoc`) > 0
          AND FIND_IN_SET('{$dept}',`departments`) > 0
          AND `enField`  = 'yes'
          $sql
          ORDER BY `orderBy`
          ", __file__, __line__);
    if (mswSQL_numrows($qF) > 0) {
      while ($F = mswSQL_fetchobj($qF)) {
        switch ($F->fieldType) {
          case 'textarea':
            $html .= customFieldManager::textarea(mswCD($F->fieldInstructions), $F->id, ++$tab, $F->fieldReq);
            break;
          case 'calendar':
            $html .= customFieldManager::calendar(mswCD($F->fieldInstructions), $F->id, ++$tab, $F->fieldReq);
            break;
          case 'input':
            $html .= customFieldManager::box(mswCD($F->fieldInstructions), $F->id, ++$tab, $F->fieldReq);
            break;
          case 'select':
            $html .= customFieldManager::select(mswCD($F->fieldInstructions), $F->id, $F->fieldOptions, ++$tab, $F->fieldReq);
            break;
          case 'checkbox':
            $html .= customFieldManager::checkbox(mswCD($F->fieldInstructions), $F->id, $F->fieldOptions, $F->fieldReq);
            break;
        }
      }
    }
    return ($html ? trim($html) : '');
  }

  // Create select/drop down menu..
  public function select($text, $id, $options, $tab, $req) {
    global $msadminlang3_1createticket;
    $html    = '';
    $wrapper = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/select.htm');
    $rqfld   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    $select  = explode(mswNL(), $options);
    foreach ($select AS $o) {
      $html .= str_replace(array(
        '{value}',
        '{selected}',
        '{text}'
      ), array(
        mswCD($o),
        (isset($_POST['customField'][$id]) ? mswSelectedItem($_POST['customField'][$id], $o) : ''),
        mswCD($o)
      ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/select-option.htm'));
    }
    return str_replace(array(
      '{id}',
      '{options}',
      '{label}',
      '{tab}',
      '{req}'
    ), array(
      $id,
      trim($html),
      mswCD($text),
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), $wrapper);
  }

  // Create checkbox..
  public function checkbox($text, $id, $options, $req) {
    global $msg_viewticket71, $msadminlang3_1createticket;
    $wrapper = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/checkbox-wrapper.htm');
    $rqfld   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    $html    = '';
    $v       = array();
    $boxes   = explode(mswNL(), $options);
    if (isset($_POST['customField'][$id]) && !empty($_POST['customField'][$id])) {
      $v = $_POST['customField'][$id];
    }
    foreach ($boxes AS $cb) {
      $html .= str_replace(array(
        '{value}',
        '{checked}',
        '{id}'
      ), array(
        mswCD($cb),
        (in_array($cb, $v) ? ' checked="checked"' : ''),
        $id
      ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/checkbox.htm'));
    }
    return str_replace(array(
      '{label}',
      '{text}',
      '{checkboxes}',
      '{id}',
      '{req}'
    ), array(
      mswCD($text),
      $msg_viewticket71,
      trim($html),
      $id,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), $wrapper);
  }

  // Create input box..
  public function box($text, $id, $tab, $req) {
    global $msadminlang3_1createticket;
    $rqfld   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    return str_replace(array(
      '{label}',
      '{value}',
      '{id}',
      '{tab}',
      '{req}'
    ), array(
      mswCD($text),
      (isset($_POST['customField'][$id]) ? mswSH($_POST['customField'][$id]) : ''),
      $id,
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/input-box.htm'));
  }
  
  // Create input calendar..
  public function calendar($text, $id, $tab, $req) {
    global $msadminlang3_1createticket;
    $rqfld   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    return str_replace(array(
      '{label}',
      '{value}',
      '{id}',
      '{tab}',
      '{req}'
    ), array(
      mswCD($text),
      (isset($_POST['customField'][$id]) ? mswSH($_POST['customField'][$id]) : ''),
      $id,
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/input-calendar.htm'));
  }

  // Create textarea..
  public function textarea($text, $id, $tab, $req) {
    global $msadminlang3_1createticket;
    $rqfld   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/required-field.htm');
    return str_replace(array(
      '{label}',
      '{value}',
      '{id}',
      '{tab}',
      '{req}'
    ), array(
      mswCD($text),
      (isset($_POST['customField'][$id]) ? mswSH($_POST['customField'][$id]) : ''),
      $id,
      $tab,
      ($req == 'yes' ? str_replace('{text}', $msadminlang3_1createticket[9], $rqfld) : '')
    ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-fields/textarea.htm'));
  }

}

?>
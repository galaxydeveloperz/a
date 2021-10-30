<?php

/* CLASS FILE
----------------------------------*/

class fieldManager {

  // Create select/drop down menu..
  public function buildSelect($text, $id, $options, $tabIndex, $value = '') {
    $html   = '<option value="nothing-selected">- - - - - - -</option>';
    $select = explode(mswNL(), $options);
    foreach ($select AS $o) {
      $html .= '<option value="' . mswSH($o) . '"' . mswSelectedItem($value, $o) . '>' . mswCD($o) . '</option>' . mswNL();
    }
    return mswNL() . '<div class="form-group"><label>' . $text . '</label>' . mswNL() . '<select name="customField[' . $id . ']" tabindex="' . $tabIndex . '" class="form-control">' . $html . '</select></div>' . mswNL();
  }

  // Create checkbox..
  public function buildCheckBox($text, $id, $options, $values = '') {
    $html  = '';
    $v     = array();
    $boxes = explode(mswNL(), $options);
    if ($values) {
      $v = explode('#####', $values);
    }
    foreach ($boxes AS $cb) {
      $html .= '<div class="checkbox"><label><input type="checkbox" name="customField[' . $id . '][]" value="' . mswSH($cb) . '"' . (in_array($cb, $v) ? ' checked="checked"' : '') . '> ' . $cb . '</label></div>' . mswNL();
    }
    return ($html ? mswNL() . '<div class="form-group"><input type="hidden" name="hiddenBoxes[]" value="' . $id . '"><label>' . $text . '</label>' . $html . '</div>' : '');
  }

  // Create input box..
  public function buildInputBox($text, $id, $tabIndex, $value = '') {
    return mswNL() . '<div class="form-group"><label>' . $text . '</label>' . mswNL() . '<input tabindex="' . $tabIndex . '" class="form-control" type="text" name="customField[' . $id . ']" value="' . mswSH($value) . '"></div>' . mswNL();
  }
  
  // Create calendar..
  public function buildCalBox($text, $id, $tabIndex, $value = '') {
    return mswNL() . '<div class="form-group"><label>' . $text . '</label>' . mswNL() . '<input tabindex="' . $tabIndex . '" class="form-control jsdatepicker" type="text" maxlength="10" name="customField[' . $id . ']" value="' . mswSH($value) . '"></div>' . mswNL();
  }

  // Create textarea..
  public function buildTextArea($text, $id, $tabIndex, $value = '') {
    return mswNL() . '<div class="form-group"><label>' . $text . '</label>' . mswNL() . '<textarea tabindex="' . $tabIndex . '" rows="5" cols="40" name="customField[' . $id . ']" class="form-control">' . mswSH($value) . '</textarea></div>' . mswNL();
  }

}

?>
<?php

/* CLASS FILE
----------------------------------*/

class msBootStrap {

  // Drop down button..
  public function button($d = array()) {
    $html = array();
    switch ($d['area']) {
      case 'admin':
        $sep    = mswTmp(PATH . 'templates/system/html/bootstrap/drop-down-button-li-sep.htm');
        $button = mswTmp(PATH . 'templates/system/html/bootstrap/drop-down-button' . (!isset($d['no-mobile']) && in_array(MSW_PFDTCT, array('mobile', 'tablet')) ? '-mobile' : '') . '.htm');
        $link   = mswTmp(PATH . 'templates/system/html/bootstrap/drop-down-button-li.htm');
        break;
      default:
        $button = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/bootstrap/drop-down-button.htm');
        $link   = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/bootstrap/drop-down-button-li.htm');
        break;
    }
    foreach ($d['links'] AS $l => $v) {
      $html[] = str_replace(array(
        '{link}',
        '{text}',
        '{extra}',
        '{icon}',
        '{active}'
      ), array(
        $v['link'],
        (isset($v['name']) ? $v['name'] : ''),
        (isset($v['extra']) ? ' ' . $v['extra'] : ''),
        (isset($v['icon']) ? $v['icon'] : ''),
        (isset($v['active']) ? $v['active'] : '')
      ), ($v['link'] == 'sep' && isset($sep) ? $sep : $link));
    }
    return str_replace(array(
      '{text}',
      '{links}',
      '{orientation}',
      '{icon}',
      '{centered}',
      '{id}'
    ), array(
      $d['text'],
      (!empty($html) ? implode(mswNL(), $html) : ''),
      $d['orientation'],
      ($d['icon'] ? $d['icon'] : ($d['orientation'] == ' dropdown-menu-right' ? 'filter' : 'sort')),
      ($d['centered'] == 'yes' ? ' center_dropdown' : ''),
      (isset($d['id']) ? $d['id'] : '')
    ), $button);
  }

}

?>
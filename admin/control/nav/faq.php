<?php if (!defined('PARENT')) { exit; }

/* MENU: FAQ
========================================================*/

if ($SETTINGS->kbase == 'yes') {
  $faqMenuArr = array('faq-cat','faq','attach','attachman','faqman','faq-catman','faq-import');
  $mR10       = array_intersect($faqMenuArr, $userAccess);
  $mR10_en    = (isset($nMenu['faq']['en']) && in_array($nMenu['faq']['en'], array('yes','no')) ? $nMenu['faq']['en'] : 'yes');

  if (!empty($mR10) || USER_ADMINISTRATOR == 'yes') {

    $slidePanelLeftMenu['faq']          = array($msg_adheader17, 'book', $mR10_en);
    $slidePanelLeftMenu['faq']['links'] = array();

    if ($mR10_en == 'yes') {
    
      // Add FAQ category..
      if (in_array('faq-cat', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=faq-cat',
          'name' => $msg_adheader44
        );
      }

      // Manage FAQ categories..
      if (in_array('faq-catman', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=faq-catman',
          'name' => $msg_adheader45
        );
      }

      // Add FAQ question..
      if (in_array('faq', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=faq',
          'name' => $msg_adheader46
        );
      }

      // Manage FAQ questions..
      if (in_array('faqman', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=faqman',
          'name' => $msg_adheader47
        );
      }

      // Import FAQ questions..
      if (in_array('faq-import', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=faq-import',
          'name' => $msg_adheader55
        );
      }

      // Add attachments..
      if (in_array('attach', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=attachments',
          'name' => $msg_adheader48
        );
      }

      // Manage attachments..
      if (in_array('attachman', $mR10) || USER_ADMINISTRATOR == 'yes') {
        $slidePanelLeftMenu['faq']['links'][] = array(
          'url' => '?p=attachman',
          'name' => $msg_adheader49
        );
      }
    
    }

  }
}

?>
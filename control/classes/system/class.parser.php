<?php

/* CLASS FILE
----------------------------------*/

class msDataParser {

  public $bbCode;
  public $settings;

  // Display text based on whats enabled..
  public function mswTxtParsingEngine($text, $admin = true) {
    $text = trim($text);
    if ($this->settings->enableBBCode == 'yes' || $admin) {
      return msDataParser::mswWordWrap($this->bbCode->bbParser($text));
    } else {
      return msDataParser::mswWordWrap(mswNL2BR(msDataParser::mswAutoLinkParser($text)));
    }
  }

  // Wordwrap..
  public function mswWordWrap($text) {
    $ww = ($this->settings->wordwrap ? unserialize($this->settings->wordwrap) : array());
    if (isset($ww[MSW_PFDTCT]) && $ww[MSW_PFDTCT] > 0) {
      return wordwrap($text, $ww[MSW_PFDTCT], mswNL(), true);
    }
    return $text;
  }

  // Make urls clickable..
  public function mswClickableUrl($matches) {
    $ret = '';
    $url = $matches[2];
    if (empty($url)) {
      return $matches[0];
    }
    // removed trailing [.,;:] from URL
    if (in_array(substr($url, -1), array(
      '.',
      ',',
      ';',
      ':'
    )) === true) {
      $ret = substr($url, -1);
      $url = substr($url, 0, strlen($url) - 1);
    }
    return $matches[1] . '<a href="' . $url . '" rel="nofollow" onclick="window.open(this);return false" title="' . $url . '">' . $url . '</a>' . $ret;
  }

  // Make FTP links clickable..
  public function mswClickableFTP($matches) {
    $ret  = '';
    $dest = $matches[2];
    $dest = 'http://' . $dest;
    if (empty($dest)) {
      return $matches[0];
    }
    // removed trailing [,;:] from URL
    if (in_array(substr($dest, -1), array(
      '.',
      ',',
      ';',
      ':'
    )) === true) {
      $ret  = substr($dest, -1);
      $dest = substr($dest, 0, strlen($dest) - 1);
    }
    return $matches[1] . '<a href="' . $dest . '" rel="nofollow" onclick="window.open(this);return false" title="' . $dest . '">' . $dest . '</a>' . $ret;
  }

  // Hyperlinks, no protocol..
  public function mswClickableUrlNP($matches) {
    $dest = $matches[2] . '.' . $matches[3] . $matches[4];
    return $matches[1] . '<a href="http://' . $dest . '" rel="nofollow">' . $dest . '</a>';
  }

  // Make email links clickable..
  public function mswClickableEmail($matches) {
    $email = $matches[2] . '@' . $matches[3];
    return $matches[1] . '<a href="mailto:' . $email . '" title="' . $email . '" rel="nofollow">' . $email . '</a>';
  }

  // Callback functions for link parsing..
  public function mswAutoLinkParser($data) {
    //$data = mswSH($data);
    $ext  = 'com|org|net|gov|edu|mil|co.uk|uk.com|us|info|biz|ws|name|mobi|cc|tv';
    // Auto parse links..borrowed from Wordpress..:)
    $data = preg_replace_callback('#(?!<.*?)(?<=[\s>])(\()?(([\w]+?)://((?:[\w\\x80-\\xff\#$%&~/\-=?@\[\](+]|[.,;:](?![\s<])|(?(1)\)(?![\s<])|\)))+))(?![^<>]*?>)#is', array(
      $this,
      'mswClickableUrl'
    ), $data);
    $data = preg_replace_callback("#(?!<.*?)([\s{}\(\)\[\]>])([a-z0-9\-\.]+[a-z0-9\-])\.($ext)((?:[/\#?][^\s<{}\(\)\[\]]*[^\.,\s<{}\(\)\[\]]?)?)(?![^<>]*?>)#is", array(
      $this,
      'mswClickableUrlNP'
    ), $data);
    $data = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', array(
      $this,
      'mswClickableFTP'
    ), $data);
    $data = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', array(
      $this,
      'mswClickableEmail'
    ), $data);
    // Clean links within links..
    $data = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $data);
    return $data;
  }

}

?>
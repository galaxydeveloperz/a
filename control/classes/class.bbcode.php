<?php

/* CLASS FILE
----------------------------------*/

class bbCode_Parser {

  const YOU_TUBE_EMBED_CODE = '<div class="youtube-container"><iframe src="https://www.youtube.com/embed/{CODE}" style="border:0 !important" allowfullscreen></iframe></div>';
  const VIMEO_EMBED_CODE = '<div class="vimeo-container"><iframe src="https://player.vimeo.com/video/{ID}" style="border:0 !important" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
  const DAILY_MOTION_EMBED_CODE = '<div class="dailymotion-container"><iframe src="https://www.dailymotion.com/embed/video/{CODE}" style="border:0 !important" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';

  // General parser..
  public function bbParser($text) {
    // Check for square brackets. If not found, no bb code exists..
    if (strpos($text, '[') === false && strpos($text, ']') === false) {
      return mswNL2BR(htmlspecialchars($text));
    }
    $tagList = array(
      '[b]' => '<span class="bbBold">',
      '[u]' => '<span class="bbUnderline">',
      '[i]' => '<span class="bbItalics">',
      '[s]' => '<span class="bbStrike">',
      '[del]' => '<span class="bbDel">',
      '[ins]' => '<span class="bbIns">',
      '[em]' => '<span class="bbEm">',
      '[h1]' => '<span class="bbH1">',
      '[h2]' => '<span class="bbH2">',
      '[h3]' => '<span class="bbH3">',
      '[h4]' => '<span class="bbH4">',
      '[center]' => '<span class="bbCentre">',
      '[list]' => '<ul class="bbUl">',
      '[list=n]' => '<ul class="bbUlNumbered">',
      '[list=a]' => '<ul class="bbUlAlpha">',
      '[list=ua]' => '<ul class="bbUlUpperAlpha">',
      '[*]' => '<li class="bbLi">',
      '[B]' => '<span class="bbBold">',
      '[U]' => '<span class="bbUnderline">',
      '[I]' => '<span class="bbItalics">',
      '[S]' => '<span class="bbStrike">',
      '[DEL]' => '<span class="bbDel">',
      '[INS]' => '<span class="bbIns">',
      '[EM]' => '<span class="bbEm">',
      '[H1]' => '<span class="bbH1">',
      '[H2]' => '<span class="bbH2">',
      '[H3]' => '<span class="bbH3">',
      '[H4]' => '<span class="bbH4">',
      '[CENTER]' => '<span class="bbCentre">',
      '[LIST]' => '<ul class="bbUl">',
      '[LIST=N]' => '<ul class="bbUlNumbered">',
      '[LIST=A]' => '<ul class="bbUlAlpha">',
      '[/b]' => '</span>',
      '[/u]' => '</span>',
      '[/i]' => '</span>',
      '[/s]' => '</span>',
      '[/del]' => '</span>',
      '[/ins]' => '</span>',
      '[/em]' => '</span>',
      '[/h1]' => '</span>',
      '[/h2]' => '</span>',
      '[/h3]' => '</span>',
      '[/h4]' => '</span>',
      '[/center]' => '</span>',
      '[/list]' => '</ul>',
      '[/list]' => '</ul>',
      '[/list]' => '</ul>',
      '[/B]' => '</span>',
      '[/U]' => '</span>',
      '[/I]' => '</span>',
      '[/S]' => '</span>',
      '[/DEL]' => '</span>',
      '[/INS]' => '</span>',
      '[/EM]' => '</span>',
      '[/H1]' => '</span>',
      '[/H2]' => '</span>',
      '[/H3]' => '</span>',
      '[/H4]' => '</span>',
      '[/CENTER]' => '</span>',
      '[/LIST]' => '</ul>',
      '[/LIST]' => '</ul>',
      '[/LIST]' => '</ul>',
      '[/*]' => '</li>'
    );
    // Deal with potential slashes..
    $text    = mswCD($text);
    // Kill html..
    $text    = htmlspecialchars($text);
    // Parse colors..
    $text    = bbCode_Parser::colorParser($text);
    // Parse urls..
    $text    = bbCode_Parser::urlParser($text);
    // Parse youtube videos..
    $text    = bbCode_Parser::youTubeParser($text);
    // Parse vimeo videos..
    $text    = bbCode_Parser::vimeoParser($text);
    // Parse daily motion videos..
    $text    = bbCode_Parser::dailyMotion($text);
    // Parse emails..
    $text    = bbCode_Parser::emailParser($text);
    // Parse images..
    $text    = bbCode_Parser::imageParser($text);
    // Also clean empty tags..
    $find    = array(
      '[u] [/u]',
      '[i] [/i]',
      '[b] [/b]',
      '[u] [/u]<br>',
      '[i] [/i]<br>',
      '[b] [/b]<br>'
    );
    $repl    = array();
    $text    = str_replace($find, $repl, $text);
    // Deal with other tags..
    $text    = strtr($text, $tagList);
    $text    = mswNL2BR(trim($text));
    // Clean up <ul> & <li> tags which have invalid linebreaks..
    $find    = array(
      '<ul><br>',
      '</ul><br>',
      '<li><br>',
      '</li><br>',
      '<ul class="bbUl"><br>',
      '<ul class="bbUlNumbered"><br>',
      '<ul class="bbUlAlpha"><br>',
      '<ul class="bbUlUpperAlpha"><br>'
    );
    $repl    = array(
      '<ul>',
      '</ul>',
      '<li>',
      '</li>',
      '<ul class="bbUl">',
      '<ul class="bbUlNumbered">',
      '<ul class="bbUlAlpha">',
      '<ul class="bbUlUpperAlpha">'
    );
    return str_replace($find, $repl, $text);
  }

  // For colour tags..
  private function colorParser($text) {
    $pattern[] = '#\[colou?r=([a-zA-Z]{3,20}|\#[0-9a-fA-F]{6}|\#[0-9a-fA-F]{3})](.*?)\[/colou?r\]#ms';
    $replace[] = '<span style="color: $1">$2</span>';
    return preg_replace($pattern, $replace, $text);
  }

  // For url tags..
  private function urlParser($text) {
    $text = preg_replace('#\[urlnew\=(.+)\](.+)\[\/urlnew\]#iUs', '<a href="$1" onclick="window.open(this);return false">$2</a>', $text);
    $text = preg_replace('#\[urlnew\](.+)\[/urlnew\]#iUs', '<a href="$1" onclick="window.open(this);return false">$1</a>', $text);
    $text = preg_replace('#\[url\=(.+)\](.+)\[\/url\]#iUs', '<a href="$1">$2</a>', $text);
    $text = preg_replace('#\[url\](.+)\[/url\]#iUs', '<a href="$1">$1</a>', $text);
    return $text;
  }

  // For YouTube tags..
  private function youTubeParser($text) {
    $text = preg_replace('#\[youtube\](.+)\[/youtube\]#iUs', str_replace('{CODE}', '$1', bbCode_Parser::YOU_TUBE_EMBED_CODE), $text);
    return $text;
  }

  // For Vimeo tags..
  private function vimeoParser($text) {
    $text = preg_replace('#\[vimeo\](.+)\[/vimeo\]#iUs', str_replace('{ID}', '$1', bbCode_Parser::VIMEO_EMBED_CODE), $text);
    return $text;
  }

  // For Daily motion tags..
  private function dailyMotion($text) {
    $text = preg_replace('#\[dailymotion\](.+)\[/dailymotion\]#iUs', str_replace('{CODE}', '$1', bbCode_Parser::DAILY_MOTION_EMBED_CODE), $text);
    return $text;
  }

  // For mailto tags..
  private function emailParser($text) {
    $pattern[] = '#\[email\]([^\[]*?)\[/email\]#';
    $pattern[] = '#\[email=([^\[]+?)\](.*?)\[/email\]#';
    $replace[] = '<a class="bbMailto" href="mailto:$1">$1</a>';
    $replace[] = '<a class="bbMailto" href="mailto:$1">$2</a>';
    return preg_replace($pattern, $replace, $text);
  }

  // For img tags..
  private function imageParser($text) {
    return preg_replace('#\[img\](.+)\[\/img\]#iUs', '<img class="img-responsive" src="$1" alt="" title="">', $text);
  }

  // Cleaner..
  public function cleaner($text) {
    $tagList = array(
      '[b]',
      '[u]',
      '[i]',
      '[s]',
      '[del]',
      '[ins]',
      '[em]',
      '[h1]',
      '[h2]',
      '[h3]',
      '[h4]',
      '[list]',
      '[list=n]',
      '[list=a]',
      '[*]',
      '[B]',
      '[U]',
      '[I]',
      '[S]',
      '[DEL]',
      '[INS]',
      '[EM]',
      '[H1]',
      '[H2]',
      '[H3]',
      '[H4]',
      '[LIST]',
      '[LIST=N]',
      '[LIST=A]',
      '[/b]',
      '[/u]',
      '[/i]',
      '[/s]',
      '[/del]',
      '[/ins]',
      '[/em]',
      '[/h1]',
      '[/h2]',
      '[/h3]',
      '[/h4]',
      '[/list]',
      '[/list]',
      '[/list]',
      '[/B]',
      '[/U]',
      '[/I]',
      '[/S]',
      '[/DEL]',
      '[/INS]',
      '[/EM]',
      '[/H1]',
      '[/H2]',
      '[/H3]',
      '[/H4]',
      '[/LIST]',
      '[/LIST]',
      '[/LIST]',
      '[/*]',
      '[colour]',
      '[color]',
      '[/color]',
      '[/colour]',
      '[urlnew]',
      '[url]',
      '[/urlnew]',
      '[/url]',
      '[email]',
      '[/email]',
      '[img]',
      '[/img]',
      '[youtube]',
      '[/youtube]',
      '[vimeo]',
      '[/vimeo]',
      '[dailymotion]',
      '[/dailymotion]'
    );
    return str_replace($tagList, array(), $text);
  }

}

?>
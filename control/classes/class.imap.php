<?php

/* CLASS FILE
----------------------------------*/

class imapRoutine {

  public $settings;
  public $datetime;
  public $upload;

  const ATTACH_CHMOD_VALUE = 0777;
  const UTF8 = 'utf-8';

  // Logs folder and log name..
  private $log = array(
    'folder' => 'logs',
    'file' => 'imap-debug-log-{id}.log'
  );

  public function __construct($imap) {
    $this->imapController = $imap;
  }

  public function filters($d = array()) {
    $ban = array(
      'txt' => 'no',
      'filter' => '',
      'type' => ''
    );
    $n = mswSQL($d['name']);
    $e = mswSQL($d['email']);
    $s = mswSQL($d['subject']);
    $c = mswSQL($d['comments']);
    // First, find filters that match some or all of the criteria..
    $q = mswSQL_query("SELECT `filter` FROM `" . DB_PREFIX . "imapban`
         WHERE (MATCH(`filter`) AGAINST('{$n}' IN BOOLEAN MODE)
          OR MATCH(`filter`) AGAINST('{$e}' IN BOOLEAN MODE)
          OR MATCH(`filter`) AGAINST('{$s}' IN BOOLEAN MODE)
          OR MATCH(`filter`) AGAINST('{$c}' IN BOOLEAN MODE)
         )");
    while ($F = mswSQL_fetchobj($q)) {
      // Check for full filter match..
      foreach (array_keys($d) AS $flt) {
        if (isset($d[$flt])) {
          $str = $d[$flt];
          if (stripos($str, $F->filter) !== false) {
            $ban['txt'] = 'yes';
            $ban['filter'] = $F->filter;
            $ban['type'] = $flt;
            return $ban;
          }
        }
      }
    }
    return $ban;
  }

  // Decode string..does nothing at the moment..
  public function decodeString($instr) {
    return $instr;
  }

  // Connect to mailbox..
  public function connectToMailBox() {
    $connect = @imap_open('{' . $this->imapController->im_host . ':' . $this->imapController->im_port . '/' . $this->imapController->im_protocol . ($this->imapController->im_ssl == 'yes' ? '/ssl' : '') . ($this->imapController->im_flags ? $this->imapController->im_flags : '') . '}' . $this->imapController->im_name, $this->imapController->im_user, $this->imapController->im_pass);
    if (!$connect) {
      if ($this->settings->imap_debug == 'yes') {
        @imap_close($connect);
        // Silent errors..
        @imap_errors();
        @imap_alerts();
      } else {
        $connect = '';
      }
    }
    // Calling imap_errors here clears stack and prevents notice errors of empty mailbox..
    @imap_errors();
    return $connect;
  }

  // Add attachment to database..
  public function addAttachmentToDB($ticket, $reply, $n, $s, $mime) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "attachments` (
    `ts`,
    `ticketID`,
    `replyID`,
    `department`,
    `fileName`,
    `fileSize`,
    `mimeType`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$ticket}',
    '{$reply}',
    '{$this->imapController->im_dept}',
    '{$n}',
    '{$s}',
    '{$mime}'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    return $id;
  }

  // Upload email attachment..
  public function uploadEmailAttachment($file, $attachment) {
    $folder = '';
    $U      = $this->settings->attachpath . '/' . $file;
    $Y      = date('Y', $this->datetime->mswTimeStamp());
    $M      = date('m', $this->datetime->mswTimeStamp());
    // Create folders..
    if (!is_dir($this->settings->attachpath . '/' . $Y)) {
      $this->upload->folderCreation($this->settings->attachpath . '/' . $Y, imapRoutine::ATTACH_CHMOD_VALUE);
    }
    if (is_dir($this->settings->attachpath . '/' . $Y)) {
      if (!is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $this->upload->folderCreation($this->settings->attachpath . '/' . $Y . '/' . $M, imapRoutine::ATTACH_CHMOD_VALUE);
      }
      if (is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $U      = $this->settings->attachpath . '/' . $Y . '/' . $M . '/' . $file;
        $folder = $Y . '/' . $M . '/';
      }
    }
    mswFPC($U, $attachment);
    return $folder;
  }

  // Read mailbox..
  public function readMailBox($connection, $msg) {
    $other             = array();
    $imapHeader        = imap_header($connection, $msg);
    $imapStruct        = imap_fetchstructure($connection, $msg);
    $headers           = imapRoutine::extractHeaderData($imapHeader);
    $enc               = imapRoutine::getParams($imapStruct);
    $other['ticketID'] = imapRoutine::getTicketID($headers['subject'], $headers['email']);
    $other['body']     = imapRoutine::getMessageBody($msg, $connection);
    // See if spam header is present..
    if ($this->settings->spam_score_header && $this->settings->spam_score_value > 0) {
      $_x          =  imap_fetchheader($connection, $msg);
      preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)\r\n/m', $_x, $matches);
      $_x_headers  = array_combine($matches[1], $matches[2]);
      $other['spamScore'] = (isset($_x_headers[$this->settings->spam_score_header]) ?
       preg_replace('/[^0-9.]/', '', $_x_headers[$this->settings->spam_score_header]) : '0');
    }
    // Attempt to clean out some of the quoted data..
    if ($this->settings->imap_clean == 'yes') {
      $other['body'] = preg_replace('/(^\w.+:\n)?(^>.*(\n|$))+/mi', '', $other['body']);
    }
    return array_merge($headers, $enc, $other);
  }

  // Move mail..
  public function moveMail($connection, $msg) {
    @imap_mail_move($connection, $msg, $this->imapController->im_move);
  }

  // Extract header data..
  public function extractHeaderData($h) {
    global $msg_piping6;
    $sender = $h->from[0];
    $_email = strtolower($sender->mailbox) . '@' . $sender->host;
    // Is reply-to available and does it contain a different email address?
    if (isset($h->reply_to[0])) {
      $sender_from = $h->reply_to[0];
      $_replyto = strtolower($sender_from->mailbox) . '@' . $sender_from->host;
      if (mswIsValidEmail($_replyto) && $_email != $_replyto) {
        $_email = $_replyto;
      }
    }
    mb_internal_encoding(self::UTF8);
    return array(
      'from' => imapRoutine::mimeDecode((isset($sender->personal) ? $sender->personal : $_email)),
      'email' => $_email,
      'subject' => ($h->subject ? iconv_mime_decode($h->subject, 0, self::UTF8) : $msg_piping6),
      'messageID' => (isset($h->message_id) ? $h->message_id : '0'),
      'timestamp' => strtotime($h->date)
    );
  }

  // Get ticket id/number from email subject..
  public function getTicketID($subject, $email) {
    $ticketid = 0;
    if (preg_match("[[#][0-9a-zA-Z\-]{1,20}]", $subject, $regs)) {
      $PORTAL = mswSQL_table('portal', 'email', mswSQL($email), '', '`id`');
      // Random ticket numbers..
      if ($this->settings->rantick == 'yes' && strpos($regs[0], '-') !== false) {
        $tdata = preg_replace('/[^0-9a-zA-Z\-]/', '', $regs[0]);
        $ticketid = mswReverseTicketNumber(0, trim($tdata));
        if (isset($PORTAL->id) && mswSQL_rows('tickets WHERE `tickno` = \'' . mswSQL($ticketid) . '\' AND `visitorID` = \'' . $PORTAL->id . '\' AND `spamFlag` = \'no\'') > 0) {
          return array(
            'yes',
            $ticketid
          );
        }
      // Standard, ticket ids..
      } else {
        $tdata = preg_replace('/[^0-9]/', '', $regs[0]);
        $ticketid = mswReverseTicketNumber(trim($tdata));
        if (isset($PORTAL->id) && mswSQL_rows('tickets WHERE `id` = \'' . (int) $ticketid . '\' AND `visitorID` = \'' . $PORTAL->id . '\' AND `spamFlag` = \'no\'') > 0) {
          return array(
            'yes',
            $ticketid
          );
        }
      }
    }
    return array(
      'no',
      0
    );
  }

  // Assign mail parameters..
  public function getParams($h) {
    $mimeTypes = array(
      'TEXT',
      'MULTIPART',
      'MESSAGE',
      'APPLICATION',
      'AUDIO',
      'IMAGE',
      'VIDEO',
      'OTHER'
    );
    try {
      $params = (is_object($h) && property_exists($h,'parameters') && is_array($h->parameters) && isset($h->parameters[0]) ? $h->parameters[0] : '');
      return array(
        'charset' => (is_object($h) && property_exists($h,'ifparameters') && is_object($params) && property_exists($params,'value') ? $params->value : self::UTF8),
        'bytes' => (is_object($h) && property_exists($h,'bytes') ? $h->bytes : ''),
        'encoding' => (is_object($h) && property_exists($h,'encoding') ? $h->encoding : ''),
        'type' => (is_object($h) && property_exists($h,'type') ? $h->type : ''),
        'attribute' => (is_object($params) && property_exists($params,'attribute') ? $params->attribute : ''),
        'mime' => (!is_object($h) || !property_exists($h,'subtype') || $h->subtype == '' ? 'TEXT/PLAIN' : (is_object($h) && property_exists($h,'type') && isset($mimeTypes[$h->type]) ? $mimeTypes[$h->type] . '/' . (is_object($h) && property_exists($h,'subtype') ? $h->subtype : 'TEXT/PLAIN') : 'TEXT/PLAIN'))
      );
    } catch(Exception $e) {
      imapRoutine::log($e->getMessage());
      return array(
        'charset' => self::UTF8,
        'bytes' => '',
        'encoding' => '',
        'type' => '',
        'attribute' => '',
        'mime' => 'TEXT/PLAIN'
      );
    }
  }

  // Attempt to remove reply quote..
  public function removeReplyQuote($text, $reply) {
    if (strrpos($text, trim($reply)) !== FALSE) {
      return substr($text, 0, strrpos($text, trim($reply)));
    } else {
      return $text;
    }
  }

  // Get message body of email..
  public function getMessageBody($msg, $connection) {
    $message = '';
    $message = imapRoutine::getPart($msg, 'TEXT/PLAIN', $connection, self::UTF8, '', 1.1);
    if ($message == '') {
      $message = imapRoutine::getPart($msg, 'TEXT/PLAIN', $connection, self::UTF8);
    }
    // If this is a base 64 encoded body, decode it..
    if (base64_encode(base64_decode($message, true)) === $message) {
      $message = base64_decode(chunk_split($message));
    }
    if (strpos($message, ' ') === false && base64_decode($message, true)) {
      $message = base64_decode($message);
    }
    if (!$message) {
      $message = imapRoutine::getPart($msg, 'TEXT/HTML', $connection, self::UTF8);
      $message = str_replace('</DIV><DIV>', "\n", $message);
      $message = str_replace(array(
        '<br>',
        '<br>',
        '<BR>'
      ), "\n", $message);
    }
    return strip_tags(html_entity_decode(trim($message)));
  }

  // Read mail..
  public function getPart($mid, $mimeType, $connection, $encoding = false, $struct = '', $partNumber = '') {
    if (!$struct && $mid) {
      $struct = imap_fetchstructure($connection, $mid);
    }
    if ($struct && !$struct->ifdparameters && in_array($mimeType, array(
      'TEXT/PLAIN',
      'TEXT/HTML'
    ))) {
      $partNumber = ($partNumber ? $partNumber : 1);
      if ($text = imap_fetchbody($connection, $mid, $partNumber)) {
        if (in_array($struct->encoding, array(0,3,4))) {
          $text    = imapRoutine::decodeText($struct->encoding, $text);
          $charset = null;
          if ($encoding) {
            if ($struct->ifparameters) {
              // Get the original charset of the message if it exists..
              if (isset($struct->parts[0]->parameters[0]->value)) {
                $charset = $struct->parts[0]->parameters[0]->value;
              } else {
                if (isset($struct->parts[0]->parameters[0]->attribute) && strcasecmp($struct->parts[0]->parameters[0]->attribute, 'US-ASCII')) {
                  $charset = trim($struct->parameters[0]->value);
                }
              }
              $text = imapRoutine::mimeEncode($text, $charset, $encoding);
            }
          }
        }
        return $text;
      }
      // Do recursive search
      $text = '';
      if ($struct && !empty($struct->parts)) {
        foreach ($struct->parts AS $i => $substruct) {
          if ($partNumber) {
            $prefix = $partNumber . '.';
            if (($result = $this->getPart($mid, $mimeType, $connection, $encoding, $substruct, $prefix . ($i + 1), $partNumber))) {
              $text .= $result;
            }
          }
        }
      }
      return $text;
    }
  }

  // Close mailbox..
  public function closeMailbox($connection) {
    imap_expunge($connection);
    imap_close($connection);
    @imap_errors();
    @imap_alerts();
  }

  // Flag message..
  public function flagMessage($connection, $msg) {
    imap_setflag_full($connection, imap_uid($connection, $msg), "\\Seen", ST_UID);
    // Delete if move option not set..
    imap_delete($connection, $msg);
  }

  // Assign mime encoding..
  public function mimeEncode($text, $charset = '', $enc = 'utf-8') {
    $raw = $text;
    if ($enc == '' || $enc == '0') {
      $enc = self::UTF8;
    }
    if ($charset == '' && $text && function_exists('mb_detect_encoding')) {
      $charset = mb_detect_encoding($text);
    }
    if ($charset == '') {
      $charset = self::UTF8;
    }
    $charset = imapRoutine::mimeNorm($charset);
    $enc = imapRoutine::mimeNorm($enc);
    if ($charset) {
      if (function_exists('iconv')) {
        $text = @iconv($charset, $enc . '//IGNORE', $text);
      } else if (function_exists('mb_convert_encoding')) {
        $text = @mb_convert_encoding($text, $enc, $charset);
      } else if (!strcasecmp($enc, self::UTF8) && function_exists('utf8_encode') && !strcasecmp($charset, 'ISO-8859-1')) {
        $text = @utf8_encode($text);
      }
    }
    return ($text ? $text : $raw);
  }

  // Normalize ambiguous charsets
  // Thanks to osTicket and https://github.com/mikel/mail/commit/88457e
  function mimeNorm($c) {
    $match = array();
    $charset = trim($c);
    switch (true) {
      // Windows charsets - force correct format
      case preg_match('`^Windows-?(\d+)$`i', $charset, $match):
        return 'Windows-' . $match[1];
        break;
      // ks_c_5601-1987: Korean alias for cp949
      case preg_match('`^ks_c_5601-1987`i', $charset):
        return 'cp949';
        break;
      case preg_match('`^iso-?(\S+)$`i', $charset, $match):
        return 'ISO-' . $match[1];
        break;
      // GBK superceded gb2312 and is backward compatible
      case preg_match('`^gb2312`i', $charset):
        return 'GBK';
        break;
      // Incorrect, bogus, ambiguous or empty charsets
      // ISO-8859-1 is assumed
      case preg_match('`^(default|x-user-defined|iso|us-ascii)$`i', $charset):
      case preg_match('`^\s*$`', $charset):
        return 'ISO-8859-1';
        break;
    }
    return $charset;
  }

  // Mime encoding..
  public function mimeDecode($text) {
    $a   = imap_mime_header_decode($text);
    $str = '';
    for ($i=0; $i<count($a); $i++) {
      $str .= $a[$i]->text;
    }
    return $str ? $str : imap_utf8($text);
  }

  // Decode text..
  public function decodeText($encoding, $text) {
    switch ($encoding) {
      case 1:
        $text = quoted_printable_decode(imap_8bit($text));
        break;
      case 2:
        $text = imap_binary($text);
        break;
      case 3:
        $text = imap_base64($text);
        break;
      case 0:
      case 4:
        $text = quoted_printable_decode($text);
        break;
      case 5:
      default:
        break;
    }
    return $text;
  }

  // Read mail attachments into array..
  public function readAttachments($connection, $msg) {
    $attachments = array();
    $att         = imapRoutine::extractAttachments($connection, $msg);
    $count       = 0;
    if (!empty($att)) {
      for ($j = 0; $j < count($att); $j++) {
        if (isset($att[$j]['is_attachment']) && isset($att[$j]['attachment'])) {
          if ($att[$j]['is_attachment'] == 'yes' && $att[$j]['attachment'] != '') {
            ++$count;
            if (LICENCE_VER == 'locked' && $count <= RESTR_ATTACH) {
              $attachments[$count]['file']       = $att[$j]['filename'];
              $attachments[$count]['attachment'] = $att[$j]['attachment'];
              $attachments[$count]['ext']        = (strpos($att[$j]['filename'], '.') !== FALSE ? strrchr(strtolower($att[$j]['filename']), '.') : '.txt');
            } else {
              if (LICENCE_VER == 'unlocked') {
                $attachments[$count]['file']       = $att[$j]['filename'];
                $attachments[$count]['attachment'] = $att[$j]['attachment'];
                $attachments[$count]['ext']        = (strpos($att[$j]['filename'], '.') !== FALSE ? strrchr(strtolower($att[$j]['filename']), '.') : '.txt');
              }
            }
          }
        }
      }
    }
    return $attachments;
  }

  // Extract attachments from email..
  public function extractAttachments($connection, $message_number) {
    $attachments = array();
    $i           = -1;
    $structure   = imap_fetchstructure($connection, $message_number);
    if (isset($structure->parts) && count($structure->parts) > 0) {
      $flatparts = imapRoutine::flattenParts($structure->parts);
      if (!empty($flatparts)) {
        foreach ($flatparts AS $fK => $fV) {
          ++$i;
          $attachments[$i] = array(
            'is_attachment' => 'no',
            'filename' => '',
            'name' => '',
            'attachment' => ''
          );
          if ($fV->ifdparameters > 0) {
            for ($k = 0; $k < count($fV->dparameters); $k++) {
              if (strtolower($fV->dparameters[$k]->attribute) == 'filename') {
                $attachments[$i]['is_attachment'] = 'yes';
                $attachments[$i]['filename']      = $fV->dparameters[$k]->value;
              }
            }
          }
          if ($attachments[$i]['is_attachment'] == 'no' && $fV->ifparameters > 0) {
            for ($j = 0; $j < count($fV->parameters); $j++) {
              if (strtolower($fV->parameters[$j]->attribute) == 'name') {
                $attachments[$i]['is_attachment'] = 'yes';
                $attachments[$i]['filename']      = $fV->parameters[$j]->value;
              }
            }
          }
          if ($attachments[$i]['is_attachment'] == 'yes') {
            $attachments[$i]['attachment'] = imap_fetchbody($connection, $message_number, $fK);
            if ($fV->encoding == 3) { // 3 = BASE64
              $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
            } elseif ($fV->encoding == 4) { // 4 = QUOTED-PRINTABLE
              $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
            }
          } else {
            unset($attachments[$i]);
          }
        }
      }
    }
    // Rebuild indices..
    if (!empty($attachments)) {
      $attachments = array_values($attachments);
    }
    return $attachments;
  }

  // Flatten the structure parts..
  public function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
    if (!empty($messageParts)) {
      foreach ($messageParts as $part) {
        $flattenedParts[$prefix . $index] = $part;
        if (isset($part->parts)) {
          if ($part->type == 2) {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix . $index . '.', 0, false);
          } elseif ($fullPrefix) {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix . $index . '.');
          } else {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix);
          }
          unset($flattenedParts[$prefix . $index]->parts);
        }
        $index++;
      }
    }
    return $flattenedParts;
  }

  // Log..
  public function log($msg) {
    if ($this->settings->imap_debug == 'yes') {
      $id       = $this->imapController->id;
      $file     = str_replace('{id}', $id, $this->log['file']);
      $existing = (file_exists(PATH . $this->log['folder'] . '/' . $file) ? trim(mswTmp(PATH . $this->log['folder'] . '/' . $file)) : '');
      if ($existing == '') {
        $message = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswNL();
        $message .= 'IMAP DEBUG LOG: ' . date('d/F/Y @ H:iA', $this->datetime->mswTimeStamp()) . mswNL();
        $message .= '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswNL(2);
        $message .= 'Imap ID: ' . $id . mswNL();
        $message .= 'Imap Host: ' . $this->imapController->im_host . mswNL();
        $message .= 'Imap User: ' . $this->imapController->im_user . mswNL();
        $message .= 'Imap Port: ' . $this->imapController->im_port . mswNL();
        $message .= 'Imap SSL: ' . ucfirst($this->imapController->im_ssl) . mswNL();
        $message .= 'Imap Folder: ' . $this->imapController->im_name . mswNL();
        $message .= mswNL() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswNL(2);
      } else {
        $message = '';
      }
      $message .= '[' . mswIP() . ' >> ' . date('d/F/Y @ H:i:s', $this->datetime->mswTimeStamp()) . '] Action/Info: ' . str_replace('{nl}', mswNL(), $msg) . mswNL();
      $message .= mswNL() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswNL(2);
      mswFPC(PATH . $this->log['folder'] . '/' . $file, $message);
    }
  }

}

?>
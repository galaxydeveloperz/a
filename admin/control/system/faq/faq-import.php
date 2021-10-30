<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Uploader class
include(BASE_PATH . 'control/classes/class.upload.php');
$MSUPL = new msUpload();

// Upload dropzone..
$mswUploadDropzone = array(
  'ajax' => 'faqimport-upload',
  'multiple' => 'false',
  'max-files' => 1,
  'drag' => 'false'
);

$title = $msg_adheader55;
$loadiBox  = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-import.php');
include(PATH . 'templates/footer.php');

?>
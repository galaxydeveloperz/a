<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

if (SAVE_DRAFTS && defined('DRAFT_AREA') && defined('DRAFT_ID') && defined('DRAFT_MSG_TIMEOUT')) {
?>
function mswGetDraft(id) {
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=tickdraft-load&id=' + id,
    dataType : 'json',
    cache    : false,
    success  : function (data) {
      if (data['msg'] == 'saved') {
        jQuery('#draft_' + id).html(data['text']);
        setTimeout(function() {
        	jQuery('#draft_' + id).fadeOut(800);
        }, <?php echo DRAFT_MSG_TIMEOUT; ?>);
        jQuery('#comments').val(data['draft']);
      } else {
        jQuery('#draft_' + id).html('');
      }
    }
  });
  return false;
}
jQuery(document).ready(function() {
  var timeoutId;
  jQuery('<?php echo DRAFT_AREA; ?>').on('keypress', function() {
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
    timeoutId = setTimeout(function() {
      jQuery.post('index.php?ajax=tickdraft-save', { 
        draft : jQuery('<?php echo DRAFT_AREA; ?>').val(),
        id    : <?php echo (DRAFT_ID == 'add' ? '\'add\'' : DRAFT_ID); ?>
        }, function(data) {
          jQuery('#draft_<?php echo DRAFT_ID; ?>').html(data['text']).show();
          setTimeout(function() {
          	jQuery('#draft_<?php echo DRAFT_ID; ?>').fadeOut(800);
          }, <?php echo DRAFT_MSG_TIMEOUT; ?>);
      }, 'json'); 
    }, <?php echo DRAFT_TIMEOUT; ?>);
  });
  mswGetDraft('<?php echo DRAFT_ID; ?>');
});
<?php
}
?>
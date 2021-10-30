<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('input[name="search"]').autocomplete({
	  source    : 'index.php?ajax=auto-search-acc',
		minLength : 3,
		select    : function(event, ui) {
      if (ui.item.value > 0) {
        mswLoadAccFilter(ui.item.value,ui.item.name,ui.item.email);
      } else {
        setTimeout(function() {
          jQuery('input[name="search"]').val('');
        }, 300);
      }
		}
  });
});
function mswLoadAccFilter(id,name,email) {
  if (jQuery('div[class="accFilterArea"]').html() == '') {
    var rembox = '<a href="#" onclick="mswRemFltrBox(\'' + id + '\');return false"><i class="fa fa-times fa-fw ms_red"></i></a>';
    jQuery('div[class="accFilterArea"]').html('<p id="acr_' + id + '">' + rembox + ' <input type="hidden" name="acc[]" value="' + id + '">' + name + ' (' + email + ')</p>');
  } else {
    var rembox = '<a href="#" onclick="mswRemFltrBox(\'' + id + '\');return false"><i class="fa fa-times fa-fw ms_red"></i></a>';
    jQuery('div[class="accFilterArea"] p').last().after('<p id="acr_' + id + '">' + rembox + ' <input type="hidden" name="acc[]" value="' + id + '">' + name + ' (' + email + ')</p>');
  }
  setTimeout(function() {
    jQuery('input[name="search"]').val('');
  }, 300);
}
function mswRemFltrBox(id) {
  jQuery('#acr_' + id).remove();
}
//]]>
</script>
<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

// Is time tracking on? Initialise timer..
if ($SETTINGS->timetrack == 'yes') {
?>
<script src="templates/js/plugins/easytimer.js"></script>
<script>
//<![CDATA[
var timer = new Timer();
<?php
if ($SETTINGS->timetrack == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->timer == 'yes')) {
?>
jQuery(document).ready(function() {
  jQuery('.bstart').on('click', function () {
    timer.start({precision: 'seconds'});
  });
  jQuery('.bpause').on('click', function () {
    timer.pause();
  });
  jQuery('.bstop').on('click', function () {
    timer.stop();
  });
  jQuery('.breset').on('click', function () {
    timer.reset();
  });
});
<?php
}
?>
//]]>
</script>
<?php
}
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('input[name="sresp"]').autocomplete({
	  source    : 'index.php?ajax=auto-response&dept=<?php echo $SUPTICK->department; ?>',
		minLength : 3,
		select    : function(event, ui) {
      if (ui.item.value > 0) {
        mswLoadResponse(ui.item.value);
      } else {
        setTimeout(function() {
          jQuery('input[name="sresp"]').val('');
        }, 400);
      }
		}
  });
  jQuery('input[name="mergeid_t"]').autocomplete({
	  source    : 'index.php?ajax=auto-merge&visitor=<?php echo $SUPTICK->visitorID; ?>&id=<?php echo $SUPTICK->id; ?>',
		minLength : 2,
		select    : function(event, ui) {
      if (ui.item.value > 0) {
        setTimeout(function() {
          jQuery('input[name="mergeid_t"]').val(ui.item.ticket);
          if (jQuery('input[name="prevtxt"]')) {
            jQuery('input[name="prevtxt"]').remove();
          }
          if (jQuery('input[name="mergeid"]')) {
            jQuery('input[name="mergeid"]').remove();
          }
          jQuery('<input type="hidden" name="prevtxt" value="' + jQuery('button[type="submit"] span').html() + '">').appendTo('form');
          jQuery('<input type="hidden" name="mergeid" value="' + ui.item.value + '">').appendTo('form');
          jQuery('button[type="submit"] span').html(ui.item.txt);
        }, 200);
      } else {
        setTimeout(function() {
          jQuery('input[name="mergeid_t"]').val('');
          if (jQuery('input[name="mergeid"]')) {
            jQuery('input[name="mergeid"]').remove();
          }
        }, 400);
      }
		}
  });
});
function mswMergeClear() {
  if (jQuery('input[name="mergeid_t"]').val() == '') {
    jQuery('button[type="submit"] span').html(jQuery('input[name="prevtxt"]').val());
    if (jQuery('input[name="mergeid"]')) {
      jQuery('input[name="mergeid"]').remove();
    }
    jQuery('input[name="prevtxt"]').remove();
  }
}
function mswLoadResponse(id) {
  jQuery('.nav-tabs a:first').tab('show');
  setTimeout(function() {
    jQuery('textarea[name="comments"]').addClass('textarea_updating');
  }, 300);
  setTimeout(function() {
    jQuery('input[name="sresp"]').val('');
    jQuery(document).ready(function() {
      jQuery.ajax({
        url      : 'index.php',
        data     : 'ajax=tickresponse&id=' + id,
        dataType : 'json',
        success  : function(data) {
          jQuery('textarea[name="comments"]').removeClass('textarea_updating');
          jQuery('textarea[name="comments"]').val(data['response']);
        }
      });
    });
    return false;
  }, 600);
}
jQuery(document).ready(function() {
  var options = {
    dataType : 'json',
    success  : mswPR_Callback
  };
  jQuery('#mswform').on('submit', function(e) {
    mswShowSpinner();
    e.preventDefault();
    jQuery(this).ajaxSubmit(options);
    return false;
  });
  <?php
  // Is time tracking on?
  if ($SETTINGS->timetrack == 'yes') {
  // Start timer automatically?
  if ($MSTEAM->startwork == 'yes') {
  ?>
  timer.start({precision: 'seconds'});
  <?php
  }
  ?>
  timer.addEventListener('secondsUpdated', function (e) {
    var ntme = timer.getTimeValues().toString();
    jQuery('input[name="worktime"]').val(ntme);
    jQuery('.timerdiv').html(ntme);
  });
  <?php
  }
  ?>
});
<?php
// Drafts
include(PATH . 'templates/js-loader/ticket-drafts.php');
?>
//]]>
</script>
function mswConf() {
  if (confirm('Are You Sure?')) {
    return true;
  } else {
    return false;
  }
}

function mswIns(op, stage) {
  if (op == 'install' && !mswConf()) {
    return false;
  }
  if (op == 'install') {
    mswShowSpin();
  }
  jQuery(document).ready(function() {
    jQuery.ajax({
      type: 'POST',
      url: (op == 'upgrade' ? 'upgrade' : 'index') + '.php?ajax-ops=' + op + (op == 'upgrade' ? '&ustage=' + stage : ''),
      data: jQuery('#formarea > form').serialize(),
      cache: false,
      dataType: 'json',
      success: function (data) {
        if (op == 'install') {
          mswCloseSpin();
        }
        switch(op) {
          case 'install':
            switch(data['status']) {
              case 'ok':
                mswDialog(data['txt'][0], data['txt'][1], data['status']);
                break;
              case 'err':
                mswDialog(data['txt'][0], data['txt'][1], data['status']);
                break;
            }
            break;
          case 'upgrade':
            if (data['next'] == 'done') {
              window.location = 'upgrade.php?done=yes';
            } else {
              jQuery('#td1_' + data['prev']).removeClass('msw_bold');
              jQuery('#td1_' + data['prev']).addClass('msw_green');
              jQuery('#td2_' + data['prev']).removeClass('msw_blue');
              jQuery('#td2_' + data['prev']).html('<i class="fa fa-check-circle fa-fw"></i> Completed');
              jQuery('#td1_' + data['next']).removeClass('msw_bold');
              jQuery('#td2_' + data['prev']).addClass('msw_blue');
              jQuery('#td2_' + data['next']).html('<i class="fa fa-spinner fa-spin fa-fw"></i> Running..');
              mswIns('upgrade', data['next']);
            }
            break;
        }
      }
    });
  });
  return false;
}

function mswDialog(txt, msg, mtype) {
  if (jQuery('.bootbox')) {
    jQuery('.bootbox').remove();
  }
  if (jQuery('.modal-backdrop')) {
    jQuery('.modal-backdrop').remove();
  }
  switch(mtype) {
    case 'err':
      bootbox.dialog({
        message   : msg,
        title     : '<i class="fa fa-warning fa-fw"></i> ' + txt,
        className : 'msw-box-error',
        onEscape  : true,
        backdrop  : true
      });
      break;
    default:
      bootbox.dialog({
        message   : msg,
        title     : '<i class="fa fa-check-circle fa-fw"></i> ' + txt,
        className : 'msw-box-ok',
        onEscape  : true,
        backdrop  : true
      });
      break;
  }
}

function mswCloseSpin() {
  jQuery('body').css({
    'opacity': '1.0'
  });
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswShowSpin() {
  jQuery('body').css({
    'opacity': '0.7'
  });
  jQuery('.overlaySpinner').css({
    'left': '50%',
    'top': '50%',
    'position': 'fixed',
    'margin-left': -jQuery('.overlaySpinner').outerWidth() / 2,
    'margin-top': -jQuery('.overlaySpinner').outerHeight() / 2
  });
  jQuery('div[class="overlaySpinner"]').show();
}
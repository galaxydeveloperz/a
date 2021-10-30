function mswPR_Callback(responseText, statusText, xhr, $form)  {
  switch (responseText['status']) {
    case 'ok':
      switch(responseText['field']) {
        case 'redirect':
          window.location = responseText['msg'];
          break;
        default:
          mswCloseSpinner();
          break;
      }
      break;
    case 'reload':
      mswCloseSpinner();
      setTimeout(function() {
        window.location.reload();
      }, 500);
      break;
    case 'err':
      mswCloseSpinner();
      mswAlert(responseText['msg'], responseText['sys'], 'err');
      break;
    default:
      mswCloseSpinner();
      break;
  }
  return false;
}

function mswProcess(page, par) {
  jQuery(document).ready(function() {
    if (page == 'create') {
      if (jQuery('input[name="js_ts"]')) {
        jQuery('input[name="js_ts"]').remove();
      }
      var d = new Date();
      jQuery('#mswform').append('<input type="hidden" name="js_ts" value="' + d.getFullYear() + '">');
    }
    mswShowSpinner();
    setTimeout(function() {
      jQuery.ajax({
        type     : 'POST',
        url      : 'index.php?ajax=' + page + (par != undefined ? '&param=' + par : ''),
        data     : jQuery('#mscontainer > form').serialize(),
        cache    : false,
        dataType : 'json',
        success  : function(data) {
          switch (data['status']) {
            case 'ok':
              switch(data['field']) {
                case 'redirect':
                  window.location = data['msg'];
                  break;
                default:
                  mswCloseSpinner();
                  if (page == 'profile') {
                    mswAlert(data['msg'], data['sys'], 'ok');
                  }
                  break;
              }
              break;
            case 'ok-dialog':
              mswCloseSpinner();
              mswNewPass();
              if (page == 'create') {
                jQuery('input[name="name"]').val('');
                jQuery('input[name="email"]').val('');
                jQuery('input[name="email2"]').val('');
              }
              mswAlert(data['msg'], data['sys']);
              break;
            case 'err':
              mswCloseSpinner();
              switch(page) {
                case 'profile':
                  if (data['tab'] && data['field']) {
                    jQuery('.nav-tabs a[href="#' + data['tab'] + '"]').tab('show')
                  }
                  break;
                default:
                  break;
              }
              mswAlert(data['msg'], data['sys'], 'err');
              break;
            default:
              mswCloseSpinner();
              break;
          }
        }
      });
    }, 1500);
  });
  return false;
}

function mswShowSpinner() {
  jQuery('body').css({'opacity' : '0.7'});
  jQuery('.overlaySpinner').css({
    'left' : '50%',
    'top' : '50%',
    'position' : 'fixed',
    'margin-left' : -jQuery('.overlaySpinner').outerWidth()/2,
    'margin-top' : -jQuery('.overlaySpinner').outerHeight()/2
  });
  jQuery('div[class="overlaySpinner"]').show();
}

function mswCloseSpinner() {
  jQuery('body').css({
    'opacity': '1.0'
  });
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswVote(obj, id) {
  switch(jQuery(obj).attr('class')) {
    case 'fa fa-thumbs-up fa-fw cursor_pointer':
      var vote = 'yes';
      jQuery(obj).attr('class', 'fa fa-spinner fa-spin fa-fw');
      break;
    default:
      var vote = 'no';
      jQuery(obj).attr('class', 'fa fa-spinner fa-spin fa-fw');
      break;
  }
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=voting&id=' + id + '&vote=' + vote,
      dataType : 'json',
      cache    : false,
      success  : function (data) {
        jQuery('div[class="row votefont"] div:first i').attr('class', 'fa fa-thumbs-up fa-fw cursor_pointer');
        jQuery('div[class="row votefont"] div:nth-child(2) i').attr('class', 'fa fa-thumbs-down fa-fw cursor_pointer');
        switch(data['status']) {
          case 'ok':
            jQuery('div[class="row votefont"] div:first span').html(data['yes']);
            jQuery('div[class="row votefont"] div:nth-child(2) span').html(data['no']);
            jQuery('span[class="votetotalarea"]').html(data['total']);
            break;
          case 'err':
            mswAlert(data['msg'], data['sys'], 'err');
            break;
        }
      }
    });
  });
  return false;
}

function closeAcc() {
  if (!jQuery('input[name="delyes"]:checked').val()) {
    jQuery('.confarea').slideDown();
    return false;
  }
  mswShowSpinner();
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=closeaccount',
    dataType : 'json',
    cache    : false,
    success  : function (data) {
      mswCloseSpinner();
      switch(data['status']) {
        case 'ok':
          mswAlert(data['msg'], data['sys'], 'ok');
          setTimeout(function() {
            window.location = data['rdr'];
          }, 1500);
          break;
        case 'err':
          mswAlert(data['msg'], data['sys'], 'err');
          break;
      }
    }
  });
  return false;
}

function mswDL(id, parm, addn) {
  mswShowSpinner();
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=' + parm + '&id=' + id + (addn != undefined ? '&ad=' + addn : ''),
    dataType : 'json',
    cache    : false,
    success  : function (data) {
      mswCloseSpinner();
      switch(data['status']) {
        case 'token':
          window.location = 'index.php?ajax=' + (parm == 'dl' ? 'token' : 'tokena') + '&cde=' + data['token'];
          break;
        case 'remote':
          window.location = data['remote'];
          break;
        case 'err':
          mswAlert(data['msg'], data['sys'], 'err');
          break;
      }
    }
  });
  return false;
}

function mswDeptLoader(deptid, autoload) {
  if (deptid == 'void') {
    if (jQuery('input[name="name"]')) {
      jQuery('input[name="name"]').focus();
    }
    return false;
  }
  var curt3 = jQuery('#three').html();
  if (!jQuery('#three').html()) {
    return false;
  }
  if (parseInt(deptid) > 0) {
    var dvale = deptid;
  } else {
    if (jQuery('select[name="dept"]').val() == '0') {
      return false;
    }
    var dvale = jQuery('select[name="dept"]').val();
  }
  jQuery(document).ready(function() {
    mswShowSpinner();
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=dept&dp=' + dvale,
      dataType : 'json',
      cache    : false,
      success  : function(data) {
        mswCloseSpinner();
        if (data['fields']) {
          jQuery('#three').html((data['fields'] ? data['fields'] : curt3));
          jQuery('.nav-tabs li:nth-child(2)').show();
        } else {
          jQuery('#three').html(curt3);
          jQuery('.nav-tabs li:nth-child(2)').hide();
        }
        if (data['subject']) {
          jQuery('input[name="subject"]').val(data['subject']);
        }
        if (data['comments']) {
          jQuery('textarea[name="comments"]').val(data['comments']);
        }
        if (parseInt(deptid) > 0) {
          jQuery('select[name="dept"]').val(deptid);
        }
        if (data['priority']) {
          jQuery('select[name="priority"]').val(data['priority']);
        }
        if (autoload != undefined) {
          if (jQuery('input[name="name"]')) {
            jQuery('input[name="name"]').focus();
          }
        }
        // Attach event handlers for calendar field boxes..
        if (data['fields']) {
          jQuery('#three input[type="text"]').each(function(){
            if (jQuery(this).hasClass('jsdatepicker')) {
              mswFldDatePicker(this); 
            }
          });
        }
      }
    });
  });
  return false;
}

function mswAlert(msg, txt, mtype) {
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
        title     : '<i class="fa fa-check fa-fw"></i> ' + txt,
        className : 'msw-box-ok',
        onEscape  : true,
        backdrop  : true
      });
      break;
  }
}

function mswPanel(panl) {
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=menu-panel&pnl=' + panl,
      dataType : 'json',
      cache    : false,
      success  : function (data) {}
    });
  });
  return false;
}
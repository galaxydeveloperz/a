<?php

/* CLASS FILE
----------------------------------*/

class sessHandlr {

  public function set($s = array(), $val = 'no') {
    foreach ($s AS $k => $v) {
      switch($val) {
        case 'yes':
          $_SESSION[$k] = $v;
          break;
        default:
          $_SESSION[sessHandlr::key($k)] = $v;
          break;
      }
    }
  }

  public function get($key) {
    $sessKey = sessHandlr::key($key);
    return (isset($_SESSION[$sessKey]) ? $_SESSION[$sessKey] : '');
  }

  public function delete($s = array()) {
    if (!empty($s)) {
      foreach ($s AS $key) {
        $sessKey = sessHandlr::key($key);
        if (isset($_SESSION[$sessKey])) {
          unset($_SESSION[$sessKey]);
        }
      }
    }
  }

  public function active($key) {
    return (isset($_SESSION[sessHandlr::key($key)]) ? 'yes' : 'no');
  }

  private function key($k) {
    return mswEncrypt(SECRET_KEY . $k . SECRET_KEY);
  }
  
  public function active_c($key) {
    return (isset($_COOKIE[sessHandlr::key($key)]) ? 'yes' : 'no');
  }
  
  public function get_c($key) {
    $sessKey = sessHandlr::key($key);
    return (isset($_COOKIE[$sessKey]) ? $_COOKIE[$sessKey] : '');
  }
  
  public function delete_c($s = array()) {
    if (!empty($s)) {
      foreach ($s AS $key) {
        $sessKey = sessHandlr::key($key);
        if (isset($_COOKIE[$sessKey])) {
          @setcookie($sessKey, '');
          unset($_COOKIE[$sessKey]);
        }
      }
    }
  }
  
  public function set_c($s = array()) {
    for ($i=0; $i<count($s); $i++) {
      setcookie(sessHandlr::key($s[$i][0]), $s[$i][1], $s[$i][2]);
    }
  }
  
  public function token() {
    return (function_exists('openssl_random_pseudo_bytes') && function_exists('bin2hex') ?
    bin2hex(openssl_random_pseudo_bytes(50)) :
    substr(sha1(uniqid(rand(),1)), 3 , 35) . substr(sha1(uniqid(rand(),1)), 3 , 45));
  }

}

?>
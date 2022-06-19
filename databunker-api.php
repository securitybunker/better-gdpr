<?php

function bettergdpr_generate_subdomain($site) {
  $s = parse_url($site);
  $host = $s["host"];
  $levels = explode('.', $host);
  array_pop($levels);
  if (end($levels) == "gov" || end($levels) == "co" || end($levels) == "com" || end($levels) == "org") {
    array_pop($levels);
  }
  if ($levels[0] == "www") {
    array_shift($levels);
  }
  return join("-", $levels);
}

function bettergdpr_get_request($url) {
  $xtoken = get_option( 'bettergdpr_xtoken', '' );
  $subdomain = get_option( 'bettergdpr_subdomain', '' );
  $srv = "https://".$subdomain.".privacybunker.cloud";

  $full_url = $srv.$url;
  $args = array(
    'headers' => array(
      'X-Bunker-Token' => $xtoken
    ),
    'blocking' => true,
    'method' => 'GET'
  );
  $response  = wp_remote_request($full_url, $args);
  $body      = wp_remote_retrieve_body( $response );
  $http_code = wp_remote_retrieve_response_code( $response );
  if ( $http_code != 200) {
    $full_url = preg_replace("|/email/.*$|", "/email/hidden@email.com", $full_url);
    error_log("[" . $http_code . "] " . $full_url);
    error_log($body);
  }
  return @json_decode($body);
}

function bettergdpr_data_request($method, $url, $data) {
  $xtoken = get_option( 'bettergdpr_xtoken', '' );
  $subdomain = get_option( 'bettergdpr_subdomain', '' );
  $srv = "https://".$subdomain.".privacybunker.cloud";
  $full_url = $srv.$url;
  $args = array(
    'headers' => array(
      'X-Bunker-Token' => $xtoken,
      'Content-Type' => 'application/json'
    ),
    'blocking' => true,
    'method'   => $method
  );
  if (!empty($data)) {
    $payload = json_encode($data);
    $args['body'] = $payload;
  }
  $response  = wp_remote_request($full_url, $args);
  $body      = wp_remote_retrieve_body( $response );
  $http_code = wp_remote_retrieve_response_code( $response );
  if ( $http_code != 200) {
    $full_url = preg_replace("|/email/.*$|", "/email/hidden@email.com", $full_url);
    error_log("[" . $http_code . "] " . $full_url);
    error_log($body);
  }
  return @json_decode($body);
}

function bettergdpr_api_get_account_standing() {
  return bettergdpr_get_request("/v1/account/standing");
}

function bettergdpr_api_get_user($method, $address) {
  $result = bettergdpr_get_request("/v1/user/".$method."/".$address);
  return $result;
}

function bettergdpr_api_get_user_agreements($method, $address) {
  $result = bettergdpr_get_request("/v1/agreement/".$method."/".$address);
  return $result;
}

function bettergdpr_api_get_all_lbasis() {
  static $saved_data;
  if (!isset($saved_data)) {
    $saved_data = bettergdpr_get_request("/v1/lbasis");
  }
  return $saved_data->rows;
}

function bettergdpr_api_agreement_accept($brief, $email) {
   return bettergdpr_data_request('POST', "/v1/agreement/$brief/email/$email", array());
}

function bettergdpr_api_delete_user($email) {
  return bettergdpr_data_request('DELETE', "/v1/user/email/$email", array());
}

function bettergdpr_api_wpsetup() {
  return bettergdpr_data_request('POST', "/v1/account/wpsetup", array());
}

function bettergdpr_api_create_pactivity($activity, $title, $desc) {
  $data = array(
	  'title' => $title,
	  'fulldesc' => $desc
  );
  return bettergdpr_data_request('POST', "/v1/pactivity/".$activity, $data);
}

function bettergdpr_api_create_lbasis($brief, $page, $required, $title, $desc, $requiredmsg, $status="active") {
  if ($status) {
    $status = 'active';
  }
  $data = array(
    'brief' => $brief,
    'module' => $page,
    'basistype' => 'consent',
    'requiredflag' => $required,
    'shortdesc' => $title,
    'fulldesc' => $desc,
    'requiredmsg' => $requiredmsg,
    'usercontrol' => True,
    'status' => $status
  );
  return bettergdpr_data_request('POST', "/v1/lbasis/".$brief, $data);
}

function bettergdpr_api_link_pactivity($activity, $brief) {
  return bettergdpr_data_request('POST', "/v1/pactivity/".$activity.'/'.$brief, array()); 
}

function bettergdpr_api_create_user($user) {
  $wordpress = $user->data;
  $email = $wordpress->user_email;
  #$login = $wordpress->user_login;
  $data = array(
    'email' => $email,
    #'login' => $login
  );
  return bettergdpr_data_request('POST', "/v1/user", $data);
}

function bettergdpr_api_create_user_by_email($email) {
  $data = array(
    'email' => $email,
  );
  return bettergdpr_data_request('POST', "/v1/user", $data);
}

function bettergdpr_api_update_user($old_email, $user) {
  $wordpress = $user->data;
  $email = $wordpress->user_email;
  if ($old_email == $email) {
    return;
  }
  #$login = $wordpress->user_login;
  $data = array(
    'email' => $email,
    #'login' => $login
  );
  return bettergdpr_data_request('PUT', "/v1/user/email/$old_email", $data);
}

function bettergdpr_api_register($code, $site, $email, $subdomain) {
  $data = array(
    'full' => "1",
    'site' => $site,
    'email' => $email,
    'subdomain' => $subdomain
  );
  if (!empty($code)) {
    $data['code'] = $code;
  }
  $full_url = "https://privacybunker.io/api/signup.php";
  $args = array(
    'headers' => array(
      'Content-Type' => 'application/x-www-form-urlencoded'
    ),
    'blocking' => true,
    'method'   => 'POST'
  );
  $payload = http_build_query($data);
  $args['body'] = $payload;
  $response  = wp_remote_request($full_url, $args);
  $body      = wp_remote_retrieve_body( $response );
  $http_code = wp_remote_retrieve_response_code( $response );
  if ( $http_code != 200) {
    $full_url = preg_replace("|/email/.*$|", "/email/hidden@email.com", $full_url);
    error_log("[" . $http_code . "] " . $full_url);
    error_log($body);
  }
  return @json_decode($body);
}

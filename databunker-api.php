<?php

function paranoidguy_get_request($url) {
  $xtoken = get_option( 'paranoidguy_xtoken', '' );
  $subdomain = get_option( 'paranoidguy_subdomain', '' );
  $srv = "https://".$subdomain.".databunker.cloud";

  $full_url = $srv.$url;
  $args = array(
    'headers' => array(
      'X-Bunker-Token' => $xtoken
    ),
    'blocking' => true
  )
  $response  = wp_remote_get($full_url, $args);
  $body      = wp_remote_retrieve_body( $response );
  $http_code = wp_remote_retrieve_response_code( $response );
  if ( $http_code != 200) {
    error_log($full_url);
    error_log($http_code);
    error_log($response);
  }
  return @json_decode($body);
}

function paranoidguy_data_request($method, $url, $data) {
  $xtoken = get_option( 'paranoidguy_xtoken', '' );
  $subdomain = get_option( 'paranoidguy_subdomain', '' );
  $srv = "https://".$subdomain.".databunker.cloud";
  $full_url = $srv.$url;
  error_log($full_url);
  $payload = json_encode($data);
  $args = array(
    'headers' => array(
      'X-Bunker-Token' => $xtoken,
      'Content-Type' => 'application/json'
    ),
    'blocking' => true,
    'method' => $method
  )
  if (!empty($data)) {
    $args['body'] = $data;
  }
  $response  = wp_remote_request($full_url, $args);
  $body      = wp_remote_retrieve_body( $response );
  $http_code = wp_remote_retrieve_response_code( $response );
  if ( $http_code != 200) {
    error_log($full_url);
    error_log($http_code);
    error_log($response);
  }
  return @json_decode($body);
}


function paranoidguy_api_get_user($method, $address) {
  $result = paranoidguy_get_request("/v1/user/".$method."/".$address);
  return $result;
}

function paranoidguy_api_get_user_agreements($method, $address) {
  $result = paranoidguy_get_request("/v1/agreement/".$method."/".$address);
  return $result;
}

function paranoidguy_api_get_all_lbasis() {
  static $saved_data;
  if (!isset($saved_data)) {
    $saved_data = paranoidguy_get_request("/v1/lbasis");
  }
  return $saved_data->rows;
}

function paranoidguy_api_agreement_accept($brief, $email) {
   return paranoidguy_data_request('POST', "/v1/agreement/$brief/email/$email", array());
}

function paranoidguy_api_delete_user($email) {
  return paranoidguy_data_request('DELETE', "/v1/user/email/$email", array());
}

function paranoidguy_api_create_pactivity($activity, $titlei, $desc) {
  $data = array(
	  'title' => $title,
	  'fulldesc' => $desc
  );
  return paranoidguy_data_request('POST', "/v1/pactivity/".$activity, $data);
}

function paranoidguy_api_create_lbasis($brief, $page, $required, $title, $desc, $requiredmsg, $status="active") {
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
  return paranoidguy_data_request('POST', "/v1/lbasis/".$brief, $data);
}

function paranoidguy_api_link_pactivity($activity, $brief) {
  return paranoidguy_data_request('POST', "/v1/pactivity/".$activity.'/'.$brief, array()); 
}

function paranoidguy_api_create_user($user) {
  $wordpress = $user->data;
  $email = $wordpress->user_email;
  $login = $wordpress->user_login;
  unset($wordpress->user_email);
  unset($wordpress->user_login);
  unset($wordpress->user_pass);
  $data = array(
    'email' => $email,
    'login' => $login,
    'wordpress' => $wordpress);
  var_error_log($data);
  return paranoidguy_data_request('POST', "/v1/user", $data);
}

function paranoidguy_api_update_user($old_email, $user) {
  $wordpress = $user->data;
  $email = $wordpress->user_email;
  $login = $wordpress->user_login;
  unset($wordpress->user_email);
  unset($wordpress->user_login);
  unset($wordpress->user_pass);
  $data = array(
    'email' => $email,
    'login' => $login,
    'wordpress' => $wordpress);
  return paranoidguy_data_request('PUT', "/v1/user/email/$old_email", $data);
}

function paranoidguy_api_register($code, $site, $email, $subdomain) {
  $data = array(
    'code' => $code,
    'site' => $site,
    'email' => $email,
    'subdomain' => $subdomain    
  );
  $full_url = "https://databunker.cloud/v1/account/step2";
  $payload = json_encode($data);
  $curl = curl_init();
  $headers = array('Content-Type: application/json');
  curl_setopt_array($curl, array(
    CURLOPT_URL => $full_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => $payload
  ));
  $response = @curl_exec($curl);
  $httpcode = @curl_getinfo($curl, CURLINFO_HTTP_CODE);
  @curl_close($curl);
  if ( $httpcode != 200) {
    error_log($httpcode);
    error_log($response);
  }
  return @json_decode($response);
}

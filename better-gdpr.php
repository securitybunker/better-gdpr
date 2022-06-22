<?php
/**
 * BetterGDPR
 *
 * @package     BetterGDPR
 * @author      Yuli Stremovsky
 * @copyright   2020 https://securitybunker.io
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Better GDPR
 * Plugin URI:  https:/privacybunker.io
 * Description: GDPR & Cookie Consent plugin built by PrivacyBunker.io team.
 * Version:     0.3.2
 * Author:      Yuli Stremovsky
 * Author URI:  https://securitybunker.io
 * Text Domain: https://privacybunker.io
 * License:     GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

require_once(ABSPATH.'/wp-admin/includes/privacy-tools.php');
require_once('databunker-api.php');
require_once('admin-user.php');


function bettergdpr_var_error_log( $object=null ){
  ob_start();                    // start buffer capture
  var_dump( $object );           // dump the values
  $contents = ob_get_contents(); // put the buffer into a variable
  ob_end_clean();                // end capture
  error_log( $contents );        // log contents of the result of var_dump( $object )
}

function bettergdpr_request_export($request) {
  $sitekey = get_option( 'bettergdpr_sitekey', '' );
  $auth = $request->get_header('authorization');
  $auth2 = $request->get_header('x-authorization');
  if (strlen($sitekey) == 0) {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  if ($auth != "Bearer $sitekey" && $auth2 != "Bearer $sitekey") {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  $email = $request["email"];
  if (strpos($email, '@') !== true) {
    $email = str_replace('%40', '@', $email);
  }
  $wc = array();
  if (function_exists('wc_get_orders')) {
    $customer_orders = wc_get_orders(
      array( 'limit'    => -1,
             'customer' => array( $email ),
             #'return'   => 'ids',
      )
    );
    if (!empty($customer_orders)) {
      $orders = array();
      foreach ( $customer_orders as $order)
      {
        $orders[] = $order->data;
      }
      $wc['orders'] = $orders;
    }
  }
  $user = get_user_by("email", $email);
  if (!$user && empty($wc)) {
    return new WP_Error( 'not_found', 'user not found', array( 'status' => 404 ));
  }
  if (!$user && !empty($wc)) {
    $wc['user_email'] = $email;
    $data = json_encode($wc);
    header('Content-Type: application/json; charset=UTF-8');
    echo($data);
    exit();
  }
  $data = $user->data;
  unset($data->user_pass);
  if (!empty($wc['orders'])) {
    $data['orders'] = $wc['orders'];
  }
  $data = json_encode($data);
  header('Content-Type: application/json; charset=UTF-8');
  echo($data);
  exit();
}

function bettergdpr_request_full_export($request) {
  $sitekey = get_option( 'bettergdpr_sitekey', '' );
  $auth = $request->get_header('authorization');
  $auth2 = $request->get_header('x-authorization');
  if (strlen($sitekey) == 0) {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  if ($auth != "Bearer $sitekey" && $auth2 != "Bearer $sitekey") {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  $email = $request["email"];
  if (strpos($email, '@') !== true) {
    $email = str_replace('%40', '@', $email);
  }
  $requests_query = new WP_Query(
    array(
      'post_type'     => 'user_request',
      'post_name__in' => array( 'export_personal_data' ), // Action name stored in post_name column.
      'title'   => $email,  // Email address stored in post_title column.
      'post_status'   => array(
        'request-pending',
        'request-confirmed',
      ),
      'fields' => 'ids',
    )
  );
  $request_id = 0;
  if ( $requests_query->found_posts ) {
    $request_id = $requests_query->posts[0];
  } else {
    $request_id = wp_create_user_request( $email, 'export_personal_data' );
  }
  if (!$request_id) {
    header('Content-Type: application/json; charset=UTF-8');
    echo('{"status":"error","status":"try again"}');
    exit();
  }
  //error_log("request id: $request_id");
  //do_action( 'wp_privacy_personal_data_export_file', $request_id );
  wp_privacy_generate_personal_data_export_file( $request_id );
  $export_file_url = get_post_meta( $request_id, '_export_file_url', true );
  #error_log("**** after get_post_meta $export_file_url ");
  header('Content-Type: application/json; charset=UTF-8');
  echo('{"status":"ok","url":"'.$export_file_url.'"}');
  exit();
}

function bettergdpr_request_delete($request) {
  $sitekey = get_option( 'bettergdpr_sitekey', '' );
  $auth = $request->get_header('authorization');
  $auth2 = $request->get_header('x-authorization');
  if (strlen($sitekey) == 0) {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  if ($auth != "Bearer $sitekey" && $auth2 != "Bearer $sitekey") {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  $email = $request["email"];
  if (strpos($email, '@') !== true) {
    $email = str_replace('%40', '@', $email);
  }
  $user = get_user_by("email", $email);
  if (!$user) {
    return new WP_Error( 'not_found', 'user not found', array( 'status' => 404 ));
  }
  if ($user->data && $user->data->ID) {
    require_once(ABSPATH.'/wp-admin/includes/user.php');
    $id = $user->data->ID;
    wp_delete_user($id);
    header('Content-Type: application/json; charset=UTF-8');
    echo('{"status":"ok","deleted":"deleted"}');
    exit();
  }
  return new WP_Error( 'not_found', 'user not found', array( 'status' => 404 ));
}

function bettergdpr_request_change_email($request) {
  $sitekey = get_option( 'bettergdpr_sitekey', '' );
  $auth = $request->get_header('authorization');
  $auth2 = $request->get_header('x-authorization');
  if (strlen($sitekey) == 0) {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  if ($auth != "Bearer $sitekey" && $auth2 != "Bearer $sitekey") {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  $email = $request["email"];
  if (strpos($email, '@') !== true) {
    $email = str_replace('%40', '@', $email);
  }
  $user = get_user_by("email", $email);
  if (!$user) {
    return new WP_Error( 'not_found', 'user not found', array( 'status' => 404 ));
  }
  $new_email = $request["newemail"];
  if (strpos($new_email, '@') !== true) {
    $new_email = str_replace('%40', '@', $new_email);
  }
  $user->user_email = $new_email;
  wp_update_user($user);
  header('Content-Type: application/json; charset=UTF-8');
  echo('{"status":"ok","changed":"changed"}');
  exit();
}

function bettergdpr_request_validate($request) {
  $sitekey = get_option( 'bettergdpr_sitekey', '' );
  $auth = $request->get_header('authorization');
  $auth2 = $request->get_header('x-authorization');
  if (strlen($sitekey) == 0) {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  if ($auth != "Bearer $sitekey" && $auth2 != "Bearer $sitekey") {
    return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ));
  }
  header('Content-Type: application/json; charset=UTF-8');
  echo('{"status":"ok","valid":"valid"}');
  exit();
}

$bettergdpr_sitekey = get_option( 'bettergdpr_sitekey', '' );
if ($bettergdpr_sitekey) {
  add_action('rest_api_init', function () {
    register_rest_route( 'bettergdpr/v1', 'export/(?P<email>[\d\%\@\.\w]+)',array(
      'methods'  => 'GET',
      'callback' => 'bettergdpr_request_export'
    ));
    register_rest_route( 'bettergdpr/v1', 'fullexport/(?P<email>[\d\%\@\.\w]+)',array(
      'methods'  => 'GET',
      'callback' => 'bettergdpr_request_full_export'
    ));
    register_rest_route( 'bettergdpr/v1', 'delete/(?P<email>[\d\%\@\.\w]+)',array(
      'methods'  => 'GET',
      'callback' => 'bettergdpr_request_delete'
    ));
    register_rest_route( 'bettergdpr/v1', 'changemail/(?P<email>[\d\%\@\.\w]+)',array(
      'methods'  => 'POST',
      'callback' => 'bettergdpr_request_change_email'
    ));
    register_rest_route( 'bettergdpr/v1', 'validate', array(
      'methods'  => 'GET',
      'callback' => 'bettergdpr_request_validate'
    ));
  });
}

function bettergdpr_show_consents($page) {
  $options = bettergdpr_api_get_all_lbasis();
  $out = "";
  foreach ($options as $row) {
    if ($row->status != "active") {
      continue;
    }
    if ($row->module != "signup-page") {
      continue;
    }

    $b = $row->brief;
    $r = ($row->requiredflag)? "required" : "";
    $desc = $row->shortdesc;
    $checked = "";
    if (isset($_POST["bettergdpr-$b"])) {
      $checked = "checked";
    }
    $out = $out . "<p><label class='pranoidguy-cb-label paranaoidguy-$b'><input type='checkbox' $checked name='bettergdpr-$b' id='bettergdpr-$b' $r>$desc</label></p>";
  }
  return $out;
}

function bettergdpr_custom_registration() {
  $out = bettergdpr_show_consents('signup-page');
  print($out);
}

function bettergdpr_woocommerce_checkout($fields) {
  $options = bettergdpr_api_get_all_lbasis();
  if ( empty( $options ) ) {
    return $fields;
  }
  foreach ($options as $row) {
    if ($row->status != "active") {
      continue;
    }
    if ($row->module != "signup-page") {
      continue;
    }
    $b = $row->brief;
    $r = ($row->requiredflag)? "required" : "";
    $desc = $row->shortdesc;
    $checked = "";
    if (isset($_POST["bettergdpr-$b"])) {
      $checked = "checked";
    }
    $fields['billing'][ 'bettergdpr-' . $b ] = array(
                        'type'     => 'checkbox',
                        'label'    => $desc,
			'required' => $r,
			'checked'  => $checked,
    );
  }
  return $fields;
}

function bettergdpr_registration_check( $errors, $sanitized_user_login, $user_email ) {
  $options = bettergdpr_api_get_all_lbasis();
  foreach ($options as $row) {
    if ($row->status != "active") {
      continue;
    }
    if ($row->module != "signup-page") {
      continue;
    }
    $b = $row->brief;
    if ($row->requiredflag && !isset($_POST['bettergdpr-'.$b])) {
      $reason = $row->requiredmsg;
      $errors->add('missing_required', "<strong>Error:</strong> $b is required. $reason");
    }
  }
  return $errors;
}

function bettergdpr_delete_user($user_id) {
  $user = get_user_by("id", $user_id);
  if (!isset($user) || !isset($user->user_email)) {
    return;
  }
  $email = $user->user_email;
  bettergdpr_api_delete_user($email);
}

function bettergdpr_registration_save($user_id ) { 
  $user = get_user_by("id", $user_id);
  $email = $user->user_email;
  $record = bettergdpr_api_get_user('email', $email);
  if (isset($record) && isset($record->status) && $record->status == "ok") {
    bettergdpr_api_update_user($email, $user);
  } else {
    bettergdpr_api_create_user($user);
  }
  $options = bettergdpr_api_get_all_lbasis();
  if (isset($options)) {
    foreach ($options as $row) {
      if ($row->status != "active") {
        continue;
      }
      $b = $row->brief;
      if (isset($_POST['bettergdpr-'.$b])) {
        bettergdpr_api_agreement_accept($b, $email);
      }
    }
  }
  if (isset($_COOKIE['BETTERGDPR'])) {
    $cookie_value = sanitize_text_field($_COOKIE['BETTERGDPR']);
    $options = explode(',', $cookie_value);
    foreach ($options as $row) {
      bettergdpr_api_agreement_accept($row, $email);
    }
  }
  return $errors;
}

function bettergdpr_woocommerce_consent_save( $customer_id, $data ) {
  $email = $data['billing_email'];
  if (!$email) {
    return;
  }
  $record = bettergdpr_api_get_user('email', $email);
  if (!(isset($record) && isset($record->status) && $record->status == "ok")) {
    bettergdpr_api_create_user_by_email($email);
  }
  $options = bettergdpr_api_get_all_lbasis();
  if (isset($options)) {
    foreach ($options as $row) {
      if ($row->status != "active") {
        continue;
      }
      $b = $row->brief;
      if (isset($data['bettergdpr-'.$b])) {
        bettergdpr_api_agreement_accept($b, $email);
      }
    }
  }
  if (isset($_COOKIE['BETTERGDPR'])) {
    $cookie_value = sanitize_text_field($_COOKIE['BETTERGDPR']);
    $options = explode(',', $cookie_value);
    foreach ($options as $row) {
      bettergdpr_api_agreement_accept($row, $email);
    }
  }
}

function bettergdpr_profile_update($user_id, $old) {
  $user = get_user_by("id", $user_id);
  $record = bettergdpr_api_get_user('email', $old->user_email);
  if (isset($record) && isset($record->status) && $record->status == "ok") {
    bettergdpr_api_update_user($old->user_email, $user);
  } else {
    bettergdpr_api_create_user($user); 
  }
}

function bettergdpr_cookie_consent() {
  $subdomain = get_option( 'bettergdpr_subdomain', '' );
  if ($subdomain == '') {
    return;
  }
  $srv = "https://".$subdomain.".privacybunker.cloud/";
  wp_enqueue_script( 'bettergdpr_js', $srv . 'site/better-gdpr.js?bettergdprtenant=' . $subdomain );
}

add_action( 'delete_user', 'bettergdpr_delete_user');
add_action( 'profile_update', 'bettergdpr_profile_update', 10, 2);
add_action( 'register_form', 'bettergdpr_custom_registration');
add_action( 'registration_errors', 'bettergdpr_registration_check', 10, 3);
add_action( 'user_register', 'bettergdpr_registration_save');
add_action( 'wp_enqueue_scripts', 'bettergdpr_cookie_consent', 1);
//add_action( 'wp_footer', 'bettergdpr_cookie_consent');

if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
  add_action( 'woocommerce_register_form', 'bettergdpr_custom_registration', 21);
}
add_filter( 'woocommerce_checkout_fields', 'bettergdpr_woocommerce_checkout');
add_action( 'woocommerce_checkout_update_user_meta', 'bettergdpr_woocommerce_consent_save', 10, 2 );

function bettergdpr_profile_edit_user( $user ) {
$subdomain = get_option( 'bettergdpr_subdomain', '' );
$srv= "https://".$subdomain.".privacybunker.cloud/";

?><h1>Privacy Portal</h1>
<p>The GDPR provides the following rights for individuals:</p>
<ol><li>The right to be informed</li>
<li>The right of access</li>
<li>The right to rectification</li>
<li>The right to erasure</li>
<li>The right to restrict processing</li>
<li>The right to data portability</li>
<li>The right to object</li>
<li>Rights in relation to automated decision making and profiling</li>
</ol>
<p>You can manage all your rights in the Privacy portal availble at <a target="_blank" href="<?php echo $srv; ?>"><?php echo $srv; ?></a></p>
<?php
}
add_action( 'show_user_profile', 'bettergdpr_profile_edit_user' );

bettergdpr_init_admin();

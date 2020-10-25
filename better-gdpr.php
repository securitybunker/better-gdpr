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
 * Version:     0.2.2
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
  $user = get_user_by("email", $email);
  if (!$user) {
    return new WP_Error( 'not_found', 'user not found', array( 'status' => 404 ));
  }
  $data = $user->data;
  unset($data->user_pass);
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
$css_file = plugin_dir_url( dirname( __FILE__ ) ) . 'better-gdpr/better-gdpr.css';
$powered_by_file = plugin_dir_url( dirname( __FILE__ ) ) . 'better-gdpr/assets/powered-by.png';
$logo_file = plugin_dir_url( dirname( __FILE__ ) ) . 'better-gdpr/assets/logo.png';

# body.faded {overflow:hidden}
?>
<script>
function bettergdpr_show_cookie_settings_popup() {
  var style = document.getElementById('bettergdpr_style_body');
  if (!style) {
    style = document.createElement('link');
    style.rel = 'stylesheet';
    style.type = 'text/css';
    style.rel = "stylesheet";
    style.id = 'bettergdpr_style_body';
    //style.innerHTML = '.bettergdpr_faded_body { overflow:hidden}';
    style.href = '<?php echo($css_file); ?>?aaa=aaa';
    document.getElementsByTagName('head')[0].appendChild(style);
  }
  setTimeout(function() {
    var body = document.getElementsByTagName('body');
    if (body && body[0]) {
      //alert(body[0].className);
      body[0].className = body[0].className + ' bettergdpr_faded_body';
    }
    bettergdpr_close_cookie_banner();
    var settings = document.getElementById("bettergdpr_settings_popup");
    if (settings) {
      settings.style.display = "block";
    }
    bettergdpr_show_cookie_settings();
  }, 500);
}
function bettergdpr_close_cookie_settings_popup() {
  var body = document.getElementsByTagName('body');
  if (body && body[0]) {
    var oldList = body[0].className.replace(" bettergdpr_faded_body", "");
    body[0].className = oldList;
  }
  var settings = document.getElementById("bettergdpr_settings_popup");
  if (settings) {
    settings.style.display = "none";
  }
}
function bettergdpr_close_cookie_banner() {
  var popup = document.getElementById('bettergdpr_cookie_banner');
  if (popup) {
    //popup.style.display = "none";
    popup.style.visibility = "hidden";
  }
}
var bettergdpr_settings_data = {};
function bettergdpr_load_settings() {
  var xhr0 = new XMLHttpRequest();
  //xhr0.open('GET', "<?php echo($srv); ?>/v1/sys/cookiesettings");
  xhr0.open('GET', "<?php echo($srv); ?>/v1/sys/cookiesettings");
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      bettergdpr_settings_data = JSON.parse(xhr0.responseText);
      const scripts = bettergdpr_settings_data["scripts"];
      const oldCookie = bettergdpr_get_cookie('BETTERGDPR');
      if (oldCookie) {
        const briefs = oldCookie.split(',');
        for (var index = 0; index < scripts.length; index++) {
          var scriptObj = scripts[index];
          var found = false;
          if (briefs[0] === "all") {
             found = true;
          } else {
            for (var j = 0; j < briefs.length; j++) {
              if (scriptObj.briefs.includes(briefs[j])) {
                found = true;
              }
            }
          }
          if (found == true) {
            if (scriptObj.script.startsWith("<script")) {
              var template = document.createElement('template');
              template.innerHTML = scriptObj.script;
              document.head.appendChild( template );
            } else {
              var script = document.createElement( "script" );
              script.text = scriptObj.script;
              document.head.appendChild( script );
            }
          }
        }
      }
      const popupConf = bettergdpr_settings_data.ui;
      if (popupConf.EnablePopup) {
        if (!oldCookie) {
	  var banner = document.getElementById('bettergdpr_cookie_banner');
          if (banner) {
	    banner.style.visibility = "visible";
            var obj = document.getElementById('bettergdpr_popup_message');
	    if (obj) {
              obj.innerHTML = popupConf.PopupMessage;
	    }
	    obj = document.getElementById('CustomPopupTitle')
            if (obj) {
              obj.innerHTML = popupConf.CustomPopupTitle;
	    }
	    obj = document.getElementById('CustomPopupDescription')
            if (obj) {
              obj.innerHTML = popupConf.CustomPopupDescription;
            }
          }
        }
      }
    }
  };
  xhr0.send();
}
function bettergdpr_set_cookie(name, briefs) {
  const value = briefs.join(',');
  var expires = "";
  const days = 30;
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days*24*60*60*1000));
    expires = "; expires=" + date.toUTCString();
  }
  var old = document.cookie;
  if (old) {
    old = old.trim();
    if (old.length > 0 && old[old.length-1] !== ';') {
      old = old + '; ';
    }
  } else {
    old = '';
  }
  document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function bettergdpr_get_cookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}
function bettergdpr_show_cookie_settings() {
  var out = '';
  const rows = bettergdpr_settings_data["rows"];
  for (var index = 0; index < rows.length; index++) {
    const r = rows[index];
    var locked = 'onclick="return false;"';
    var checked = 'checked';
    var flag = '&nbsp;*';
    if (!r['requiredflag']) {
      checked= '';
      locked = '';
      flag = '';
    }
    out = out + '<div class="r">';
    out = out + '<div class="h">'+r["shortdesc"]+flag+'</div>';
    out = out + '<div class="c"><label class="switch"><input type="checkbox" '+checked+' '+locked+' name="'+r["brief"]+'"><span class="slider round"></span></label></div>';
    out = out + '<p>'+r["fulldesc"]+'</p>';
    out = out + '</div>';
    //console.log(r);
  }
  var page = document.getElementById('bettergdpr_settings_items');
  page.innerHTML = out;
}
function bettergdpr_allow_all_cookies() {
  var briefs = ["all"];
  bettergdpr_set_cookie('BETTERGDPR', briefs);
  bettergdpr_close_cookie_settings_popup();
  bettergdpr_close_cookie_banner();
}
function  bettergdpr_allow_custom_cookies() {
  var selected = [];
  var page = document.getElementById('bettergdpr_settings_items');
  var inputs = page.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {  
    if (inputs[i].type == "checkbox" && inputs[i].checked) {  
      selected.push(inputs[i].name);  
    }
  }
  bettergdpr_set_cookie('BETTERGDPR', selected);
  bettergdpr_close_cookie_settings_popup();
  bettergdpr_close_cookie_banner();
}
function  bettergdpr_allow_required_cookies() {
  var briefs = [];
  const rows = bettergdpr_settings_data["rows"];
  for (var index = 0; index < rows.length; index++) {
    const r = rows[index];
    if (r['requiredflag']) {
      briefs.push(r['brief']);
    }
  }
  bettergdpr_set_cookie('BETTERGDPR', briefs);
  bettergdpr_close_cookie_settings_popup();
  bettergdpr_close_cookie_banner();
}
bettergdpr_load_settings();
</script>
<style>
#bettergdpr_cookie_banner {
background-color:rgba(71,81,84,.95);box-shadow: 0 -8px 20px 0 rgba(0,0,0,.2);width:100%;margin:0 auto;padding:5px;font-size: 1em;color: #6d6d6d;bottom:0px;position:fixed;left: 0px;opacity:0.9;filter:alpha(opacity=80);height:auto;max-height:500px;z-index:9999999999;overflow:hidden;line-height: 1.25;
}
#bettergdpr_popup_message {display:block;color:#fff;width: 100%;padding:10px;}
#bettergdpr_popup_buttons {display:flex;flex-direction:row;flex-wrap:wrap;flex-grow:2;justify-content:space-between;margin:0 auto;text-align:center;vertical-align: middle;padding:0px 10px 0px 10px;}
#bettergdpr_cookie_banner #bettergdpr_agree_btn {text-decoration:none;font-weight: 400;text-transform: uppercase;cursor: pointer;background-color: #2eb8ff;min-width: 150px;min-height: 33px;margin: 0;padding: 5px 2px;font-size: 15px;color: #fff;border: none;border-radius: 3px;outline: none;line-height: inherit;}
#bettergdpr_cookie_banner #bettergdpr_req_btn {text-decoration:none;font-weight: 400;text-transform: uppercase;background-color:transparent;cursor: pointer;min-width: 150px;min-height: 30px;margin: 0px;padding: 5px 2px;font-size: 15px;color: #fff;border: 1px solid #fff;border-radius: 3px;outline: none;line-height: inherit;}
#bettergdpr_branding {color:#fff;padding:10px;}
#bettergdpr_powered_by {float:left;font-size:12px;}
@media only screen and (min-width:769px){
#bettergdpr_cookie_banner {max-width:70%;}
}
@media only screen and (max-width:500px){
#bettergdpr_popup_message {padding:5px;}
#bettergdpr_popup_buttons {padding:0px 5px 0px 5px;}
#bettergdpr_popup_buttons #bettergdpr_agree_btn {  margin-right: 10px; }
#bettergdpr_powered_by {display:none;}
#bettergdpr_branding {padding:5px;}
}
</style>
<div id="bettergdpr_settings_popup" style="background: rgba(0, 0, 0, 0.7);position: fixed;top: 0;right: 0;bottom: 0;left: 0;z-index:999999999; display:none;">
  <div style="position:absolute; top:20px; right:20px; background: transparent;cursor: pointer;color:#fff;font-family: 'Helvetica', 'Arial', sans-serif;font-size: 2em;font-weight: 400;text-align: center;width: 40px;height: 40px;border-radius: 5px;margin: 0 auto;" onclick="bettergdpr_close_cookie_settings_popup()">X</div>
  <div style="display:block;height:10%;">&nbsp;</div>
  <div id="bettergdpr_settings_page">
  <div style="display:block;">
  <h3 id="CustomPopupTitle" style="float:left;"></h3>
  <div style="float:right;"><a target="_blank" href="https://privacybunker.io/"><img width=200 src="<?php echo($logo_file); ?>" /></a></div>
  <div style="clear: both;"></div>
  </div>
  <p style='text-align: justify;' id="CustomPopupDescription"></p>
  <center><button onclick='bettergdpr_allow_all_cookies();'>Allow All</button></center>
  <h4>Manage individual settings</h4>
  <div id="bettergdpr_settings_items"></div>
  <center><button onclick='bettergdpr_allow_custom_cookies();'>Save settings</button></center>
  </div>
</div>
<div id="bettergdpr_cookie_banner" style="visibility:hidden;">
 <div id='bettergdpr_popup_message'></div>
 <div id='bettergdpr_popup_buttons'>
  <button id='bettergdpr_req_btn' onclick='bettergdpr_allow_required_cookies();'>Required only</button>
  <button id='bettergdpr_agree_btn' onclick='bettergdpr_allow_all_cookies();'>I agree&nbsp;<span style="font-weight: 700;style:inline-block;height:25px;">✓</span></button>
  <div style="display:inline;padding:0;margin:0;"><u style="color:#fff;font-weight: 400;background-color:transparent;cursor: pointer;font-size:12px;" onclick="bettergdpr_show_cookie_settings_popup();">Customize settings</u></div>
 </div>
 <div id='bettergdpr_branding'>
  <div id='bettergdpr_powered_by'>Powered by&nbsp;&nbsp;<a target='_blank' href='https://privacybunker.io/'><img style="display:inline;margin-top:-5px;" width=140 src="<?php echo($powered_by_file); ?>"/></a></div>
  <div style="float:right;font-size:12px;">Privacy portal <a style='color:#fff;font-weight: 300;background-color:transparent;cursor: pointer;font-size:12px;text-decoration:underline;' href='<?php echo($srv); ?>' target='_blank'><?php echo($srv); ?></a></div>
 </div>
</div>
<?php
}

add_action( 'delete_user', 'bettergdpr_delete_user');
add_action( 'profile_update', 'bettergdpr_profile_update', 10, 2);
add_action( 'register_form', 'bettergdpr_custom_registration');
add_action( 'registration_errors', 'bettergdpr_registration_check', 10, 3);
add_action( 'user_register', 'bettergdpr_registration_save');
add_action( 'wp_footer', 'bettergdpr_cookie_consent');
if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) {
  add_action( 'woocommerce_register_form', 'bettergdpr_custom_registration', 21);
}
add_filter( 'woocommerce_checkout_fields', 'bettergdpr_woocommerce_checkout');


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

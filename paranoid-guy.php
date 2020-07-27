<?php
/**
 * BetterGDPR
 *
 * @package     BetterGDPR
 * @author      Yuli Stremovsky
 * @copyright   2020 https://paranoidguy.com
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Better GDPR
 * Plugin URI:  https://paranoidguy.com
 * Description: GDPR & Cookie Consent plugin built by ParanoidGuy.com team.
 * Version:     1.0.0
 * Author:      Yuli Stremovsky
 * Author URI:  https://paranoidguy.com/about-us
 * Text Domain: https://paranoidguy.com
 * License:     GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

require_once(ABSPATH.'/wp-admin/includes/privacy-tools.php');
require_once('databunker-api.php');
require_once('admin-user.php');


function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}

function paranoidguy_request_export($request) {
	$auth = $request->get_header('authorization');
	if (!$auth) {
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
			'title'	 => $email,	// Email address stored in post_title column.
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
		$request = wp_create_user_request( $email, 'export_personal_data' );
		$request_id = $request->ID;
	}
	//do_action( 'wp_privacy_personal_data_export_file', $request_id );
	wp_privacy_generate_personal_data_export_file( $request_id );
	$export_file_url = get_post_meta( $request_id, '_export_file_url', true );
	error_log("**** after get_post_meta $export_file_url ");
	header('Content-Type: application/json; charset=UTF-8');
        echo('{"status":"ok","url":"'.$export_file_url.'"}');
        exit();
	#return $response;
}


add_action('rest_api_init', function () {
  register_rest_route( 'paranoidguy/v1', 'export/(?P<email>[\d\%\@\.\w]+)',array(
    'methods'  => 'GET',
    'callback' => 'paranoidguy_request_export'
  ));
});


function paranoidguy_show_consents($page) {
  $options = paranoidguy_api_get_all_lbasis();
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
    if (isset($_POST["paranoidguy-$b"])) {
      $checked = "checked";
    }
    $out = $out . "<p><label class='pranoidguy-cb-label paranaoidguy-$b'><input type='checkbox' $checked name='paranoidguy-$b' id='paranoidguy-$b' $r>$desc</label></p>";
  }
  return $out;
}

function paranoidguy_custom_registration() {
  $out = paranoidguy_show_consents('signup-page');
  print($out);
}

function paranoidguy_registration_check( $errors, $sanitized_user_login, $user_email ) {
  $options = paranoidguy_api_get_all_lbasis();
  foreach ($options as $row) {
    if ($row->status != "active") {
      continue;
    }
    if ($row->module != "signup-page") {
      continue;
    }
    $b = $row->brief;
    if ($row->requiredflag && !isset($_POST['paranoidguy-'.$b])) {
      $reason = $row->requiredmsg;
      $errors->add('missing_required', "<strong>Error:</strong> $b is required. $reason");
    }
  }
  return $errors;
}

function paranoidguy_delete_user($user_id) {
  $user = get_user_by("id", $user_id);
  if (!isset($user) || !isset($user->user_email)) {
    return;
  }
  $email = $user->user_email;
  paranoidguy_api_delete_user($email);
}

function paranoidguy_registration_save($user_id ) { 
  $user = get_user_by("id", $user_id);
  $email = $user->user_email;
  $record = paranoidguy_api_get_user('email', $email);
  if (isset($record) && isset($record->status) && $record->status == "ok") {
    paranoidguy_api_update_user($email, $user);
  } else {
    paranoidguy_api_create_user($user);
  }
  $options = paranoidguy_api_get_all_lbasis();
  if (isset($options)) {
    foreach ($options as $row) {
      if ($row->status != "active") {
        continue;
      }
      $b = $row->brief;
      if (isset($_POST['paranoidguy-'.$b])) {
        paranoidguy_api_agreement_accept($b, $email);
      }
    }
  }
  if (isset($_COOKIE['paranoidguy'])) {
    $options = explode(',', $_COOKIE['paranoidguy']);
    foreach ($options as $row) {
      paranoidguy_api_agreement_accept($row, $email);
    }
  }
  return $errors;
}

function paranoidguy_profile_update($user_id, $old) {
  $user = get_user_by("id", $user_id);
  $record = paranoidguy_api_get_user('email', $old->user_email);
  if (isset($record) && isset($record->status) && $record->status == "ok") {
    paranoidguy_api_update_user($old->user_email, $user);
  } else {
    paranoidguy_api_create_user($user); 
  }
}

function paranoidguy_cookie_consent() {
$subdomain = get_option( 'paranoidguy_subdomain', '' );
$srv = "https://".$subdomain.".databunker.cloud/";
$css_file = plugin_dir_url( dirname( __FILE__ ) ) . 'paranoid-guy/paranoid-guy.css';

# body.faded {overflow:hidden}
?>
<script>
function paranoidguy_show_cookie_settings_popup() {
  var style = document.getElementById('paranoidguy_style_body');
  if (!style) {
    style = document.createElement('link');
    style.type = 'text/css';
    style.rel = "stylesheet";
    style.id = 'paranoidguy_style_body';
    //style.innerHTML = '.paranoidguy_faded_body { overflow:hidden}';
    style.href = '<?php echo($css_file); ?>?aaa=aaa';
    document.getElementsByTagName('head')[0].appendChild(style);
  }
  setTimeout(function() {
    var body = document.getElementsByTagName('body');
    if (body && body[0]) {
      //alert(body[0].className);
      body[0].className = body[0].className + ' paranoidguy_faded_body';
    }
    paranoidguy_close_cookie_banner();
    var settings = document.getElementById("paranoidguy_settings_popup");
    if (settings) {
      settings.style.display = "block";
    }
    paranoidguy_show_cookie_settings();
  }, 100);
}
function paranoidguy_close_cookie_settings_popup() {
  var body = document.getElementsByTagName('body');
  if (body && body[0]) {
    var oldList = body[0].className.replace(" paranoidguy_faded_body", "");
    body[0].className = oldList;
  }
  var settings = document.getElementById("paranoidguy_settings_popup");
  if (settings) {
    settings.style.display = "none";
  }
}
function paranoidguy_close_cookie_banner() {
  var popup = document.getElementById('paranoidguy_cookie_banner');
  if (popup) {
    //popup.style.display = "none";
    popup.style.visibility = "hidden";
  }
}
var paranoidguy_settings_data = {};
function paranoidguy_load_settings() {
  var xhr0 = new XMLHttpRequest();
  //xhr0.open('GET', "<?php echo($srv); ?>/v1/sys/cookiesettings");
  xhr0.open('GET', "<?php echo($srv); ?>/v1/sys/cookiesettings");
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      paranoidguy_settings_data = JSON.parse(xhr0.responseText);
      const scripts = paranoidguy_settings_data["scripts"];
      const oldCookie = paranoidguy_get_cookie('paranoidguy');
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
    }
  };
  xhr0.send();
}
function paranoidguy_set_cookie(name, briefs) {
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
      old = old[old.length-1] + '; ';
    }
  } else {
    old = '';
  }
  document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function paranoidguy_get_cookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}
function paranoidguy_show_cookie_settings() {
  var out = '';
  const rows = paranoidguy_settings_data["rows"];
  for (var index = 0; index < rows.length; index++) {
    const r = rows[index];
    var locked = 'onclick="return false;"';
    var flag = '&nbsp;*';
    if (!r['requiredflag']) {
      locked = '';
      flag = '';
    }
    out = out + '<div class="r">';
    out = out + '<div class="h">'+r["shortdesc"]+flag+'</div>';
    out = out + '<div class="c"><label class="switch"><input type="checkbox" checked '+locked+' name="'+r["brief"]+'"><span class="slider round"></span></label></div>';
    out = out + '<p>'+r["fulldesc"]+'</p>';
    out = out + '</div>';
    console.log(r);
  }
  var page = document.getElementById('paranoidguy_settings_items');
  page.innerHTML = out;
  //var briefs = ["aaa","bbb","ccc"];
  //paranoidguy_set_cookie('paranoidguy', briefs);
}
function paranoidguy_allow_all_cookies() {
  var briefs = ["all"];
  paranoidguy_set_cookie('paranoidguy', briefs);
  paranoidguy_close_cookie_settings_popup();
  paranoidguy_close_cookie_banner();
}
function  paranoidguy_allow_custom_cookies() {
  var selected = [];
  var page = document.getElementById('paranoidguy_settings_items');
  var inputs = page.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {  
    if (inputs[i].type == "checkbox" && inputs[i].checked) {  
      selected.push(inputs[i].name);  
    }
  }
  paranoidguy_set_cookie('paranoidguy', selected);
  paranoidguy_close_cookie_settings_popup();
  paranoidguy_close_cookie_banner();
}
function  paranoidguy_allow_required_cookies() {
  var briefs = [];
  const rows = paranoidguy_settings_data["rows"];
  for (var index = 0; index < rows.length; index++) {
    const r = rows[index];
    if (r['requiredflag']) {
      briefs.push(r['brief']);
    }
  }
  paranoidguy_set_cookie('paranoidguy', briefs);
  paranoidguy_close_cookie_settings_popup();
  paranoidguy_close_cookie_banner();
}
paranoidguy_load_settings();
</script>
<div id="paranoidguy_settings_popup" style="background: rgba(0, 0, 0, 0.7);position: fixed;top: 0;right: 0;bottom: 0;left: 0;z-index:999999999; display:none;">
  <div style="position:absolute; top:20px; right:20px; background: transparent;cursor: pointer;color:#fff;font-family: 'Helvetica', 'Arial', sans-serif;font-size: 2em;font-weight: 400;text-align: center;width: 40px;height: 40px;border-radius: 5px;margin: 0 auto;" onclick="paranoidguy_close_cookie_settings_popup()">X</div>
  <div style="display:block;height:10%;">&nbsp;</div>
  <div id="paranoidguy_settings_page">
  <h3>Privacy settings</h3>
  <p>When you visit any website, it may store or retrieve information on your browser, mostly in the form of cookies. This information might be about you, your preferences or your device and is mostly used to make the site work as you expect it to. The information does not usually directly identify you, but it can give you a more personalized web experience. Because we respect your right to privacy, you can choose not to allow some types of cookies. Click on the different category headings to find out more and change our default settings. However, blocking some types of cookies may impact your experience of the site and the services we are able to offer.</p>
  <center><button onclick='paranoidguy_allow_all_cookies();'>Allow All</button></center>
  <h4>Manage individual settings</h4>
  <div id="paranoidguy_settings_items"></div>
  <center><button onclick='paranoidguy_allow_custom_cookies();'>Save settings</button></center>
  </div>
</div>
<div id="paranoidguy_cookie_banner" style="visibility:hidden;background-color:rgba(71,81,84,.95);box-shadow: 0 -8px 20px 0 rgba(0,0,0,.2);width:100%;margin:0 auto;padding:5px;font-size: 1em;color: #6d6d6d;bottom:0px;position:fixed;left: 0px;opacity:0.9;filter:alpha(opacity=80);height:auto;max-height:500px;z-index:9999999999;overflow:hidden;">
<div style="float:left;color:#fff;width: calc(100% - 200px);padding:10px;">
This site uses cookies and related technologies for site operation, analytics, and third party
advertising purposes as described in our Privacy and Data Processing Policy. You may choose to consent
 to our use of these technologies, reject non-essential technologies, or further manage your preferences.
</div>
<div style="float:left;width:200px;margin:0 auto;text-align:center;vertical-align: middle;padding-top:15px;">
<button style="text-decoration:none;font-weight: 400;text-transform: uppercase;cursor: pointer;background-color: #2eb8ff;min-width: 160px;min-height: 33px;margin: 0;padding: .5rem 1rem;font-size: 1.3rem;color: #fff;border: none;border-radius: 3px;outline: none;" onclick='paranoidguy_allow_all_cookies();'>I agree&nbsp;<span style="font-weight: 700;style:inline-block;height:25px;">✓</span></button>
<button style="text-decoration:none;font-weight: 400;text-transform: uppercase;background-color:transparent;cursor: pointer;min-width: 160px;min-height: 30px;margin: 5px 0 0 0;padding: .5rem 1rem;font-size: 1.1rem;color: #fff;border: 1px solid #fff;border-radius: 3px;outline: none;" onclick='paranoidguy_allow_required_cookies();'>Required only</button>
<div style="display:block;padding:0;margin:0;"><a style="color:#fff;font-weight: 400;background-color:transparent;cursor: pointer;font-size:1rem;" href="#" onclick="paranoidguy_show_cookie_settings_popup();">Customize settings</a></div>
</div>
</div>
<script>
const oldCookie = paranoidguy_get_cookie('paranoidguy');
if (!oldCookie) {
  var banner = document.getElementById('paranoidguy_cookie_banner');
  banner.style.visibility = "visible";
}
</script>
<?php
}

add_action( 'delete_user', 'paranoidguy_delete_user');
add_action( 'profile_update', 'paranoidguy_profile_update', 10, 2);
add_action( 'register_form', 'paranoidguy_custom_registration');
add_action( 'registration_errors', 'paranoidguy_registration_check', 10, 3);
add_action( 'user_register', 'paranoidguy_registration_save');
add_action( 'wp_footer', 'paranoidguy_cookie_consent');

function paranoidguy_profile_edit_user( $user ) {
$subdomain = get_option( 'paranoidguy_subdomain', '' );
$srv= "https://".$subdomain.".databunker.cloud/";

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
add_action( 'show_user_profile', 'paranoidguy_profile_edit_user' );

paranoidguy_init_admin();

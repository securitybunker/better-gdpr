<?php

function bettergdpr_admin_page() {
  $xtoken = get_option( 'bettergdpr_xtoken', '' );
  $subdomain = get_option( 'bettergdpr_subdomain', '' );
  if ($xtoken == '' && $subdomain == '') {
    return bettergdpr_setup_page();
  } else {
    return bettergdpr_show_admin_ui();
  }
}

function bettergdpr_register_tenant($code, $site, $email, $subdomain) {
  $result = bettergdpr_api_register($code, $site, $email, $subdomain);
  if ($result->status != "ok") {
    return $result;
  }
  update_option('bettergdpr_subdomain', $subdomain);
  update_option('bettergdpr_sitekey', $result->sitekey);
  update_option('bettergdpr_xtoken', $result->xtoken);
  # configure wp plugin configuration
  bettergdpr_api_wpsetup();
  return $result;
}

function bettergdpr_show_admin_ui() {
$xtoken = get_option( 'bettergdpr_xtoken', '' );
$xtoken_end = substr($xtoken, -6);
$subdomain = get_option( 'bettergdpr_subdomain', '' );
$service = "https://".$subdomain.".privacybunker.cloud/";
$url = "https://".$subdomain.".privacybunker.cloud/site/admin-redirect.html#".$xtoken;
$info = bettergdpr_api_get_account_standing();
$standing = "";
if ($info && $info->status == "ok") {
  if ($info->standing && $info->standing != "") {
    $standing = $info->standing;
  }
}
if ($standing == "" or $standing == "deleted") {
?>
<script type='text/javascript' src='<?php echo($service); ?>/site/wizard.js' ></script>
<link rel='stylesheet' type='text/css' media='all' href='<?php echo($service); ?>/site/wizard.css' />
<div class='better-gdpr-admin'>
<div id='bettergdpr-wizard'>&nbsp;</div>
<script type="text/javascript">
jQuery( document ).ready(function() {
  bettergdprShowWizardPage('end');
});
</script>
</div>
<?php
  return;
}
?>
<script>
function bettergdpr_copy_token() {
  const el = document.createElement('textarea');
  el.value = "<?php echo($xtoken); ?>";
  document.body.appendChild(el);
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);
}
</script>
<h3>Privacy Bunker Access</h3>
<p style="font-size:150%;">One-click access: <a target="_blank" href="<?php echo($url); ?>">click here</a></p>
<p>&nbsp;<p>
<p>Admin access token for your website: <a href='#' onclick="bettergdpr_copy_token();">(copy)</a></p>
<p>Privacy Bunker Service direct URL: <a target="_blank" href="<?php echo($service); ?>"><?php echo($service); ?></a></p>
<p>If you have any questions you can contact our support at <u>onboarding@privacybunker.io</u></p>
<?php
}

/*
function my_load_scripts($hook) {
  $subdomain = get_option( 'bettergdpr_subdomain', '' );
  if ($subdomain) {
    $service = "https://".$subdomain.".privacybunker.cloud/";
    //$my_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'wizard.js' ));
    //$my_css_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'wizard.css' ));
    //wp_enqueue_script( 'wizard_js', plugins_url( 'wizard.js', __FILE__ ), array(), $my_js_ver );
    //wp_enqueue_style( 'wizard_css', plugins_url( 'wizard.css', __FILE__ ), array(), $my_css_ver );
    wp_enqueue_script( 'wizard_js', $service.'site/wizard.js',  array(), '3', true);
    wp_enqueue_style( 'wizard_css', $service.'site/wizard.css', 'all');
  }
}
add_action('admin_enqueue_scripts', 'my_load_scripts');
*/

function bettergdpr_wizard_page() {
$xtoken = get_option( 'bettergdpr_xtoken', '' );
$subdomain = get_option( 'bettergdpr_subdomain', '' );
$service = "https://".$subdomain.".privacybunker.cloud/";
?>
<script type='text/javascript' src='<?php echo($service); ?>/site/wizard.js' ></script>
<link rel='stylesheet' type='text/css' media='all' href='<?php echo($service); ?>/site/wizard.css' />
<div class='better-gdpr-admin'>
<div id='bettergdpr-wizard'>&nbsp;</div>
<script type="text/javascript">
jQuery( document ).ready(function() {
  bettergdprLoadSettings('<?php echo($xtoken); ?>', '<?php echo($service); ?>', 'v1/account/technologies');
  bettergdprLoadSettings('<?php echo($xtoken); ?>', '<?php echo($service); ?>', 'v1/account/objectives');
  bettergdprShowWizardPage('objectives');
});
</script>
<center><p>If you have any questions you can contact us at <u>onboarding@privacybunker.io</u></p></center>
</div>
<?php
}

function bettergdpr_setup_page() {
  $account_email = get_settings('admin_email');
  $site = get_settings('siteurl');
  $subdomain = bettergdpr_generate_subdomain($site);
  $srv = "https://privacybunker.cloud";
  if (isset($_POST["email"])) {
    $account_email = sanitize_email($_POST["email"]);
  }
  if (isset($_POST["subdomain"])) {
    $subdomain = sanitize_text_field($_POST["subdomain"]);
  }
  $errmsg = "";
  $step = 0;
  $errstyle = "display:none;";
  if (isset($_POST["email"]) && isset($_POST["code"]) && isset($_POST["subdomain"])) {
    $code = sanitize_text_field($_POST["code"]);
    $result = bettergdpr_register_tenant($code, $site, $account_email, $subdomain);
    if ($result->status == "ok") {
      //bettergdpr_show_admin_ui();
      bettergdpr_wizard_page();
      return;
    } else {
      $errmsg = $result->message;
      $errstyle = "display:block;";
      $step = 1;
    }
  }
  $logo_file = plugin_dir_url( dirname( __FILE__ ) ) . 'better-gdpr/images/logo.png';
?>
<script>
function bettergdpr_validate_subdomain(obj) {
  const msg = {};
  msg["site"] = "<?php echo($site); ?>";
  msg["subdomain"] = obj.value;
  var xhr0 = new XMLHttpRequest();
  xhr0.open('POST', "<?php echo($srv); ?>/v1/account/validate");
  xhr0.setRequestHeader('Content-Type', 'application/xml');
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      var data = JSON.parse(xhr0.responseText);
      var err = document.getElementById('bettergdpr_error');
      if (data && data.status && data.status === "error") {
	err.innerHTML = data.message;
	err.style.display = "block";
      } else {
        err.style.display = "none";
      }
    }
  };
  xhr0.send(JSON.stringify(msg));
}
function bettergdpr_start() {
  var form0 = document.getElementById('bettergdpr_step0');
  var form1 = document.getElementById('bettergdpr_step1');
  var err = document.getElementById('bettergdpr_error');
  err.style.display = "none";
  form1.style.display = "none";
  form0.style.display = "block";
}
function bettergdpr_register1() {
  var email = document.getElementById('edit-mail').value;
  var subdomain = document.getElementById('edit-subdomain').value;
  const msg = {};
  msg["email"] = email;
  msg["site"] = "<?php echo($site); ?>";
  msg["subdomain"] = subdomain;
  var xhr0 = new XMLHttpRequest();
  xhr0.open('POST', "<?php echo($srv); ?>/v1/account/step1");
  xhr0.setRequestHeader('Content-Type', 'application/xml');
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      var data = JSON.parse(xhr0.responseText);
      var err = document.getElementById('bettergdpr_error');
      var form0 = document.getElementById('bettergdpr_step0');
      var form1 = document.getElementById('bettergdpr_step1');
      if (data && data.status && data.status === "error") {
        err.innerHTML = data.message;
        err.style.display = "block";
      } else {
        err.style.display = "none";
	form0.style.display = "none";
	form1.style.display = "block";
      }
    }
  };
  xhr0.send(JSON.stringify(msg));
}
function submit_step2(form) {
  var code = document.getElementById('edit-code').value;
  var email = document.getElementById('edit-mail').value;
  var subdomain = document.getElementById('edit-subdomain').value;
  form.code.value = code;
  form.email.value = email;
  form.subdomain.value = subdomain;
  console.log(form);
  return true;
}
</script>
<div style="width:100%;display:block;height:100px;">&nbsp;</div>
<div style="clear:both;"></div>
<center>
<div style="margin:0 auto;width:500px; border:5px solid #51859B;border-radius: 7px;background:#EEE;">
<div class="header" style="">
<img alt="Logo" src="<?php echo($logo_file); ?>" width=200 style="padding:20px;"/>
</div>
<div style="padding:10px;text-align:left;">
 <h2 style="padding:0 0 5px 0;margin:0;">Start with plugin activation</h2>
 <div id="bettergdpr_error" class="error" style="<?php echo($errstyle); ?>"><?php echo($errmsg); ?></div>
 <div style="display:block;height:20px;"></div>
 <form id="bettergdpr_step0" accept-charset="UTF-8" method="post" action="#" style="display:<?php echo(($step==0)?"block":"none")?>;">
   <i>Type your email address bellow to activate your account.</i>
   <div class="form-item" id="edit-mail-wrapper" style="padding-top:10px;">
     <label for="edit-mail" style="float:left;padding-top:6px;">E-mail address:</label>
     <input type="text" maxlength="54" name="account_email" id="edit-mail" size="50" value="<?php echo $account_email; ?>" style="float:right;width:315px;" />
     <div style="clear:both;"></div>
   </div>
   <div class="form-item" id="edit-subdomain-wrapper" style="padding-top:10px;">
     <label for="edit-subdomain0" style="float:left;padding-top:6px;">Privacy portal address:</label>
     <div id="edit-subdomain0" style="float:right;width:315px;"><span>https://&nbsp;</span><span><input type="text" maxlength="54" name="subdomain" id="edit-subdomain" size="50" value="<?php echo $subdomain; ?>" style="width:125px;" onchange="bettergdpr_validate_subdomain(this)" /></span><span>&nbsp;.privacybunker.cloud/</span></div>
     <div style="clear:both;"></div>
   </div>
   <div class="form-item" id="submit-wrapper" style="clear:left;padding-top:10px;">
     <button type="button" name="register" id="edit-submit" value="Activate" class="form-submit btn btn-primary button button-primary" style="margin-left:165px;" onclick="bettergdpr_register1();">Register Me</button>
   </div>
 </form>
 <form id="bettergdpr_step1" accept-charset="UTF-8" method="post" style="display:<?php echo(($step==1)?"block":"none")?>;" onsubmit="submit_step2(this)">
   <i>Enter code you received by email to finish registration.</i>
   <input type="hidden" name="email" value="register" />
   <input type="hidden" name="subdomain" value="register" />
   <div class="form-item" id="edit-code-wrapper" style="padding-top:10px;">
     <label for="edit-code" style="float:left;padding-top:6px;">Enter code:</label>
     <input type="text" maxlength="54" name="code" id="edit-code" size="50" value="" style="float:right;width:315px;" />
     <div style="clear:both;"></div>
   </div>
   <div class="form-item" id="submit-wrapper" style="clear:left;padding-top:10px;">
     <input type="submit" name="register" id="edit-submit" value="Validate Code" class="form-submit button button-primary" style="margin-left:165px;" />
     <button type="button" name="cancel" id="edit-cancel" class="form-submit button button-secondary" style="margin-left:10px;" onclick="bettergdpr_start();">Cancel</button>
   </div>
 </form> 
</div>
<hr>
<center>
<a href="https://privacybunker.cloud/" target="_blank">Privacybunker.Cloud Term Of Service</a>
</center>
</div>
</center>
<?php

}

function bettergdpr_admin_menu() {
  add_menu_page(
    'Better GDPR',// page title
    'Better GDPR',// menu title
    'manage_options',// capability
    'bettergdpr',// menu slug
    'bettergdpr_admin_page' // callback function
  );
  add_submenu_page(
    'bettergdpr',// menu slug
    'Setup',// menu title
    'Setup',
    'manage_options',// capability
    'bettergdpr_setup',// menu slug
    'bettergdpr_setup_page' // callback function
  );
}

function bettergdpr_get_user_consents( $val, $column_name, $user_id ) {
  if ( $column_name === 'bettergdpr') {
    $user = get_user_by("id", $user_id);
    if ($user && $user->user_email) {
      $records = bettergdpr_api_get_user_agreements('email', $user->user_email);
      $consents = array();
      if ($records && $records->rows) {
        foreach ($records->rows as $row) {
          if ($row->status === 'yes') {
            $consents[] = $row->brief;
          }
        }
        if (count($consents) > 0) {
          return implode( ', ', $consents );
        }
      }
    }
  }
  return 'N/A';
}

function bettergdpr_add_consents_column( $column_headers ) {
  $column_headers['bettergdpr'] = 'Privacy Agreements';
  return $column_headers;
}

function bettergdpr_plugin_action_links_callback( $actions, $plugin_file, $plugin_data, $context ) {
  if (strpos($plugin_file, 'better-gdpr') !== false && array_key_exists( 'deactivate', $actions )) {
    array_unshift( $actions, '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=bettergdpr') ) .'">Settings</a>');
  }
  return $actions;
}

function bettergdpr_uninstall() {
  delete_option('bettergdpr_subdomain');
  delete_option('bettergdpr_xtoken');
  delete_option('bettergdpr_sitekey');
}

function bettergdpr_init_admin() {
  add_action('admin_menu', 'bettergdpr_admin_menu');
  add_filter( 'plugin_action_links', 'bettergdpr_plugin_action_links_callback', 10, 4 );
  // add column to the list of users to display list of given consents
  // it is available at /wp-admin/users.php
  add_filter( 'manage_users_custom_column', 'bettergdpr_get_user_consents', 10, 6 );
  add_filter( 'manage_users_columns', 'bettergdpr_add_consents_column' );
  // register_uninstall_hook( __FILE__, 'bettergdpr_uninstall' );
  // register_deactivation_hook(  __FILE__, 'bettergdpr_uninstall' );
}


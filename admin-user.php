<?php

function paranoidguy_admin_page() {
  $xtoken = get_option( 'paranoidguy_xtoken', '' );
  $subdomain = get_option( 'paranoidguy_subdomain', '' );
  if ($xtoken == '' && $subdomain == '') {
    return paranoidguy_setup_page();
  } else {
    return paranoidguy_show_admin_ui();
  }
}

function paranoidguy_generate_subdomain($site) {
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

function paranoidguy_register_tenant($code, $site, $email, $subdomain) {
  $result = paranoidguy_api_register($code, $site, $email, $subdomain);
  if ($result->status != "ok") {
    return $result;
  }
  update_option('paranoidguy_subdomain', $subdomain);
  update_option('paranoidguy_xtoken', $result->xtoken);
  # create processing operations
  # "integrate-advertising-networks"
  paranoidguy_api_create_pactivity("advertising-networks-user-tracking", "Ad Network User tracing", "Enable internal or 3rd party advertising / affiliate network scripts to use cookies to track users (tracking pixel) for remarketting; display targeted ads; etc...");
  paranoidguy_api_create_lbasis("advertising-cookies-consent", "cookie-popup", False, 'Advertising cookies', "These cookies are used to help better tailor advertising to your interests, both within and beyond this website.", "");
  paranoidguy_api_link_pactivity("advertising-networks-user-tracking", "advertising-cookies-consent");

  paranoidguy_api_create_pactivity("analytic-tools-user-tracking", "Analytics tools user tracking", "Enable internal or 3rd party analytics and logging tools on our website to use cookies to track users");
  paranoidguy_api_create_lbasis("analytics-tools-consent", "cookie-popup", False, 'Analytics cookies', "These cookies allow us to analyze site usage so we can measure and improve performance of our site.", "");
  paranoidguy_api_link_pactivity("analytic-tools-user-tracking", "analytics-tools-consent");

  paranoidguy_api_create_pactivity("required-components-user-tracking", "Website required components user tracking", "Enable internal or 3rd party components required for website functionality to use cookie. For example: internal user login & logout, Youtube videos, usage of CDN; chat; captcha; online maps, social sharing; social login, etc...");
  paranoidguy_api_create_lbasis("required-cookies-consent", "cookie-popup", True, 'Required cookies', "These cookies ensure that the website functions properly.", "Our website will not function properly.");
  paranoidguy_api_link_pactivity("required-components-user-tracking", "required-cookies-consent");

  paranoidguy_api_create_lbasis("send-email-on-login", "login", False, 'Send email on login', 'Confirm to allow sending access code using 3rd party email gateway', 'You will not be able to login');
  paranoidguy_api_create_lbasis("send-sms-on-login", "login", False, 'Send SMS on login', 'Confirm to allow sending access code using 3rd party SMS gateway', 'You will not be able to login');

/*
  paranoidguy_api_create_pactivity("online-analytics-tracking", "Online user tracking by web analytics companies");
  paranoidguy_api_create_lbasis("analytics-tracking-consent", "cookie-popup", False, "Accept web analytics tracking", "");
  paranoidguy_api_link_pactivity("online-analytics-tracking", "analytics-tracking-consent");

  paranoidguy_api_create_pactivity("online-advertising-tracking", "Online user tracking by display ads companies");
  paranoidguy_api_create_lbasis("advertising-tracking-consent", "cookie-popup", False, "Accept advertising pixel tracking", "");
  paranoidguy_api_link_pactivity("online-advertising-tracking", "advertising-tracking-consent");

  paranoidguy_api_create_pactivity("online-affiliate-tracking", "Online user tracking by affiliate network companies");

  paranoidguy_api_create_pactivity("online-remarketting-pixel-tracking", "Online user remarketting pixel tracking by advertising, social, affiliate companies");

  paranoidguy_api_create_pactivity("online-chat-tracking", "Online user tracking by web chat companies");
  paranoidguy_api_create_lbasis("chat-tracking-consent", "cookie-popup", False, "Accept tracking by online chat companies.", "");
  paranoidguy_api_link_pactivity("online-chat-tracking", "chat-tracking-consent");

  paranoidguy_api_create_pactivity("internal-website-tracking", "Internal online website tracking of logged in users");

  paranoidguy_api_create_pactivity("video-companies-tracking", "Online user tracking by video broadcasting companies");
  paranoidguy_api_create_lbasis("video-tracking-consent", "cookie-popup", False, "Accept tracking by video broadcasting companies, i.e. Youtube", "");

  paranoidguy_api_create_pactivity("podcast-companies-tracking", "Online user tracking by podcast broadcasting companies");
  paranoidguy_api_create_pactivity("radio-companies-tracking", "Online user tracking by radio broadcasting companies");
  paranoidguy_api_create_pactivity("online-captcha-tracking", "Online user tracking by captcha companies");
  paranoidguy_api_create_pactivity("online-cdn-tracking", "Online user tracking by CDN companies");
  paranoidguy_api_create_pactivity("online-fonts-tracking", "Online user tracking by companies offering online fonts");
  paranoidguy_api_create_pactivity("online-map-tracking", "Online user tracking by online map companies");
  paranoidguy_api_create_pactivity("social-network-tracking", "Online user tracking by social networks services");
  paranoidguy_api_create_pactivity("page-share-tracking", "Online user tracking by site & page sharing companies");
  paranoidguy_api_create_pactivity("comment-hosting-tracking", "Online tracking by website comment hosting companies");
  paranoidguy_api_create_pactivity("share-with-crm-company", "Save user details in CRM company");
  paranoidguy_api_create_pactivity("share-with-email-marketing-comp", "Save user details with email marketing company");
  paranoidguy_api_create_pactivity("share-with-sms-company", "Save user details with SMS sending company");
  paranoidguy_api_create_lbasis("accept-terms-of-service", "signup-page", True, "Accept terms of service.", "We will not be able to provide you with the service.");
  paranoidguy_api_link_pactivity("internal-website-tracking", "accept-terms-of-service");
*/
  return $result;
}

function paranoidguy_show_admin_ui() {
$xtoken = get_option( 'paranoidguy_xtoken', '' );
$subdomain = get_option( 'paranoidguy_subdomain', '' );
$url = "https://".$subdomain.".databunker.cloud/site/admin-redirect.html?token=".$xtoken;

# onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+'px';"
?>
<div style="width:100%;min-height: 1000px;position:relative;">
<div style="position: absolute; top: 0px; left: 0px; right: 0px;bottom: 0px;">
<iframe id="paranoidguy_iframe" src="<?php echo($url); ?>" style="height: 100%;width: 100%;"></iframe>
</div>
</div>
<?php
}

function paranoidguy_setup_page() {
  $account_email = get_settings('admin_email');
  $site = get_settings('siteurl');
  $subdomain = paranoidguy_generate_subdomain($site);
  $srv = "https://databunker.cloud";
  if (isset($_POST["email"])) {
    $account_email = sanitize_email($_POST["email"]);
  }
  if (isset($_POST["subdomain"])) {
    $subdomain = $_POST["subdomain"];
  }
  $errmsg = "";
  $step = 0;
  $errstyle = "display:none;";
  if (isset($_POST["email"]) && isset($_POST["code"]) && isset($_POST["subdomain"])) {
    $code = $_POST["code"];
    $result = paranoidguy_register_tenant($code, $site, $account_email, $subdomain);
    if ($result->status == "ok") {
      paranoidguy_show_admin_ui();
      return;
    } else {
      $errmsg = $result->error;
      $errstyle = "display:block;";
      $step = 1;
    }
  }
?>
<script>
function paranoidguy_validate_subdomain(obj) {
  const msg = {};
  msg["site"] = "<?php echo($site); ?>";
  msg["subdomain"] = obj.value;
  var xhr0 = new XMLHttpRequest();
  xhr0.open('POST', "<?php echo($srv); ?>/v1/account/validate");
  xhr0.setRequestHeader('Content-Type', 'application/xml');
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      var data = JSON.parse(xhr0.responseText);
      var err = document.getElementById('paranoidguy_error');
      if (data && data.status && data.status === "error") {
	err.innerHTML = data.error;
	err.style.display = "block";
      } else {
        err.style.display = "none";
      }
    }
  };
  xhr0.send(JSON.stringify(msg));
}
function paranoidguy_start() {
  var form0 = document.getElementById('paranoidguy_step0');
  var form1 = document.getElementById('paranoidguy_step1');
  var err = document.getElementById('paranoidguy_error');
  err.style.display = "none";
  form1.style.display = "none";
  form0.style.display = "block";
}
function paranoidguy_register1() {
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
      var err = document.getElementById('paranoidguy_error');
      var form0 = document.getElementById('paranoidguy_step0');
      var form1 = document.getElementById('paranoidguy_step1');
      if (data && data.status && data.status === "error") {
        err.innerHTML = data.error;
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
<div class="header" style="background-color: #0179AB;">
<img alt="Logo" src="https://paranoidguy.com/logo.png" style="padding:20px;"/>
</div>
<div style="padding:10px;text-align:left;">
 <h2 style="padding:0 0 5px 0;margin:0;">ParanoidGuy Databunker.Cloud Plugin Activation</h2>
 <div id="paranoidguy_error" class="error" style="<?php echo($errstyle); ?>"><?php echo($errmsg); ?></div>
 <div style="display:block;height:20px;"></div>
 <form id="paranoidguy_step0" accept-charset="UTF-8" method="post" action="#" style="display:<?php echo(($step==0)?"block":"none")?>;">
   <i>Type your email address bellow to activate your Databunker.cloud account.</i>
   <div class="form-item" id="edit-mail-wrapper" style="padding-top:10px;">
     <label for="edit-mail" style="float:left;padding-top:6px;">E-mail address:</label>
     <input type="text" maxlength="54" name="account_email" id="edit-mail" size="50" value="<?php echo $account_email; ?>" style="float:right;width:315px;" />
     <div style="clear:both;"></div>
   </div>
   <div class="form-item" id="edit-subdomain-wrapper" style="padding-top:10px;">
     <label for="edit-subdomain0" style="float:left;padding-top:6px;">Privacy portal address:</label>
     <div id="edit-subdomain0" style="float:right;width:315px;"><span>https://&nbsp;</span><span><input type="text" maxlength="54" name="subdomain" id="edit-subdomain" size="50" value="<?php echo $subdomain; ?>" style="width:145px;" onchange="paranoidguy_validate_subdomain(this)" /></span><span>&nbsp;.databunker.cloud/</span></div>
     <div style="clear:both;"></div>
   </div>
   <div class="form-item" id="submit-wrapper" style="clear:left;padding-top:10px;">
     <button type="button" name="register" id="edit-submit" value="Activate" class="form-submit btn btn-primary button button-primary" style="margin-left:165px;" onclick="paranoidguy_register1();">Register Me</button>
   </div>
 </form>
 <form id="paranoidguy_step1" accept-charset="UTF-8" method="post" style="display:<?php echo(($step==1)?"block":"none")?>;" onsubmit="submit_step2(this)">
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
     <button type="button" name="cancel" id="edit-cancel" class="form-submit button button-secondary" style="margin-left:10px;" onclick="paranoidguy_start();">Cancel</button>
   </div>
 </form> 
</div>
<hr>
<center>
<a href="https://databunker.cloud/" target="_blank">Databunker.Cloud Term Of Service</a>
</center>
</div>
</center>
<?php

}

function paranoidguy_admin_menu() {
  add_menu_page(
    'Better GDPR',// page title
    'Better GDPR',// menu title
    'manage_options',// capability
    'paranoidguy',// menu slug
    'paranoidguy_admin_page' // callback function
  );
  add_submenu_page(
    'paranoidguy',// menu slug
    'Setup',// menu title
    'Setup',
    'manage_options',// capability
    'paranoidguy_setup',// menu slug
    'paranoidguy_setup_page' // callback function
  );
  add_submenu_page(
    'paranoidguy',// menu slug
    'Admin',// menu title
    'Admin',
    'manage_options',// capability
    'paranoidguy_admin',// menu slug
    'paranoidguy_show_admin_ui' // callback function
  );
}

function paranoidguy_get_user_consents( $val, $column_name, $user_id ) {
  if ( $column_name === 'paranoidguy') {
    $user = get_user_by("id", $user_id);
    if ($user && $user->user_email) {
      $records = paranoidguy_api_get_user_agreements('email', $user->user_email);
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

function paranoidguy_add_consents_column( $column_headers ) {
  $column_headers['paranoidguy'] = 'Privacy Agreements';
  return $column_headers;
}

function paranoidguy_init_admin() {
  add_action('admin_menu', 'paranoidguy_admin_menu');
  // add column to the list of users to display list of given consents
  // it is available at /wp-admin/users.php
  add_filter( 'manage_users_custom_column', 'paranoidguy_get_user_consents', 10, 6 );
  add_filter( 'manage_users_columns', 'paranoidguy_add_consents_column' );
}

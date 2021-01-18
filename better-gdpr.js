/*! Better GDPR js code */
var bettergdpr_tenant;
var bettergdpr_full_domain;
var bettergdpr_full_url_dir;

const bettergdpr_temp_scripts = document.getElementsByTagName('script');
for (var index = 0; index < bettergdpr_temp_scripts.length; index++) {
  if (bettergdpr_temp_scripts[index].src &&
      bettergdpr_temp_scripts[index].src.includes('bettergdprtenant=')) {
    var bettergdpr_url = new URL(bettergdpr_temp_scripts[index].src);
    if (bettergdpr_url) {
      bettergdpr_tenant = bettergdpr_url.searchParams.get('bettergdprtenant');
      if (bettergdpr_tenant) {
        bettergdpr_full_domain = "https://"+bettergdpr_tenant+".privacybunker.cloud/";
        bettergdpr_full_url_dir = bettergdpr_url.protocol + '//' + bettergdpr_url.host;
        if ( bettergdpr_url.port ) {
          bettergdpr_full_url_dir = bettergdpr_full_url_dir + ':' + bettergdpr_url.port;
        }
        bettergdpr_full_url_dir = bettergdpr_full_url_dir + bettergdpr_url.pathname.replace('/better-gdpr.js', '/');
      }
    }
  }
}

var bettergdpr_html_code = `<div id="bettergdpr_settings_popup" style="background: rgba(0, 0, 0, 0.7);position: fixed;top: 0;right: 0;bottom: 0;left: 0;z-index:999999999; display:none;">
  <div style="position:absolute; top:20px; right:20px; background: transparent;cursor: pointer;color:#fff;font-family: 'Helvetica', 'Arial', sans-serif;font-size: 2em;font-weight: 400;text-align: center;width: 40px;height: 40px;border-radius: 5px;margin: 0 auto;" onclick="bettergdpr_close_cookie_settings_modal()">X</div>
  <div style="display:block;height:10%;">&nbsp;</div>
  <div id="bettergdpr_settings_page">
  <div style="display:block;">
  <h3 id="CustomPopupTitle" style="float:left;"></h3>
  <div style="float:right;"><a target="_blank" href="https://privacybunker.io/"><img width=200 src="`+bettergdpr_full_url_dir+`images/logo.png" /></a></div>
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
  <div style="display:inline;padding:7px 0px 0px 0px;margin:0;"><u style="color:#fff;font-weight: 400;background-color:transparent;cursor: pointer;font-size:12px;" onclick="bettergdpr_show_cookie_settings_modal();">Customize settings</u></div>
 </div>
 <div id='bettergdpr_branding'>
  <div id='bettergdpr_powered_by'>Powered by&nbsp;&nbsp;<a target='_blank' href='https://privacybunker.io/'><img style="display:inline;margin-top:-5px;" width=140 src="`+ bettergdpr_full_url_dir +`images/powered-by.png" /></a></div>
  <div style="float:right;font-size:12px;">Privacy portal <a style='color:#fff;font-weight: 300;background-color:transparent;cursor: pointer;font-size:12px;text-decoration:underline;' href='`+bettergdpr_full_domain+`' target='_blank'>`+bettergdpr_full_domain+`</a></div>
 </div>
</div>`;

function bettergdpr_close_cookie_settings_modal() {
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
  xhr0.open('GET', bettergdpr_full_domain+"v1/sys/cookiesettings");
  xhr0.onload = function () {
    if (xhr0.status === 200) {
      bettergdpr_settings_data = JSON.parse(xhr0.responseText);
      const scripts = bettergdpr_settings_data["scripts"];
      const oldCookie = bettergdpr_get_cookie('BETTERGDPR');
      if (oldCookie && scripts) {
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
            if (scriptObj.script.startsWith("http")) {
              var script = document.createElement( "script" );
              script.setAttribute('type', 'text/javascript');
              script.setAttribute('src', scriptObj.script);
              //script.text = scriptObj.script;
              document.head.appendChild( script );
            } else { 
              var range = document.createRange();
              if (document.head) {
                range.selectNode(document.head);
              } else if (document.body) {
                range.selectNode(document.body);
              }
              var docFrg = range.createContextualFragment(scriptObj.script);
              document.body.appendChild(docFrg);
            }
          }
        }
      }
      const popupConf = bettergdpr_settings_data.ui;
      if (popupConf.EnablePopup) {
	bettergdpr_init_body();
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
function bettergdpr_init_body() {
  var style = document.getElementById('bettergdpr_style_body');
  if (!style) {
    style = document.createElement('link');
    style.rel = 'stylesheet';
    style.type = 'text/css';
    style.rel = "stylesheet";
    style.id = 'bettergdpr_style_body';
    //style.innerHTML = '.bettergdpr_faded_body { overflow:hidden}';
    style.href = bettergdpr_full_url_dir + 'better-gdpr.css';
    document.getElementsByTagName('head')[0].appendChild(style);
  }

  var body = document.getElementsByTagName('body');
  if (body && body[0]) {
    body[0].innerHTML += bettergdpr_html_code;
  }
}
function bettergdpr_show_cookie_settings_modal() {
  bettergdpr_close_cookie_banner();
  var body = document.getElementsByTagName('body');
  if (body && body[0]) {
    //alert(body[0].className);
    body[0].className = body[0].className + ' bettergdpr_faded_body';
  }
  var settings = document.getElementById("bettergdpr_settings_popup");
  if (settings) {
    settings.style.display = "block";
  }
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
  bettergdpr_close_cookie_settings_modal();
  bettergdpr_close_cookie_banner();
}
function bettergdpr_allow_custom_cookies() {
  var selected = [];
  var page = document.getElementById('bettergdpr_settings_items');
  var inputs = page.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {  
    if (inputs[i].type == "checkbox" && inputs[i].checked) {  
      selected.push(inputs[i].name);  
    }
  }
  bettergdpr_set_cookie('BETTERGDPR', selected);
  bettergdpr_close_cookie_settings_modal();
  bettergdpr_close_cookie_banner();
}
function bettergdpr_allow_required_cookies() {
  var briefs = [];
  const rows = bettergdpr_settings_data["rows"];
  for (var index = 0; index < rows.length; index++) {
    const r = rows[index];
    if (r['requiredflag']) {
      briefs.push(r['brief']);
    }
  }
  bettergdpr_set_cookie('BETTERGDPR', briefs);
  bettergdpr_close_cookie_settings_modal();
  bettergdpr_close_cookie_banner();
}
if (bettergdpr_full_domain) {
  // load all settings
  bettergdpr_load_settings();
}


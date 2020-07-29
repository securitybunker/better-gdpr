=== Plugin Name ===
Contributors: stremovsky
Donate link: https://paranoidguy.com/
Tags: gdpr, privacy
Requires at least: 4.7
Tested up to: 5.4
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

GDPR & Cookie Consent plugin built by ParanoidGuy.com team.

== Description ==

This is an official version of the Better-GDPR plugin built by PranoidGuy.com team.

= Plugin main features: =

1. Cookie consent popup
2. GDPR Privacy portal
3. Support for user registration form

= 1. Cookie consent plugin =

It is very similar to other cookie consent tools. The main difference between most of the
similar tools if that we inject not-required components (javascript) only after we got actual
user consent. For example, Advertising related javascript code will be injected after the user
gave us Advertising consent. You will need to configure this optional javascript code 
(i.e. facebook pixel) using our management tool.

Under the hood, user consent categories are saved in a special cookie called: "BETTERGDPR".
So, when the page loads for the user for the first time in a session, we check the value of the
"BETTERGDPR" cookie value and inject approved javascript code. If it is a new user, we will show
the user our cookie consent popup.

= 2. Privacy portal =


== Frequently Asked Questions ==

= Do you plan supporting other languages? =

Yes. We are planing to add multiple languages in the nearest future.

== Screenshots ==

1. Advanced Cookie Consent

== Changelog ==

= 0.2.0 =
* Initial product release.

== Upgrade Notice ==


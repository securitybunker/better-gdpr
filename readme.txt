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
Let's keep in touch and share your experience. Mail us at yuli @ paranoidguy.com

= Plugin main features: =

1. Cookie consent popup
2. GDPR Privacy portal
3. User profile change propagation (BETA)

= 1. Cookie consent plugin =

It is very similar to other cookie consent tools. The main difference between most of the
similar tools is that we inject not-required components (javascript) only after we got actual
user consent. For example, Advertising related javascript code will be injected after the user
gave us Advertising consent. You will need to specify actual javascript code to inject (i.e.
Facebook pixel) under specific categories using our management tool.

Under the hood, user consent categories are saved in a special cookie called: "BETTERGDPR".
So, when the page loads for the user for the first time in a session, we check the value of the
"BETTERGDPR" cookie value and inject approved javascript code. If it is a new user, we will show
the user our cookie consent popup.

= 2. Privacy portal =
If configured, upon your user registration, we will duplicate user details (user name, email,
telephone, country) and create a user object in the Custom-Subdomain.Databunker.cloud service
that will be opened only for your website. Databunker.cloud runs a professional version of the
ParanoidGuy Databunker service - a service that gives the customers full privacy control in
terms of the GDPR. It automates the execution of the following GDPR user rights:

1. The right to be informed
2. The right of access
3. The right to rectification
4. The right to erasure
5. The right to restrict processing
6. The right to data portability
7. The right to object
8. Rights in relation to automated decision making and profiling


= 3. User profile change propagation (BETA) =

If configured, upon user profile change, Databunker can automatically sync user details (name, email, etc...)
with the following SAAS services:

1. GetReponse
2. MailChimp
3. HubSpot
4. Salesforce

If user is deleted, we automatically delete user record from all these service.

== Frequently Asked Questions ==

= Do you plan supporting other languages? =

Yes. We are planing to add multiple languages in the nearest future.

== Screenshots ==

1. Advanced Cookie Consent

== Changelog ==

= 0.2.0 =
* Initial product release.

== Upgrade Notice ==


=== Plugin Name ===
Contributors: stremovsky
Donate link: https://paranoidguy.com/
Tags: gdpr, privacy, cookie-consent
Requires at least: 4.7
Tested up to: 5.4
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

GDPR & Cookie Consent plugin built by ParanoidGuy.com team.

== Description ==

This is an official version of the Better-GDPR plugin built by ParanoidGuy.com team.
We are paranoid about customer data privacy at ParanoidGuy.com.
Let's keep in touch and help us to improve the product.
Mail us at office @ paranoidguy.com

= Plugin main features: =

1. Cookie consent popup
2. Better architecute
3. PrivacyBunker.cloud portal integration
3. User profile sync and change propagation (BETA)

= 1. Cookie consent plugin =

It is very similar to other cookie consent popup plugins. The main difference between most of the
similar tools is that we inject not-required components (javascript) only after we got actual
user consent. For example, Advertising related javascript code will be injected after the user
gave us Advertising consent. You will need to specify actual javascript code to inject (i.e.
Facebook pixel) under specific categories using our management tool.

Under the hood, user consent categories are saved in a special cookie called: "BETTERGDPR".
So, when the page loads, for the user, for the first time in a session, we check the value of the
"BETTERGDPR" cookie value and inject approved javascript code. If it is a new user, we will show
the user our cookie consent popup.

= 2. Better architecute =

BetterGDPR plugin does not change your database structure nor it does not create any additional
tables to store user consent values. Instead, the plugin will open for your site a tenant 
subdomain at PrivacyBunker.cloud - a service build and maintained by the PranoidGuy.com team.
This domain will be a custom privacy portal for your users.

What are the advantages of this architecture:

1. No changes in your WordPress production database
2. User gets privacy portal covered bellow
3. Personal data is encrypted and stored in AWS Aurora PostgreSQL cluster deployed in Frankfurt.


= 3. PrivacyBunker.cloud privacy portal integration =

Upon your end-user registration, we will register him an account under Custom-Subdomain.PrivacyBunker.cloud
service. PrivacyBunker.cloud service gives your customers full control in terms of the GDPR. It automates
the execution of the following GDPR user rights:

1. The right to be informed
2. The right of access
3. The right to rectification
4. The right to erasure
5. The right to restrict processing
6. The right to data portability
7. The right to object
8. Rights in relation to automated decision making and profiling


= 4. User profile change propagation (BETA) =

If configured, upon user profile change, Databunker can automatically sync user details (name, email, etc...)
with the following SAAS services:

1. GetReponse
2. MailChimp
3. HubSpot
4. Salesforce

If user is deleted, the service, can be configured to automatically delete user record from all SAAS services.

== Frequently Asked Questions ==

= Do you plan supporting other languages? =

Yes. We are planing to add multiple languages in the nearest future.

== Screenshots ==

1. BetterGDPR Solution Architecture
2. Advanced Cookie Consent Screen

== Changelog ==

= 0.2.0 =
* Initial product release.

== Upgrade Notice ==


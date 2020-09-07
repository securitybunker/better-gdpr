=== Plugin Name ===
Contributors: stremovsky
Donate link: https://paranoidguy.com/
Tags: gdpr, privacy, privacy-tools, compliance, cookie-consent, security, cookie-banner, GDPR-compliance, general-data-protection-regulation, law, saas, regulations
Requires at least: 4.7
Tested up to: 5.5
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

GDPR Automation & Cookie Consent plugin built by ParanoidGuy.com team.

== Description ==

Better-GDPR is a part of GDPR automation service built by the <a target='_blank' href='https://paranoidguy.com/'>ParanoidGuy.com team</a>. In addition to regular services like cookie consent and GDPR user request management, the plugin knows to automatically sync user records with different cloud SAAS services. For example Mailchimp, GetResponse, Hubspot, SAP, Salesforce, etc...

= Plugin main advantages: =

1. GDPR compliant cookie consent popup
2. Simple integration
3. User privacy portal
4. User profile sync and change propagation

= 1. GDPR compliant cookie consent plugin =

It is very similar to other cookie consent popup plugins. The main difference between most of the similar tools is that we inject not-required components (JavaScript) only after we got actual user consent. For example, Advertising related JavaScript code will be injected after the user gave us Advertising consent. You will need to specify actual JavaScript code to inject (i.e. Facebook pixel) under specific categories using our management tool.

Under the hood, user consent categories are saved in a special cookie called: "BETTERGDPR". So, when the page loads, for the user, we check the value of the "BETTERGDPR" cookie value and inject approved JavaScript code. If it is a new user, we will show the user our cookie consent popup.

= 2. Simple Integration =

BetterGDPR plugin does not change your database structure, nor it does not create any additional tables. Instead, the plugin will open for your site a tenant subdomain at PrivacyBunker.cloud - a service build and maintained by the **PranoidGuy.com team**. This domain will be used as a privacy portal for your users.

What are the advantages of this architecture:

1. No changes in your WordPress production database.
2. The user can use the Privacy Portal covered bellow.
3. Personal data is encrypted and stored in the AWS Aurora PostgreSQL cluster deployed in Frankfurt.


= 3. User privacy portal =

All your users will get access to the cloud Privacy Portal. This Privacy Portal will allow your customers to fetch details from other 3rd party SAAS services and give you a solution for the following GDPR requirements:

* **Access**: your customers can fetch and view PII from WordPress and from other SAAS services you might use.
* **Withdraw**: your customers can withdraw consents given previously.
* **Update**: your customers can change their personal data.
* **Delete**: your customers can perform a "forget-me" request inside the Privacy Portal.
* **Restrict**: your customers can restrict the sharing and processing of their personal records.

= 4. User profile change propagation =

If configured, upon user profile change, Databunker can automatically sync user details (name, email, etc...) with the following SAAS services:

1. GetReponse
2. MailChimp
3. HubSpot
4. Salesforce
5. Etc...

If a user is deleted, the service can be configured to automatically delete user records from specific SAAS services.


== Frequently Asked Questions ==

= Do you plan supporting other languages? =

Yes. We are planning to add multiple languages in the nearest future.


== Screenshots ==

1. BetterGDPR Solution Architecture
2. Advanced Cookie Consent Screen
3. User Controls


== Changelog ==

= 0.2.0 =
* Initial product release.

== Upgrade Notice ==


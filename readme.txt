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

Better-GDPR is a part of the <a target='_blank' href='https://privacybunker.io/'>GDPR automation service</a>.

== Why we are better? ==

The service provides **visibility** and **control** to the end-users over the personal data both on the WordPress website, inside online CRM systems (**HubSpot, Salesforce, SAP, Zoho, etc...**), inside online email marketing services (**GetResponse, Mailchimp, ActiveCampaign, etc...**), inside online support systems (**Zendesk, Freshdesk, etc...**) and other **SAAS services** and everything from the user **Privacy Portal** provided by the service.

In addition, the plugin has regular features like **cookie consent banner** and integration with different **WordPress forms** (login, signup, contact, purchase, etc...).

== Plugin main advantages: ==

1. User Privacy Portal
2. Simple integration - no DB changes
3. User profile change propagation
4. GDPR compliant cookie consent popup

= 1. User Privacy Portal =

All your users will get access to the Privacy Portal. The **Privacy Portal** allows your customers to fetch personal details from your website and from other 3rd party SAAS services. Privacy Portal gives a solution for the following **GDPR requirements**:

* **Access**: your customers can fetch and view personal data.
* **Withdraw**: your customers can withdraw consents given previously.
* **Update**: your customers can change their personal data.
* **Delete**: your customers can perform a "forget-me" request inside the Privacy Portal.
* **Restrict**: your customers can restrict the sharing and processing of their personal records.

= 2. Simple Integration =

BetterGDPR plugin does not change your database structure, nor it does not create any additional tables. Instead, the plugin will open for your site a **tenant subdomain** at **PrivacyBunker.cloud** - a service build and maintained by the <a target='_blank' href='https://paranoidguy.com/'>ParanoidGuy.com team</a>. This domain will be used as a **Privacy Portal** for your users.

**What are the advantages of this architecture?**

1. No changes in your WordPress production database.
2. Your customer can use the Privacy Portal.
3. Personal data is encrypted and stored in the AWS Aurora PostgreSQL cluster deployed in Frankfurt.

= 3. User profile change propagation =

If configured, upon user profile change, the service can automatically sync user details with supported SAAS services. In case the user record is deleted and you approve this, the service will automatically delete user details from these SAAS services.

= 4. GDPR compliant cookie consent plugin =

It is very similar to other cookie consent popup plugins. The plugin injects JavaScript only after it got actual user consent. For example, Advertising related JavaScript code will be injected after the user gave us Advertising consent. You will need to specify actual JavaScript code to inject (i.e. Facebook pixel) under a specific category in the service management tool.

Under the hood, user consent categories are saved in a special cookie called: "BETTERGDPR". So, when the page loads, the plugin checks the value of the "BETTERGDPR" cookie and injects the approved JavaScript code. If it is a new site visitor, we will show the user our cookie consent popup.


== Frequently Asked Questions ==

= Do you plan supporting other languages? =

Yes. We are planning to add multiple languages in the nearest future.


== Screenshots ==

1. BetterGDPR Solution Architecture
2. Advanced Cookie Consent Screen
3. User Privacy Portal


== Changelog ==

= 0.2.0 =
* Initial product release.

= 0.2.1 =
* Automatically change banner position.

= 0.2.2 =
* Disable checked checkboxes by default.

== Upgrade Notice ==


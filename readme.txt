=== AlertMe! – Post Update Notifications to Subscribers ===
Contributors: chrisbloomwp, wpnipun
Tags: email, subscriber, notify, post notification, web traffic
Donate link: https://bloomwp.com/donate/
Requires at least: 4.9
Tested up to: 5.7
Requires PHP: 5.4
Stable tag: 2.0.3
License: GPLv3

Send email alerts to subscribers whenever a page/post/cpt has been updated.  

== Description ==
AlertMe! enables website owners the ability to add a simple email subscribe box to any page, post, or custom post type, which sends an email alert whenever that page/post/cpt has been updated. View and download excel document of all subscribers via the Wordpress admin. View list of Subscribers and which page/post/cpt has the most subscriptions. Effortlessly pull visitors back into your website, without the need for costly email, inbound, or CRM systems.

https://youtu.be/eAXWwyJCaJQ

**Easily collect subscribers on your website**: Insert a clean and simple looking subscription box on your website. This subscription box is professionally designed to grab attention. Uses the CSS styles of your theme.

**Automated publishing to subscriber emails whenever a post/page/cpt is updated**: AlertMe! saves you time by automating the process of sending emails each time a post or page is updated.

**Simple setup**: Takes 5 minutes or less to configure and start capturing subscribers. Via the AlertMe! settings page, options include automatically or manually adding AlertMe! to pages, posts, or cpt. Displaying subscribe box top, bottom, or both – and even a [alertme-form-show] shortcode display option. 

**Compatible with all Page Builders**: Works with Gutenberg and all other popular page builders, such as Elementor, Visual Composer, SiteOrigin, and Divi Builder.   

**Pull visitors back to your website with a single click!**



**All new features in version 2**:

- Single Click Subscribe: Logged in users now have the ability to subscribe to additional page/post/cpt’s of your site with a single click.
- Subscriber Confirmation: Automated email to confirm visitors subscription. Spam BOTs hate this.
- Manage Subscriptions page: Each user can now manage their page/post/cpt subscription preferences from a simple "Manage Subscriptions" page.
- Subscriber Statistics: Now see which page/posts/cpt’s have the most subscribers.
[Read more about the new features in version 2](https://bloomwp.com/2020/09/07/alertme-2-0-is-here/)


Potential Uses:

- What's New page
- New Features page
- New Products page
- Documentation
- Image or PDF Attachment page
- Law Firm Case Investigation page
- News Site Breaking Story page
- Bridal Registry page
- Baby Registry page
- For Sale page
- Local School Events and Weather page
- Local Church Events page
- Blog subscribe
- The possibilities are endless!


== Installation ==
1. Download `Alert Me.zip`
2. Unzip
3. Upload `Alert Me` directory to your `/wp-content/plugins` directory
4. Go to the plugin management page and enable the plugin
5. After installation and plugin activation, you will find 1 new tab in Wp-admin panel sidebar – AlertMe!
6. Under the “Display Settings” tab – Configure the subscribe box placement options
7. Create and publish a new page “Manage Subscriptions” and add shortcode: [alertme-subscriptions-list] – this page will be used by your subscribers to manage subscriptions
8. If you are facing any difficulty with AlertMe! plugin installation, then please contact me at: chris@bloomwp.com

== Frequently Asked Questions ==

= Why would I want to use this instead of an Email or Inbound Marketing platform? =
This is a set-it-and-forget-it tool, seriously. Simply select the page/post/cpt you would like AlertMe! to appear on, and move onto other things. When you update a page/post/cpt AlertMe! appears on, simply check "Send update notification" before clicking the "Update" button and your subscribed visitors will instantly receive an email alert.

= What is required for this plugin to work on my website? =
1. A theme that has a JS library included.
2. Classic editor plugin, page builder, or Gutenberg.
3. The ability to send email from your website.

= Is there documentation for AlertMe!? =

Yes, you will find all the documentation you need on the [AlertMe! documentation page](https://bloomwp.com/plugins/alertme).

= I have an idea for a great way to improve this plugin =

Awesome, we'd love to hear from you! Please send us your feature requests: [form](https://bloomwp.com/support/)!

= Is there a working demo of AlertMe!? =

Yes, check out [AlertMe! sample 1](https://caseadvisor.org/active-investigations/ford-shelby-gt350-engine-failure/) and [AlertMe! sample 2](https://www.boltontechnology.com/new/).

= Is there a shortcode option for AlertMe!? =

Yes, [alertme-form-show] is the shortcode you can use to place the subscribe box anywhere on your website.

== Screenshots ==
1. The AlertMe! settings interface allows full customization, such as selecting placement of subscribe field, post types to display on, automatic or manual placement of subscribe box, customization of the subscribe box field text, and alert email message
2. Email notification message can be customized with text, HTML, and even images
3. Manually adding a AlertMe! subscribe field to a page, post, or cpt is easy – simply check the "Show alert me box"
4. When ready to alert subscribers of an update, check "Send update notification" and update the post or page
5. Subscribers automatically receive an email informing them of the page update - pulling them back to your website
6. AlertMe! Subscribe box is simple and elegant. Or if you prefer, use your own CSS to customize the appearance
7. View list of all subscribers, active and nonactive, and which post they are subscribed to. Download list in CSV format for retargeting campaigns
8. View which page/posts/cpt’s have the most subscribers

== Changelog ==

= 2.0.3 =
* Fixed: PHP 8 compatibility issues.
= 2.0.2 =
* Fixed: Download Subscription issue.
= 2.0.1 =
* Fixed: Conflict with WP Offload SES plugin.
= 2.0.0 =
* New feature: Manage Subscriptions page allows users to manage their page/post/cpt subscription preferences.
* New feature: Subscriber Confirmation by Email to confirm new subscription and help ward off Spam BOTs.
* New feature: Subscriber Statistics displays how many subscribers each page/post/cpt has active. 
* New feature: “You are already subscribed” message to remind visitors that they have subscribed to that page/post/cpt they are viewing on return visit.
* New feature: Single click subscribe for logged in users. No longer need to add email for every page/post/cpt subscription.
* New feature: Settings interface redesigned with the inclusion of tabs to show/hide plugin options.
* New feature: AlertMe! is it’s own sidebar item for faster access.
* Feature enhancement: Subscribe box redesigned for easier CSS styling, and the removal of Google Font usage.
* Bug fix: When deactivating then reactivating the plugin all subscribers would be cleared from database. 
* Bug fix: Fixed issue when adding email address and hitting the enter/return key, form would not submit but instead scroll to the top of the page.
* Bug fix: Fixed issue of placement of subscribe box always displaying in top position.

= 1.2.1 =
* Bug fix: Fixed issue when using shortcode results in the placement of the subscribe box not displaying in desired position.
* Bug fix: Fixed json error message when page/post saved using shortcode option with Gutenberg.    
 
= 1.2 =
* New feature: Populate the email subject field with your custom text.
* New feature: Customize the email body section with your text, HTML, and images.

= 1.1.5 =
* New feature: Option to add custom signature to alert email.

= 1.1.4 =
* New feature: Select which page visitor will see when unsubscribing to alert emails.
* Feature enhancement: Updated email template to avoid emails getting caught in a spam filter. 

= 1.1.3 =
* New feature: Added ability to place AlertMe! using [alertme-form-show] shortcode. 

= 1.1.2 =
* Bug fix: Fixed a spelling error and spacing issue between text.  

= 1.1.1 =
* Bug fix: Fixed an issue when AlertMe! is activated causes post content to disappear.    

= 1.1.0 =
* New feature: Added the ability to automatically display AlertMe! on all Posts/Pages/Attachments, or to manually select which Posts/Pages/Attachments to display AlertMe! on.     

= 1.0.0 =
* Release Date: January 1, 2020

== Upgrade Notice ==

= 2.0.3 =
* Fixed: PHP 8 compatibility issues.

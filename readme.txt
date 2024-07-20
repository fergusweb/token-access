=== Token Access ===
Author:             Anthony Ferguson
Author URI:         https://www.ferguson.codes
Contributors:		ajferg
Requires at least:	6.0
Tested up to:		6.6
Stable tag:			1.8.1
Requires PHP:       7.4
License:            GPLv3 or later
License URI:        https://www.gnu.org/licenses/gpl-3.0.html
Tags:				private, cookie, access, public, whitelist, developer


Limit access to the site to those with a cookie token.  Visitors without the cookie see a customisable "coming soon" style of page.

== Description ==

This plugin will limit access to the site.  Only visitors with a cookie token will be able to access the public website.  Visitors without the cookie will see a "coming soon" style of page.

You can customise the "coming soon" content, and you can change the way tokens work.  Visit yoursite.com/?add_token to receive the token allowing you to see the site.  To remove the limited access, simply disable this plugin.

By default, the token is "letmein".  So use these links:
site.com?letmein
site.com?takemeoff

This plugin is useful when you're developing your site.  You can work on your live web server without having your site open to the public.

== Installation ==

1. Upload plugin to your wordpress installation plugin directory
1. Activate plugin through the `Plugins` menu in Wordpress
1. Look at the configuration screen (found under `Tools` in the Wordpress menu)
1. You can change settings or customise your 'coming soon' content here.

== Changelog ==

= 1.8.1 =
* Version bump for build process

= 1.8.0 =
* Refactor plugin and check compatibility with latest WP

= 1.7.1 = 
* Fix: It was showing the placeholder on the wp-login.php page too.

= 1.7.0 =
* Rewrote plugin to meet code standards.
* Now stores custom placeholder in options table, instead of writing to a file that will be overwritten when the plugin updates.
* Using the proper WP Settings API to manage options.

= 1.6.3 =
* Updating versions & changelog
* Better wp-cli compatibility

= 1.4.0 =
* Overhaul for better use of WP functions, and more acceptable CSS rules.
* Compatible with WordPress 4.4

= 1.2.0 =
* Initial public release.

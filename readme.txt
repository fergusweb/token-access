=== Token Access ===
Contributors:		ajferg
Tags:				private, cookie, access, public, whitelist, developer
Requires at least:	3.0
Tested up to:		5.4.1
Stable tag:			1.6.3

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

= 1.6.3 =
* Updating versions & changelog
* Better wp-cli compatibility

= 1.4 =
* Overhaul for better use of WP functions, and more acceptable CSS rules.
* Compatible with WordPress 4.4

= 1.2 =
* Initial public release.

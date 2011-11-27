=== Disable Comments ===
Contributors: solarissmoke
Tags: comments, disable, global
Requires at least: 3.1
Tested up to: 3.3
Stable tag: trunk

Allows administrators to globally disable comments on their site. Comments can be disabled according to post type.

== Description ==

This plugin allows administrators to globally disable comments on any post types (posts, pages, attachments, etc.) so that these settings cannot be overridden for individual posts. It also removes all comment-related fields from edit and quick-edit screens. Additionally, comment-related items can be removed from the Dashboard, the Admin Menu and the Admin Bar.

Use this plugin if you don't want comments at all on your site (or on certain post types). Don't use it if you want to selectively disable comments on individual posts - WordPress lets you do that anyway.

It requires PHP version 5 or greater.

If you come across any bugs or have suggestions, please contact me at [rayofsolaris.net](http://rayofsolaris.net). Please check the [FAQs](http://rayofsolaris.net/code/disable-comments-for-wordpress#faq) for common issues.

== Changelog ==

= 0.3.3 =
* Bugfix: Custom post types which don't support comments shouldn't appear on the settings page
* Add warning notice to Discussion settings when comments are disabled

= 0.3.2 =
* Bugfix: Some dashboard items were incorrectly hidden in multisite

= 0.3.1 =
* Compatibility fix for WordPress 3.3

= 0.3 =
* Added the ability to remove links to comment admin pages from the Dashboard, Admin Bar and Admin Menu

= 0.2.1 =
* Usability improvements to help first-time users configure the plugin.

= 0.2 =
* Bugfix: Make sure pingbacks are also prevented when comments are disabled.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin settings can be accessed via the 'Settings' menu in the administration area
 
=== GD bbPress Attachments ===
Contributors: gdragon
Donate link: http://www.dev4press.com/
Version: 1.0.2
Tags: bbpress, attachments, gdragon, dev4press, upload
Requires at least: 3.2
Tested up to: 3.3
Stable tag: trunk

Implements attachments upload to the topics and replies in bbPress plugin through media library and adds additional forum based controls.

== Description ==
Attachments for forum topic and replies are handled through WordPress media library. You can control file sizes from the main plugin settings panel, or you can do it individually for each forum you have set. You can limit number of files user can attach for each topic and reply.

On admin side, topic and reply panels have column with attachments count, and on the individual edit pages you will see meta box with list of attachments.

= Important URL's =
[Plugin Home](http://www.dev4press.com/plugins/gd-bbpress-attachments/) |
[Feedburner](http://feeds2.feedburner.com/dev4press) |
[Twitter](http://twitter.com/milangd)

== Installation ==
= General Requirements =
* PHP: 5.x.x

= WordPress Requirements =
* WordPress: 3.2

= bbPress Requirements =
* bbPress Plugin: 2.0

= Basic Installation =
* Plugin folder in the WordPress plugins folder must be `gd-bbpress-attachments`
* Upload folder `ggd-bbpress-attachments` to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
* Where can I configure the plugin?
Open the Forums menu, and you will see Attachments item there. This will open a panel with global plugin settings.

* Will this plugin work with standalone bbPress instalation?
No. Plugin requires the plugin versions of bbPress.

== Changelog ==
= 1.0.2 =
* Improvements to the main settings panel
* Fixed missing variable for topic attachments saving
* Fixed ignoring selected roles to display upload form elements
* Fixed upgrading plugin settings process
* Fixed few more undefined variables warnings

= 1.0.1 =
* Screenshots added

== Upgrade Notice ==
= 1.0.2 =
* Improvements to the main settings panel. Fixed missing variable for topic attachments saving. Fixed ignoring selected roles to display upload form elements. Fixed upgrading plugin settings process. Fixed few more undefined variables warnings.

== Screenshots ==
1. Main plugins settings
2. Reply with 2 attachments
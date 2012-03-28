=== GD bbPress Attachments ===
Contributors: GDragoN
Donate link: http://www.dev4press.com/
Version: 1.7
Tags: bbpress, attachments, gdragon, dev4press, upload, forum, topic, reply, media library, limit
Requires at least: 3.2
Tested up to: 3.4
Stable tag: trunk

Implements attachments upload to the topics and replies in bbPress plugin through media library and adds additional forum based controls.

== Description ==
Attachments for forum topics and replies are handled through WordPress media library. You can control file sizes from the main plugin settings panel, or you can do it individually for each forum you have set. You can limit number of files user can attach for each topic and reply. Plugin can embed list of attached files into topics and replies, and images can be displayed as thumbnails. All upload errors are logged and topic/reply author and administrators can see those errors.

On admin side, topic and reply panels have column with attachments count, and on the individual edit pages you will see meta box with list of attachments and upload errors.

Supported languages: English, Serbian, Polish, Dutch, German, Spanish.

= Important URL's =
[Plugin Home](http://www.dev4press.com/plugins/gd-bbpress-attachments/) |
[Support Forum](http://www.dev4press.com/forums/forum/free-plugins/gd-bbpress-attachments/) |
[Feedburner](http://feeds2.feedburner.com/dev4press) |
[Twitter](http://twitter.com/milangd) |
[Facebook Page](http://www.facebook.com/dev4press)

== Installation ==
= General Requirements =
* PHP: 5.2.x
* bbPress: 2.x.x

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
No. This plugin requires the plugin versions of bbPress 2.0 or higher.

* What are the common problems that can prevent upload to work?
In some cases, it can happen that jQuery is not included in the page, even so the bbPress requires it to be loaded. That can happen if something else is unloading jQuery. If the jQuery is not present, upload will not work.
Other common issue is that WordPress Media Library upload is not working. If that is not set up, attachments upload can't work.

* Why is Media Library required?
All attachments uploads are handled by the WordPress Media Library, and plugin uses native WordPress upload functions. When file is uploaded it will be available through Media Library. Consult WordPress documentation about Media Library requirements.

== Translations ==
* English
* Serbian
* Polish: Dawid Karabin - http://www.hinok.net/
* Dutch: Wouter van Vliet - http://www.interpotential.com/
* German: David Decker - http://deckerweb.de/
* Spanish: Jhonathan Arroyo - http://www.siswer.com/

== Changelog ==
= 1.7 =
* Loading optimization with separate admin and front end code
* Added options for deleting and detaching attachments
* Added several new filters for additional plugin control
* Added option for error logging visibility for moderators
* Fixed logging of multiple upload errors
* Fixed several issues with displaying upload errors

= 1.6 =
* Added hide attachments from visitors option
* Added option to hook in topic and reply deletion
* Added Polish translation
* Improved adding of plugin styling and JavaScript
* Fixed visibility of meta settings for non admins

= 1.5.3 =
* Context Help for WordPress 3.3

= 1.5.2 =
* Rel attribute allows use of topic or reply ID
* Admin topic and reply editor list of errors
* Updated German and Serbian translations
* Updated readme file with error logging information

= 1.5.1 =
* Fixed logging of empty error messages

= 1.5 =
* Improved tabbed admin interface
* Image attachments display and styling
* Error logging displayed to admin and author
* Fixed upload from edit topic and reply
* Fixed including of jQuery into header
* Fixed bbPress detection for edit pages

= 1.2.4 =
* Improved Dutch Translation
* Updated Frequently Asked Questions

= 1.2.3 =
* Minor change to user roles detection
* Fixed problem with displaying attachments to visitors

= 1.2.2 =
* Spanish Translation

= 1.2.1 =
* German Translation
* Check for the bbPress to add JavaScript and CSS

= 1.2.0 =
* Disable attachments for individual forums
* Improved admin side topic and reply editor integration

= 1.1.0 =
* Attachments icons in the attachment lists

= 1.0.4 =
* Attachment icon of forums

= 1.0.3 =
* Serbian Translation
* Dutch Translation

= 1.0.2 =
* Improvements to the main settings panel
* Fixed missing variable for topic attachments saving
* Fixed ignoring selected roles to display upload form elements
* Fixed upgrading plugin settings process
* Fixed few more undefined variables warnings

= 1.0.1 =
* Screenshots added

== Upgrade Notice ==
= 1.7 =
Loading optimization with separate admin and front end code. Added options for deleting and detaching attachments. Added several new filters for additional plugin control. Added option for error logging visibility for moderators. Fixed logging of multiple upload errors. Fixed several issues with displaying upload errors.

== Screenshots ==
1. Main plugins settings panel
2. Reply with attachments and file type icons
3. Image attachments with upload errors
4. Attachments with delete and detach actions
5. Attachments upload elements in the form
6. Single forum meta box with settings
7. Icons for the forums with attachments

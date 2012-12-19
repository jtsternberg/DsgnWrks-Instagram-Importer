=== Plugin Name ===
DsgnWrks Instagram Importer

Contributors: jtsternberg
Plugin Name: DsgnWrks Instagram Importer
Plugin URI: http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer
Tags: instagram, import, backup, photo, photos, importer
Author URI: http://about.me/jtsternberg
Author: Jtsternberg
Donate link: http://j.ustin.co/rYL89n
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.1
Version: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Backup your instagram photos & display your instagram archive. Supports importing to custom post-types & adding custom taxonomies.

== Description ==

In the spirit of WordPress and "owning your data," this plugin will allow you to import and backup your instagram photos to your WordPress site. Includes robust options to allow you to control the imported posts formatting including built-in support for WordPress custom post-types, custom taxonomies, post-formats. You can control the content of the title and content of the imported posts using tags like `**insta-image**`, `**insta-text**`, and others. Add an unlimited number of user accounts for backup and importing.

Plugin is built with developers in mind and has many filters to manipulate the imported posts.

Like this plugin? Checkout the [DsgnWrks Twitter Importer](http://j.ustin.co/QbMC8N). Feel free to [contribute to this plugin on github](http://j.ustin.co/QbQKpw).

== Installation ==

1. Upload the `dsgnwrks-instagram-importer` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the plugin settings page (`/wp-admin/tools.php?page=dsgnwrks-instagram-importer-settings`) to add your instagram usernames and adjust your import settings. If you want to see how how the output will look, I suggest you set the date filter to the last month or so. If you have a lot of instagram photos, you may want to import the photos in chunks (set the date further and further back between imports till you have them all) to avoid server overload or timeouts.
4. Import!

== Frequently Asked Questions ==

= ?? =
If you run into a problem or have a question, contact me ([contact form](http://j.ustin.co/scbo43) or [@jtsternberg on twitter](http://j.ustin.co/wUfBD3)). I'll add them here.


== Screenshots ==

1. Welcome Panel.
2. After authenticating a user, this is the options panel you'll be presented with. If you select a custom post-type in the post-type selector, the options may change based on the post-type's supports, as well as any custom taxonomies.

== Changelog ==

= 1.1 =
* Convert plugin to an OOP class and remove amazon S3 links from post content. Props to [@UltraNurd](https://github.com/UltraNurd).

= 1.0.2 =
* Fixes a bug with new user profile images not showing correctly

= 1.0.1 =
* Fixed a bug where imported instagram times could be set to the future

= 1.0 =
* Launch.


== Upgrade Notice ==

= 1.1 =
* Convert plugin to an OOP class and remove amazon S3 links from post content. Props to [@UltraNurd](https://github.com/UltraNurd).

= 1.0.2 =
* Fixes a bug with new user profile images not showing correctly

= 1.0.1 =
* Fixed a bug where imported instagram times could be set to the future

= 1.0 =
* Launch
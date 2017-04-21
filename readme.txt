=== Plugin Name ===
DsgnWrks Instagram Importer

Contributors: jtsternberg
Plugin Name: DsgnWrks Instagram Importer
Plugin URI: http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer
Tags: instagram, import, backup, photo, photos, importer
Author URI: http://jtsternberg.com/about
Author: Jtsternberg
Donate link: http://j.ustin.co/rYL89n
Requires at least: 3.1
Tested up to: 4.7.3
Stable tag: 1.4.1
Version: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Backup your instagram photos & display your instagram archive. Supports importing to custom post-types & adding custom taxonomies.

== Description ==

In the spirit of WordPress and "owning your data," this plugin will allow you to import and backup your instagram photos to your WordPress site. Includes robust options to allow you to control the imported posts formatting including built-in support for WordPress custom post-types, custom taxonomies, post-formats. You can control the content of the title and content of the imported posts using tags like `**insta-image**`, `**insta-text**`, and others, or use the new conditional tags `[if-insta-text]Photo Caption: **insta-text**[/if-insta-text]` and `[if-insta-location]Photo taken at: **insta-location**[/if-insta-location]`. Add an unlimited number of user accounts for backup and importing.

As of version 1.2.0, you can now import and backup your instagram photos automatically! The plugin gives you the option to choose from the default WordPress cron schedules, but if you wish to add a custom interval, you may want to add the [wp-crontrol plugin](http://wordpress.org/extend/plugins/wp-crontrol/).

Version 1.2.6 introduced many new features for Instagram video. Your videos will now be imported to the WordPress media library, as well as the cover image. The new shortcode, `[dsgnwrks_instagram_embed src="INSTAGRAM_MEDIA_URL"]`, displays your imported media as an Instagram embed (works great for video!) and finally, you can now use the tags, `**insta-embed-image**`, and `**insta-embed-video**`, in the Post Content template to save the `dsgnwrks_instagram_embed` shortcode to the post.

Plugin is built with developers in mind and has many filters to manipulate the imported posts.

--------------------------

= Sites That Have Used the Importer =

* [bigredfro.com](http://bigredfro.com/category/funny-instagram-pictures/)
* [instadre.com](http://instadre.com/)
* [photos.jkudish.com](http://photos.jkudish.com/)
* [photos.jtsternberg.com](http://photos.jtsternberg.com)
* [bakersfieldvintage.com](http://bakersfieldvintage.com/recent/)
* [fernwehblues.de/category/momente](http://www.fernwehblues.de/category/momente)

(send me your site if you want to be featured here)

Like this plugin? Checkout the [DsgnWrks Twitter Importer](http://j.ustin.co/QbMC8N). Feel free to [contribute to this plugin on github](http://j.ustin.co/QbQKpw).

== Installation ==

1. Upload the `dsgnwrks-instagram-importer` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the plugin settings page (`/wp-admin/tools.php?page=dsgnwrks-instagram-importer-settings`) to add your instagram usernames and adjust your import settings. If you want to see how how the output will look, I suggest you set the date filter to the last month or so. If you have a lot of instagram photos, you may want to import the photos in chunks (set the date further and further back between imports till you have them all) to avoid server overload or timeouts.
4. Import!

== Frequently Asked Questions ==

= How do I use these snippets? =
* Below are some common requests, and some helper snippets to accomplish them. To install these gists, download the gist from github, unzip, and copy the `.php` file inside to your site's `wp-content/mu-plugins` directory. If you do not have the `mu-plugins` directory, simply create it. For more information, [read this post](https://gregrickaby.com/2013/10/create-mu-plugin-for-wordpress/).

= Is it possible to set the default image display size in a post? =
* If you're importing as the featured image and your theme supports featured images, that is the size that will be used. If you're instead importing the image to the post, there is a filter in the plugin for overriding the image size. If you wanted to instead use the "medium" image size created by WordPress, you can use this snippet: [https://gist.github.com/jtsternberg/1c6b332b2db6da7e38226b88dff5c6a0](https://gist.github.com/jtsternberg/1c6b332b2db6da7e38226b88dff5c6a0).
That is a filter on the $size parameter passed to `wp_get_attachment_image_src()` so you can use any values you would use there. `wp_get_attachment_image_src()` on the codex: http://codex.wordpress.org/Function_Reference/wp_get_attachment_image_src

= Is it possible to limit the length of the imported posts? =
* Yes, use this snippet: [gist.github.com/jtsternberg/6148635](https://gist.github.com/jtsternberg/6148635)

= Is it possible to set the title of the imported posts to the date of the image? =
* Yes, use this snippet: [gist.github.com/jtsternberg/1b83e43348cfe4ec08a3](https://gist.github.com/jtsternberg/1b83e43348cfe4ec08a3)

= Can I save my own post-meta fields for each post? =
* Yes, use this snippet: [gist.github.com/jtsternberg/f784e8d0e8c2da371702](https://gist.github.com/jtsternberg/f784e8d0e8c2da371702)

= Is it possible to automatically center align the imported images? =
* Yes, use this snippet: [gist.github.com/jtsternberg/60e201662691ec9d4a8e](https://gist.github.com/jtsternberg/60e201662691ec9d4a8e) (will only work if your theme supports the 'aligncenter' class)

= Can I remove the text from the excerpt field? =
* Yes, use this snippet: [https://gist.github.com/jtsternberg/2797bf20ac6e5cf09417d22098e65c1d](https://https://gist.github.com/jtsternberg/2797bf20ac6e5cf09417d22098e65c1d)

= Is it possible to store the location data in the recommended WordPress GPS coordinates format/standard? =
* Yes, use this snippet: [https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1](https://https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1)

= Is it possible to modify where the instagram meta data is stored? =
* Yes, review/use this snippet: [https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1](https://https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1)

= Is it possible to embed the imported videos with WordPress native video player, instead of the Instagram embed? =
* Yes, review/use this snippet: [https://gist.github.com/jtsternberg/b7c3b5371c6f639693b8f086859ad129](https://https://gist.github.com/jtsternberg/b7c3b5371c6f639693b8f086859ad129)

= ?? =
* If you run into a problem or have a question, contact me ([contact form](http://j.ustin.co/scbo43) or [@jtsternberg on twitter](http://j.ustin.co/wUfBD3)). I'll add them here.


== Screenshots ==

1. Welcome Panel.
2. After authenticating a user, this is the options panel you'll be presented with. If you select a custom post-type in the post-type selector, the options may change based on the post-type's supports, as well as any custom taxonomies.

== Changelog ==

= 1.4.1 =
* Add `dsgnwrks_instagram_post_meta_pre_save` filter to allow saving meta to user-defined keys. Fixes [Issue 29](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/29).
* Add `alt` attribute to instagram image.
* Fix bug where other instagram plugin's settings would redirect to our plugin setting page ([forum post](https://wordpress.org/support/topic/get-token-issue-with-other-ig-plugins/#post-9037260)).

= 1.4.0 =
* Fix condition markup, if condition is the first bit in the content.
* Allow deletion of users when the key is 0. hat-tip to Pablo de la Vega: [http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208](http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208)
* Fix issue w/ cached user data not being stored to transient.
* Delete user-data transient when deleting user.
* Cleanup wysiwyg editor instance.
* No longer need bio, website, fullname, and profile_picture from authentication callback as we fetch in a separate request (or data is not needed).

= 1.3.9 =
* Fix issues where Instagram usernames with certain characters could not show the settings panel.

= 1.3.8 =
* Fix issues where Instagram usernames with certain characters could not import media.

= 1.3.7 =
* Update: fix unit tests.
* Update: Switch to a singleton for primary plugin class. (this helps address some issues with the debug plugin)

= 1.3.6 =
* Bug fix: Fix "Call to undefined method DsgnWrksInstagram::get_options()" error.

= 1.3.5 =
* Bug fix: Fix "Call to undefined method DsgnWrksInstagram_Settings::debugsend()" error.

= 1.3.4 =
* Bug fix: Some checkboxes were not displaying the saved value.

= 1.3.3 =
* Occasionally update the stored user's data from the instagram API to keep it fresh.
* Fix fatal error when deactivating the plugin.

= 1.3.2 =
* When possible, import the full-resolution non-square instagram images.

= 1.3.1 =
* Update importer image markup to support coming responsive images update to WordPress core.

= 1.3.0 =
* When possible, import the full-resolution instagram images (1080x1080).

= 1.2.9 =
* Bug fix: Made the auto-import feature off by default. Would sometimes be triggered on plugin activation.
* Saved the Instagram username to post-meta (`instagram_username`) along with the entire Instagram user object (`instagram_user`).

= 1.2.8 =
* Bug fix: Tag filter is now more reliable.

= 1.2.7 =
* Bug fix: Adding a new user no longer resets the auto-import frequency setting.
* Bug fix: User settings would occasionally not save correctly.
* Conflict fix: Do not publicize imported posts via Jetpack.
* New: Template tag for getting the instagram image, `dw_get_instagram_image`, and for displaying the image, `dw_instagram_image`.

= 1.2.6 =
* New: Shortcode for displaying instagram embed, `dsgnwrks_instagram_embed`.
* New: `**insta-embed-image**`, and `**insta-embed-video**` import content tags to add the embed shortcode. Using these tags will negate the `**insta-image**` tag.
* New: Plugin option for selecting to remove #hashtags when saving posts' Title/Content/Excerpt.
* New: `dsgnwrks_instagram_import_types` filter - Modify to exclude images or video (or others?) from the import.
* New: `dsgnwrks_instagram_post_excerpt` filter - Modifies the imported posts' excerpts.
* New: `dsgnwrks_instagram_post_title` filter - Modifies the imported posts' titles.
* New: `dsgnwrks_instagram_post_content` filter - Modifies the imported posts' content.
* New: `dsgnwrks_instagram_{$tag}` filter - Allows granular modification of each content tag's replacement.
* Improvement: Better ajax importing of images/posts. Each imported post will show live feedback during the import process.
* Improvement: Better styling for users with MP6 installed.
* Fixed: Authenticating users with Emoji (or other special characters in their bios) would cause the plugin to break.
* Fixed: Post format selector didn't have correct class and so wasn't getting shown correctly.

= 1.2.5 =
* Added: POT translation file.
* Improvement: Import now runs via AJAX, and imported post messages have improved styling.
* Fixed: Previously had no uninstall hook. Now deletes plugin option data (not imported posts) when uninstalling plugin.

= 1.2.4 =
* Fixed: `**insta-image-link**` now pulls in the full 612x612 image size.
* Added: dsgnwrks_instagram_image_size filter for changing from 'full' to any registered image size.
* Added: dsgnwrks_instagram_insta_image filter to allow manipulation of the `**insta-image**` html markup (add classes, etc).

= 1.2.3 =
* Fixed: Better SSL management

= 1.2.2 =
* Added: Option to save Instagram hashtags as taxonomy terms (tags, categories, etc).
* Added: Filter on Settings page to allow other plugins/themes to add extra settings fields.
* Added: More of the Instagram photo data is saved to post_meta. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/5816ddade00b92fa0975fb47b49ca8467779e2a4)
* Fixed: Better management and display of API connection errors. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/6fec092cafc7d241c1b1d75e4a80b42d28eff2d5)

= 1.2.1 =
* Added: Internationalization (i18n) translation support, and debugging infrastructure.

= 1.2.0 =
* Added: It's finally here! Option to auto-import/backup your instagram shots.

= 1.1.4 =
* Added: Option to conditionally add "insta-text" & "insta-location."
* Updated: Default options when first adding a user, including the "insta-location" conditional in the post content.
* Fixed: When unchecking "set as featured image," the posts would still add the featured image.

= 1.1.3 =
* Fixed: When unchecking "set as featured image" the input would still display as checked

= 1.1.2 =
* Fix infinite redirect when adding a new user

= 1.1.1 =
* Update plugin instructions that state setting the image as featured is required for images to be backed-up. As of version 1.1, this is no longer a requirement.

= 1.1 =
* Convert plugin to an OOP class and remove amazon S3 links from post content. Props to [@UltraNurd](https://github.com/UltraNurd).

= 1.0.2 =
* Fixes a bug with new user profile images not showing correctly

= 1.0.1 =
* Fixed a bug where imported instagram times could be set to the future

= 1.0 =
* Launch.


== Upgrade Notice ==

= 1.4.1 =
* Add `dsgnwrks_instagram_post_meta_pre_save` filter to allow saving meta to user-defined keys. Fixes [Issue 29](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/29).
* Add `alt` attribute to instagram image.
* Fix bug where other instagram plugin's settings would redirect to our plugin setting page ([forum post](https://wordpress.org/support/topic/get-token-issue-with-other-ig-plugins/#post-9037260)).

= 1.4.0 =
* Fix condition markup, if condition is the first bit in the content.
* Allow deletion of users when the key is 0. hat-tip to Pablo de la Vega: [http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208](http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208)
* Fix issue w/ cached user data not being stored to transient.
* Delete user-data transient when deleting user.
* Cleanup wysiwyg editor instance.
* No longer need bio, website, fullname, and profile_picture from authentication callback as we fetch in a separate request (or data is not needed).

= 1.3.9 =
* Fix issues where Instagram usernames with certain characters could not show the settings panel.

= 1.3.8 =
* Fix issues where Instagram usernames with certain characters could not import media.

= 1.3.7 =
* Update: fix unit tests.
* Update: Switch to a singleton for primary plugin class. (this helps address some issues with the debug plugin)

= 1.3.6 =
* Bug fix: Fix "Call to undefined method DsgnWrksInstagram::get_options()" error.

= 1.3.5 =
* Bug fix: Fix "Call to undefined method DsgnWrksInstagram_Settings::debugsend()" error.

= 1.3.4 =
* Bug fix: Some checkboxes were not displaying the saved value.

= 1.3.3 =
* Occasionally update the stored user's data from the instagram API to keep it fresh.
* Fix fatal error when deactivating the plugin.

= 1.3.2 =
* When possible, import the full-resolution non-square instagram images.

= 1.3.1 =
* Update importer image markup to support coming responsive images update to WordPress core.

= 1.3.0 =
* When possible, import the full-resolution instagram images (1080x1080).

= 1.2.9 =
* Bug fix: Made the auto-import feature off by default. Would sometimes be triggered on plugin activation.
* Saved the Instagram username to post-meta (`instagram_username`) along with the entire Instagram user object (`instagram_user`).

= 1.2.8 =
* Bug fix: Tag filter is now more reliable.

= 1.2.7 =
* Bug fix: Adding a new user no longer resets the auto-import frequency setting.
* Bug fix: User settings would occasionally not save correctly.
* Conflict fix: Do not publicize imported posts via Jetpack.
* New: Template tag for getting the instagram image, `dw_get_instagram_image`, and for displaying the image, `dw_instagram_image`.

= 1.2.6 =
* New: Shortcode for displaying instagram embed, `dsgnwrks_instagram_embed`.
* New: `**insta-embed-image**`, and `**insta-embed-video**` import content tags to add the embed shortcode. Using these tags will negate the `**insta-image**` tag.
* New: Plugin option for selecting to remove #hashtags when saving posts' Title/Content/Excerpt.
* New: `dsgnwrks_instagram_import_types` filter - Modify to exclude images or video (or others?) from the import.
* New: `dsgnwrks_instagram_post_excerpt` filter - Modifies the imported posts' excerpts.
* New: `dsgnwrks_instagram_post_title` filter - Modifies the imported posts' titles.
* New: `dsgnwrks_instagram_post_content` filter - Modifies the imported posts' content.
* New: `dsgnwrks_instagram_{$tag}` filter - Allows granular modification of each content tag's replacement.
* Improvement: Better ajax importing of images/posts. Each imported post will show live feedback during the import process.
* Improvement: Better styling for users with MP6 installed.
* Fixed: Authenticating users with Emoji (or other special characters in their bios) would cause the plugin to break.
* Fixed: Post format selector didn't have correct class and so wasn't getting shown correctly.

= 1.2.5 =
* Added: POT translation file.
* Improvement: Import now runs via AJAX, and imported post messages have improved styling.
* Fixed: Previously had no uninstall hook. Now deletes plugin option data (not imported posts) when uninstalling plugin.

= 1.2.4 =
* Fixed: `**insta-image-link**` now pulls in the full 612x612 image size.
* Added: dsgnwrks_instagram_image_size filter for changing from 'full' to any registered image size.
* Added: dsgnwrks_instagram_insta_image filter to allow manipulation of the `**insta-image**` html markup (add classes, etc).

= 1.2.3 =
* Fixed: Better SSL management

= 1.2.2 =
* Added: Option to save Instagram hashtags as taxonomy terms (tags, categories, etc).
* Added: Filter on Settings page to allow other plugins/themes to add extra settings fields.
* Added: More of the Instagram photo data is saved to post_meta. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/5816ddade00b92fa0975fb47b49ca8467779e2a4)
* Fixed: Better management and display of API connection errors. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/6fec092cafc7d241c1b1d75e4a80b42d28eff2d5)

= 1.2.1 =
* Added: Internationalization (i18n) translation support, and debugging infrastructure.

= 1.2.0 =
* Added: It's finally here! Option to auto-import/backup your instagram shots.

= 1.1.4 =
* Added: Option to conditionally add "insta-text" & "insta-location."
* Updated: Default options when first adding a user, including the "insta-location" conditional in the post content.
* Fixed: When unchecking "set as featured image," the posts would still add the featured image.

= 1.1.3 =
* Fixed: When unchecking "set as featured image" the input would still display as checked

= 1.1.2 =
* Fix infinite redirect when adding a new user

= 1.1.1 =
* Update plugin instructions that state setting the image as featured is required for images to be backed-up. As of version 1.1, this is no longer a requirement.

= 1.1 =
* Convert plugin to an OOP class and remove amazon S3 links from post content. Props to [@UltraNurd](https://github.com/UltraNurd).

= 1.0.2 =
* Fixes a bug with new user profile images not showing correctly

= 1.0.1 =
* Fixed a bug where imported instagram times could be set to the future

= 1.0 =
* Launch

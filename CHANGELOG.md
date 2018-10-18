# Changelog
All notable changes to this project will be documented in this file.

## Unreleased
*

## 2.0.0

* Provide filter (`dsgnwrks_instagram_video_import_resolutions`) for specifying which video resolutions to import.
* Improved imported items display, including:
	* Excerpted title in case they get super long
	* Update titles for imported video items
	* Trash link for instantly curating
	* Edit link for getting to the image quickly
	* Added `<li>` title attribute for "imported & created successfully"
	* Fix formatting of imported item output for videos
* Fix issue where it looks like we are still looping and no-new-to-import does not show
* Fix issue with log function not being available in some instances
* Enable importing instagram carousel posts. Fixes [#43](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/43) and [#30](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/30) TODO: enable gallery settings, etc
* Update/fix Travis CI configuration for better testing. Props [Nathan Friedly](https://github.com/nfriedly)
* Fix issue with `access_token` query var being used by other plugins
* Fixed several issues with unit tests
* Added `dsgnwrks_instagram_post_meta_pre_save` filter to allow changing meta keys. Fixes [#29](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/29).
* Add the ability to reimport missed photos. Fixes [#32](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/32), and [#37](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/37)
* Cleaned up broken bits of UI.
* Store deleted instagram-imported posts to blacklist, to keep from reimporting intentionally deleted items.
* Add a UI for editing the blacklist in the settings.

## 1.4.1

* Add `dsgnwrks_instagram_post_meta_pre_save` filter to allow saving meta to user-defined keys. Fixes [#29](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/issues/29).
* Add `alt` attribute to instagram image.
* Fix bug where other instagram plugin's settings would redirect to our plugin setting page ([forum post](https://wordpress.org/support/topic/get-token-issue-with-other-ig-plugins/#post-9037260)).

## 1.4.0

* Fix condition markup, if condition is the first bit in the content.
* Allow deletion of users when the key is 0. hat-tip to Pablo de la Vega: [http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208](http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer/#comment-12208)
* Fix issue w/ cached user data not being stored to transient.
* Delete user-data transient when deleting user.
* Cleanup wysiwyg editor instance.
* No longer need bio, website, fullname, and profile_picture from authentication callback as we fetch in a separate request (or data is not needed).

## 1.3.9

* Fix issues where Instagram usernames with certain characters could not show the settings panel.

## 1.3.8

* Fix issues where Instagram usernames with certain characters could not import media.

## 1.3.7

* Update: fix unit tests.
* Update: Switch to a singleton for primary plugin class. (this helps address some issues with the debug plugin)

## 1.3.6

* Bug fix: Fix "Call to undefined method DsgnWrksInstagram::get_options()" error.

## 1.3.5

* Bug fix: Fix "Call to undefined method DsgnWrksInstagram_Settings::debugsend()" error.

## 1.3.4

* Bug fix: Some checkboxes were not displaying the saved value.

## 1.3.3

* Occasionally update the stored user's data from the instagram API to keep it fresh.
* Fix fatal error when deactivating the plugin.

## 1.3.2

* When possible, import the full-resolution non-square instagram images.

## 1.3.1

* Update importer image markup to support coming responsive images update to WordPress core.

## 1.3.0

* When possible, import the full-resolution instagram images (1080x1080).

## 1.2.9

* Bug fix: Made the auto-import feature off by default. Would sometimes be triggered on plugin activation.
* Saved the Instagram username to post-meta (`instagram_username`) along with the entire Instagram user object (`instagram_user`).

## 1.2.8

* Bug fix: Tag filter is now more reliable.

## 1.2.7

* Bug fix: Adding a new user no longer resets the auto-import frequency setting.
* Bug fix: User settings would occasionally not save correctly.
* Conflict fix: Do not publicize imported posts via Jetpack.
* New: Template tag for getting the instagram image, `dw_get_instagram_image`, and for displaying the image, `dw_instagram_image`.

## 1.2.6

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

## 1.2.5

* Added: POT translation file.
* Improvement: Import now runs via AJAX, and imported post messages have improved styling.
* Fixed: Previously had no uninstall hook. Now deletes plugin option data (not imported posts) when uninstalling plugin.

## 1.2.4

* Fixed: `**insta-image-link**` now pulls in the full 612x612 image size.
* Added: dsgnwrks_instagram_image_size filter for changing from 'full' to any registered image size.
* Added: dsgnwrks_instagram_insta_image filter to allow manipulation of the `**insta-image**` html markup (add classes, etc).

## 1.2.3

* Fixed: Better SSL management

## 1.2.2

* Added: Option to save Instagram hashtags as taxonomy terms (tags, categories, etc).
* Added: Filter on Settings page to allow other plugins/themes to add extra settings fields.
* Added: More of the Instagram photo data is saved to post_meta. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/5816ddade00b92fa0975fb47b49ca8467779e2a4)
* Fixed: Better management and display of API connection errors. props [csenf](https://github.com/csenf/DsgnWrks-Instagram-Importer-WordPress-Plugin/commit/6fec092cafc7d241c1b1d75e4a80b42d28eff2d5)

## 1.2.1

* Added: Internationalization (i18n) translation support, and debugging infrastructure.

## 1.2.0

* Added: It's finally here! Option to auto-import/backup your instagram shots.

## 1.1.4

* Added: Option to conditionally add "insta-text" & "insta-location."
* Updated: Default options when first adding a user, including the "insta-location" conditional in the post content.
* Fixed: When unchecking "set as featured image," the posts would still add the featured image.

## 1.1.3

* Fixed: When unchecking "set as featured image" the input would still display as checked

## 1.1.2

* Fix infinite redirect when adding a new user

## 1.1.1

* Update plugin instructions that state setting the image as featured is required for images to be backed-up. As of version 1.1, this is no longer a requirement.

## 1.1

* Convert plugin to an OOP class and remove amazon S3 links from post content. Props to [@UltraNurd](https://github.com/UltraNurd).

## 1.0.2

* Fixes a bug with new user profile images not showing correctly

## 1.0.1

* Fixed a bug where imported instagram times could be set to the future

## 1.0

* Launch.

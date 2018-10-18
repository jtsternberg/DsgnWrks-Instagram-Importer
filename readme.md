# DsgnWrks Instagram Importer #

**Contributors:** jtsternberg  
**Plugin Name:** DsgnWrks Instagram Importer  
**Plugin URI:** http://dsgnwrks.pro/plugins/dsgnwrks-instagram-importer  
**Tags:** instagram, import, backup, photo, photos, importer  
**Author URI:** http://jtsternberg.com/about  
**Author:** Jtsternberg  
**Donate link:** http://j.ustin.co/rYL89n 
**Requires at least:** 3.1  
**Tested up to:** 4.7.3  
**Version:** 1.4.1  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Backup your instagram photos & display your instagram archive. Supports importing to custom post-types & adding custom taxonomies.

## Description ##

In the spirit of WordPress and "owning your data," this plugin will allow you to import and backup your instagram photos to your WordPress site. Includes robust options to allow you to control the imported posts formatting including built-in support for WordPress custom post-types, custom taxonomies, post-formats. You can control the content of the title and content of the imported posts using tags like `**insta-image**`, `**insta-text**`, and others, or use the new conditional tags `[if-insta-text]Photo Caption: **insta-text**[/if-insta-text]` and `[if-insta-location]Photo taken at: **insta-location**[/if-insta-location]`. Add an unlimited number of user accounts for backup and importing.

As of version 1.2.0, you can now import and backup your instagram photos automatically! The plugin gives you the option to choose from the default WordPress cron schedules, but if you wish to add a custom interval, you may want to add the [wp-crontrol plugin](http://wordpress.org/extend/plugins/wp-crontrol/).

Version 1.2.6 introduced many new features for Instagram video. Your videos will now be imported to the WordPress media library, as well as the cover image. The new shortcode, `[dsgnwrks_instagram_embed src="INSTAGRAM_MEDIA_URL"]`, displays your imported media as an Instagram embed (works great for video!) and finally, you can now use the tags, `**insta-embed-image**`, and `**insta-embed-video**`, in the Post Content template to save the `dsgnwrks_instagram_embed` shortcode to the post.

Plugin is built with developers in mind and has many filters to manipulate the imported posts.

--------------------------

### Sites That Have Used the Importer ###

* [bigredfro.com](http://bigredfro.com/category/funny-instagram-pictures/)
* [instadre.com](http://instadre.com/)
* [photos.jkudish.com](http://photos.jkudish.com/)
* [photos.jtsternberg.com](http://photos.jtsternberg.com)
* [bakersfieldvintage.com](http://bakersfieldvintage.com/recent/)
* [fernwehblues.de/category/momente](http://www.fernwehblues.de/category/momente)

(send me your site if you want to be featured here)

Like this plugin? Checkout the [DsgnWrks Twitter Importer](http://j.ustin.co/QbMC8N). Feel free to [contribute to this plugin on github](http://j.ustin.co/QbQKpw).

## Installation ##

1. Upload the `dsgnwrks-instagram-importer` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the plugin settings page (`/wp-admin/tools.php?page=dsgnwrks-instagram-importer-settings`) to add your instagram usernames and adjust your import settings. If you want to see how how the output will look, I suggest you set the date filter to the last month or so. If you have a lot of instagram photos, you may want to import the photos in chunks (set the date further and further back between imports till you have them all) to avoid server overload or timeouts.
4. Import!

## Frequently Asked Questions ##

### How do I use these snippets?
* Below are some common requests, and some helper snippets to accomplish them. To install these gists, download the gist from github, unzip, and copy the `.php` file inside to your site's `wp-content/mu-plugins` directory. If you do not have the `mu-plugins` directory, simply create it. For more information, [read this post](https://gregrickaby.com/2013/10/create-mu-plugin-for-wordpress/).

### Is it possible to set the default image display size in a post?
* If you're importing as the featured image and your theme supports featured images, that is the size that will be used. If you're instead importing the image to the post, there is a filter in the plugin for overriding the image size. If you wanted to instead use the "medium" image size created by WordPress, you can use this snippet: [https://gist.github.com/jtsternberg/1c6b332b2db6da7e38226b88dff5c6a0](https://gist.github.com/jtsternberg/1c6b332b2db6da7e38226b88dff5c6a0).
That is a filter on the $size parameter passed to `wp_get_attachment_image_src()` so you can use any values you would use there. `wp_get_attachment_image_src()` on the codex: http://codex.wordpress.org/Function_Reference/wp_get_attachment_image_src

### Is it possible to limit the length of the imported posts?
* Yes, use this snippet: [gist.github.com/jtsternberg/6148635](https://gist.github.com/jtsternberg/6148635)

### Is it possible to set the title of the imported posts to the date of the image?
* Yes, use this snippet: [gist.github.com/jtsternberg/1b83e43348cfe4ec08a3](https://gist.github.com/jtsternberg/1b83e43348cfe4ec08a3)

### Can I save my own post-meta fields for each post?
* Yes, use this snippet: [gist.github.com/jtsternberg/f784e8d0e8c2da371702](https://gist.github.com/jtsternberg/f784e8d0e8c2da371702)

### Is it possible to automatically center align the imported images?
* Yes, use this snippet: [gist.github.com/jtsternberg/60e201662691ec9d4a8e](https://gist.github.com/jtsternberg/60e201662691ec9d4a8e) (will only work if your theme supports the 'aligncenter' class)

### Can I remove the text from the excerpt field?
* Yes, use this snippet: [https://gist.github.com/jtsternberg/2797bf20ac6e5cf09417d22098e65c1d](https://gist.github.com/jtsternberg/2797bf20ac6e5cf09417d22098e65c1d)

### Is it possible to store the location data in the recommended WordPress GPS coordinates format/standard?
* Yes, use this snippet: [https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1](https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1)

### Is it possible to modify where the instagram meta data is stored?
* Yes, review/use this snippet: [https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1](https://gist.github.com/jtsternberg/a5914ac04198a57ebfca38567cc382e1)

### Is it possible to embed the imported videos with WordPress native video player, instead of the Instagram embed?
* Yes, review/use this snippet: [https://gist.github.com/jtsternberg/b7c3b5371c6f639693b8f086859ad129](https://gist.github.com/jtsternberg/b7c3b5371c6f639693b8f086859ad129)

### ??
* If you run into a problem or have a question, contact me ([contact form](http://j.ustin.co/scbo43) or [@jtsternberg on twitter](http://j.ustin.co/wUfBD3)). I'll add them here.


## Screenshots ##

1. Welcome Panel.
![Welcome Panel.](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer-WordPress-Plugin/raw/master/screenshot-1.jpg)

2. After authenticating a user, this is the options panel you'll be presented with. If you select a custom post-type in the post-type selector, the options may change based on the post-type's supports, as well as any custom taxonomies.
![After authenticating a user, this is the options panel you'll be presented with. If you select a custom post-type in the post-type selector, the options may change based on the post-type's supports, as well as any custom taxonomies.](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer-WordPress-Plugin/raw/master/screenshot-2.jpg)


## Changelog ##

**[View CHANGELOG](https://github.com/jtsternberg/DsgnWrks-Instagram-Importer/blob/master/CHANGELOG.md)**

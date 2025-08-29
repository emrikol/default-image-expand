=== Default Image "Enlarge on click" ===
Contributors: emrikol
Tags: blocks, image, lightbox, gutenberg, expand
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sets the Image block's default Link setting to "Enlarge on click" (core lightbox) in the Block Editor.

== Description ==

This plugin automatically sets the default Link setting for Image blocks to "Enlarge on click" in the WordPress Block Editor. This means that when you add new images to your posts, they will default to opening in WordPress's built-in lightbox instead of having no link behavior.

**Key Features:**

* **New Image blocks default to lightbox** - No need to manually enable "Enlarge on click" for every image
* **Respects user choices** - If you manually change an image's link setting, your choice is preserved
* **Per-user control** - Users can disable this default behavior in their profile settings
* **No impact on existing content** - Only affects new images or when editing existing posts
* **WordPress core lightbox** - Uses the built-in WordPress lightbox feature, no additional JavaScript
* **Translation ready** - Fully internationalized and ready for translation
* **Clean and secure** - Follows WordPress security best practices with proper nonce verification

**How It Works:**

The plugin uses WordPress's theme.json system to set sensible defaults for the core Image block. When you insert a new Image block, it will automatically have "Enlarge on click" selected as the Link destination.

**Perfect for:**

* Sites that frequently use image galleries
* Photography websites
* Blogs with lots of images
* Any site where you want images to be more interactive by default

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/default-image-expand/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. That's it! New Image blocks will now default to "Enlarge on click" behavior.

== Frequently Asked Questions ==

= Does this affect existing images on my site? =

Only when you edit existing posts. The plugin sets the site-wide default for Image blocks, so when you edit a post with existing images, they will get the lightbox behavior unless you've explicitly set them to something else.

= Can I disable this for specific images? =

Yes! Simply edit the image block and change the Link setting to "None" or any other option. Your choice will be saved and respected.

= Can I disable this feature for myself? =

Yes! Go to your user profile (Users > Your Profile) and check the box labeled "Disable 'Enlarge on click' default for new Image blocks". This will turn off the default behavior for you while leaving it active for other users.

= Does this work with custom themes? =

Yes! The plugin uses WordPress's standard theme.json system, so it works with any theme that supports the block editor.

= Does this add any JavaScript to my site? =

No additional JavaScript is added. The plugin uses WordPress's built-in lightbox functionality that's already part of the core.

= Can I use this with the Gutenberg plugin? =

Yes! The plugin is compatible with both WordPress core and the Gutenberg plugin.

== Screenshots ==

1. New Image blocks automatically show "Enlarge on click" as the default Link setting
2. User profile option to disable the feature per user
3. Images with explicit link settings are always respected

== Changelog ==

= 1.0.1 =
* Update wording from "Expand on click" to "Enlarge on click" throughout README and descriptions

= 1.0.0 =
* Initial release
* Automatic "Enlarge on click" default for new Image blocks
* Per-user disable option in profile settings
* Full compatibility with WordPress core and Gutenberg plugin
* Respects explicit user link choices
* Translation ready with proper textdomain loading
* Enhanced security with improved capability checks
* Clean deactivation with optional user data cleanup

== Upgrade Notice ==

= 1.0.0 =
Initial release of the plugin.

== Technical Details ==

This plugin uses WordPress's theme.json system via the `wp_theme_json_data_default` filter to set block defaults. It follows WordPress coding standards and best practices for block editor integration.

The plugin is lightweight and only loads the necessary code in the admin area for user profile management.

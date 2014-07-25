<center>[Previous](basic-template.md) | [Home](index.md) | [Next](sass.md)</center>

Launchpad Hooks
===============

Launchpad offers various filters to modify default values via WordPres's built-in <code>add_filter</code> to avoid modifying core Launchpad code.  These filters range from an effort to accomodate different file preferences to altering the behavior of Launchpad.

Several of the following filters are showin in lib/custom/examples.php if you need more specific guidance on how to use them.

## File Preferences

If you like to use a folder called "img" instead of "images", you'll need to add filters to the following.  Sample usage can be found in the examples file.  You can also use these to add custom rewite and cache paths.

<dl>
	<dt>launchpad_cache_manifest_file_paths</dt>
	<dd>Sends an array of the cache manifest paths that are searched for content.</dd>
	<dt>launchpad_rewrite_rules</dt>
	<dd>Sends an array of the rewrite rules.</dd>
</dl>


## Activation

If you want to use different settings for activation-related values (e.g. the path of news), these filters will allow you to do so.  Remember, you must add these filters **before** activating Launchpad.

<dl>
	<dt>launchpad_activate_home_name</dt>
	<dd>The post title of the home page as a string.  The slug is generated for you.</dd>
	<dt>launchpad_activate_articles_name</dt>
	<dd>The post title of the page to use as the posts page as a string.  The slug is generated for you.</dd>
	<dt>launchpad_activate_articles_path</dt>
	<dd>The permalink path for posts as a string.  This can be changed in Settings &gt; Permalinks after activation.  The fefault is <kbd>/articles/%postname%/</kbd></dd>
	<dt>launchpad_activate_upload_path</dt>
	<dd>The path for uploaded files as a string.  The default is <kbd>assets</kbd>.  See the examples file.</dd>
</dl>

## Basic Customizations

<dl>
	<dt>launchpad_image_sizes</dt>
	<dd>Sends an array of images sizes.  Launchpad offers a simplified array format to register image sizes.  To add an image size, append an array such as <code>array($image_name, $width, $height, $crop)</code>.  Sample code is in the examples file.</dd>
	<dt>launchpad_post_types</dt>
	<dd>An array of post types is sent.  There are samples in the example file and detailed a explanation in the [Post Types](post-types.md) section of this codex.</dd>
	<dt>launchpad_post_formats</dt>
	<dd>If you need access to post formats, include this filter and add to the array that is passed.  By default, post formats are disabled.</dd>
	<dt>launchpad_nav_menus</dt>
	<dd>To add or remove menus, modify the array.  The array is a key / value list of menu slugs / menu names (respectively).</dd>
	<dt>launchpad_custom_post_types</dt>
	<dd>If you want to add custom post types, this is where you do it.  See the [post types section](post-types.md) for more info.</dd>
</dl>

## Template Customization

<dl>
	<dt>launchpad_title</dt>
	<dd>To change anything that goes in the <code>&lt;title&gt;</code>, modify this string.</dd>
	<dt>launchpad_excerpt</dt>
	<dd>To change anything that goes in the excerpt, including the meta description excerpt, modift this string.</dd>
	<dt>launchpad_modify_default_flexible_modules</dt>
	<dd>Launchpad ships with a few built-in flexible content modules.  If you would like to add or remove any, you can do it through this filter by editing the array.</dd>
	<dt>launchpad_body_class</dt>
	<dd>An array of classes that will be applied to the <code>body</code> can be modified with this filter.</dd>
</dl>


## Admin Modifications

<dl>
	<dt>launchpad_mce_style_formats</dt>
	<dd>To add additional fields to the styles dropdown on in TinyMCE, use this filter.  You can see sample code in <kbd>lib/custom/examples.php</kbd>.</dd>
	<dt>mce_buttons</dt>
	<dd>You can use this filter to add back built-in buttons on TinyMCE.  You can see sample code in <kbd>lib/custom/examples.php</kbd>.</dd>
	<dt>launchpad_setting_fields</dt>
	<dd>To modify the settings fields that appear in the Launchpad Settings screen, modify it here.  They are basically the same as the metabox field format.  You should be able to figure it out if you add the filter and <code>var_dump</code> the array.</dd>
	<dt>launchpad_theme_options_page</dt>
	<dd>If you don't want the settings screen to appear under Settings &gt; Launchpad, you can change it here.  You can see sample code in <kbd>lib/custom/examples.php</kbd>.</dd>
</dl>

## Advanced Backend Modifications

<dl>
	<dt>launchpad_cache_file_path</dt>
	<dd>Use this filter to change the path (as a string) where Launchpad puts cache files.</dd>
</dl>

Now that you know a few hooks, check out [Launchpad SASS](sass.md).
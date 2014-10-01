<?php
/**
 * Functions Includes
 *
 * Include the hard work of the theme.
 * 
 * @package 	Launchpad
 * @since		1.0
 */

global $site_options;

$site_options = get_option('launchpad_site_options', '');

/** YOUR FUNCTIONS GO IN HERE.  */
locate_template('lib/custom/custom.php', true, true);


/** System functions like theme activation, rewrites, etc. */
locate_template('lib/core/system.php', true, true);
/** Code that manipulates the .htaccess file. */
locate_template('lib/core/htaccess.php', true, true);
/** Modifications to the admin area like options pages and admin cleanup. */
locate_template('lib/core/admin.php', true, true);
/** Handle metaboxes and metabox forms. */
locate_template('lib/core/metaboxes.php', true, true);
/** SEO related stuff that isn't part of the template tags. */
locate_template('lib/core/seo.php', true, true);
/** Security related features like limit login attempts. */
locate_template('lib/core/security.php', true, true);
/** Post Type related code for registering and creating metaboxes. */
locate_template('lib/core/post-types.php', true, true);
/** Cache-related functions. */
locate_template('lib/core/cache.php', true, true);
/** Template related modifications such as nav menu registration, header cleanup, page cache, etc.  */
locate_template('lib/core/template.php', true, true);
/** Custom functions for handling various duties. */
locate_template('lib/core/utilities.php', true, true);
/** Code for custom API calls. */
locate_template('lib/core/api.php', true, true);
/** Code for tools like regen thumbnails. */
locate_template('lib/core/tools.php', true, true);

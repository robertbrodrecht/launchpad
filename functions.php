<?php
/**
 * Functions Includes
 *
 * Include the hard work of the theme.
 * 
 * @package 	Launchpad
 * @since		1.0
 * @todo		Do a second pass on where filtering is supported.
 * @todo		Determine where to trigger custom actions.  http://archive.extralogical.net/2007/06/wphooks/
 */

global $site_options;

$site_options = get_option('launchpad_site_options', '');

/** YOUR FUNCTIONS GO HERE.  */
locate_template('functions-custom.php', true, true);



/** System functions like theme activation, rewrites, etc. */
locate_template('functions/system.php', true, true);
/** Modifications to the admin area like options pages and admin cleanup. */
locate_template('functions/admin.php', true, true);
/** Security related features like limit login attempts. */
locate_template('functions/security.php', true, true);
/** Post Type related code for registering and creating metaboxes.  */
locate_template('functions/post-types.php', true, true);
/** Code for custom API calls.  */
locate_template('functions/api.php', true, true);
/** Template related modifications such as nav menu registration, header cleanup, page cache, etc.  */
locate_template('functions/template.php', true, true);
/** Cache-related functions.  */
locate_template('functions/cache.php', true, true);
/** Custom functions for handling various duties.  */
locate_template('functions/utilities.php', true, true);
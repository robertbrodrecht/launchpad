Launchpad
=========

Launchpad is an extremely opinionated WordPress theme for developers.  This theme is still "in beta," but is probably usable if you test settings extensively before launch.  The theme is meant to be hacked on directly, but you should be able to child-theme it to some degree.  

I'm not sure how compatible this theme is WPMU.

A few sites have been developed on top of Launchpad, and more are in the works.  Feedback from this process is being integrated back into Launchpad as issues arise.

[Codex](_codex/index.md)
========================

For a detailed walk through, check out the [Codex](_codex/index.md).

For the long feature list that was formerly here, see the [Codex Features List](_codex/features.md).

To Do For 1.1
=============

* Flexible content modules and field types.
  * What other modules should be built-in?
  * Other Field Types

Future Wants
============

* Codex review.
* Decide About:
  * More child theme work.
  * Figuring out any multi-site issues.
* Continue improving flexible content if more needs have arisen.
* Add PDF generating library and support for /pdf/ like /download/.
* Research OCR for better alt tag creation.
	* This was not helpful. Still very alpha. http://phpocr.sourceforge.net
* Database and Asset Migration
* Pretty Search URLs
* Make Gravity Forms output better and include generic form stylesheets in _objects.scss.
  * add_filter('gform_field_content', 'launchpad_fix_gravity_forms_output', 10, 5);
  * http://www.gravityhelp.com/documentation/page/Gform_field_content
* Ad Designer.
  * Create ads as post type.
  * Design ads in the browser.  Fonts in theme and positioning.
  * Developer-approved CSS / JS handles how they work.
  * Skate Integrated (Once Skate is 2.0)
* Custom Headers
  * Single Image
  * Ads
* Something about widgets.
* Updates through WP Admin.
* SEO+Social Stuff
  * Flipboard
* Security: some way to check MD5 of files to make sure they weren't modified.
* Optimization: Selectively add_filter( 'jpeg_quality', create_function( '', 'return 100;' ) );
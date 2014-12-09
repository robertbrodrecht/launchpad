Launchpad
=========

Launchpad is an extremely opinionated WordPress theme for developers.  This theme is still "in beta," but is probably usable if you test settings extensively before launch.  The theme is meant to be hacked on directly, but you should be able to child-theme it to some degree.  

I'm not sure how compatible this theme is WPMU.

A few sites have been developed on top of Launchpad, and more are in the works.  Feedback from this process is being integrated back into Launchpad as issues arise.

[Codex](_codex/index.md)
========================

For a detailed walk through, check out the [Codex](_codex/index.md).

For the long feature list that was formerly here, see the [Codex Features List](_codex/features.md).

To Do For 1.3
=============

* Clean nav classes
* AddThis support
* Flexible content modules and field types.
  * What other modules should be built-in?
    * Gallery
    * Image with Caption
  * Other Field Types?
* Improve Filters (e.g. add to relationship field results, etc.)
* Squash as many PHP notices as you can.
* Codex review.
* Update things like the hamburger menu to use checkbox/radio instead of target.

To Do For 1.5
=============
* MU Support
* Child Theme
* Forms
* Boilerplate Post Types
  * Employees
  * Jobs
  * Menus

Future Wants
============

* Continue child theme optimizations.
* Figuring out any multi-site issues.
* Continue improving flexible content if more needs have arisen.
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
* Security: some way to check MD5 of files to make sure they weren't modified.

Really Want But Can't Find A Suitable Way
=========================================

* Optimization: Selectively add_filter( 'jpeg_quality', create_function( '', 'return 100;' ) );

Would Like But Not Sure If Realistic
====================================

* Research OCR for better alt tag creation.
	* This was not helpful. Still very alpha. http://phpocr.sourceforge.net

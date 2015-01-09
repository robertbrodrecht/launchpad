Launchpad
=========

Launchpad is an extremely opinionated WordPress theme for developers.  This theme is still "in beta," but is probably usable if you test settings extensively before launch.  The theme is meant to be hacked on directly, but you should be able to child-theme it to some degree.  

I'm not sure how compatible this theme is WPMU.

A few sites have been developed on top of Launchpad, and more are in the works.  Feedback from this process is being integrated back into Launchpad as issues arise.

[Codex](_codex/index.md)
========================

For a detailed walk through, check out the [Codex](_codex/index.md).

For the long feature list that was formerly here, see the [Codex Features List](_codex/features.md).

To Do For 1.4
=============

* Determine feasibility of conditional logic for meta fields.
* Figure out how to break out some excess JS code as examples, e.g. ajax page loads.
* Add settings for iOS icons and images.
* Add settings for Facebook OG and Twitter Card support.

To Do For 1.5
=============

* Look into image optimization based on ImageOptim workflow:
  * [PNGOut](http://www.advsys.net/ken/util/pngout.htm)
  * [Zopfli](http://googledevelopers.blogspot.co.uk/2013/02/compress-data-more-densely-with-zopfli.html)
  * [Pngcrush](http://pmt.sourceforge.net/pngcrush/)
  * [AdvPNG](http://advancemame.sourceforge.net/doc-advpng.html)
  * [OptiPNG](http://optipng.sourceforge.net/)
  * [JpegOptim](http://www.kokkonen.net/tjko/projects.html)
  * jpegrescan
  * jpegtran
* Flexible content modules and field types.
  * What other modules should be built-in?
  * Other Field Types?
* Database and Asset Migration

To Do For 1.6
=============

* Take another stab at MU Support

Future Wants
============

* Figure out if anything needs to be put in different projects for better sharing.
* Header Ad Designer.
  * Create ads as post type.
  * Design ads in the browser.  Fonts in theme and positioning.
  * Developer-approved CSS / JS handles how they work.
  * Skate Integrated (Once Skate is 2.0)
* Custom Headers
  * Single Image
  * Ads
* Something about widgets.
* Updates through WP Admin.
* Security: some way to check MD5 of WP Core files to make sure they weren't modified.

Really Want But Can't Find A Suitable Way
=========================================

* Optimization: Selectively add_filter( 'jpeg_quality', create_function( '', 'return 100;' ) );

Would Like But Not Sure If Realistic
====================================

* Research OCR for better alt tag creation.
	* This was not helpful. Still very alpha. http://phpocr.sourceforge.net
	* Maybe this can help? http://antimatter15.com/ocrad.js/demo.html

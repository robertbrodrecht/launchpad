Launchpad
=========

Launchpad is an extremely opinionated WordPress theme for developers.  This theme is still "in beta," but is probably usable if you test settings extensively before launch.  The theme is meant to be hacked on directly, but you should be able to child-theme it to some degree.  

I'm not sure how compatible this theme is WPMU.

A few sites have been developed on top of Launchpad, and more are in the works.  Feedback from this process is being integrated back into Launchpad as issues arise.

[Codex](_codex/index.md)
========================

For a detailed walk through, check out the [Codex](_codex/index.md).

For the long feature list that was formerly here, see the [Codex Features List](_codex/features.md).

To Do For 1.7 and 1.8
=====================

While I may pop back in for compatibility updates, I'm taking a break on LaunchPad to work on a child theme.

* Migration
  * Select Post Types
  * Investigate syncing
  * Consider adding an e-mail when complete option.
* Flexible content modules and field types.
  * What other modules should be built-in?
  * Other Field Types?
* Revisit SEO
  * Only pulls post_content for SEO checks
* Review Child Theme and MU support
* Designer feedback.
* Responsive Images
* Refactor the world to use create_element().
* From 4.2: Added the ability to make admin notices dismissible. Plugin and theme authors: adding .notice and .is-dismissible as adjacent classes to your notice containers should automatically make them dismissible. Please test.

Future Wants
============

* Figure out if anything needs to be put in different projects for better sharing.
* Updates through WP Admin.
* Security: some way to check MD5 of WP Core files to make sure they weren't modified.
* Maybe some UI for creating Metaboxes and Flexible Content

Would Like But Not Sure If Realistic
====================================

* Research OCR for better alt tag creation.
	* This was not helpful. Still very alpha. http://phpocr.sourceforge.net
	* Maybe this can help? http://antimatter15.com/ocrad.js/demo.html

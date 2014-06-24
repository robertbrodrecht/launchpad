Launchpad
=========

Launchpad is an extremely opinionated WordPress theme for developers.  This theme is still in beta, but is probably usable if you test settings extensively before launch.  The theme is meant to be hacked on directly, but you should be able to child-theme it to some degree.  

I'm doubtful that this theme is highly compatible with plugins at this point.  I'm not sure how compatible this theme is WPMU.  I'm certain that using this theme in a sub-folder-install of WordPress or without mod_rewrite will require a lot of tweaking.

I am currently developing the first production site based on Launchpad and the second is in development by @iamdangavin.  Feedback from this process is being integrated back into Launchpad as issues arise.

I use CodeKit 2 to compile SASS and handle JavaScript includes and minification.  Child-theme support is on the list for possible features, but it is probably a long way off.


Features
========

This theme has been in the works for over a year.  After working with a team that was using the [Roots WordPress theme](http://roots.io), I adopted a few conventions from there (e.g. more useful body classes, root-relative URLs, built-in custom rewrites, and HTML5 Boilerplate integration) and combined them with some of my previous developer-friendly theme ideas.  I also had some thoughts on how to make a modern website work (e.g. built-in content caching, offline support with intelligent applicationCache reloading, and AJAX page loading), so I added those to it.

I'll be building out the feature list as I get the time.


## Front-end Features

* CodeKit 2 support.
* Developer-mode test grid based on CSS rules to help you understand your baseline grid.  Access by pressing "g" key.

### HTML-ish

* Offline support with intelligent cache refreshing via applicationCache.
* Input placeholder polyfill because WTF IE9.
* Templates for Apple Startup Images and dummy files for Apple Touch Icons and Favicon.
* HTML5 Shiv included for IE8 (until I decide to stop supporting IE8 on the front end, which will probably be some time in late 2014).

### SASS / CSS

* Calculated percentage root font size based on SASS variable.  You enter '10px' and that gets converted to 62.5%.
* REM mixin based on root font size variable (see previous bullet) to make it dead easy to use REMs with a fallback.  E.g. <code>@include rem('padding', 5px 20px);</code>  A new version of the mixin might be better but it needs testing.
* Vertical rhythm based on SASS variables.
* Grid system.  Recently rewritten (and therefore untested) because the old one was too slow and didn't match my co-worker's concept of what grid systems do.
* Conditional comments for IE8/9 that don't make your HTML (specifically the actual <code>html</code> element) look like a janky mess.  Use <code>.msie-8 ~ *</code> as your a prefix to a selector to change styles for IE8 and <code>.msie-9 ~ *</code> for IE9.
* Unsupported browser "[Universal Stylesheet](https://code.google.com/p/universal-ie6-css/)."

### jQuery / JavaScript

* Ajax page loads with History PushState/PopState and Google Analytics pageview events.
* Limited JavaScript feature detection for features that matter (screen DPI, position sticky, css transitions, and touch-capable) instead of including the full Modernizr suite.
* Built-in <code>:target</code>-based "hamburger" menu.  You still have to style it, but the code handles some of the tedious bits.
* [60FPS scrolling](http://www.thecssninja.com/javascript/follow-up-60fps-scroll) option.  Add data-scroll-helper to the body.
* Various custom events for hooking into JavaScript.  Currently: launchpadInit, launchpadReinit, ajaxRequestStart, ajaxRequestEnd.  More will be available eventually, I think.
* jQuery Custom Events for scrollStart, scrollStop, resizeStart, and resizeStop so that you don't have to shoot yourself in the foot by using resize and scroll events when you don't have to.
* jQuery-based Height Match via <code>@data-height-match-group</code> with children containing <code>@data-height-match</code> or <code>@data-height-match-children</code> to height match all children.  Use either a min-width as a number or media query for when heightmatch should work.  Media queries as height-match values are not supported by IE8 (always returns false because IE8 doesn't support Media Queries).


## SEO and Social Media Related Features

* Rel Canonical built in for posts, pages, and single custom post types.
* SEO Title with fallback to page title for posts, pages, and single custom post types.
* Meta Description 
  * Custom meta descriptions.
  * Generated from excerpts automatically if no custom value provided.
* Keyword density and title checks based on various best practices with suggestions on improvements.
* SERP Preview Snippet
* OpenGraph tags generated automatically.
* Twitter Card tags generated automatically.
* Google Analytics Support.
* hCard example in footer if you're into that sort of thing.
* Noindex, follow on archive pages.
* XML Sitemaps generated automatically following schema.org standards.


## Back-end Features

* Content caching with configurable cache timeouts and intelligent cache invalidating (i.e. on save).
* Automagic AppCache Manifest generation that pays attention to individual file size and total cache size to avoid overloading the cache and avoid the browser holding onto old caches.
* Custom rewites:
  * /images/ rewrites to the theme's /images/ folder.
  * /css/ rewrites to the theme's /css/ folder.
  * /js/ rewrites to the theme's /js/ folder.
  * /api/ rewrites to /wp-admin/admin-ajax.php for easier JavaScript API calls.
  * /support/ rewrites to the theme's /support/ folder.
  * manifest.appcache rewrites to the API call for creating the manifest.
* Phone number formatting function.
* US, Canadian, and UK postal code formatting.
* Automatic headers for X-UA-Compatible (IE=edge,chrome=1) so you don't have to put it in your markup.
* Settings for HTML5 Boilerplate's .htaccess.
* Support for saving custom fields and examples of how to set them up.
* Easy creation of custom post types.
* Easy creation of custom fields on those custom post types, and easily add metaboxes to existing post types.
* Flexible content.  Build modules in code as PHP arrays attached to post types. Includes built-in modules for:
  * Accordion: A title, WYSIWYG editor, and a repeater field with title and editor to create accordion lists.
  * Link List: A title, WYSIWYG editor, and relationship field for creating lists that link to other pages.
  * Section Navigation: Select title, starting point, and depth to render a list of child pages.
  * Simple Content: A title and WYSIWYG editor.
* Flexible content is automatically included in searches, so you don't have to worry about important content not being considered in search.  NOTE: Certain fields like Relationships use IDs. So, for example, if you build a link list, the titles of the posts you are linking to won't be considered in search because they are stored as IDs.
* Metabox and Flexible Content Fields:
  * Basic HTML inputs:
    * Checkbox
    * Multiple Select
    * Select
    * Text
    * Textarea
  * Visual Editor
  * Repeaters (Fields of Fields)
  * Relationships (Attach one or more posts in one or more post types)
  * Taxonomy (Select one or more taxonomies)
  * Menu selector (Select a menu created in Appearance > Menus)
  * A ton of filters for modifying stuff.  See the functions-custom.php file for details.  More details eventually and more coming.
  * WordPress can consume a ton of memory.  If your peak memory usage gets within 500KB of the memory limit, the admin e-mail will get a message.
  * Use /download/path/to/local/file or /download/?file=http://path.to/file to force-download a file


## WordPress Features

* Deletes sample page and hello world post on theme activations so you don't have to do it.
* Visual editor and print stylesheets automatically generated from reset, typography, and objects SASS files.
* Support for a.button and a few other custom classes for the Visual Editor styles drop down.
* WordPress admin stylesheet.
* WordPress admin JavaScript.
* Sample admin-ajax API call that you can copy/paste to help you get going faster.
* Automatically sets up a post and home page associations.
* Automatically sets /assets/ as upload folder.
* Automatically adds header and footer navigation.
* Easily-modifiable theme options.  Fields go in an array and the code does the rest.
* Root-relative URLs in Visual Editor and beyond.
* Self-closing / void tag closing slash removal.
* Removal of title and alt attributes on images (because that is better than the default garbage most people leave).
* Change "Howdy" to "Hello" on the admin bar menu.
* Custom login skin with settings to change key colors and logo.
* Semantic rewrite of the WordPress Gallery shortcode.
* Smart 404 page.  Presents options for "Go Back" (if HTTP_REFERER is present), check URL for typos, and go to home page.  Finally, the URL is parsed for search terms and a search is executed for matching pages.  If any are found, the user is presented with the results.


## Security Features

* Configurable login attempts limiter.  Locks a user out based on username+IP for a configurable amount of time after a configurable number of failed attempts.  Save the theme settings to clear all lockouts.


Notes
=====

In many cases, I'm trying to force best practices.  JavaScript embeds are in the footer, for example, and you can use <code>body.no-js</code> and <code>body.js</code> as hooks for styling with Progressive Enhancement.


To Do For 1.0
=============

* Is fragment caching helping? Can I prevent the query from running?
* Do a COMPLETE feature / code review.
  * Refactor. Do a code review and make sure comments are helpful enough.
  * Make sure everything still works.
    * Maybe call in some help.



To Do For 1.1
=============

* Flexible content modules and field types.
  * What other modules should be built-in?
  * Other field types?
* Implementation Documentation: Make a "Codex."  Note: https://help.github.com/articles/relative-links-in-readmes
* Add "basic" stylesheet that handles wireframe related things (i.e. Built-in nav classes with drop downs).


Future Wants
============

* Decide About:
  * More child theme work.
  * Figuring out the plug-in issue.
  * Figuring out the subfolder issue.
  * Figuring out any multi-site issues.
* Continue improving flexible content if more needs have arisen.
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
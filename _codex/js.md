<center>[Previous](sass.md) | [Home](index.md) | [Next](install.md)</center>

Launchpad JavaScript
====================

Launchpad has helpful additions as well as code to handle core theme functionality.  Any JavaScript you write should go in custom.js.  DO NOT modify launchpad.js unless you know what you are doing.  Instead, use Launchpad's custom events to hook into special areas.

## Launchpad's JavaScript Core Features

Launchpad includes support for the following features in no particular order:

1. Special Polyfills
   A. Input placeholder support (phased out once IE9 support is dropped).
   B. matchMedia  (phased out once IE9 support is dropped).
2. Custom events for scrollStart, scrollEnd, resizeStart, and resizeEnd.
3. Manage built-in fleible content, i.e. accordion list.
4. 60FPS scrolling hack to improve scrolling on complex sites.  Just add a data-scroll-helper attribute to the body.
5. Ajax page loads.
6. HTML5 Application Cache management.
7. Startup Image management for Apple's "Add to Home Screen" feature.
8. Mobile nav menu management.
9. Feature detection for important things so you don't HAVE to use Modernizr.
   A. 2x DPI detection.
   B. Touch support.
   C. Position sticky support.
   D. CSS Transition support.
10. Height Matching.

Some of these features require more detailed explanation.

## Feature Detection

You don't need the full Modernizr suite to get your job done.  The most common questions that I encounter in my work are:

1. Do I need to create JavaScript animations because this browser does not support CSS transitions?
2. Is this a touch-capable device that I need to add touch support to?
3. Is this a retina display that could recieve a higher resolution image?

And, more recently:

4. Does this device support sticky position or am I going to have to write custom code?

So, detection of those properties are built in.  You can access this information via classes on the body attribute or in the window.supports object:

<dl>
	<dt>2x DPI Detection</dt>
	<dd><code>window.supports.dpi</code> will be either 1 for 1x displays or 2 for 2x displays.</dd>
	<dt>Touch-Capable</dt>
	<dd><code>window.supports.touch</code> will be true if the device is touch-capable and a <code>touch</code> or <code>no-touch</code> class will be added to the body.</dd>
	<dt>CSS Transitions</dt>
	<dd><code>window.supports.transitions</code> will be true if transitions are supporteda and a <code>css-transitions</code> class will be added to the body.</dd>
	<dt>Sticky Positioning</dt>
	<dd><code>window.supports.sticky</code> will be true if the device knows sticky positioning and a <code>css-sticky</code> or <code>css-not-sticky</code> class will be added to the body.</dd>
</dl>

## Ajax Page Loads

## Application Cache Management

## Height Matching


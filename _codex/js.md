<center>[Previous](sass.md) | [Home](index.md) | [Next](admin.md)</center>

Launchpad JavaScript
====================

Launchpad has helpful additions as well as code to handle core theme functionality.  Any JavaScript you write should go in custom.js.  DO NOT modify launchpad.js unless you know what you are doing.  Instead, use Launchpad's custom events to hook into special areas.

## Launchpad's JavaScript Core Features

Launchpad includes support for the following features in no particular order:

1. Special Polyfills
   1. Input placeholder support (phased out once IE9 support is dropped).
   2. matchMedia  (phased out once IE9 support is dropped).
2. Custom events for scrollStart, scrollEnd, resizeStart, and resizeEnd.
3. Manage built-in fleible content, i.e. accordion list.
4. 60FPS scrolling hack to improve scrolling on complex sites.  Just add a data-scroll-helper attribute to the body.
5. HTML5 Application Cache management.
6. Startup Image management for Apple's "Add to Home Screen" feature.
7. Mobile nav menu management.
8. Feature detection for important things so you don't HAVE to use Modernizr.
   1. 2x DPI detection.
   2. Touch support.
   3. Position sticky support.
   4. CSS Transition support.
9. Height Matching.

Some of these features require more detailed explanation.

## Launchpad Events

<dl>
	<dt>launchpadPreInit</dt>
	<dd>Fires on the <code>body</code> as soon as Launchpad starts initing before it does anything.  If you wish to modify the supported browsers list, this is where to do it.</dd>
	<dt>launchpadInit</dt>
	<dd>Fires on the <code>body</code> as soon as Launchpad does the first init.</dd>
	<dt>launchpadReinit</dt>
	<dd>Fires on the <code>body</code> every time Launchpad reinits (e.g. after ajax loads).</dd>
	<dt>launchpadMenuOpen</dt>
	<dd>Fires on the <code>body</code> when the mobile nav menu opens via a click to the hamburger.</dd>
	<dt>launchpadMenuClose</dt>
	<dd>Fires on the <code>body</code> when the mobile nav menu closes via a click to the hamburger.</dd>
	<dt>scrollStart</dt>
	<dd>When the scroll event fires for the first time.</dd>
	<dt>scrollEnd</dt>
	<dd>Fires when the user stops scrolling for 100ms.</dd>
	<dt>resizeStart</dt>
	<dd>Fires when the user starts resizing for the first time.</dd>
	<dt>resizeEnd</dt>
	<dd>Fires when the user stops scrolling for 250ms.</dd>
</dl>

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

## Application Cache Management

In order to make the Application Cache work better, Launchpad attempts to handle invalidating it.  When the user comes online, the JavaScript attempts to reload the cache.  The JavaScript also monitors whether the user has logged in every 60 seconds.  If the user logs in, the JavaScript attempts to reload the cache.  If all this works properly, users will have their cache invalidated whenever they are online and admins will see the freshest content always.

## Height Matching

Launchpad's height matching ties into the resizeEnd event to handle height matching.  If you want to height match all children, add a <code>data-height-match-children</code> to the parent.  If you want more control, add <code>data-height-match-group</code> to the parent and <code>data-height-match</code> to each child that needs to be height matched.

Both <code>data-height-match-children</code> and <code>data-height-match-group</code> both accept a parameter of either the width in pixels above which height matching should happen or a media query that specifies when the height matching should happen.  Outside of those values, the height matching is removed.

If you need to height match something that is not a child of the <code>data-height-match-group</code>, you can specify a <code>data-height-match-query</code> on the element that has <code>data-height-match-group</code>.  The query is passed to jQuery via <code>.find()</code>.  This would allow you to, for example, height match all figures inside of an unordered list by using something like this:

```
<ul data-height-match-group="568" data-height-match-query="figure">
	<li><figure data-height-match><img src="clear.gif" width="100" height="300></figure>Some Text</li>
	<li><figure data-height-match><img src="clear.gif" width="100" height="350></figure>Some Text</li>
</ul>
```

Now that you have an idea about how the JavaScript works, you can dig into the [WordPress admin modifications](admin.md).
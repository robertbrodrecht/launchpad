<center>[Previous](hooks.md) | [Home](index.md) | [Next](js.md)</center>

Launchpad SASS
==============

Launchpad ships with some helpful SASS features to help get you started quicker and help you organize your files.  Launchpad's SASS attempts to push OOCSS, includes features to help with vertical rhythm, and tries to help you along the way with print stylesheets and editor stylesheets.

## Organization

Launhpad has five "top-level" SASS files:

1. screen.scss
2. print.scss
3. editor-style.scss
4. admin-style.scss
5. unsupported.scss

### screen.scss

The screen style sheet includes the bulk of the SASS partials.  These are the partials and their functions:

<dl>
	<dt>variables</dt>
	<dd>Primarily contains variables to control Launchpad functionality such as grids, but can include any variables you need.  Comments are left in the file to help you understand how to modify variables.</dd>
	<dt>mixins</dt>
	<dd>Launchpad's default mixins (discussed further below).  It is wise not to modify this file as it may cause you problems with apply updates.</dd>
	<dt>grid</dt>
	<dd>Launchpad's lightweight grid framework (discussed further below).</dd>
	<dt>objects</dt>
	<dd>Object-oriented CSS goes in this file.  It is convenient to keep objects out of the main stylehseets so that you don't make anything too specific to one page.</dd>
	<dt>reset</dt>
	<dd>The reset is an amalgamation of [Eric Meyer's CSS Reset](http://meyerweb.com/eric/tools/css/reset/) and HTML5 [Boilerplate's Normalize](https://necolas.github.io/normalize.css/) with additions to help set up the page's vertical rhythm.</dd>
	<dt>typography</dt>
	<dd>Anything that is related to the styling of main-content type should go here.  The goal is to include anything that might be applied to what the user edits so that the WordPress visual editor is more WYSIWYG than it otherwise would be while also aiding in the print stylesheet.</dd>
	<dt>wireframe</dt>
	<dd>This partial contains some basic features to help you wireframe in the browser on top of WordPress.  All code in this file should be considered throw away, and it is likely that you'll want to remove this file once you've migrated the basic wireframes into the real CSS.</dd>
	<dt>ie</dt>
	<dd>Code that is specific to older versions of Internet Explorer.  This is discussed in more detail later.</dd>
</dl>

### print.scss

The print style sheet just pulls the basics to provide a simple print stylesheet.  It also includes code to remove the header, footer, navigation, and WordPress admin bar, as well as some link niceties.  The following partials are included.  Notably, the objects partial is not included.  Because objects may hold JavaScript-dependent CSS or interactive CSS, it is not included to prevent content being inadvertently hidden.

* variables
* mixins
* reset
* typography

### editor-style.scss

* variables
* mixins
* reset
* typography
* objects

### admin-style.scss

The admin stylesheet is used to support Launchpad's admin customizations.  It is wise not to modify this file.

### unsupported.scss

The unsupported browser CSS is taken from [Andrew Clarke's Universal IE6 Stylesheet](https://code.google.com/p/universal-ie6-css/).  If the browser is identified as unsupported ([see Caveats for more details](caveats.md)), the main stylesheet is removed via JavaScript and replaces with this stylesheet.

## Mixins

Launchpad provides several mixins to make life a little easier and (hopefully) help you avoid using additional frameworks.  Here are the mixins:

<dl>
	<dt>rem($property, $value)</dt>
	<dd>Creates a property with a pixel value fallback and a rem value based on the root font size as defined in the variables partial.</dd>
	<dt>rems($values: (property: value[, property: value ...]))</dt>
	<dd>Unlike <code>rem</code> this mixin allows you to pass a list of properties and values to be converted to rem units.  This is slightly nicer because it takes a little less rejiggering if you write some CSS, then realize it was something you wanted to convert to rems. Otherwise, it works the same.  It takes the property and generates a pixel value fallback and a rem value based on the root font size.</dd>
	<dt>media-2x</dt>
	<dd>Creates a media query for @2x resolutions.</dd>
	<dt>media-max</dt>
	<dd>Creates an <code>@media (max-width)</code>  media query. The width can be a unit or keywords defined in <code>$break-points</code> (found in the variable partial).</dd>
	<dt>media-min($width)</dt>
	<dd>Creates an <code>@media (min-width)</code>  media query. The width can be a unit or keywords defined in <code>$break-points</code> (found in the variable partial).</dd>
	<dt>media-range($small, $large)</dt>
	<dd>Create a media query that targets between small and large.  For example, you want something to be red between 500px and 900px, you would use this mixin.  <code>$small</code> and <code>$large</code> can be either units or keywords defined in <code>$break-points</code> (found in the variable partial).  If you happen to invert the parameter order, the mixin will swap the values for you.</dd>
	<dt>media-range-outer($small, $large)</dt>
	<dd>Create a media query that targets small-and-lower and large-and-higher, excluding the area in the middle.  For example, you want something to be red below 500px and above 900px, you would use this mixin.  <code>$small</code> and <code>$large</code> can be either units or keywords defined in <code>$break-points</code> (found in the variable partial).  If you happen to invert the parameter order, the mixin will swap the values for you.</dd>
	<dt>dropdown($options: (fit, fade))</dt>
	<dd>Convert a list into a drop-down style navigation bar.  If you specify "fit" as an option, the nav will take up all the width available in its container and the list items will be distributed evenly.  If you specify "fade", a fade animation will occur when the user hovers over an element with a sub-nav.</dd>
	<dt>frontload</dt>
	<dd>This is the same as "hide from the browser."  It's useful to front load a list or section so that screen readers can introduce something that may be obvious to sighted users but may not be obvious when read aloud.<dd>
	<dt>image-replace</dt>
	<dd>If you are going to do image replacement, this mixing gets you pretty far down the road.  Just add width, height, and the background image.</dd>
	<dt>ugly-clearfix</dt>
	<dd>The ugly way to clearfix with <code>:before</code> and <code>:after</code>.  It's more verbose (ugly) but much more flexible than <code>overflow: hidden</code>.  Use it when you need it.</dd>
	<dt>unlist</dt>
	<dd>Makes a list not look like a list.</dd>
</dl>

## Grid

Launchpad's light-weight grid format is designed to be as flexible as possible.  By default, a grid column is specified in percentages.  That is technically a 100-column grid. If you are more familiar with working against an N-column grid, you must first specify in the variables partial that you want to use an N-column grid by setting the <code>$column-count</code> as such: <code>$column-count: 12;</code>.

If you intend to use built-gutters, you may also want to set <code>$column-master-outer-width</code> and <code>$column-master-gutter-width</code>.  These two values are used to calculate the gutter as a percent.  That is, at a width of <code>$column-master-outer-width</code>, a gutter should be <code>$column-master-gutter-width</code> wide, so the gutter should be <code>$column-master-gutter-width/$column-master-outer-width*100</code>.  **BEWARE**: Using a gutter row removes <code>margin: auto</code> from the row so that the columns can have a <code>margin-left: $column-master-gutter-width/$column-master-outer-width*100</code>.  This is achieved by setting the row as <code>margin-left: 0-$column-master-gutter-width/$column-master-outer-width*100</code>.

### <code>@include row($options: (gutter table no-margin no-float))</code>

<code>@include row()</code> makes the element a row container.  The options control how the row behaves.  With no options, a classic grid row will be created with the row centered via <code>margin: auto</code> and the children <code>float: left</code>.

<dl>
	<dt>gutter</dt>
	<dd>Including this option will apply a negative <code>margin-left</code> to account for guttered columns.</dd>
	<dt>table</dt>
	<dd>Including this option makes the row <code>display: table</code> and children <code>display: table-cell</code>.</dd>
	<dt>no-margin</dt>
	<dd>Including this option will cause the row to have no <code>margin</code> set.</dd>
	<dt>no-float</dt>
	<dd>Including this option will prevent children from having <code>float: left</code> applied.</dd>
</dl>

### <code>@include col($width, $options: (gutter-margin gutter))</code>

Makes the column a certain number of columns wide and applies any gutter options.  Using the option <code>gutter-margin</code> will apply a <code>margin-left</code> of the calculated gutter and <code>gutter</code> will make the column the width specified minus the gutter width.

### <code>push($width)</code> and <code>pull($width)</code>

<code>push</code> and <code>pull</code> will cause a column to be pushed to the right or pulled to the left the number of columns specified.  This is helpful for achieving layouts where the column order doesn't match the source order.

## Dealing with Internet Explorer 8 and 9

Launchpad uses conditional comments for Internet Explorer 8 and 9.  Most developers achieve this by wrapping the <code>html</code> element in conditional comments.  I find this pattern a reprehensible abuse of conditional comments.  Instead, Launchpad creates two conditional comments that wrap <code>span</code> elements just below the <code>body</code> open tag.  Using SASS, you can target elements two ways.  If the element is a child of the <code>body</code>, such as <code> nav</code>:

```css
.msie-8 ~ nav {
	// IE8 tweaks here.
}
```

If you want to target an element that is not a child of <code>body</code> such as the logo:

```css
.msie-8 ~ * #logo {
	// IE8 tweaks here.
}
```

In the event that you need something higher up than this, feel free to implement your own conditional comment aberration wherever you like.

Now that you have an understanding of how the SASS works, you may be interested in the details of the [Launchpad JavaScript](js.md).
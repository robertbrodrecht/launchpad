Launchpad
=========

Launchpad is an extremely opinionated WordPress theme created for developers.  Launchpad aims to:

<dl>
	<dt>Work with WordPress's natural order while extending its capabilities.</dt>
	<dd>The template folder hierarchy follows the practices set up by WordPress's built-in themes and ads modern features like flexible content and caching.</dd>
	<dt>Improve the end-user experience.</dt>
	<dd>Launchpad keeps core tools (SASS and JavaScript) to a minimum and includes features like Ajax page loading, offline caching, and page fragment caching.</dd>
	<dt>Prefer built-in solutions over plugins.</dt>
	<dd>It's hard to trust plugins, so Launchpad includes important features in the core theme, including custom fields, flexible content, login attempts limiting, and caching.</dd>
	<dt>Enforce best practices whenever possible.</dt>
	<dd>Web developers have been espousing accessibility, performance, standards, and semantics for over a decade now, and many developers seem to have a short memory.  Rather than continue evangelizing, Launchpad tries to drag developers kicking and screaming.  JavaScript is included in the footer to prevent blocking rendering (if no plugins are installed) and to promote progressive enhancement.  The default JavaScript and CSS is written with progressive enhancement in mind.  The templates use semantic HTML, including a rewrite of the WordPress gallery short code.</dd>
	<dt>Provide a toolset developers need.</dt>
	<dd>Launchpad is a bare-bones "boilerplate" theme that developers can build upon. The front end is built with SASS+Compass and jQuery, so the tools you need are ready out of the box.  On the back end, Launchpad streamlines a lot of complex functionality like custom post types, custom fields, and flexible content by making them easy to create with simple PHP arrays, and follows it up with filters for just about everything.</dd>
</dl>

Launchpad's feature set is massive and continually growing.  As time permits, I'll build out complete documentation on how to work with Launchpad.

This is a quick outline of documentation goals:

* [Caveats and Browser Support](caveats.md)
* [Installation](install.md)
* [Settings](settings.md)
* [Site Images](site-images.md)
* [Custom Post Types, Taxonomies, Metaboxes, and Flexible Content](post-types.md)
* [Basic Template Editing](basic-template.md)
* [Launchpad Hooks](hooks.md)
* [Launchpad SASS](sass.md)
* [Launchpad JavaScript](js.md)
* Admin Customizations
* SEO
* Caching
* Security
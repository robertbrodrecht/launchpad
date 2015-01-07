<center>[Previous](index.md) | [Home](index.md) | [Next](install.md)</center>

Launchpad Caveats and Browser Support
=====================================

Launchpad was created for a specific development mindset and workflow.  If you don't agree with this, it is likely that you will struggle making Launchpad work for you.  So, it's important to understand what you're getting into.

## Browser Support

Launchpad's front end code currently supports the latest version of the following browsers:

* Safari
* Chrome
* Firefox
* Internet Explorer

Due to the slow update cycle of Internet Explorer, the Launchpad front end currently supports the following legacy versions of Internet Explorer:

* Internet Explorer 10
* Internet Explorer 9

**Internet Explorer 8 support was dropped at the end of 2014!**

There are polyfills for <code>input @placeholder</code> and <code>window.matchMedia</code> that will be removed when IE9 support is dropped.  After IE10 is dropped, the HTML5 Shiv will be removed.

Unsupported versions of Internet Explorer recieve [Andrew Clarke's Universal Stylesheet](https://code.google.com/p/universal-ie6-css/).  Right now, that swap is based on a series of regular expressions in the JavaScript.  If you want to modify the default, create a global variable called <code>doNotSupportOverride</code> that is an array of regular expressions to test.  You must set this BEFORE jQuery's <code>document.ready</code> or you can tie into the <code>launchpadPreInit</code> event that fires on the <code>body</code>:

```javascript
$(document.body).on(
	'launchpadPreInit',
	function() {
		doNotSupportOverride = [/BrowserA/, /BrowserB/];
	}
);
```

## Development Workflow

While Launchpad *should* work if installed in a sub-folder, it is designed around root installs.  The ideal environment would be something like this:

<dl>
	<dt>Development Environment</dt>
	<dd>yourdomain.dev running on your local machine.</dd>
	<dt>Staging Environment</dt>
	<dd>dev.yourdomain.com running on the same server that the production site runs on.</dd>
	<dt>Production Environment</dt>
	<dd>yourdomain.com</dd>
</dl>

All environments need the minimum requirements for WordPress.  Development of WordPress is done with PHP 5.4 and mySQL 5.6.  So, you'll probably want to use at least that version.  If you need help with this, check out [MAMP](http://www.mamp.info/).

Ideally, you will have [CodeKit2](https://incident57.com/codekit/) for compiling SASS and minifying JavaScript.  If you prefer a different tool, you should be able to make it work.

If you're comfortable with all that, you can [start installing](install.md).
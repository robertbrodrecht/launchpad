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
* Internet Explorer 8

**Internet Explorer 8 support will be dropped by the end of 2014!!!***

There are polyfills for <code>input @placeholder</code> and <code>window.matchMedia</code> that will be removed when IE9 support is dropped.  After IE10 is dropped, the HTML5 Shiv will be removed.

Unsupported versions of Internet Explorer recieve [Andrew Clarke's Universal Stylesheet](https://code.google.com/p/universal-ie6-css/).  Right now, that swap is based on a series of regular expressions in the JavaScript.  If you want to modify the default, create a global variable called <code>doNotSupportOverride</code> that is an array of regular expressions to test.  You must set this BEFORE jQuery's <code>document.ready</code> or you can tie into the <code>launchpadPreInit</code> event that fires on the <code>body</code>:

	$(document.body).on(
		'launchpadPreInit',
		function() {
			doNotSupportOverride = [/Safari/];
		}
	);

## Next
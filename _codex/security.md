<center>[Previous](caching.md) | [Home](index.md) | [Next](features.md)</center>

Launchpad Security
==================

Launchpad implements several security features.  The first is a login attempts limiter.  This is configured in the Launchpad Settings.  If a user has too many failed attempts at a specific username in a given time from one IP address, they will be locked out.  If the user is able to successfully login with the specific username from the specific IP address, the lockout counter for that username+IP combo will be reset.

This feature is designed to fight brute force login attempts without hindering site owners and developers.  Typically, these are done on the admin user.  However, it can also be very inconvenient for users that don't access the site very often.  This IP+username combo helps alleviate instances where users from the same IP get locked out because one user failed too many times.

If your client gets locked out, all you have to do is save the Launchpad settings to clear all lockouts.

The rest of Launchpad's security features are built into the HTML5 Boilerplate htaccess file.  These special additions add [the 5G Blacklist](http://perishablepress.com/5g-blacklist-2013/) and some best practices like denying directory listings, hidden files, and WordPress's wp-includes files.

Thanks for your interest in Launchpad.  You have reached the end of this document.  You may be interested at looking through this [feature list](features.md).
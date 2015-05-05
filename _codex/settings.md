<center>[Previous](install.md) | [Home](index.md) | [Next](tools.md)</center>

Launchpad Settings
==================

Launchpad included many features that are typically relegated to plugins to avoid the unnecessary work of keeping plugins up to date and synced up between live and dev environments.  The Launchpad Settings page is used to control these options.

## Warnings

At the top of the settings page, you may see warnings.  These are to help you remember to fix issues before they become a problem.

## Security Settings

Launchpad's built-in brute force prevention mechanism allows you to set a number of failures before lockout and the amount of time a lockout prevents a login.  How you configure this should account for the type of users you have.  If you are using Launchpad for a personal site, you may want to set the lockout number low and the lockout time high.  If you are using Launchpad for a client site, you may want to use the opposite.  Any brute force prevention is better than nothing.

More information on how the brute force prevention works is in the [security section](security.md) of the codex.

## SEO and Social

The SEO and Social section allows you to enter common social media and SEO values.  If you have a Google Analytics ID, entering it into the appropriate field will automatically include the GA embed code and activate various events in the JavaScript.  If you have a FaceBook App ID and Admin IDs, entering those will create the appropriate OpenGraph meta tags.  Finally, if you use Twitter Cards, entering the Twitter Card Username will add the appropriate Twitter Card username meta tag.

## Caching

Launchpad's built-in content fragment caching can be disabled completely or configured to cache data for a specific amount of time.  Caching is desirable because it helps speed up page load time and decrease server strain by using a copy of HTML output instead of building the page from scratch ever time.  Launchpad also includes an extension to WordPress's database class to cache queries to avoid having to execute repetative database queries.  Both caches are directly affected by the Cache Duration value.  If you wish to disable caching altogether, set the Cache Duration to "Do Not Cache."  Otherwise, pick an appropriate time length to cache.  Currently, Launchpad clears page-specific cache files when the page is saved and clears all caches when settings are saved.  The cache duration will largely only matter to sites with dynamic content that is either user-generated, pulled remotely, or includes random output, but over-caching can be mitigated in the code by being smart about when you use fragment caching.

The next option is to include debug comments about caching.  These reveal whether the page fragment was generated from scratch or from cache, where the cache file is, and how old the cache file is.  Since this can potentially reveal senstitive paths on your server, it is best to disable debug messages for production sites.

## Offline Support

Launchpad can use HTML5 Application Cache to store files for offline use.  As Jake Archibald wrote, [Application Cache is a Douchebag](http://alistapart.com/article/application-cache-is-a-douchebag).  Launchpad does its best to mitigate issues with AppCache, but it is highly recommended to disable offline support until development is complete, then test the hell out of it to make sure it works the way you want it to if you want to have it enabled.

## HTML5 Boilerplate

If enabled, Launchpad will add a slightly modified version of the [HTML5 Boilerplate](http://html5boilerplate.com) .htaccess configuration to your .htaccess file.  This file attempts to set great defaults for Apache, including content caching, gzipping, and special MIME types.

## Organization Contact Info

These fields are commonly needed.  They are here by default.

## Social Media

These fields are commonly needed.  They are here by default.

## Login Customizations

Launchpad has a custom login screen that looks more like the new WordPress 3-dot-whatever darker admin theme.  You can specify a primary color that gets applied to buttons, a secondary color that gets applied to the border around the login field container, and a logo that replaces the WordPress logo.

Once you have configured Launchpad, you might want to generate default [site images](site-images.md).
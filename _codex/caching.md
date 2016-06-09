<center>[Previous](seo.md) | [Home](index.md) | [Next](security.md)</center>

Launchpad Cache
===============

WordPress is not a lightweight platform.  Much of WordPress's work involved heavy database use.  WordPress has done much to help the situation by building caching into many queries, but it still relies on developers to be smart about how code is executed.  To help speed things up even more, Launchpad has implemented a few caching techniques.

## Database Cache

In the right situations, database queries can be cached to a file and the results read back quickly without having to access the database.  Launchpad's database caching is done through a little-known WordPress feature that includes wp-content/db.php if it exists.  Launchpad's light-weight database caching makes use of this.  By simply replacing one function, we can use file-based caching to circumvent the database.  If you enable caching, db.php will be created.  In some server configurations, you MUST create a file at wp-content/db.php with permissions set to 777 so that Launchpad can populate db.php.

Launchpad's database cache attempts to honor the setting for cache timeouts.  However, many database queries are performed before certain functions are loaded, so the function does not have access to your settings.  So, by default, a query's cache timeout is 60 seconds.  Once the database function has access to site settings, the cache will honor the site settings.

## Page Fragment Cache

The most expensive action is rendering a page.  Since WordPress doesn't cache any of the page, all the work to build the page is performed every time the page is loading.  The simplest fix is to cache fragments of a page.  This is done through the <code>launchpad_get_template_part</code>.  <code>launchpad_get_template_part</code> is a pass through to <code>get_template_part</code>.  Pass the same parameters.  If there is no cache or the cache has expired, the page will compile normally and the output will be saved in a file.  If there is a cache, the cache will be served instead.

If you must serve 3rd-party dynamic content or randomized content, you must do one of the following:

1. Accept the the content will not change until the cache times out.
2. Run the code on the client (i.e. with JavaScript) so that it can be randomized.
3. Include your non-dynamic content with <code>launchpad_get_template_part</code> and your dynamic content with <code>get_template_part</code>, like this:

```php

get_header();	
launchpad_get_template_part('static-top-content', get_post_type());
get_template_part('randomized-listing', get_post_type());
launchpad_get_template_part('static-bottom-content', get_post_type());
get_footer();

```

There are other ways to achieve #3, so feel free to be creative.

Now that you understand how caching works, take a look at [Launchpad Security](security.md).
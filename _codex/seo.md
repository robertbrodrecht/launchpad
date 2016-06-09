<center>[Previous](admin.md) | [Home](index.md) | [Next](caching.md)</center>

Launchpad SEO
=============

SEO is a big part of running a website.  Unfortunately, it's also voodoo.  While Google has published a [SEO Starter Guide](http://static.googleusercontent.com/media/www.google.com/en/us/webmasters/docs/search-engine-optimization-starter-guide.pdf), it's impossible to counter the wealth of opinions about how to SEO.  Armed with this knowledge, Launchpad comes with SEO best-practices built in.

## The Basics Are There

Launchpad's basic framework is built to be SEO-friendly.  By using WordPress's built-in menus and progressive enhancement approach, Launchpad ensures that content is easily crawlable by spiders (that is, the programs that crawl web pages for content).  Launchpad encourages pretty URLs, which WordPress has built-in.  Starting with the right platform can take you a long way.

## Programatic Enhancements

A lot of SEO best practices are related to difficult programming techniques.  Since Launchpad knows about the user's content, a lot of this can be handled in code instead of requiring the user to make it work.  Here are SEO features that you won't have to worry about:

* XML Sitemaps are automatically generated per the [sitemaps.org specs](http://www.sitemaps.org/).
* Meta rel canonical automatically generated.
* Noindex,Follow on archive and listing pages.
* Google Analytics built into Launchpad Settings.
* OpenGraph and Twitter cards generated automatically.
* Meta descriptions are generated automatically if none are specifically created.

## Manual Enhancements

Launchpad comes with built-in SEO helpers.  When editing public posts, pages, and custom post types, a metabox appears that allows you to make SEO tweaks.  You can specify a specific SEO'd title without changing the page name, as well as creating a custom SEO'd meta description.  In both cases, you're limited to providing copy that is no longer than best practices suggest.

Once you publish a page and add a specific SEO keyphrase that you want to target your content at, helpful suggestions are provided to you.  The following checks are made:

* Keyphrase density.
* Content length.
* Ease of reading.
* Use of keywords in special places:
  * Title
  * Meta Description
  * Heading tags
  * The page slug

These checks are accompanied by suggestions on how to improve your content.

Now that you understand the included SEO modifications, you may be interested in finding out more about how Launchpad speeds up your site with [caching](caching.md).
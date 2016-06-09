<center>[Previous](post-types.md) | [Home](index.md) | [Next](hooks.md)</center>

Basic Template Editing in Launchpad
===================================

## Please Note

If you are hacking directly on Launchpad, you may have to update Launchpad manually.  So, you will save yourself some heartburn if you AVOID modifying any of these files:

* header.php (use the wp_head action)
* flexible/core/* (use flexible/custom/ instead, which can override flexible/core/ files)
* functions.php (use lib/custom/custom.php and [Launchpad Hooks](hooks.md))
* lib/core/*  (use lib/custom/custom.php and [Launchpad Hooks](hooks.md))
* lib/third-party/*  (you can add stuff, but be aware that Launchpad may add files in the future that collide with yours)
* js/main.js (use js/custom.js)
* js/launchpad.js (use js/custom.js)
* sass/_mixins.scss and sass/_grid.scss (create your own includes, though these may get prefixed eventually)

## Template Files

Launchpad embraces WordPress's file layout that has been espoused since the twentyeleven theme was released: master page files with includes to specific content files.  Therefore, the [template hierarchy](http://codex.wordpress.org/Template_Hierarchy) is preserved.

Launchpad ships with an index.php file that attempts to find the best possible content file.  By default, this will be content-posttype.php and will be overridden when more specific templates are found.  The hierarchy is something like this:

- Single Objects
  - content-slug.php
  - content-page-slug.php
  - content-posttype-postformat.php
  - content-postformat.php
  - content-single-posttype.php
  - content-single.php
- Taxonomy Pages
  - content-taxid-termslug.php
  - content-taxid.php
  - content-archive-posttype.php
  - content-archive.php
  - content-posttype.php
- Archive Pages
  - content-archive.php
  - content-posttype.php
- Search
  - content-search.php

The inclusion of template files in index.php is handled through Launchpad's caching system.  If you are working on a page that includes randomly generated content or content pulled from a third-party that requires different caching than the caching used for the page, you should break you template into chunks.  For example:

```php
get_header();	
launchpad_get_template_part('content', get_post_type() . '-top');
get_template_part('content', 'randomized_ad_space');
launchpad_get_template_part('content', get_post_type() . '-bottom');
get_footer();
```

The above code would pull a "top" and "bottom" portion for a specific post type that gets cached and a middle portion of the page that pulls randomized ad code.  If you require specific blocks inside of a content file to be randomized to achieve a particular layout, you might organize the parent template as: 

```php
get_header();	
get_template_part('content', get_post_type());
get_footer();
```

With the content-post_type.php file containing the complex layout:

```php
echo '<div class="row"><div class="col-25">';
launchpad_get_template_part('content', get_post_type() . '-left');
echo '</div><div class="col-50">';
get_template_part('content', 'randomized_ad_space');
echo '</div><div class="col-25">';
launchpad_get_template_part('content', get_post_type() . '-right');
echo '</div></div>';
```

If you use a <code>launchpad_get_template_part</code> inside of a template being included in a <code>launchpad_get_template_part</code>, the template part doesn't cache since it is already caching.

## Helpful Functions

Launchpad comes with a few helpful functions:

### file_get_contents_cache

<code>file_get_contents_cache($url, $cache_timeout = 60, $context = false)</code>

This function fetches a local or remote file (if your server supports).  Pass a string <code>$url</code> of the API.  Optionally, include an integer <code>$cache_timeout</code> that specifies how long the cache is valid.  Finally, if you need to pass any <code>stream_context_create</code> context, use the <code>$context</code> parameter.

**Note: If you are pulling a third-party API, it is highly suggested that you use Launchpad's <code>file_get_contents_cache</code> so that you don't blow up the API or get blocked!!!***

### format_postal_code

<code>format_postal_code($postal_code)</code>

If you are displaying a postal code, use <code>format_postal_code</code> to format.  Launchpad auto-formats US ZIP and ZIP+4 codes, Canadian codes, and UK codes.

### format_phone

<code>format_phone($number = '', $mask = '(###) ###-####', $ext = ' x', $country = '+# ')</code>

If you are displaying phone numbers, use <code>format_phone</code>.  Pass the phone number, an optional mask, an optional extension separator, and an optional country code format mask.

### launchpad_auto_paginate

<code>launchpad_auto_paginate()</code>

In generic cases, Launchpad can automatically handle your pagination via <code>launchpad_auto_paginate()</code>.

Now that you are familiar with basic template editing in Launchpad, you can get more advanced with [Launchpad Hooks](hooks.md).
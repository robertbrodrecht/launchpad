<center>[Previous](post-types.md) | [Home](index.md) | Next</center>

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

Launchpad ships with an index.php file that attempts to use "content-post_type.php" where "post_type" is the post type "code" sent as the first parameter to <code>register_post_type</code> (e.g. post or page for WordPress's built-in post types).  By default this is handled through Launchpad's caching system.  If you are working on a page that includes randomly generated content or content pulled from a third-party that requires different caching than the caching used for the page, you should break you template into chunks.  For example:

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

**Note: If you are pulling a third-party API, it is highly suggested that you use Launchpad's <code>file_get_contents_cache</code> so that you don't blow up the API or get blocked!!!***

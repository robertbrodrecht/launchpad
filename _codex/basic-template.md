Basic Template Editing in Launchpad
===================================

Launchpad embraces WordPress's file layout that has been espoused since the twentyeleven theme was released: master page files with includes to specific content files.

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
<?php

/**
 * Main page content
 *
 * The main content of the page inside the loop.  To include different content types,
 * use a duplicate of this file with content-post_type.php
 *
 * @package 	Launchpad
 * @since		1.0
 */

global $wp_query;

// Figure out the appropriate content-main template to use.
$content_type = launchpad_determine_best_template_file($post, 'main');

// Whether to use a sidebar.
$launchpad_use_sidebar = ($post->post_type === 'post' && is_active_sidebar('blog_sidebar'));
$launchpad_use_sidebar = apply_filters('launchpad_use_sidebar', $launchpad_use_sidebar, $post);

// If the sidebar position is not explicitly above, it goes below.
if($launchpad_use_sidebar && $launchpad_use_sidebar !== 'above') {
	$launchpad_use_sidebar = 'below';
}

// Whether to add classes to push/pull so the desktop look doesn't match source order.
$launchpad_sidebar_coerce = $post->sidebar_coerce;
$launchpad_sidebar_coerce = apply_filters('launchpad_coerce_sidebar', $launchpad_sidebar_coerce, $post);

// By default, we're not coercing.  So, left + above or right + below result in no additional class.
$launchpad_add_sidebar_class = '';

// If the user wants a left sidebar with the sidebar last in source, coerce it to the left.
if($launchpad_sidebar_coerce === 'left' && $launchpad_use_sidebar === 'below') {
	$launchpad_add_sidebar_class = 'sidebar-left';

// If the user wants a right sidebar with the sidebar first in source, coerce it to the right.
} else if($launchpad_sidebar_coerce === 'right' && $launchpad_use_sidebar === 'above') {
	$launchpad_add_sidebar_class = 'sidebar-right';
}

// If we're using a sidebar, output the container.
if($launchpad_use_sidebar) {
?>

		<div class="content-with-sidebar <?= $launchpad_add_sidebar_class ?>">
			<?php if($launchpad_use_sidebar === 'above') { ?>
			<aside class="sidebar-content">
				<?php do_action('launchpad_sidebar', $post); ?>
			</aside>
			<?php } ?>
			<section class="main-content">
<?php
}

if(!have_posts()) {

?>
			<article>
				<h1>Sorry!</h1>
				<p>No matching content was found.</p>
			</article>

<?php

} else {
	
	$use_template = locate_template('content-main' . ($content_type ? '-' . $content_type : '') . '.php', false, false);
	
	while(have_posts()) {
		the_post();
		
		if($use_template) {
			get_template_part('content-main', $content_type);
		} else {
?>
			<article>
				<header>
					<?php 
						
						$header = '';
						
						if(has_post_thumbnail()) {
							$header .= '<figure><a href="' . get_permalink($post->ID) . '">' . get_the_post_thumbnail($post->ID, (is_single() || is_singular() ? 'large' : 'medium')) . '</a></figure>';
						}
						
						$header .= '<h1><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></h1>';
						
						$header = apply_filters('launchpad_post_header_string', $header, $post);
						echo $header;
						
					?>
				</header>
				<section>
					<?php
						
						$content = '';
						$is_excerpt = true;
						if(is_home() || is_archive()) {
							if(has_excerpt($post->ID)) {
								$excerpt = $post->post_excerpt;
							} else {
								$excerpt = apply_filters('the_content', $post->post_content);
								$excerpt = str_replace('&nbsp;', '', $excerpt);
								$excerpt = preg_replace('/<div.*?class="wp-caption.*?>.*?<\/div>/', '', $excerpt);
								$excerpt = trim(strip_tags($excerpt));
								$excerpt = explode('<!--more-->', $excerpt);
								if(count($excerpt) > 1) {
									$excerpt = $excerpt[0];
								} else {
									$excerpt = explode("\n", $excerpt[0]);
									
									if(strlen($excerpt[0]) < 140) {
										$excerpt = array(implode(' ', $excerpt));
									}
									
									if(count($excerpt) == 1) {	
										$excerpt[0] = preg_replace('/(.*?\.\s.*?\.).*/', '$1', $excerpt[0]);
									}
									
									$excerpt = $excerpt[0];
								}
								if(is_array($excerpt)) {
									$excerpt = $excerpt[0];
								}
								if($excerpt) {
									$excerpt = apply_filters('the_content', $excerpt);
								}
								
							}
							$content = $excerpt;
						} else {
							$content = apply_filters('the_content', $post->post_content);
							$is_excerpt = false;
						}
						$content = apply_filters('launchpad_post_content_string', $content, $post, $is_excerpt);
						echo $content;
						
					?>
					<?php edit_post_link('Edit', '<p class="edit-link-container">', '</p>'); ?>
				
				</section>
				<?php 
					
					launchpad_flexible_content($post, 'main'); 
					
				?>
			</article>
<?php 
		}
	}
}

// Add pagination.
launchpad_auto_paginate();

// If we are using a sidebar, close out the container.
if($launchpad_use_sidebar) {
	
	?>
	
			</section>
			<?php if($launchpad_use_sidebar === 'below') { ?>
			<aside class="sidebar-content">
				<?php do_action('launchpad_sidebar', $post); ?>
			</aside>
			<?php } ?>
		</div>
	<?php
}
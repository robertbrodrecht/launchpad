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

if(!have_posts()) {

?>
			<article>
				<h1>Sorry!</h1>
				<p>No matching posts were found.</p>
			</article>

<?php

}

while(have_posts()) {
	the_post();

?>
			<article>
				<header>
					<?php if(has_post_thumbnail()) { ?>

					<figure>
						<?php the_post_thumbnail(); ?>

					</figure>
					<?php } ?>

					<h1><a href="<?php echo the_permalink() ?>"><?php the_title(); ?></a></h1>
				</header>
				<section>
					<?php the_content(); ?>
					<?php edit_post_link('Edit', '<p class="edit-link-container">', '</p>'); ?>
				
				</section>
				<?php
				
				// Handle the flexible content.
				// Get the post types.
				$post_types = launchpad_get_post_types();
				
				// If there is flexible content for our current post type, render the flexible content.
				if(isset($post_types) && isset($post_types[$post->post_type]['flexible'])) {
					
					// Loop the flexible types.
					foreach($post_types[$post->post_type]['flexible'] as $flexible_type => $flexible_details) {
						
						// This is using the WordPress location as a signal for where the content will go.
						// I'm not entirely sure this is "good" or "smart," but I'm doing it anyway.
						if($flexible_details['location'] !== 'sidebar') {
							
							// Get the post meta value for the current flexible type.
							$flexible = get_post_meta($post->ID, $flexible_type, true);
							
							// If there is any matching post meta, we need to render a field.
							if($flexible) {
								
								// Loop the values of the flexible content.
								foreach($flexible as $flex) {
									
									// Pull out key information from the flexible type.
									list($flex_type, $flex_values) = each($flex);
									$flexible_prototype = $flexible_details['modules'][$flex_type];
									
									// Use "include locate_template" so that variables are still in scope.
									switch($flex_type) {
										case 'accordion':
											include locate_template('flexible/accordion.php');
										break;
										case 'link_list':
											include locate_template('flexible/link_list.php');
										break;
										case 'section_navigation':
											include locate_template('flexible/section_navigation.php');
										break;
										case 'simple_content':
											include locate_template('flexible/simple_content.php');
										break;
									}
								}
							}
						}
					}
				}
				
				?>

			</article>
<?php 

}

// Add pagination.
launchpad_auto_paginate();

?>
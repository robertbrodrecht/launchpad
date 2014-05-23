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
			<section>
				<h1>Sorry!</h1>
				<p>No matching posts were found.</p>
			</section>

<?php

}

while(have_posts()) {
	the_post();

?>
			<section>
				<h1><a href="<?php echo the_permalink() ?>"><?php the_title(); ?></a></h1>
				<?php 
					
					the_content();
					
				?>
				<?php edit_post_link('Edit', '<p>', '</p>'); ?> 
				<?php
				
				$post_types = launchpad_get_post_types();
				
				if(isset($post_types) && isset($post_types[$post->post_type]['flexible'])) {
					foreach($post_types[$post->post_type]['flexible'] as $flexible_type => $flexible_details) {
						if($flexible_details['location'] !== 'sidebar') {
							$flexible = get_post_meta($post->ID, $flexible_type, true);
							
							foreach($flexible as $flex) {
								list($flex_type, $flex_values) = each($flex);
								$flexible_prototype = $flexible_details['modules'][$flex_type];
								
								
								
								switch($flex_type) {
									case 'accordion':
										include locate_template('flexible/accordion.php');
									break;
									case 'link_list':
										include locate_template('flexible/link_list.php');
									break;
									case 'simple_content':
										include locate_template('flexible/simple_content.php');
									break;
								}
							}
						}
					}
				}
				
				?>
			</section>

<?php } ?>

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
			</section>

<?php } ?>

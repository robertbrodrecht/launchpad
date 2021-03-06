<section class="flexible flexible-<?php echo sanitize_title($flex_type) ?>">
<?php if($flex_values['title']) { ?>

	<h1><?php echo $flex_values['title'] ?></h1>
<?php } ?>
<?php 

if($flex_values['description']) {
	echo apply_filters('the_content', $flex_values['description']);
} 

?>
<?php if($flex_values['links']) { ?>

	<ul class="flexible-links-list">
		<?php
		
		$links = new WP_Query(
				array(
					'ignore_sticky_posts' => true,
					'post__in' => $flex_values['links'],
					'post_type' => 'any',
					'order' => 'ASC',
					'orderby' => 'post__in'
				)
			);
		
		foreach($links->posts as $link) {
				?>

				<li><a href="<?php echo get_permalink($link->ID) ?>"><?php echo $link->post_title ?></a></li>
				
				<?php
		} 
		
		?>

	</ul>
<?php } ?>

</section>
<?php

// Do some cleanup.

$links = null;

unset($links);
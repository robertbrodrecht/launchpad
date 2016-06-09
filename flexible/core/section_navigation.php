<?php 

$start = $flex_values['start'];
$depth = $flex_values['depth'];

if((int) $start === 0) {
	$ancestors = get_post_ancestors($post->ID);
	if(!$ancestors) {
		$ancestors = array($post->ID);
	}
} else {
	$ancestors = array($post->post_parent ? $post->post_parent : $post->ID);
}

$start = array_pop($ancestors);

$children = get_children(
		array(
			'ignore_sticky_posts' => true,
			'post_parent' => $start,
			'numberposts' => -1,
			'post_type' => 'any',
			'post_status' => 'publish'
		)
	);
	
if($children) {

?>

<section class="flexible-<?php echo sanitize_title($flex_type) ?>">
<?php if($flex_values['title']) { ?>
	<h1><?php echo $flex_values['title'] ?></h1>
<?php } ?>

	<ul class="flexible-section-navigation">
		<?php 
		
		
		
		foreach($children as $child) {
			?>

			<li>
				<a href="<?php echo get_permalink($child->ID) ?>"><?php echo $child->post_title ?></a>
				<?php 
				
				if($depth > 0) {
				
					$grand_children = get_children(
						array(
								'ignore_sticky_posts' => true,
								'post_parent' => $child->ID,
								'numberposts' => -1,
								'post_type' => 'any',
								'post_status' => 'publish'
							)
						);
					
					if($grand_children) {
					
						?>

						<ul>
							<?php foreach($grand_children as $grand_child) { ?>

								<li>
									<a href="<?php echo get_permalink($grand_child->ID) ?>"><?php echo $grand_child->post_title ?></a>
									
									<?php
									
									if($depth > 1) {
				
										$great_grand_children = get_children(
											array(
													'ignore_sticky_posts' => true,
													'post_parent' => $grand_child->ID,
													'numberposts' => -1,
													'post_type' => 'any',
													'post_status' => 'publish'
												)
											);
										
										if($great_grand_children) {
										
										?>

											<ul>
												<?php foreach($great_grand_children as $great_grand_child) { ?>

													<li><a href="<?php echo get_permalink($great_grand_child->ID) ?>"><?php echo $great_grand_child->post_title ?></a></li>
												<?php } ?>
											</ul>
										<?php
										
										}
									}
									
									?>

								</li>
							<?php } ?>

						</ul>
						<?php
					}
				}
				
				?>

			</li>
			<?php
		} 
		
		?>

	</ul>
</section>
<?php

	// Do some cleanup.
	
	$children = null;
	$child = null;
	
	$grand_children = null;
	$grand_child = null;
	
	$great_grand_children = null;
	$great_grand_child = null;
	
	unset($children, $child, $grand_children, $grand_child, $great_grand_children, $great_grand_child);
}
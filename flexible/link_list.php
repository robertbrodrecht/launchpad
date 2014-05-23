<section class="flexible-<?php echo sanitize_title($flex_type) ?>">
<?php if($flex_values['title']) { ?>
	<h1><?php echo $flex_values['title'] ?></h1>
<?php } ?>
<?php 

if($flex_values['description']) {
	echo wpautop($flex_values['description']);
} 

?>
<?php if($flex_values['links']) { ?>
	<ul class="flexible-links-list">
		<?php 
		
		foreach($flex_values['links'] as $link) {
			$link = get_post($link);
			if($link) {
				?>
				
				<li><a href="<?php echo get_permalink($link->ID) ?>"><?php echo $link->post_title ?></a></li>
				
				<?php
			}
		} 
		
		?>
	</ul>
<?php } ?>
</section>

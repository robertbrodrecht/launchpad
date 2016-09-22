<section class="flexible flexible-<?php echo sanitize_title($flex_type) ?>">
	<h1><?php echo $flex_values['title'] ?></h1>
	<?php 
	
	echo apply_filters('the_content', $flex_values['editor']);
	
	?>

</section>

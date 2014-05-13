<section class="flexible-<?php echo sanitize_title($flex_type) ?>">
	<h1><?php echo $flex_values['title'] ?></h1>
	<?php 
	
	echo wpautop($flex_values['editor']);
	
	?>
</section>

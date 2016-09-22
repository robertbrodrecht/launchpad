
<section class="flexible flexible-<?php echo sanitize_title($flex_type) ?>">
<?php if($flex_values['title']) { ?>

	<h1><?php echo $flex_values['title'] ?></h1>
<?php } ?>
<?php 

if($flex_values['description']) {
	echo apply_filters('the_content', $flex_values['description']);
} 

?>
<?php if($flex_values['accordion']) { ?>
	<?php $ids = array(); ?>

	<dl class="flexible-accordion-list">
		<?php foreach($flex_values['accordion'] as $accordion) { ?>
			<?php 
			
			$id = sanitize_title($accordion['title']);
			
			$id_tmp = $id;
			
			$cnt = 1;
			
			while(in_array($id_tmp, $ids)) {
				$id_tmp = $id . '-' . $cnt;
				$cnt++;
			}
			
			$id = $id_tmp;
			$ids[] = $id;
			
			?>

			<dt id="<?php echo $id ?>"><a href="#<?php echo $id ?>"><?php echo $accordion['title'] ?></a></dt>
			<dd>
				<?php echo apply_filters('the_content', $accordion['description']); ?>
			</dd>
		<?php } ?>

	</dl>
<?php } ?>

</section>
<?php

// Do some cleanup.

$accordion = null;

unset($accordion);
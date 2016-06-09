
<section class="flexible-<?php echo sanitize_title($flex_type) ?>">
<?php if($flex_values['title']) { ?>

	<h1><?php echo $flex_values['title'] ?></h1>
<?php } ?>
<?php 

$uuid = uniqid();

if($flex_values['description']) {
	echo apply_filters('the_content', $flex_values['description']);
} 

$gallery_items = array();
foreach($flex_values['gallery'] as $gallery_item) {
	if((int) $gallery_item['image']) {
		$gallery_item['image'] = wp_get_attachment_image_src($gallery_item['image'], 'large');
		if($gallery_item['image']) {
			$gallery_items[] = array(
				'caption' => $gallery_item['caption'],
				'src' => $gallery_item['image'][0],
				'width' => $gallery_item['image'][1],
				'height' => $gallery_item['image'][2],
				'id' => 'flex-gallery-' . $uuid . '-' . uniqid()
			);
		}
	}
}

?>
<?php if($gallery_items) { ?>
	<?php if(count($gallery_items) > 1) { ?>
	<figure class="flexible-gallery-container">
		<?php foreach($gallery_items as $imgcount => $image) { ?>
			<input type="radio" name="toggle-<?= $uuid ?>" id="toggle-<?= $uuid ?>-<?= $imgcount ?>" class="ui-toggle slide-toggle-<?= $imgcount ?>" <?= $imgcount == 0 ? ' checked="checked"' : '' ?>>
		<?php } ?>
	<?php } ?>
		<?php foreach($gallery_items as $imgcount => $image) { ?>
			<figure class="flexible-gallery-item slide-target-<?= $imgcount ?>">
				<img src="<?= $image['src'] ?>" width="<?= $image['width'] ?>" height="<?= $image['height'] ?>" alt="">
				<?php if($image['caption']) { ?>
				<figcaption>
					<?= $image['caption'] ?>
				</figcaption>
				<?php } ?>
			</figure>
		<?php } ?>

	<?php if(count($gallery_items) > 1) { ?>
		<figcaption>
			<ul>
			<?php foreach($gallery_items as $imgcount => $image) { ?>
				<li class="slide-indicator-<?= $imgcount ?>">
					<label for="toggle-<?= $uuid ?>-<?= $imgcount ?>">
						<?= $imgcount+1 ?>
					</label>
				</li>
			<?php } ?>
			</ul>
		</figcaption>
	</figure>
	<?php } ?>
<?php } ?>

</section>
<?php

// Do some cleanup.

$gallery_items = null;
$gallery_item = null;
$uuid = null;
$image = null;
$imgcount = null;

unset($gallery_items, $gallery_item, $uuid, $image, $imgcount);
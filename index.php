<?php

/**
 * Base file
 *
 * The majority of the template-deciding work can be done here.
 *
 * @package 	Launchpad
 * @since		1.0
 */

get_header();	
launchpad_get_template_part('content', get_post_type());
?>
<div class="row no-gutter small-to-100 medium-to-gutter">
	<div class="column-25 medium-to-50" style="background: #FCC">real: 1; push: 1</div>
	<div class="column-25 medium-to-50 push-50" style="background: #CFC">real: 2; push: 3</div>
	<div class="column-50 medium-to-100 pull-25" style="background: #CCF">real: 3; push: 2</div>
</div>
<?php
get_footer();
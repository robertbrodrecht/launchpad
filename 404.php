<?php

/**
 * Base file
 *
 * The majority of the template-deciding work can be done here.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */


get_header();

?>

			<section class="404">
				<h1>Page Not Found</h1>
				<p>
					Sorry, but the page you were trying to view does not exist.  You may be interested in one of the following options:
				</p>
				<ul>
					<?php if($_SERVER['HTTP_REFERER']) { ?>

					<li><a href="<?php echo $_SERVER['HTTP_REFERER'] ?>">Go back and try again</a></li>

					<?php } ?>
					<li>Check for typos: <?php

						$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
						if ($_SERVER["SERVER_PORT"] != "80") {
						    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
						}  else {
						    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
						}
						
						$pageURL = preg_replace('/[\&\?]launchpad_ajax=true/', '', $pageURL);
						
						echo $pageURL;

					?></li>
					<li><a href="/">Start over at the home page</a></li>
				</ul>
				<?php
				
				$s = implode(' ', array_slice(explode('/', $pageURL), 3));
				
				$s = preg_replace('/\.....?$/', '', $s);
				$s = preg_replace('/[^A-Z0-9]/i', ' ', $s);
				$s = preg_replace('/([A-Z])/', ' $1', $s);
				$s = preg_replace('/([A-Z])([0-9])/i', '$1 $2', $s);
				$s = trim($s);
				$s = preg_replace('/\s+/', ' ', $s);
				
				query_posts('s=' . $s);
				if(have_posts()) {
				
				?>

				<p>
					We also ran a search for "<kbd><?php echo $s ?></kbd>" you to see if anything relevant turned up:
				</p>
				<ul>
				<?php while(have_posts()) { the_post(); ?>
					<li>
						<a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a>
					</li>

				<?php } ?>
				</ul>

				<?php } ?>

			</section>

<?php

get_footer();
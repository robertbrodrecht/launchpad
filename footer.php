<?php

/**
 * Page Footer
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */

global $site_options;


?>

		</section>
		<footer id="footer">
			<div>
				<section>
					<h1 class="frontload">Site Links</h1>
					<nav>
						<?php launchpad_wp_nav_menu(array('theme_location' => 'footer', 'menu_class' => 'nav-footer')); ?>
					</nav>
				</section>
				
				<section class="vcard">
					<h1 class="fn"><?php echo $site_options['organization_name']; ?></h1>
					<p class="adr">
						<span class="street-address"><?php echo $site_options['organization_address']; ?></span><br>
						<span class="locality"><?php echo $site_options['organization_city']; ?></span>,
						<span class="region"><?php echo $site_options['organization_state']; ?></span>
						<span class="postal-code"><?php echo $site_options['organization_zip']; ?></span>
					</p>
					<p>
						<span class="tel"><span class="type">Phone</span> <?php echo format_phone($site_options['organization_phone']); ?></span><br>
						<span class="fax"><span class="type">Fax</span> <?php echo format_phone($site_options['organization_fax']); ?></span>
					</p>
					<p>Social Media</p>
					<ul>
						<li><a href="<?php echo $site_options['organization_facebook']; ?>" class="url" rel="me" target="_blank">Facebook</a></li>
						<li><a href="<?php echo $site_options['organization_twitter']; ?>" class="url" rel="me" target="_blank">Twitter</a></li>
						<li><a href="<?php echo $site_options['organization_pinterest']; ?>" class="url" rel="me" target="_blank">Pinterest</a></li>
						<li><a href="<?php echo $site_options['organization_instagram']; ?>" class="url" rel="me" target="_blank">Instagram</a></li>
						<li><a href="<?php echo $site_options['organization_google']; ?>" class="url" rel="me" target="_blank">Google+</a></li>
					</ul>
				</section>
				
			</div>
		</footer>
		<?php
		
		if(stristr($_SERVER['HTTP_HOST'], 'dev') !== false) {
			echo "		<script>window.dev = true;</script>\n";
		}
		
		?>
		<script src="/js/jquery-1.11.0.min.js"></script>
		<script src="/js/main-min.js"></script>
		<?php wp_footer(); ?>
	</body>
</html>
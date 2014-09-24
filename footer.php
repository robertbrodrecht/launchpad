<?php

/**
 * Page Footer
 *
 * @package 	Launchpad
 * @since		1.0
 */

global $site_options;


?>
		</section>
		<footer id="footer">
			<section>
				<h1 class="frontload">Site Links</h1>
				<nav>
					<?php launchpad_wp_nav_menu(array('theme_location' => 'footer', 'menu_class' => 'nav-footer', 'container' => false)); ?>
				</nav>
			</section>
			<section class="vcard">
				<?php if(isset($site_options['organization_name']) && $site_options['organization_name']) { ?>
				<h1 class="fn"><?php echo $site_options['organization_name']; ?></h1>

				<?php } ?>
				<?php
				
				$adr = '';
				
				if(isset($site_options['organization_address']) && $site_options['organization_address']) {
					$adr .= '<span class="street-address">' . $site_options['organization_address'] .'</span><br>';
				}
				
				if(isset($site_options['organization_city']) && $site_options['organization_city']) {
					$adr .= '<span class="locality">' . $site_options['organization_city'] .'</span>';
				}
				
				if(isset($site_options['organization_state']) && $site_options['organization_state']) {
					if(isset($site_options['organization_city']) && $site_options['organization_city']) {
						$adr .= ', ';
					}
					
					$adr .= '<span class="region">' . $site_options['organization_state'] .'</span><br>';
				}
				
				if(isset($site_options['organization_zip']) && $site_options['organization_zip']) {
					if(isset($site_options['organization_city']) && $site_options['organization_city'] && (!isset($site_options['organization_state']) || !$site_options['organization_state'])) {
						$adr .= ', ';
					}
					
					$adr .= '<span class="postal-code">' . $site_options['organization_zip'] .'</span>';
				}
				
				if($adr) {
				
				?>
				
				<p class="adr">
					<?php echo $adr ?>
				</p>
				<?php } ?>
				
				<?php
				
				$phone = '';
				
				if(isset($site_options['organization_phone']) && $site_options['organization_phone']) {
					$phone .= '<span class="tel"><span class="type">Phone</span> ' .  format_phone($site_options['organization_phone']) . '</span><br>';
				}
				
				if(isset($site_options['organization_fax']) && $site_options['organization_fax']) {
					$phone .= '<span class="fax"><span class="type">Phone</span> ' .  format_phone($site_options['organization_fax']) . '</span><br>';
				}
				
				if($phone) {
								
				?>
				<p>
					<span class="tel"><span class="type">Phone</span> <?php echo format_phone($site_options['organization_phone']); ?></span><br>
					<span class="fax"><span class="type">Fax</span> <?php echo format_phone($site_options['organization_fax']); ?></span>
				</p>
				<?php } ?>
				
				<?php
				
				$phone = '';
				
				if(isset($site_options['organization_phone']) && $site_options['organization_phone']) {
					$phone .= '<span class="tel"><span class="type">Phone</span> ' .  format_phone($site_options['organization_phone']) . '</span><br>';
				}
				
				if(isset($site_options['organization_fax']) && $site_options['organization_fax']) {
					$phone .= '<span class="fax"><span class="type">Phone</span> ' .  format_phone($site_options['organization_fax']) . '</span><br>';
				}
				
				$social_links = array(
					'Facebook' => 'organization_facebook',
					'Twitter' => 'organization_twitter',
					'Pinterest' => 'organization_pinterest',
					'Instagram' => 'organization_instagram',
					'Google+' => 'organization_google',
				);
				
				$social = '';
				
				foreach($social_links as $social_title => $social_key) {
					if(isset($site_options[$social_key]) && $site_options[$social_key]) {
						$social .= '<li><a href="' . $site_options[$social_key] . '" class="url" rel="me" target="_blank">' . $social_title . '</a></li>';
					}
				}
				
				if($social) {
								
				?>
				<p class="frontload">Social Media</p>
				<ul>
					<?php echo $social; ?>
				</ul>
				<?php } ?>
				
			</section>
		</footer>
		<?php
		
		// If no plugins are active, we can do scripts the right way.
		// By right, I mean put them at the bottom so they don't block rendering.
		if(!get_option('active_plugins')) {
			if(stristr($_SERVER['HTTP_HOST'], '.dev') !== false || stristr($_SERVER['HTTP_HOST'], '.git') !== false) {
				echo "<script>window.dev = true;</script>\n";
			}
		?>
		<script src="/js/jquery-1.11.0.min.js"></script>
		<script src="/js/main-min.js"></script>

		<?php 
		
		} 
		
		wp_footer(); 
		
		?>

	</body>
</html>
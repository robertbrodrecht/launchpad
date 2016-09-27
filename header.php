<?php

/**
 * The default page header
 *
 * @package 	Launchpad
 * @since		1.0
 */

global $site_options;

$add_this_id = $site_options['add_this_id'];

$excerpt = launchpad_seo_excerpt();

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php launchpad_title(true); ?></title>
		<?php
		
		wp_head(); 
		
		
		?>

	</head>
	<body <?php body_class('no-js'); ?><?= $add_this_id ? ' data-addthis="' . $add_this_id . '"' : '' ?>>
		<script>document.body.className = document.body.className.replace(/no-js/g, 'js');</script>
		<!--[if IE 9]><span class="msie-9"></span><![endif]-->
		<?php if(!isset($_GET['mpdf'])) { ?>
		<input type="checkbox" id="mobile-nav-toggle" class="ui-toggle">
		<a href="#main" id="skip-to-content">Skip to Content</a>
		<?php } ?>
		<header id="header" role="banner">
			<h1 id="logo"><a href="/"><?php bloginfo('name') ?></a></h1>
		</header>
		<nav id="navigation" role="navigation">
			<label for="mobile-nav-toggle" class="ui-toggle-target hamburger">Show Menu</label>
			<?php launchpad_wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'nav-header', 'container' => false)); ?>
		</nav>
		<section id="main" class="main" role="main" aria-live="polite" aria-relevant="text">

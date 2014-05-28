<?php

/**
 * The default page header
 *
 * @package 	Launchpad
 * @since		1.0
 */

global $site_options;

$ajax = '';
if($site_options['ajax_page_loads'] === true) {
	$ajax = 'true';
}

$offline = '';
if($site_options['offline_support'] === true) {
	$offline = '/manifest.appcache';
}

$excerpt = launchpad_seo_excerpt();

?><!DOCTYPE html>
<html lang="en"<?php echo $offline ? ' manifest="' . $offline . '"' : '' ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php launchpad_title(true); ?></title>
		
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<link rel="stylesheet" type="text/css" id="screen-css" media="screen, projection, handheld, tv" href="/css/screen.css">
		<link rel="stylesheet" type="text/css" media="print" href="/css/print.css">
		<?php if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/design-tweaks.css')) { ?>
		
		<link rel="stylesheet" type="text/css" id="screen-css" media="screen, projection, handheld, tv" href="/design-tweaks.css">
		<?php } ?>
		
		<link rel="icon" href="/images/icons/favicon.png">
		<link rel="icon" href="/images/icons/favicon_2x.png" media="(-webkit-min-device-pixel-ratio: 2)">
		
		<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name') ?> RSS Feed" href="/feed/">
		<?php if(is_single() || is_page()) { ?>
		
		<link rel="canonical" href="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		<?php } ?>
		
		<link rel="apple-touch-icon" sizes="57x57"   href="/images/icons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="72x72"   href="/images/icons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/images/icons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/images/icons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/images/icons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/images/icons/apple-touch-icon-152x152.png">
		
		<link href="/images/icons/startup-iphone-320x460.jpg" rel="apple-touch-startup-image" media="(device-width: 320px)">
		<link href="/images/icons/startup-iphone4-640x920.jpg" rel="apple-touch-startup-image" media="(device-width: 320px) and (-webkit-min-device-pixel-ratio: 2)">
		<link href="/images/icons/startup-iphone5-640x1096.jpg" rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-min-device-pixel-ratio: 2)">
		<link href="/images/icons/ipad-portrait-768x1004.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait)">
		<link href="/images/icons/ipad-landscape-1024x748.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape)">
		<link href="/images/icons/ipad-retina-portrait-1536x2008.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)">
		<link href="/images/icons/ipad-retina-landscape-2048x1496.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)">
		
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name') ?>">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		
		<meta name="description" content="<?php echo $excerpt; ?>">
		
		<?php
		
		if(!$wp_query->is_single && !$wp_query->is_singular) {
			echo '<meta name="robots" content="noindex, follow">';	
		}
		
		?>
		
		
		<meta property="og:title" content="<?php launchpad_title(true); ?>">
		<meta property="og:description" content="<?php echo $excerpt; ?>">
		<meta property="og:type" content="website">
		<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		<meta property="og:site_name" content="<?php bloginfo('name') ?>">
<?php
		
		if(has_post_thumbnail()) {
			$thumbnail = get_post_thumbnail_id();
			$thumbnail = wp_get_attachment_image_src($thumbnail, 'opengraph');
			if($thumbnail) {
				?>
				<meta property="og:image" content="<?php echo $thumbnail[0] ?>">
				<meta property="og:image:width" content="<?php echo $thumbnail[1] ?>">
				<meta property="og:image:height" content="<?php echo $thumbnail[2] ?>">
				
				<?php
			}
		}
		
?>
		<?php if(defined('GA_ID') && GA_ID != '') { ?>

		<script>
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '<?php echo GA_ID ?>']);
			_gaq.push(['_trackPageview']);
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		<?php } ?>

	</head>
	<body <?php body_class('no-js'); ?> data-ajax="<?php echo $ajax; ?>">
		<script>document.body.className = document.body.className.replace(/no-js/g, 'js');</script>
		<!--[if IE 9]><span class="msie-9"></span><![endif]-->
		<!--[if IE 8]><span class="msie-8"></span><![endif]-->
		<a href="#main" id="skip-to-content">Skip to Content</a>
		<header id="header" role="banner">
			<h1 id="logo"><a href="/"><?php bloginfo('name') ?></a></h1>
		</header>
		<nav id="navigation" role="navigation">
			<a href="#navigation" class="hamburger">Show Menu</a>
			<?php launchpad_wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'nav-header', 'container' => false)); ?>
		</nav>
		<section id="main" role="main" aria-live="polite" aria-relevant="text">
			
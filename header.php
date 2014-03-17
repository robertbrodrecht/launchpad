<?php

/**
 * The default page header
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */

global $site_options;

$ajax = '';
if($site_options['ajax_page_loads'] === true) {
	$ajax = 'true';
}

$offline = '';
if($site_options['offline_support'] === true) {
	$offline = '/manifest.appcache';
	if(is_404() || is_user_logged_in()) {
		$offline = '/manifest.obsolete.appcache';
	}
}

$excerpt = launchpad_excerpt();

?><!DOCTYPE html>
<html lang="en"<?php echo $offline ? ' manifest="' . $offline . '"' : '' ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php launchpad_title(true); ?></title>
		
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<link rel="stylesheet" type="text/css" id="screen-css" media="screen, projection, handheld, tv" href="/css/screen.css">
		<link rel="stylesheet" type="text/css" media="print" href="/css/print.css">
		
		<link rel="icon" href="/images/icons/favicon.png">
		<link rel="icon" href="/images/icons/favicon_2x.png" media="(-webkit-min-device-pixel-ratio: 2)">
		
		<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name') ?> RSS Feed" href="/feed/">
		<link rel="canonical" href="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		
		<link rel="apple-touch-icon" href="/images/icons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/images/icons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/images/icons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/images/icons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/images/icons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/images/icons/apple-touch-icon-152x152.png">
		
		<!-- SEEMS WRONG ON THESE WxH -->
		<link rel="apple-touch-startup-image" media="(max-device-width: 480px) and not (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/startup-iphone-320x460.png">
		<link rel="apple-touch-startup-image" media="(max-device-width: 480px) and     (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/startup-iphone4-640x920.png">
		<link rel="apple-touch-startup-image" media="(max-device-width: 548px) and     (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/startup-iphone5-640x1096.png">
		<link rel="apple-touch-startup-image" media="(min-device-width: 768px) and (orientation: portrait)  and not (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/ipad-mini-portrait-768x1004.jpg">
		<link rel="apple-touch-startup-image" media="(min-device-width: 768px) and (orientation: landscape) and not (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/ipad-mini-landscape-1024x748.jpg">
		<link rel="apple-touch-startup-image" media="(min-device-width: 768px) and (orientation: portrait)  and     (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/ipad-portrait-2008x1536.jpg">
		<link rel="apple-touch-startup-image" media="(min-device-width: 768px) and (orientation: landscape) and     (-webkit-min-device-pixel-ratio: 2)" href="/images/icons/ipad-landscape-1496x2048.jpg">
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
		<meta name="description" content="<?php echo $excerpt; ?>">
		
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name') ?>">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
		
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
			<?php launchpad_wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'nav-header')); ?>
		</nav>
		<section id="main" role="main" aria-live="polite" aria-relevant="text" data-ajax-target="<?php echo $ajax; ?>">
			
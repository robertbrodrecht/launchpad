<?php
/**
 * SEO Related Features
 * 
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Add to Robots
 * 
 * Hooks into robots.txt to add the XML Sitemap Index
 *
 * @param		str $txt The existing robots.txt content.
 * @since		1.0
 */
function launchpad_robots_txt($txt) {
	$url = get_bloginfo('url');
	return "Sitemap: $url/sitemap-index.xml\n\n" . $txt;	
}
add_filter('robots_txt', 'launchpad_robots_txt');


/**
 * Create XML Sitemaps
 * 
 * Depending on whether this is a request for the index or a single sitemap, display the sitemap.
 *
 * @since		1.0
 */
function launchpad_sitemap() {
	global $wpdb;
	$wp_post_table = $wpdb->posts;
	$ignore_types = "'nav_menu_item', 'attachment', 'revision'";
	$posts_per_page = 10000;
	$offset = $posts_per_page * ((int) $_GET['sitemap'] - 1);
	$url = get_bloginfo('url');
	
	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	
	// Render the sitemap index since no sitemap "page" ID is set.
	if(!isset($_GET['sitemap']) || !$_GET['sitemap']) {
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		$results = $wpdb->get_results(
				"SELECT CEIL(COUNT(ID)/$posts_per_page) as total FROM $wp_post_table " . 
				" WHERE post_type NOT IN ($ignore_types) AND post_status='publish'"
			);
			
		for($i = 0; $i < $results[0]->total; $i++) {
			echo '<sitemap><loc>' . $url . '/sitemap-' . ($i+1) . '.xml</loc></sitemap>';
		}
		
		echo '</sitemapindex>';
		
	// Render a sitemap since a sitemap "page" ID is set.
	} else {
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		$results = $wpdb->get_results(
				"SELECT ID, DATE_FORMAT(post_modified, '%Y-%m-%d') as last_mod FROM $wp_post_table WHERE " .
				" post_type NOT IN ($ignore_types) AND  post_status='publish' ORDER BY `post_modified` " . 
				" DESC LIMIT $offset, $posts_per_page"
			);
		
		foreach($results as $row) {
			echo '<url><loc>' . get_permalink($row->ID) . '</loc><lastmod>' . $row->last_mod . '</lastmod></url>';
		}
		
		echo '</urlset>';
	}
	exit;
}
add_action('wp_ajax_sitemap', 'launchpad_sitemap');
add_action('wp_ajax_nopriv_sitemap', 'launchpad_sitemap');
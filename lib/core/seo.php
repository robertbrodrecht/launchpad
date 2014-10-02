<?php

/**
 * SEO Related Features
 * 
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Add SEO Meta Boxes
 *
 * @since		1.0
 */
function launchpad_add_seo_metabox() {
	// Get all registered post types.
	$post_types = get_post_types(array(), 'objects');
	
	// For SEO-able post types, create metaboxes for SEO.
	foreach($post_types as $post_type => $post_details) {
		switch($post_type) {
			case 'attachment':
			case 'revision':
			case 'nav_menu_item':
			break;
			default:
				
				if($post_details->_builtin || $post_details->publicly_queryable) {
					add_meta_box(
						'launchpad-seo',
						'SEO and Social Media Options',
						'launchpad_seo_meta_box_handler',
						$post_type,
						'normal',
						'low'
					);
				}
			break;
		}
	}
}
if(is_admin()) {
	add_action('add_meta_boxes', 'launchpad_add_seo_metabox', 10, 1);
}


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
	
	// Get the posts table name.
	$wp_post_table = $wpdb->posts;
	
	// These types don't need to appear in a sitemap.
	$ignore_types = "'nav_menu_item', 'attachment', 'revision'";
	
	// Add any post types that don't have human-accessible pages.
	$post_types = get_post_types(array(), 'objects');
	foreach($post_types as $post_type => $post_details) {
		if(!$post_details->_builtin && !$post_details->publicly_queryable) {
			$ignore_types .= ", '$post_type'";
		}
	}
	
	// Include 10,000 posts per sitemap file.
	// The max is 50,000 but we need to stay under 10MB uncompressed.
	// So, we're just going to hope 10K is low enough to stay under the limit.
	$posts_per_page = 10000;
	
	// Figure out the current offset.
	$offset = $posts_per_page * ((int) $_GET['sitemap'] - 1);
	
	// The blog's root URL.
	$url = get_bloginfo('url');
	
	// This is a common requirement for both sitemap indexes and sitemaps.
	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	
	// Render the sitemap index since no sitemap "page" ID is set.
	if(!isset($_GET['sitemap']) || !$_GET['sitemap']) {
		// This is the index container.
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		// Figure out how many sitemap files are required to house every URL.
		$results = $wpdb->get_results(
				"SELECT CEIL(COUNT(ID)/$posts_per_page) as total FROM $wp_post_table " . 
				" WHERE post_type NOT IN ($ignore_types) AND post_status='publish'"
			);
		
		// Create a sitemap link for each.
		for($i = 0; $i < $results[0]->total; $i++) {
			echo '<sitemap><loc>' . $url . '/sitemap-' . ($i+1) . '.xml</loc></sitemap>';
		}
		
		// Close the container.
		echo '</sitemapindex>';
		
	// Render a sitemap since a sitemap "page" ID is set.
	} else {
		
		// This is the container for a single sitemap.
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		// Get the ID and last modified for every post within the offset.
		$results = $wpdb->get_results(
				"SELECT ID, DATE_FORMAT(post_modified, '%Y-%m-%d') as last_mod FROM $wp_post_table WHERE " .
				" post_type NOT IN ($ignore_types) AND  post_status='publish' ORDER BY `post_modified` " . 
				" DESC LIMIT $offset, $posts_per_page"
			);
		
		// Loop them and create the url entry.
		foreach($results as $row) {
			echo '<url><loc>' . get_permalink($row->ID) . '</loc><lastmod>' . $row->last_mod . '</lastmod></url>';
		}
		
		// Close the container.
		echo '</urlset>';
	}
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_sitemap', 'launchpad_sitemap');
	add_action('wp_ajax_nopriv_sitemap', 'launchpad_sitemap');
}



/**
 * SEO Meta Box Handler
 * 
 * Generates all the code to handle the SEO metabox.  It's a horrible mess of stuff.  Sorry.
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @since		1.0
 */
function launchpad_seo_meta_box_handler($post, $args) {
	
	// Load the TextStatistics library to handle calculating text complexity.
	locate_template('lib/third-party/TextStatistics.php', true, true);
	
	// Stopwords to ignore when calculating statistics.
	$stopwords = explode(',', "a,about,above,after,again,against,all,am,an,and,any,are,aren't,as,at,be,because,been,before,being,below,between,both,but,by,can't,cannot,could,couldn't,did,didn't,do,does,doesn't,doing,don't,down,during,each,few,for,from,further,had,hadn't,has,hasn't,have,haven't,having,he,he'd,he'll,he's,her,here,here's,hers,herself,him,himself,his,how,how's,i,i'd,i'll,i'm,i've,if,in,into,is,isn't,it,it's,its,itself,let's,me,more,most,mustn't,my,myself,no,nor,not,of,off,on,once,only,or,other,ought,our,ours,ourselves,out,over,own,same,shan't,she,she'd,she'll,she's,should,shouldn't,so,some,such,than,that,that's,the,their,theirs,them,themselves,then,there,there's,these,they,they'd,they'll,they're,they've,this,those,through,to,too,under,until,up,very,was,wasn't,we,we'd,we'll,we're,we've,were,weren't,what,what's,when,when's,where,where's,which,while,who,who's,whom,why,why's,with,won't,would,wouldn't,you,you'd,you'll,you're,you've,your,yours,yourself,yourselves");
	
	// If the post is published, we can run some calculations.
	if($post->post_status === 'publish') {
		
		// Grab the full page.
		$full_content = @file_get_contents(get_permalink($post->ID));
		
		// Grab the title title from the page.
		preg_match_all('|<title>(.*?)</title>|s', $full_content, $title);
		
		// If there is a title, save it.
		if(isset($title[1][0])) {
			$title_natural = $title[1][0];
			$title = strtolower($title_natural);
		} else {
			$title = false;
		}
		
		// Remove all the HTML to try to get at the raw text of the page.
		$cont = strip_tags($full_content);
		$cont = preg_replace('|<script.*?>.*?</script>|s', '', $cont);
		$cont = preg_replace('|<style.*?>.*?</style>|s', '', $cont);
		$cont = preg_replace("/(\r\n|\r|\n)/", ' ', $cont);
		$cont = strtolower($cont);
		$cont = html_entity_decode($cont);
		
		// Remove the stopwords.
		foreach($stopwords as $stopword) {
			$cont = preg_replace('/\b' . $stopword . '\b/', '', $cont);
		}
		
		// Clean up messy spaces.
		$cont = preg_replace('/\s+/', ' ', $cont);
		
		// Perform a word count.
		$word_count = str_word_count($cont);
		
		// Run TextStatistics.
		$txt_stat = new TextStatistics();
		$txt_flesch = $txt_stat->flesch_kincaid_reading_ease($post->post_content);
		$txt_gunning_fog = $txt_stat->gunning_fog_score($post->post_content);
	}
	
	// Get the saved SEO metadata for the page.
	$meta = get_post_meta($post->ID, 'SEO', true);
	
	// Generate a word count.
	$post_word_count = str_word_count(strip_tags($post->post_content));
	
	// Generate the "default" excerpt for the post.
	$seo_exerpt = launchpad_seo_excerpt(64, false, $post->ID);

	// Render the form fields for managing SEO.
	
	?>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>In order to run automated tests, you must enter your primary keyword / keyphrase that you want to target in your copy.</p>
				</div>
			</div>
			<label>
				Page Target Keyword / Keyphrase
				<input type="text" name="launchpad_meta[SEO][keyword]" value="<?php echo esc_textarea(@$meta['keyword']) ?>">
			</label>
		</div>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>In some cases, you may want your page's title tag to contain specific keywords without having those keywords in the title of your page inside of WordPress.  If you like, enter a more SEO-friendly title here.  If you don't enter one, the page name will be used.  Here are some tips:</p>
					<ul>
						<li>Keep the title less than 70 characters.</li>
						<li>Put the primary keyword near the start of the title.</li>
						<li>Craft your title so that people want to click them.</li>
						<li>Try to make your title a call to action, a promise, or question that the page fulfills.</li>
						<li>Vary page titles.  Don't use the same page title on other pages.</li>
						<li>Don't use your page title in your SEO description.</li>
					</ul>
				</div>
			</div>
			<label>
				SEO'd Title
				<input type="text" name="launchpad_meta[SEO][title]" value="<?php echo esc_textarea(@$meta['title']) ?>" maxlength="70">
			</label>
		</div>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>This field contains the meta description content.  Meta description is seen on SERPs (search engine results page) if the search query matches terms in the meta description.  A meta description should be a maximum of 160 characters as SERPs typically truncate the description to 160 characters.  As of 2009, Google does not use meta description in page rank algorithms.  If you do not enter a meta description, one will be automatically generated when the page loads based on either the first 32 words of the post, via the post excerpt, or via the text before the "more" tag.</p>
				</div>
			</div>
			<div>
			<label>
				SEO Description
				<textarea name="launchpad_meta[SEO][meta_description]" rows="10" cols="50" class="small-text" maxlength="160"><?php echo esc_textarea(@substr($meta['meta_description'], 0 , 160)) ?></textarea>
			</label>
			</div>
		</div>
		<?php 
		
		// If we have published and a keyword has been set, we can display some statistics.
		if($post->post_status === 'publish' && trim(@$meta['keyword'])) {
			
			// Get the page permalink.
			$permalink = get_permalink($post->ID);
			
			// Prep the keyword.
			$keyword = trim($meta['keyword']);
			$keyword_orig = $keyword;
			
			// Clean up the keyword.
			$keyword = preg_replace("/(\r\n|\r|\n)/", ' ', $keyword);
			$keyword = preg_replace('/\s+/', ' ', $keyword);
			$keyword = strtolower($keyword);
			$keyword = html_entity_decode($keyword);
			
			// Create a Regular Expression to use for bolding keywords in the snippet preview.
			$keyword_bold_preg = '/(' . str_replace(' ', '|', $keyword) . ')/i';
			
			// Get a keyword count to show keyphrase vs keyword usage.
			$keyword_count = substr_count($cont, $keyword);
			
			// Get the number of matches for each word in the keyphrase.
			$keyword_count_each = preg_match_all($keyword_bold_preg, $cont);
			
			// Calculate the denstity for keyphrase and each keyword.
			$percent = round($keyword_count/$word_count*100, 2);
			$percent_each = round($keyword_count_each/$word_count*100, 2);
			
			// Show the text statistics.
		?>
		<hr class="launchpad-hr">
		<h3 class="launchpad-sub-section-heading">SERP Preview: Would you click through to read your content?</h3>
		<div class="launchpad-serp-preview">
			<div id="serp-heading" data-post-title="<?php echo esc_html($post->post_title); ?>" class="launchpad-serp-heading"><?php echo preg_replace($keyword_bold_preg, '<strong>$1</strong>', substr($title_natural, 0 , 70)) . (strlen($title_natural) > 70 ? '...' : ''); ?></div>
			<div class="launchpad-serp-url"><?php echo preg_replace($keyword_bold_preg, '<strong>$1</strong>', preg_replace('|^http://|i','', $permalink)) ?></div>
			<div id="serp-meta" data-post-excerpt="<?php echo esc_html(launchpad_excerpt(100, false, $post->ID)); ?>" class="launchpad-serp-meta"><?php echo preg_replace($keyword_bold_preg, '<strong>$1</strong>', substr($seo_exerpt, 0 , 155)) . (strlen($seo_exerpt) > 155 ? ' ...' : ''); ?></div>
		</div>
		<div class="launchpad-table-columns">
			<div>
				<h3 class="launchpad-sub-section-heading">Statistics (Save to Update)</h3>
				<dl class="launchpad-inline-listing launchpad-statistics-list">
					<dt><span class="dashicons <?php echo $word_count >= 300 ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Total Words On Page</dt>
					<dd>
						<?php echo $word_count; ?>
					</dd>
					<dt><span class="dashicons <?php echo $keyword_count >= 1 ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Exact Keyphrase Count</dt>
					<dd>
						<?php echo $keyword_count; ?>
					</dd>
					<dt><span class="dashicons <?php echo $percent >= .5 && $percent <= 2 ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Exact Keyphrase Density</dt>
					<dd>
						<?php echo $percent ?>%
						<?php
						
						if($percent > 10) {
							echo ' (Your Copy May Sound Spammy)';
						} else if($percent > 2) {
							echo ' (High, but <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t Stress Over It</a>)';
						} else if($percent < .5) {
							echo ' (Low, but <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t Stress Over It</a>)';
						}
						
						?>
					</dd>
					<dt><span class="dashicons <?php echo $keyword_count_each > count(explode(' ', $keyword)) ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Keywords Count</dt>
					<dd>
						<?php echo $keyword_count_each; ?>
					</dd>
					<dt><span class="dashicons <?php echo $percent >= .5*count(explode(' ', $keyword)) && $percent <= 2*count(explode(' ', $keyword)) ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Keywords Density</dt>
					<dd>
						<?php echo $percent_each ?>%
						<?php
						
						if($percent > 2*count(explode(' ', $keyword))) {
							echo ' (High, but <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t Stress Over It</a>)';
						} else if($percent < .5*count(explode(' ', $keyword))) {
							echo ' (Low, but <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t Stress Over It</a>)';
						}
						
						?>
					</dd>
					<dt><span class="dashicons <?php echo $txt_flesch > 60 ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Flesch Reading Ease</dt>
					<dd>
						<?php 
						
						echo $txt_flesch;
						
						if($txt_flesch < 30) {
							echo ' (Very Confusing)';
						} else if($txt_flesch < 50) {
							echo ' (Difficult)';
						} else if($txt_flesch < 60) {
							echo ' (Fairly Difficult)';
						} else if($txt_flesch < 70) {
							echo ' (Standard)';
						} else if($txt_flesch < 80) {
							echo ' (Fairly Easy)';
						} else if($txt_flesch < 90) {
							echo ' (Easy)';
						} else {
							echo ' (Very Easy)';
						}
							
						?>
					</dd>
					<dt><span class="dashicons <?php echo $txt_gunning_fog < 11 ? 'dashicons-yes' : 'dashicons-no' ?>"></span> Gunning Fog</dt>
					<dd>
						<?php 
						
						echo $txt_gunning_fog;
						
						if($txt_gunning_fog < 6) {
							echo ' (Very Easy)';
						} else if($txt_gunning_fog < 10) {
							echo ' (Standard)';
						} else {
							echo ' (Difficult)';
						}
						
						?>
					</dd>
				</dl>
			</div>
			<div>
				<h3 class="launchpad-sub-section-heading">Suggestions (Save to Update)</h3>
				<dl class="launchpad-inline-listing launchpad-statistics-list">
					<?php
					
						if(preg_match_all($keyword_bold_preg, $permalink) >= count(explode(' ', trim($keyword)))) {
							$test_results = 'No suggestions.';
							$pass = true;
						} else {
							$test_results = 'Consider using your keywords in the slug.';
							$pass = false;
						}
					
					?>
					<dt><span class="dashicons <?php echo $pass ? 'dashicons-yes' : 'dashicons-visibility' ?>"></span> URL</dt>
					<dd><?php echo $test_results ?></dd>
					<?php
						
						$pass = false;
						if(!$title) {
							$test_results = 'Make sure you have a title on the page!!!';
						} else {
							if(strlen($title) > 70) {
								$test_results = 'Consider shortening your title.';
							} else {
								$keyword_pos = stripos($title, $keyword);
								if($keyword_pos === false) {
									$test_results = 'Consider using this keyword in your title.';
								} else if($keyword_pos/strlen($title)*100 > 35) {
									$test_results = 'Consider placing your keyword closer to the start of the title.';
								} else {
									$test_results = 'No suggestions.';
									$pass = true;
								}
							}
						}
						
					?>
					<dt><span class="dashicons <?php echo $pass ? 'dashicons-yes' : 'dashicons-visibility' ?>"></span> Title</dt>
					<dd><?php echo $test_results ?></dd>
					<?php
					
						if(substr_count(strtolower(@$meta['meta_description']), strtolower($keyword))) {
							$test_results = 'No suggestions.';
							$pass = true;
						} else {
							$test_results = 'Consider using your keyword in the SEO description.';
							$pass = false;							
						}
					
					?>
					<dt><span class="dashicons <?php echo $pass ? 'dashicons-yes' : 'dashicons-visibility' ?>"></span> SEO Description</dt>
					<dd><?php echo $test_results ?></dd>
					<?php
						$pass = true;
						
						if($percent < .5) {
							$test_results = 'Increase keyword usage to .5% to 2%. <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t stress over it too much</a>.';
							$pass = false;
						} else if($percent < 2) {
							$test_results = 'Acceptable keyword usage.';
						} else if($percent > 10) {
							$test_results = 'Your copy may be in danger of being viewed as "keyword stuffing."  You should consider fine tuning your copy if you have over-used your keyphrase to the point where the copy is difficult to read.  Use your best judgement.';
							$pass = false;
						} else {
							$test_results = 'Consider decreasing keyword density to under 2%. <a href="https://www.youtube.com/watch?v=Rk4qgQdp2UA" target="_blank">Don\'t stress over it too much</a>.';
							$pass = false;
						}
						
						if(!preg_match('/\<h[123456]\>.*?(' . trim($keyword) . ').*?\<\/h[123456]>/i', $full_content)) {
							$test_results .= ' Consider placing your keyword in a heading tag (H1-H6).';
							$pass = false;
						}
						
						if($post_word_count < 300) {
							$test_results .= ' Consider increasing your content length to 300+ words.  You currently have ' . $post_word_count . ' word' . ($post_word_count === 1 ? '' : 's') . ' in your main content (not accounting for flexible content modules).';
							$pass = false;
						}
						
						if($txt_flesch < 60 || $txt_gunning_fog > 10) {
							$test_results .= ' Consider whether the reading difficulty is too high.';
							$pass = false;
						}
					
					?>
					<dt><span class="dashicons <?php echo $pass ? 'dashicons-yes' : 'dashicons-visibility' ?>"></span> Content</dt>
					<dd><?php echo $test_results ?></dd>
				</dl>
			</div>
		</div>
	<?php
	
	// If we haven't published, show a message saying to publish to get statistics.
	} else {
		echo '<p><strong>Save the post with the published status and specify a Page Target Keyword / Keyphrase to get an SEO report.</strong></p>';
	}
}
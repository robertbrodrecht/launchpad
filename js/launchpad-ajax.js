/**
 * Manage Ajax Page Loads
 *
 * @since	1.0
 */
(function($) {
	if($ === undefined) {
		if(window.dev && window.console) {
			console.log('jQuery failed to load in time.  Launchpad JavaScript is disabled.');
		}
		return;
	}
	
	var body = $(document.body),
		ignoreLastClick = false,
		gaon = ($('#google-analytics').length > 0);
	
	function handlePopState(e) {
		if(history.ready) {
			// For some reason, anchor jumps are triggering popStates.
			// This helps make that not happen.
			if(ignoreLastClick) {
				ignoreLastClick = false;
				return;
			}
			
			if(window.dev) {
				console.log('Handling popState.');
			}
			
			e.preventDefault();
			handleLinkClick.call(
					$('<a href="' + location.href + '"></a>').get(0),
					{preventDefault: function() {}, pushState: false}
				);
		} else {
			if(window.dev) {
				console.log('History is ready for pushState.');
			}
			history.ready = true;
		}
	}
	
	function handleLinkClick(e) {
		var me = $(this),
			href = me.attr('href');
		
		// For some reason, anchor jumps are triggering popStates.
		// This helps make that not happen.
		if(href.indexOf('#') === 0) {
			ignoreLastClick = true;
			return;
		}
		
		if(href.indexOf('wp-admin') !== -1 || href.indexOf('wp-login') !== -1) {
			return;
		}
		
		if(
			(href.substr(0, 1) === '/' || location.href.split('/')[2] === href.split('/')[2]) &&
			!href.match(/\.(jpg|jpeg|gif|png|pdf|doc|docx)$/)
		) {
			if(window.dev) {
				console.log('Initializing ajax request.');
			}
			
			e.preventDefault();
			body.trigger('ajaxRequestStart');
			
			$.get(
					href + (href.indexOf('?') === -1 ? '?' : '&') + 'launchpad_ajax=true'
				)
				.done(
					function(data) {
						var title = data.match(/<title>(.*?)<\/title>/),
							bodyclass = data.match(/<body.*?class="(.*?)".*?>/),
							htmlclass = data.match(/<html.*?class="(.*?)".*?>/),
							content = $(
									$.parseHTML(
										'<div>' +
										data.replace(/[\s\S]+<body.*?>/, '').replace(/<\/body>[\s\S]+/, '') +
										'</div>'
									)
								);
						
						if(content.length) {
							if(window.dev) {
								console.log('Swapping ajax results.');
							}
							if(title && title.length > 1) {
								document.title = $('<span>' + title[1] + '</span>').text();
							}
							if(htmlclass && htmlclass.length > 1) {
								document.documentRoot.className = htmlclass[1];
							}
							if(bodyclass && bodyclass.length > 1) {
								document.body.className = bodyclass[1];
							}
							
							window.scrollTo(0, 0);
							
							body.html(content.html());
							
							if(gaon) {
								ga('send', 'pageview', {'page': href,'title': document.title});
							}
							
							if(history.pushState && e.pushState !== false) {
								if(window.dev) {
									console.log('Handling pushState.');
								}
								history.pushState('', '', href);
							}
						} else {
							if(window.dev) {
								console.log('Ajax results were not compatible with theme. Manually redirecting.');
								location.href = href;
							}
						}
					}
				)
				.fail(
					function() {
						if(window.dev) {
							console.log('Ajax request failed.');
						}
						location.href = href;
					}
				).always(
					function() {
						if(window.dev) {
							console.log('Ajax request complete.');
						}
						body.trigger('ajaxRequestEnd');
					}
				);
		}
	}
	
	body.on('click', 'a', handleLinkClick);
	$(window).on('popstate', handlePopState);
})(window.jQuery, this);
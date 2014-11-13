/*jslint browser: true, devel: true, sloppy: true, todo: true, white: true */

/**
 * Front End JavaScript
 *
 * Handles all the JavaScript needs of the front end.
 *
 * @package	Launchpad
 * @since	1.0
 */


/**
 * A Closure to Prevent Collision
 *
 * @since	1.0
 */
(function($, window, undefined) {
	if($ === undefined) {
		if(window.dev && window.console) {
			console.log('jQuery failed to load in time.  Launchpad JavaScript is disabled.');
		}
		return;
	}
	
	var gaon = ($('#google-analytics').length > 0);
	
	
	/**
	 * Attempt to Manage ApplicationCache Refresh Intelligently
	 *
	 * @since	1.0
	 */
	function initMonitorLogin() {
		var login_interval = false,
			cache_update_interval = false,
			logged_in = false,
			is_first_run = true;
		
		function updateCache() {
			if(logged_in && navigator.onLine && $('html[manifest]').length) {
				if(window.dev) {
					console.log('Forcing AppCache to update because user is logged in.');
				}
				window.applicationCache.update();
			}
		}
		
		function checkLogin() {
			if(navigator.onLine) {
				$.get('/api/?action=user_logged_in').done(
					function(data) {
						logged_in = data;
						if(logged_in) {
							if(!cache_update_interval) {
								if(window.dev) {
									console.log('Starting to invalidate AppCache every 60 seconds because user is logged in.');
								}
								updateCache();
								cache_update_interval = setInterval(updateCache, 60000);
							}
						} else if(cache_update_interval) {
							if(window.dev) {
								console.log('Removing AppCache invalidation interval because user is not logged in.');
							}
							clearInterval(cache_update_interval);
							cache_update_interval = false;
						} else if(is_first_run) {
							if(window.dev) {
								console.log('User is not logged in.  Allowing AppCache to self-manage.');
							}
						}
						is_first_run = false;
					}
				);
			} else {
				clearInterval(cache_update_interval);
			}
		}
		
		checkLogin();
		login_interval = setInterval(checkLogin, 60000);
	}
	
	
	/**
	 * Create Height Match Data from Children Data
	 *
	 * @since	1.0
	 */
	function reinitHeightMatch() {
		$('[data-height-match-children]').each(
			function() {
				var me = $(this),
					cur_height_match = me.attr('data-height-match-children');
				me.removeAttr('data-height-match-children')
				.attr('data-height-match-group', cur_height_match)
				.children().each(
					function() {
						$(this).attr('data-height-match', '');
					}
				);
			}
		);
	}
	
	
	/**
	 * Manage Height Matching
	 *
	 * @since	1.0
	 */
	function initHeightMatch() {
		function heightMatch() {
			$('[data-height-match-group]').each(
				function() {
					var me = $(this),
						height = 0,
						lowest_width = me.attr('data-height-match-group'),
						is_match = false,
						matchable = me.children('[data-height-match]');
					
					if(me.attr('data-height-match-query')) {
						matchable = me.find(me.attr('data-height-match-query') + '[data-height-match]');
					}
					
					if(isNaN(+lowest_width)) {
						is_match = window.matchMedia(lowest_width);
						if(is_match !== false) {
							is_match = is_match.matches;
						} else {
							is_match = true;
						}
					} else {
						is_match = (+lowest_width < $(document.body).width());
					}
					
					if(is_match) {
						matchable.css('height', 'auto').each(
								function() {
									var h = $(this).outerHeight();
									if(h > height) {
										height = h;
									}
								}
							).css('height', height);
					} else {
						matchable.css('height', 'auto');
					}
				}
			);
		}
		
		reinitHeightMatch();
		
		setTimeout(heightMatch, 1);
		
		$(window).on(
				'resizeEnd',
				heightMatch
			);
			
		$(document.body).on(
				'ajaxRequestEnd load',
				function() {
					setTimeout(heightMatch, 100);
				}
			);
	}
	
	
	/**
	 * Functionality Related to Offline Support.
	 *
	 * @since	1.0
	 */
	function initOfflineSupport() {
		// Watch to see if the user logs in or out so we can manage their cache.
		initMonitorLogin();
	
		// Handle online/offline transitions.
		$(window).on(
				'offline',
				function() {
					if(window.dev) {
						console.log('Browser is offline.');
					}
					$(document.body).append(
						$('<div id="offline-notification" class="system-notification">✱ You are currently offline.</div>')
					);
				}
			).on(
				'online',
				function() {
					if(window.dev) {
						console.log('Browser is online.');
					}
					$('#offline-notification').remove();
					try {
						applicationCache.update();
					} catch(err) {
						if(window.dev) {
							console.log('Attempting to force an update to the appCache threw an error, but we caught it.', err);
						}
					}
				}
			);
		
		// Handle appcache updates.
		if(!navigator.onLine) {
			$(window).trigger('offline');
		} else {
			if(window.dev) {
				console.log('Browser lacks cache. Initiating appcache update.');
			}
			$(window).trigger('online');
		}
		
		// Add listeners for cache updates.
		applicationCache.addEventListener(
				'updateready',
				function() {
					if(applicationCache.status === applicationCache.UPDATEREADY) {
						if(window.dev) {
							console.log('Appcache updated. Swapping now.');
						}
						applicationCache.swapCache();
					}
				}
			);
		
		// If in dev mode, show helpful event logs.
		if(window.dev) {
			applicationCache.addEventListener(
					'progress',
					function(e) {
						console.log('Appcache loading ' + e.loaded + ' of ' + e.total + '.');
					}
				);
			applicationCache.addEventListener(
					'obsolete',
					function() {
						console.log('Appcache has been obsoleted.');
					}
				);
		}
	}
	
	
	/**
	 * Manage Ajax Page Loads
	 *
	 * @since	1.0
	 */
	function initAjax() {
		var body = $(document.body),
			ignoreLastClick = false;
		
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
	}
	
	
	/**
	 * Detect CSS Transition Support
	 *
	 * @since	1.0
	 */
	function detectTouchCapable() {
		if(window.supports.touch !== undefined) {
			if(window.supports.touch) {
				$(document.body).addClass('touch');
			} else {
				$(document.body).addClass('no-touch');
			}
			return;
		}
		if(window.hasOwnProperty && window.hasOwnProperty('ontouchstart')) {
			$(document.body).addClass('touch');
			window.supports.touch = true;
		} else {
			$(document.body).addClass('no-touch');
			window.supports.touch = false;
		}
		
		if(gaon) {
			ga('set', 'metric1', window.supports.touch ? 1 : 0);
		}
	}
	
	/**
	 * Detect CSS Transition Support
	 *
	 * @since	1.0
	 */
	function detectTransitions() {
		var test;
		if(window.supports.transitions !== undefined) {
			if(window.supports.transitions) {
				$(document.body).addClass('css-transitions');
			}
			return;
		}
		test = document.createElement('p').style;
		if('transition' in test || 'WebkitTransition' in test || 'MozTransition' in test || 'msTransition' in test) {
			$(document.body).addClass('css-transitions');
			window.supports.transitions = true;
		} else {
			window.supports.transitions = false;
		}
		
		if(gaon) {
			ga('set', 'metric2', window.supports.transitions ? 1 : 0);
		}
	}
	
	/**
	 * Detect CSS Position Sticky
	 *
	 * @since	1.0
	 */
	function detectPositionSticky() {
		var test;
		if(window.supports.sticky !== undefined) {
			if(window.supports.sticky) {
				$(document.body).addClass('css-sticky');
			} else {
				$(document.body).addClass('css-not-sticky');
			}
			return;
		}
		test = $('<div style="position: absolute; position: -webkit-sticky; position: -moz-sticky; position: -ms-sticky; position: sticky; "></div>');
		
		if(test.css('position') !== 'absolute') {
			window.supports.sticky = true;
			$(document.body).addClass('css-sticky');
		} else {
			window.supports.sticky = false;
			$(document.body).addClass('css-not-sticky');
		}
		
		
		if(gaon) {
			ga('set', 'metric3', window.supports.sticky ? 1 : 0);
		}
	}
	
	/**
	 * Detect Screen DPI
	 *
	 * @since	1.0
	 */
	function detectDPI() {
		window.supports.dpi = 1;
		if(window.devicePixelRatio !== undefined) {
			window.supports.dpi = window.devicePixelRatio;
		}
		
		if(gaon) {
			ga('set', 'metric4', window.supports.dpi);
		}
	}
	
	/**
	 * Hide or show the grid
	 * 
	 * It is helpful to have a grid overlay when dealing with alignment.  This shows a grid when the user presses "G"
	 * if the site is deemed to be in development mode (indicated by .dev in the domain name).
	 *
	 * @param		object e The event object
	 * @since	1.0
	 */
	function handleGrid(e) {
		var win = $(window),
			body = $(document.body),
			coverel = win.height() > body.height() ? win : body,
			grid = body.css('line-height'), c, l;
		if(e.which === 71 && $(e.target).is('body')) {
			if(body.hasClass('grid')) {
				$('.grid-item').remove();
				body.removeClass('grid');
			} else {
				l = $('<p>');
				$(document.body).append(l);
				grid = +l.css('line-height').replace('px', '');
				l.remove();
				body.addClass('grid');
				for(c = grid, l = coverel.width(); c < l; c += grid) {
					body.append($('<div class="grid-item vertical"></div>').css('left', c + 'px'));
				}
				for(c = grid, l = coverel.height(); c < l; c += grid) {
					body.append($('<div class="grid-item horizontal"></div>').css('top', c + 'px'));
				}
			}
		}
	}
	
	
	/**
	 * Re-initialize the Front End
	 *
	 * Some events do not bubble, so we need to initialize them every time we load new content.  Also, we need to update body classes.
	 *
	 * @since	1.0
	 */
	function reinit() {
		if(window.dev) {
			console.log('Executing multi-run initialization.');
		}
		document.body.className = document.body.className.replace(/no-js/g, 'js');
		if(window.navigator.standalone) {
			document.body.className += ' apple-standalone';
		}
		detectDPI();
		detectPositionSticky();
		detectTransitions();
		detectTouchCapable();
		reinitHeightMatch();
		$(document.body).trigger('launchpadReinit');
	}
	
	
	/**
	 * Initialize the Front End
	 *
	 * @since	1.0
	 */
	function init() {
		var body = $(document.body),
			scrollingIsJanky = body.data('scroll-helper'),
			doNotSupport = [/MSIE [1234567]\.(?!.*IEMobile)/],
			l, i, startupImage = false;
		
		body.trigger('launchpadPreInit');
		
		window.supports = {};
		
		if(window.dev) {
			window.dev = (window.console && window.dev);
		}
		
		if(window.dev) {
			console.log('Executing first run initialization.');
		}
		
		if(!window._gaq) {
			window._gaq = [];
		}
		
		if(window.doNotSupportOverride) {
			doNotSupport = doNotSupportOverride;
		}
		
		for(i = 0, l = doNotSupport.length; i < l; i++) {
			if(navigator.userAgent.match(doNotSupport[i])) {
				$('#screen-css').remove();
				$('head').append($('<link rel="stylesheet" type="text/css" id="screen-css" media="screen, projection, handheld, tv" href="/css/unsupported.css">'));
				return;
			}
		}
		
		
		initHeightMatch();
		$(window).load(initHeightMatch);
		
		if($('[data-ajax="true"]').length && !!history.pushState) {
			window.supports.ajax = true;
			initAjax();
		} else {
			window.supports.ajax = false;
		}
		
		// Start up offline support code if needed.
		if(window.applicationCache && $('html[manifest]').length) {
			initOfflineSupport();
		}
		
		if(window.navigator.standalone && window.supports.ajax) {
			$('link[rel=apple-touch-startup-image]').each(
				function() {
					var me = $(this),
						media = me.attr('media');
					if(media && window.matchMedia(media).matches) {
						startupImage = me.attr('href');
					}
				}
			);
			
			if(startupImage) {
				startupImage = $('<img src="' + startupImage + '">').load(
					function() {
						setTimeout(
							function() {
								$('#apple-standalone-startup-image').animate(
									{'opacity': 0},
									750,
									function() {
										$(this).remove();
									}
								);
							}, 1000);
					}
				);
				body.append($('<div id="apple-standalone-startup-image"></div>'));
				$('#apple-standalone-startup-image').append(startupImage);
			}
		}
		
		body.on(
				'click',
				'*',
				function(e) {
					var i = $(this),
						href = i.attr('href'),
						nav = $('#navigation');
					if(!href) {
						return;
					}
					if(i.is('a.hamburger')) {
						e.preventDefault();
						nav.toggleClass('target');
						body.trigger(nav.hasClass('target') ? 'launchpadMenuOpen' : 'launchpadMenuClose');
						if(location.hash === '#navigation' && !nav.hasClass('target')) {
							location.hash = '';
						}
					} else if(window.navigator.standalone && !window.supports.ajax) {
						if(href.substr(0, 1) === '/' || location.href.split('/')[2] === href.split('/')[2]) {
							e.preventDefault();
							location.href = href;
						}
					} else {
						nav.removeClass('target');
						body.trigger('launchpadMenuClose');
						if(location.hash === '#navigation') {
							location.hash = '';
						}
					}
				}
			).on('ajaxRequestEnd', reinit);
		
		/**
		 * 60 Frames Per Second Scrolling
		 * 
		 * This is a supposed fix to allow 60FPS scrolling.  
		 * Enable at your own risk, probably when your scrolling gets janky.
		 *
		 * @link	http://www.thecssninja.com/javascript/follow-up-60fps-scroll
		 */
		if(scrollingIsJanky) {
			$(window).on(
					'scrollStart scrollEnd',
					function(e) {
						switch(e.type) {
							case 'scrollStart':
								$(document.body).append(
									$('<div id="launchpad-cover"></div>').css(
										{
											'-webkit-transform': 'translate3d(0,0,0)',
											'transform': 'translate3d(0,0,0)',
											'position': 'fixed',
											'top': '0',
											'right': '0',
											'left': '0',
											'bottom': '0',
											'opacity': '0',
											'z-index': '9',
											'pointer-events': 'none'
										}
									)
								);
							break;
							case 'scrollEnd':
								$('#launchpad-cover').remove();
							break;
						}
					}
				);
		}
		
		body.on(
			'click',
			'.flexible-accordion-list dt a',
			function(e) {
				e.preventDefault();
				$(this).closest('dt').toggleClass('target');
			}
		);
		
		if(window.dev === true) {
			$(document).on('keyup', 'body', handleGrid);
		}
		
		body.trigger('launchpadInit');
		reinit();
	}
	
	$(document).ready(init);
})(window.jQuery, this);


/**
 * Custom jQuery Events
 *
 * @since	1.0
 */
(function($, window, undefined) {
	'use strict';

	var customEvents = ['scrollStart', 'scrollEnd', 'resizeStart', 'resizeEnd'],
		timeoutResize = 250,
		timeoutScroll = 100;
	
	if($ === undefined) {
		return;
	}
	
	$.event.special.scrollStart = {
		enabled: true,
		setup: function() {
			var me = this,
				jqme = $(me),
				timer;
			
			function trigger() {
				$.event.dispatch.call(me, 'scrollStart');
			}
			
			if(typeof jqme.data('track-scroll') === 'undefined') {
				jqme.data('track-scroll-start', false);
			}
			
			jqme.bind('touchmove.scrollstart scroll.scrollstart', function() {
				if(!jqme.data('track-scroll-start')) {
					jqme.data('track-scroll-start', true);
					trigger();
				}
				
				clearTimeout(timer);
				timer = setTimeout(
						function() {
							jqme.data('track-scroll-start', false);
						}, timeoutScroll
					);
			});
		},
		teardown: function() {
			$(this).unbind('touchmove.scrollstart scroll.scrollstart');
		}
	};
	
	$.event.special.scrollEnd = {
		enabled: true,
		setup: function() {
			var me = this,
				jqme = $(me),
				timer;
			
			function trigger() {
				$.event.dispatch.call(me, 'scrollEnd');
			}
			
			if(typeof jqme.data('track-scroll-end') === 'undefined') {
				jqme.data('track-scroll-end', false);
			}
			
			jqme.bind('touchmove.scrollend scroll.scrollend', function() {
				if(!jqme.data('track-scroll-end')) {
					jqme.data('track-scroll-end', true);
				}
				
				clearTimeout(timer);
				timer = setTimeout(
						function() {
							jqme.data('track-scroll-end', false);
							trigger();
						}, timeoutScroll
					);
			});
		},
		teardown: function() {
			$(this).unbind('touchmove.scrollend scroll.scrollend');
		}
	};
	
	$.event.special.resizeStart = {
		enabled: true,
		setup: function() {
			var me = this,
				jqme = $(me),
				timer;
				
			function trigger() {
				$.event.dispatch.call(me, 'resizeStart');
			}
			
			if(typeof jqme.data('track-resize') === 'undefined') {
				jqme.data('track-resize-start', false);
			}
			
			jqme.bind('resize.resizestart', function() {
				if(!jqme.data('track-resize-start')) {
					jqme.data('track-resize-start', true);
					trigger();
				}
				
				clearTimeout(timer);
				timer = setTimeout(
						function() {
							jqme.data('track-resize-start', false);
						}, timeoutResize
					);
			});
		},
		teardown: function() {
			$(this).unbind('resize.resizestart');
		}
	};
	
	$.event.special.resizeEnd = {
		enabled: true,
		setup: function() {
			var me = this,
				jqme = $(me),
				timer;
			
			function trigger() {
				$.event.dispatch.call(me, 'resizeEnd');
			}
			
			if(typeof jqme.data('track-resize-end') === 'undefined') {
				jqme.data('track-resize-end', false);
			}
			
			jqme.bind('resize.resizeend', function() {
				if(!jqme.data('track-resize-end')) {
					jqme.data('track-resize-end', true);
				}
				
				clearTimeout(timer);
				timer = setTimeout(
						function() {
							jqme.data('track-resize-end', false);
							trigger();
						}, timeoutResize
					);
			});
		},
		teardown: function() {
			$(this).unbind('resize.resizeend');
		}
	};
	
	$.each(
		customEvents,
		function(i, name) {
			$.fn[name] = function(handler) {
				return handler ? this.bind(name, handler) : this.trigger(name);
			};
		}
	);
	

})(window.jQuery, this);



/**
 * Placeholder Polyfill
 * 
 * Required until we drop IE9.
 *
 * @since	1.0
 */
(function () {
	function placeHolderFocus(e) {
		e = e || event;
		var t = e.target || e.srcElement,
			placeholder = t.getAttribute('placeholder');
		if(t.value === placeholder) {
			t.value = '';
		}
	}
	function placeHolderBlur(e) {
		e = e || event;
		var t = e.target || e.srcElement,
			placeholder = t.getAttribute('placeholder');
		if(t.value === '') {
			t.value = placeholder;
		}
	}
	function init() {
		var standards = window.addEventListener,
			els = document.getElementsByTagName('*'),
			l = els.length, c = 0, cur;
		for(c; c < l; c = c+1) {
			cur = els[c];
			switch(cur.nodeName.toLowerCase()) {
				case 'input':
				case 'textarea':
					if(cur.getAttribute('placeholder')) {
						if(standards) {
							cur.addEventListener('focus', placeHolderFocus);
							cur.addEventListener('blur', placeHolderBlur);
						} else {
							cur.attachEvent('onfocus', placeHolderFocus);
							cur.attachEvent('onblur', placeHolderBlur);
						}
						if(cur.value === '') {
							cur.value = cur.getAttribute('placeholder');
						}
					}
				break;
			}
		}
	}
	if(!('placeholder' in document.createElement('input'))) {
		init();
	}
}());



/**
 * mediaMatch Polyfill
 * 
 * Required until we drop IE9.  IE8 and lower does not support media queries. Maybe respond.js supports that insanity?
 *
 * Test with This: window.pollyfillMediaMatch('(max-width: 90000px)');
 * 
 * @since	1.0
 */
(function () {
	window.pollyfillMediaMatch = function(mq) {
		var ie_precheck = /MSIE [2345678]\./,
			testel_width = 4,
			el_id_base = 'media-match-polyfill-check-' + new Date().getTime(),
			syle_base = el_id_base + '-style',
			style = document.createElement('style'),
			style_cont = '#' + el_id_base + '{width: ' + testel_width + 'px; position: absolute;}',
			testel = document.createElement('div');
		
		if(navigator.userAgent.match(ie_precheck)) {
			return false;
		}
		
		testel.id = el_id_base;
		style.id = syle_base;
		
		style_cont = '@media ' + mq + ' { ' + style_cont + ' }';
		
		if(style.styleSheet) {
			style.styleSheet.cssText = style_cont;
		} else {
			style.appendChild(document.createTextNode(style_cont));
		}
		
		(document.head || document.getElementsByName('head')[0]).appendChild(style);
		document.body.appendChild(testel);
		
		testel_width = (testel.offsetWidth === testel_width);
		
		style.parentNode.removeChild(style);
		testel.parentNode.removeChild(testel);
		
		return {
				'matches': testel_width,
				'media': mq
			};
	};
	if(!window.matchMedia) {
		window.matchMedia = window.pollyfillMediaMatch;
	}
}());

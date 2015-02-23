/*jslint browser: true, devel: true, sloppy: true, todo: true, white: true */

/**
 * Admin JavaScript
 *
 * Handles all the JavaScript needs of the admin area.
 *
 * @package		Launchpad
 * @since		1.0
 */

jQuery(document).ready(
	function($, undefined) {
		
		function makeSortable() {
			//jQuery('.launchpad-flexible-container, .launchpad-repeater-container').sortable('destroy');
			$('.launchpad-flexible-container, .launchpad-repeater-container').sortable(
				{
					handle: 'h3',
					opacity: 0.5,
					placeholder: 'launchpad-flexible-container-placeholder',
					forcePlaceholderSize: true,
					revert: true,
					containment: 'parent',
					axis: 'y',
					items: '> div'
				}
			);
			
			$('.launchpad-relationship-items').sortable(
				{
					handle: 'a',
					opacity: 0.5,
					placeholder: 'launchpad-flexible-container-placeholder',
					forcePlaceholderSize: true,
					revert: true,
					containment: 'parent',
					axis: 'y',
					items: '> li'
				}
			);
			
		}
		
		function handleUpdatingFlexibleModules() {
			var tinymceconfig = $.extend(true, {}, tinyMCEPreInit.mceInit.content),
				qtconfig = $.extend(true, {}, tinyMCEPreInit.qtInit.content),
				edId = this.id;
			
			tinymceconfig.selector = '#' + edId;
			qtconfig.id = edId;
			
			tinyMCEPreInit.mceInit[edId] = tinymceconfig;
			tinyMCEPreInit.qtInit[edId] = qtconfig;
			
			tinyMCE.init(tinymceconfig);
			
			try {
				//quicktags(qtconfig);
				QTags(qtconfig);
				QTags._buttonsInit();
			} catch(e){}
			
			switchEditors.switchto(
				$(tinymceconfig.selector)
				.closest('.wp-editor-wrap')
				.find(
						'.wp-switch-editor.switch-' +
						(getUserSetting('editor') === 'html' ? 'html' : 'tmce')
					).get(0)
			);
			
			if (!window.wpActiveEditor) {
				window.wpActiveEditor = edId;
			}
		}
		
		function handleToggleStates() {
			$('[data-toggle]').each(
				function() {
					var me = $(this),
						elval = me.val(),
						cont = me.closest('.launchpad-flexible-metabox-container, .postbox')
						toggle = me.data('toggle');
					
					if(elval === null) {
						elval = '';
					}
					
					if(!$.isArray(elval)) {
						elval = [elval];
					}
					
					$.each(
						elval,
						function(index) {
							elval[index] = (elval[index] + '').toString().toLowerCase();
						}
					);
					
					$.each(
						toggle,
						function(index) {
							var val = this,
								ishidden = true,
								tmp;
							
							if(me.parent().parent().css('display') !== 'none') {							
								if(this.hide_when !== undefined) {
									ishidden = false;
									val = val.hide_when;
									if(!$.isArray(val)) {
										val = [val];
									}
									$.each(
										val,
										function(index) {
											val[index] = (val[index] + '').toString().toLowerCase();
										}
									);
									if(me.is('[type=radio]') || me.is('[type=checkbox]')) {
										if(val != 0 && me.prop('checked')) {
											ishidden = true;
										} else if(val == 0 && !me.prop('checked')) {
											ishidden = true;
										}
									} else {
										$.each(
											elval,
											function() {
												if($.inArray(this + '', val) > -1) {
													ishidden = true;
												}
											}
										);
									}
								} else if(val.show_when !== undefined) {
									ishidden = true;
									val = val.show_when;
									if(!$.isArray(val)) {
										val = [val];
									}
									$.each(
										val,
										function(index) {
											val[index] = (val[index] + '').toString().toLowerCase();
										}
									);
									if(me.is('[type=radio]') || me.is('[type=checkbox]')) {
										if(val != 0 && me.prop('checked')) {
											ishidden = false;
										} else if(val == 0 && !me.prop('checked')) {
											ishidden = false;
										}
									} else {
										$.each(
											elval,
											function() {
												if($.inArray(this + '', val) > -1) {
													ishidden = false;
												}
											}
										);
									}
								} else {
									return;
								}
							}
														
							cont.find('[name*="[' + index + ']"]').each(
								function () {
									if(ishidden) {
										if($(this).is('.launchpad-repeater-container')) {
											$(this).parent().addClass('launchpad-toggle-hidden');
										} else {
											$(this).closest('.launchpad-metabox-field').addClass('launchpad-toggle-hidden');
										}
									} else {
										if($(this).is('.launchpad-repeater-container')) {
											$(this).parent().removeClass('launchpad-toggle-hidden');
										} else {
											$(this).closest('.launchpad-metabox-field').removeClass('launchpad-toggle-hidden');
										}
									}
								}
							);
						}
					);
				}
			);
		}
		
		function handleWatchStates() {
			$('[data-watch]').each(
				function() {
					var me = $(this),
						watch = me.data('watch'),
						show_me = true;
						
					$.each(
						watch,
						function(index) {
							val = $(index).val();
							
							if($(index).is('[type=checkbox]')) {
								val = $(index).is(':checked')
							}
							
							if(this.hide_when !== undefined) {
								if(val == this.hide_when) {
									show_me = false;
								} else {
									show_me = true;
								}
							} else if(this.show_when !== undefined) {
								if(val == this.show_when) {
									show_me = true;
								} else {
									show_me = false;
								}
							}
						}
					);
					
					me = me.parent().parent();
					if(me.hasClass('launchpad-toggle-hidden') && show_me) {
						me.removeClass('launchpad-toggle-hidden');
					} else if(!me.hasClass('launchpad-toggle-hidden') && !show_me) {
						me.addClass('launchpad-toggle-hidden');
					}
				}
			);
		}
		
		$(document).on(
			'change keyup',
			'input, select, textarea',
			function(e) {
				handleWatchStates();
				handleToggleStates();
			}
		);
		
		var regenerate_thumbnail_ids = [];
		
		makeSortable();
		handleWatchStates();
		handleToggleStates();
		
		if($("input.launchpad-date-picker").length) {
			$("input.launchpad-date-picker").datepicker().unbind('keydown').unbind('keyup').unbind('keypress');
		}
	
		// Do admin stuff.
		$(document.body).on(
			'click',
			'#start-regen',
			function() {
				var status_area = $('#launchpad-regen-thumbnail-status'),
					button = $(this),
					percent_area = $('#launchpad-processing-percent');
				
				if(button.data('processing') !== true) {
					status_area.html('');
					button.data('processing', true).attr('value', 'Stop Processing');
					percent_area.html('0% Complete');
					$.get(
						'/wp-admin/admin-ajax.php?action=get_attachment_list&nonce=' + launchpad_nonce,
						function(data) {
							function process() {
								var cur;
								if(regenerate_thumbnail_ids.length) {
									cur = regenerate_thumbnail_ids.shift();
									$('#launchpad-regen-' + cur).attr('class', 'launchpad-regen-processing');
									$('#launchpad-regen-' + cur).find('.status').html('Processing...');
									$.get(
										'/wp-admin/admin-ajax.php?action=do_regenerate_image&attachment_id=' + cur + '&nonce=' + launchpad_nonce,
										function(data) {
											var message;
											
											$('#launchpad-regen-' + data.attachment_id).attr('class', '');
											
											if(data.status === 1) {
												message = 'Complete.';
												$('#launchpad-regen-' + data.attachment_id).addClass('launchpad-regen-complete');
											} else {
												message = 'Failed.';
												$('#launchpad-regen-' + data.attachment_id).addClass('launchpad-regen-failed');
											}
											$('#launchpad-regen-' + data.attachment_id).find('.status').html(message);
											
											button.attr('value', 'Stop Processing');
												
											percent_area.html(Math.round(status_area.find('div.launchpad-regen-complete, div.launchpad-regen-fail').length/status_area.find('div').length*100) + '% Complete');
											
											process();
										}
									);
								} else {
									button.data('processing', false).attr('value', 'Start Regenerating Thumbnails');
								}
							}
							
							regenerate_thumbnail_ids = data;
							$.each(
								regenerate_thumbnail_ids,
								function() {
									status_area.append('<div id="launchpad-regen-' + this + '" class="launchpad-regen-waiting">Image № ' + this + ': <span class="status">Waiting...</span></div>');
								}
							);
							process();
						}
					);
				} else {
					button.data('processing', false).attr('value', 'Restart Regenerating Thumbnails');
					$.each(
						regenerate_thumbnail_ids,
						function() {
							$('#launchpad-regen-' + this).attr('class', '');
							$('#launchpad-regen-' + this).addClass('launchpad-regen-canceled');
							$('#launchpad-regen-' + this).find('.status').html('Canceled.');
						}
					);
					regenerate_thumbnail_ids = [];
				}
			}
		);
		
		$(document.body).on(
			'click',
			'.launchpad-file-button',
			function(e) {
				var me = $(this),
					config	= {
						title: 'Upload File',
						button: {
							text: 'Add File'
						},
						multiple: false  // Set this to true to allow multiple files to be selected
					},
					custom_uploader;
					
					if(me.data('limit')) {
						config.library = {type: me.data('limit')};
					}
					
					custom_uploader = wp.media(config).on(
						'select', 
						function() {
							var attachment = custom_uploader.state().get('selection').first().toJSON(),
								update = $('#' + me.data('for')),
								delete_link = update.parent().find('.launchpad-delete-file'),
								remove_link;
							if(update.length) {
								update.attr('value', attachment.id);
								
								remove_link = $('<a href="#" class="launchpad-delete-file" data-for="' + me.data('for') + '" onclick="document.getElementById(this.getAttribute(\'data-for\')).value=\'\'; this.parentNode.removeChild(this); return false;"><img src="' + (attachment.sizes && attachment.sizes.thumbnail ?  attachment.sizes.thumbnail.url :  attachment.icon) + '"></a>');
								
								if(delete_link.length) {
									delete_link.replaceWith(remove_link);
								} else {
									update.parent().append(remove_link);
								}
							} else {
								alert('There was a problem attaching the media.  Please contact your developer.');
							}
						}
					).open();
				e.stopImmediatePropagation();
				e.preventDefault();
			}
		);
		
		
		$(document.body).on(
			'click',
			'button.launchpad-repeater-add',
			function() {
				var me = $(this),
					container_id = me.data('for'),
					container = $('#' + container_id),
					master = container.children().first().clone(),
					master_replace_with = 'launchpad-' + new Date().getTime() + '-repeater',
					visualeditors;
					
				console.log(master);
				
				master.find('[name], [data-field-name], button[data-for]').each(
					function() {
						var me = $(this);
						
						if(me.is('[name]')) {
							me.attr('name', me.attr('name').replace(/launchpad\-.*?\-repeater/g, master_replace_with));
							if(me.attr('id')) {
								me.attr('id', me.attr('id').replace(/launchpad\-.*?\-repeater/g, master_replace_with));
							}
						}
						
						if(me.is('button[data-for]')) {
							me.attr('data-for', me.attr('data-for').replace(/launchpad\-.*?\-repeater/g, master_replace_with));
							me.parent().find('a.launchpad-delete-file').remove();
							me.parent().find('input[type=hidden]').get(0).value = '';
						}
						
						if(me.is('[data-field-name]')) {
							me.attr('data-field-name', me.attr('data-field-name').replace(/launchpad\-.*?\-repeater/g, master_replace_with));
						}
						
						if(me.is('input:not(checkbox)')) {
							me.val('');
						}
						if(me.is('select')) {
							me.val('');
						}
					}
				);
				
				// Replace inner HTML
				master.find('.launchpad-relationship-items').html('');
				
				console.log(master);
				
				container.append(master);
				
				visualeditors = master.find('textarea.wp-editor-area');

				if(visualeditors.length) {
					// Attempting to fix visual editor on repeaters.
					visualeditors.each(
						function() {
							var me = $(this),
								editor = me.closest('.wp-editor-wrap'),
								editor_current_id = editor.attr('id').slice(3, -5),
								cnt = 1;
							
							while($('#wp-' + editor_current_id + cnt + '-wrap').length) {
								cnt++;
							}
							editor_current_id = editor_current_id + cnt;
							
							editor.addClass('launchpad-editor-loading');
							
							$.get(
								'/wp-admin/admin-ajax.php?action=get_editor&id=' + editor_current_id + '&name=' + me.attr('name') + '&nonce=' + launchpad_nonce,
								function(data) {
									data = $(data);
									editor.replaceWith(data);
									
									data.find('textarea.wp-editor-area').each(handleUpdatingFlexibleModules);
									
									$('.wp-editor-wrap').off('click.wp-editor').on('click.wp-editor', function() {
										if(this.id) {
											window.wpActiveEditor = this.id.slice(3, -5);
										}
									});
									
								}
							);
						}
					);
					

				}
				
				makeSortable();
				handleToggleStates()
			}
		);
		
		$(document.body).on(
			'click',
			'a.launchpad-flexible-link',
			function(e) {
				var me = $(this);
				e.preventDefault();
				$.get(
					'/wp-admin/admin-ajax.php?action=get_flexible_field&type=' + me.data('launchpad-flexible-type') + '&name=' + me.data('launchpad-flexible-name') + '&id=' + me.data('launchpad-flexible-post-id') + '&nonce=' + launchpad_nonce,
					function(data) {
						var visualeditors;
						data = $(data);
						
						visualeditors = data.find('textarea.wp-editor-area');
						
						$('#launchpad-flexible-container-' + me.data('launchpad-flexible-type')).append(data);

						if(visualeditors.length) {
							visualeditors.each(handleUpdatingFlexibleModules);
							
							$('.wp-editor-wrap').off('click.wp-editor').on('click.wp-editor', function() {
								if(this.id) {
									window.wpActiveEditor = this.id.slice(3, -5);
								}
							});
						}
						
						makeSortable();
						handleToggleStates()
					}
				);
			}
		);
		
		$(document.body).on(
			'keyup input',
			'.launchpad-relationship-search-field',
			function(e) {
				var me = $(this),
					container = me.closest('.launchpad-relationship-container'),
					listing = container.find('.launchpad-relationship-list');
				if(e.type === 'input' && this.value.replace(/\s/, '') !== '') {
					return;
				}
				
				$.get(
					'/wp-admin/admin-ajax.php?action=search_posts&post_type=' + container.data('post-type') + '&query=' + (container.data('query') ? encodeURIComponent($.param(container.data('query'))) : '') + '&terms=' + me.val() + '&nonce=' + launchpad_nonce,
					function(data) {
						listing.html('');
						$.each(
							data,
							function() {
								listing.append(
									$('<li><a href="#" data-id="' + this.ID + '">' + this.post_title + ' <small>' + this.ancestor_chain + '</small></a></li>')
								);
							}
						);
					}
				);
			}
		).on(
			'click',
			'.launchpad-relationship-list a',
			function(e) {
				var me = $(this),
					cp = me.clone(),
					container = me.closest('.launchpad-relationship-container'),
					addto = container.find('.launchpad-relationship-items'),
					fname = container.data('field-name'),
					limit = container.data('limit');
				
				e.preventDefault();
				
				cp.append($('<input type="hidden" name="' + fname + '" value="' + me.data('id') + '">'));
				
				if(!$('[value="' + me.data('id') + '"]', addto).length && (+limit <= 0 || $('[value]', addto).length < +limit)) {
					me = $('<li>');
					me.css('height', 0);
					me.append(cp);
					addto.append(me);
					me.animate({height: cp.outerHeight()});
				}
			}
		).on(
			'click',
			'.launchpad-relationship-items a',
			function(e) {
				var me = $(this);
				
				e.preventDefault();
				me.parent().animate(
					{height: 0},
					function() {
						me.parent().remove();
					}
				);
			}		
		).on(
			'change',
			'fieldset.launchpad-address input',
			function() {
				var me = $(this),
					fs = me.closest('.launchpad-address'),
					addr = '';
				
				$('input:not([type=hidden])', fs).each(
					function() {
						var me = $(this);
						if(me.attr('name').indexOf('[number]') === -1) {
							addr += me.val();
							if(me.attr('name').indexOf('[street]') !== -1 || me.attr('name').indexOf('[city]') !== -1 && addr) {
								addr += ',';
							}
							addr += ' ';
						}
					}
				);
				
				addr = addr.replace(/\s\s+/g, ' ').replace(/ , /, ' ').replace(/, ?$/, '');
				
				$('.launchpad-google-map-embed', fs).html('<div>Searching for: ' + addr + '</div>');
				
				if(addr) {
					$.post(
						'/wp-admin/admin-ajax.php',
						{action: 'geocode', 'address': addr, 'nonce': launchpad_nonce},
						function(data) {
							$('input', fs).each(
								function() {
									var me = $(this);
									if(me.attr('name').indexOf('[latitude]') !== -1) {
										me.val(data.lat);
									}
									if(me.attr('name').indexOf('[longitude]') !== -1) {
										me.val(data.lng);
									}
								}
							);
							
							if(data.lat == 0 && data.lng == 0) {
								$('.launchpad-google-map-embed', fs).html('<div>No Matches Found.</div>');
							} else {						
								$('.launchpad-google-map-embed', fs).html('<iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="//maps.google.com/maps?q=' + data.lat + ',' + data.lng + '+(Your Location)&amp;output=embed"></iframe>');
							}
						}
					);
				} else {
					$('.launchpad-google-map-embed', fs).html('<div>No Address Provided.</div>');
				}
			}
		);
		
		$('textarea[maxlength]').keyup(
			function(e) {
				var me = $(this),
					range;
				if(e.ctrlKey || e.altKey || e.metaKey || e.which === 91) {
					return;
				}
				if(me.val().length > me.attr('maxlength')) {
					me.val(me.val().substr(0, me.attr('maxlength')));
					if (typeof this.selectionStart == "number") {
						this.selectionStart = this.selectionEnd = this.value.length;
					} else if (typeof this.createTextRange != "undefined") {
						this.focus();
						range = this.createTextRange();
						range.collapse(false);
						range.select();
					}
				}
			}
		).add('input[maxlength]').on(
			'keyup',
			function() {
				var me = $(this);
				me.parent().find('.launchpad-char-count').html(+me.attr('maxlength')-me.val().length);
			}
		).each(
			function() {
				var me = $(this),
					ml = me.attr('maxlength');
				me.parent().append('<small>Characters Left: <span class="launchpad-char-count">' + (+ml-me.val().length) + '</span> of ' + ml + '</small>');
			}
		);
		
		$('[name="launchpad_meta[SEO][title]"], [name="launchpad_meta[SEO][keyword]"]').keyup(
			function() {
				var serp_head = $('#serp-heading'),
					parsed_val = $('[name="launchpad_meta[SEO][title]"]').val();
				
				parsed_val = parsed_val.replace(/^\s+/, '').replace(/\s+$/, '');
				
				if(parsed_val === '') {
					parsed_val = serp_head.data('post-title');
				}
				if(parsed_val) {
					serp_head.html(
							parsed_val.substr(0, 70).replace(
								new RegExp(
									'(' + $('[name="launchpad_meta[SEO][keyword]"]').val().replace(/\s+/g, '|') + ')', 
									'ig'
								), 
								'<strong>$1</strong>'
								) + (parsed_val.length > 70 ? ' ...' : '')
							);
				}
			}
		);
		
		$('[name="launchpad_meta[SEO][meta_description]"], [name="launchpad_meta[SEO][keyword]"]').keyup(
			function() {
				var serp_head = $('#serp-meta'),
					parsed_val = $('[name="launchpad_meta[SEO][meta_description]"]').val();
				
				parsed_val = parsed_val.replace(/^\s+/, '').replace(/\s+$/, '');
				
				if(parsed_val === '') {
					parsed_val = serp_head.data('post-excerpt');
				}
				if(parsed_val) {
					serp_head.html(
							parsed_val.substr(0, 160).replace(
								new RegExp(
									'(' + $('[name="launchpad_meta[SEO][keyword]"]').val().replace(/\s+/g, '|') + ')', 
									'ig'
								), 
								'<strong>$1</strong>'
								) + (parsed_val.length > 160 ? ' ...' : '')
							);
				}
			}
		);
		
		$('.launchpad-checkbox-toggle').each(
			function() {
				var me = $(this),
					toggle = $('<div class="launchpad-metabox-field launchpad-metabox-toggle-all"><label><input type="checkbox"> Toggle All</label></div>');
				
				if(me.find('[type=checkbox]').length == me.find('[type=checkbox]:checked').length) {
					toggle.find('[type=checkbox]').attr('checked', 'checked');
				}
				
				$('legend', me).after(toggle);
				
				toggle.find('[type=checkbox]').on(
					'change',
					function() {
						me = $(this);
						if(me.is(':checked')) {
							me.closest('.launchpad-checkbox-toggle').find('[type=checkbox]').attr('checked', 'checked');
						} else {
							me.closest('.launchpad-checkbox-toggle').find('[type=checkbox]').removeAttr('checked');
						}
					}
				);
			}
		);
		
		$('#migrate-table-checkbox, [name=migrate_attached_files]').on(
			'change',
			function() {
				setTimeout(
					function() {
						var total_rows = 0,
							total_files = 0,
							count_rows = $('[name=migrate_attached_files]').is(':checked');
						
						$('#migrate-table-checkbox :checked').each(
							function() {
								var me = $(this);
								if(me.data('rows')) {
									total_rows += (+me.data('rows'));
								}
								if(count_rows) {
									if(me.data('files')) {
										total_files += (+me.data('files'));
									}
								}
							}
						);
						
						$('#migrate-rows-total').html(total_rows);
						$('#migrate-files-total').html(total_files);
					}
				, 250);
			}
		);
	}
);

/**
 * Admin JavaScript
 *
 * Handles all the JavaScript needs of the admin area.
 *
 * @package		Launchpad
 * @since		1.0
 */

jQuery(document).ready(
	function($) {
	
		function makeSortable() {
			//jQuery('.launchpad-flexible-container, .launchpad-repeater-container').sortable('destroy');
			$('.launchpad-flexible-container, .launchpad-repeater-container').sortable(
				{
					handle: 'h3',
					opacity: .5,
					placeholder: 'launchpad-flexible-container-placeholder',
					forcePlaceholderSize: true,
					revert: true,
					containment: 'parent',
					axis: 'y',
					items: '> div'
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
			} catch(e){};
			
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
		
		makeSortable();
	
		// Do admin stuff.
		$(document.body).on(
			'click',
			'.launchpad-file-button',
			function(e) {
				var me = $(this),
					custom_uploader = wp.media(
						{
							title: 'Upload File',
							button: {
								text: 'Add File'
							},
							multiple: false  // Set this to true to allow multiple files to be selected
						}
					).on(
						'select', 
						function() {
							var attachment = custom_uploader.state().get('selection').first().toJSON(),
								update = $('#' + me.data('for')),
								delete_link = update.parent().find('.launchpad-delete-file'),
								ret = '', remove_link;
							if(update.length) {
								update.attr('value', attachment.id);
								
								remove_link = $('<a href="#" class="launchpad-delete-file" data-for="' + me.data('for') + '" onclick="document.getElementById(this.getAttribute(\'data-for\')).value=\'\'; this.parentNode.removeChild(this); return false;"><img src="' + (attachment.sizes && attachment.sizes.thumbnail ?  attachment.sizes.thumbnail.url :  attachment.url) + '"></a>');
								
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
				
				master.find('[name]').each(
					function() {
						var me = $(this);
						me.attr('name', me.attr('name').replace(/launchpad\-.*?\-repeater/g, master_replace_with));
						if(me.is('input:not(checkbox)')) {
							me.val('');
						}
						if(me.is('select')) {
							me.val('');
						}
					}
				);
				
				container.append(master);
				
				visualeditors = master.find('textarea.wp-editor-area');

				if(visualeditors.length) {
					visualeditors.each(handleUpdatingFlexibleModules);
					
					$('.wp-editor-wrap').off('click.wp-editor').on('click.wp-editor', function() {
						if(this.id) {
							window.wpActiveEditor = this.id.slice(3, -5);
						}
					});
				}
				
				makeSortable();
			}
		);
		
		$(document.body).on(
			'click',
			'a.launchpad-flexible-link',
			function(e) {
				var me = $(this);
				e.preventDefault();
				$.get(
					'/api/?action=get_flexible_field&type=' + me.data('launchpad-flexible-type') + '&name=' + me.data('launchpad-flexible-name') + '&id=' + me.data('launchpad-flexible-post-id'),
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
					}
				);
			}
		);
	}
);

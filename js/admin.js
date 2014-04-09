/**
 * Admin JavaScript
 *
 * Handles all the JavaScript needs of the admin area.
 *
 * @package		Launchpad
 * @since		1.0
 */

jQuery(document).ready(
	function() {
		// Do admin stuff.
		jQuery(document.body).on(
			'click',
			'.launchpad-file-button',
			function(e) {
				var me = jQuery(this),
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
								update = jQuery('#' + me.data('for')),
								delete_link = update.parent().find('.launchpad-delete-file'),
								ret = '', remove_link;
							if(update.length) {
								update.attr('value', attachment.id);
								
								remove_link = jQuery('<a href="#" class="launchpad-delete-file" data-for="' + me.data('for') + '" onclick="document.getElementById(this.getAttribute(\'data-for\')).value=\'\'; this.parentNode.removeChild(this); return false;"><img src="' + (attachment.sizes && attachment.sizes.thumbnail ?  attachment.sizes.thumbnail.url :  attachment.url) + '"></a>');
								
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
	}
);

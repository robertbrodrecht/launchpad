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
			'.file-button',
			function() {
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
								ret = '';
							if(update.length) {
								update.attr('value', attachment.id);
								update.parent().append(
									'<br><a href="#" class="launchpad-delete-file" onclick="document.getElementById(this.attribute(\'data-for\')).value=\'\'; this.parentNode.removeChild(this); return false;"><img src="' + attachment.sizes.thumbnail.url + '"></a>'
								);
							} else {
								alert('There was a problem attaching the media.  Please contact your developer.');
							}
						}
					).open();
			}
		);
	}
);

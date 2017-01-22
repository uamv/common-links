(function( $ ) {
	'use strict';

	/**
	 * All of the code for your Dashboard-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	// Toggle internal lable for site URL input
	$( document ).ready( function() {

		$('.tb-close-icon').click( function () {
			$('body.wp-admin').removeClass('usual-links-active');
		});
		$('#edit-usual-links').click( function () {
	        $('body.wp-admin').addClass('usual-links-active');
	    });

		$('input#usual-links-url').focus( function(){

			$('#link-url-prompt-text').addClass('screen-reader-text');

		});

		$('input#usual-links-url').blur( function(){

			if ( !this.value ) {

				$('#link-url-prompt-text').removeClass('screen-reader-text');

			}

		});

	});

})( jQuery );

function AddUsualLink() {

	var usual_link_title = jQuery('#new_usual_link_title').val();
	var usual_link_content = jQuery('#new_usual_link_content').val().replace('http://','').replace('https://','');
	var usual_link_security = jQuery('#usual_link_security').val();


	// respond to server with new usual link
	jQuery.post(UsualLink.ajaxurl, {
		action: 'add_usual_link',
		title: usual_link_title,
		content: usual_link_content,
		usual_link_security: usual_link_security,
	}, function (response) {
		if ( response.success ) {
			if ( usual_link_content.length > 42 ) {
				var usual_link_content_trimmed = jQuery.trim(usual_link_content).substring(0, 41).trim(this) + "…";
			} else {
				var usual_link_content_trimmed = usual_link_content;
			}
			if ( usual_link_title.length > 12 ) {
				var usual_link_title_trimmed = jQuery.trim(usual_link_title).substring(0, 11).trim(this) + "…";
			} else {
				var usual_link_title_trimmed = usual_link_title;
			}

			var new_usual_link = '<li id="usual-link-' + response.usual_link.ID + '" class="usual-link"><span class="usual-link-title" title="' + usual_link_title + '">' + usual_link_title_trimmed + '</span><a href="http://' + usual_link_content + '" title="' + usual_link_content + '">' + usual_link_content_trimmed + '</a><div id="remove-usual-link-' + response.usual_link.ID + '" onclick="RemoveUsualLink(' + response.usual_link.ID + ')" class="dashicons dashicons-trash" title="Trash Link" data-link-id="' + response.usual_link.ID + '"></div></li>';
			jQuery('#usual-linked-list').append(new_usual_link);
			jQuery('input#new_usual_link_title').val('').focus();
			jQuery('input#new_usual_link_content').val('');

		} else {

		} // end if/else

		jQuery('#usual-linked-list').before(response.notice);
		// jQuery('#TB_window').before(response.notice);
		jQuery('.usual-link-notice').delay(4000).slideUp();

	});

}

function RemoveUsualLink(usual_link_ID) {

	var usual_link_security = jQuery('#usual_link_security').val();

	jQuery.post(UsualLink.ajaxurl, {
		action: 'remove_usual_link',
		link_ID: usual_link_ID,
		usual_link_security: usual_link_security,
	}, function (response) {
		// If the server returns '1', then we can mark this post as read, so we'll hide the checkbox
		// container. Next time the user browses the index, this post won't appear
		if ( response.success ) {

			jQuery('#usual-link-'+usual_link_ID).remove();

		} else {

			alert('There was an error in deleting the item(s).');

		} // end if/else

		jQuery('#usual-linked-list').append(response.notice);
		jQuery('.usual-link-notice').delay(4000).slideUp();

	});

}

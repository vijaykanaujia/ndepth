(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$(function() {
		$("input#ndepth_browse_media").click(function(e) {
	        e.preventDefault();
	        var image_frame;
	        if (image_frame) {
	            image_frame.open();
	        }
	        // Define image_frame as wp.media object
	        image_frame = wp.media({
	            title: "Select Media",
	            multiple: false,
	            library: {
	                type: "image",
	            },
	        });

	        image_frame.on("close", function() {
	            // On close, get selections and save to the hidden input
	            // plus other AJAX stuff to refresh the image preview
	            var selection = image_frame.state().get("selection");
	            var gallery_ids = new Array();
	            var my_index = 0;
	            selection.each(function(attachment) {
	                gallery_ids[my_index] = attachment["id"];
	                my_index++;
	            });
	            var ids = gallery_ids.join(",");
	            jQuery("input#ndepth_image").val(ids);
	            Refresh_Image(ids);
	        });

	        image_frame.on("open", function() {
	            // On open, get the id from the hidden input
	            // and select the appropiate images in the media manager
	            var selection = image_frame.state().get("selection");
	            var img_id = jQuery("input#ndepth_image").val();
	            if (img_id) {
	                var ids = img_id.split(",");
	                ids.forEach(function(id) {
	                    var attachment = wp.media.attachment(id);
	                    attachment.fetch();
	                    selection.add(attachment ? [attachment] : []);
	                });
	            }
	        });

	        image_frame.open();
	    });

	    $(".ndepth-parent").select2({
	        ajax: {
	            url: ajaxurl,
	            dataType: 'json',
	            delay: false,
	            data: function(params) {
	                return {
	                    action: 'ndepth_get_parent_list',
	                    q: params.term, // search term
	                    page: params.page
	                };
	            },
	            processResults: function(data, params) {
	                // parse the results into the format expected by Select2
	                // since we are using custom formatting functions we do not need to
	                // alter the remote JSON data, except to indicate that infinite
	                // scrolling can be used
	                params.page = params.page || 1;

	                return {
	                    results: data.data
	                        // pagination: {
	                        //     more: (params.page * 30) < data.total_count
	                        // }
	                };
	            },
	            cache: true
	        },
	        placeholder: 'Search for a Page',
	        disabled: true
	        // minimumInputLength: 1,
	        // templateResult: formatRepo,
	        // templateSelection: formatRepoSelection
	    });
    });

})( jQuery );

// Ajax request to refresh the image preview
function Refresh_Image(the_id) {
    var data = {
        action: "ndepth_get_refresh_image",
        id: the_id,
    };

    jQuery.get(ajaxurl, data, function(response) {
        if (response.success === true) {
            jQuery("#ndepth-preview-image").replaceWith(response.data.image);
        }
    });
}

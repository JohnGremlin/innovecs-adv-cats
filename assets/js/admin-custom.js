
jQuery(document).ready(function(e){

	// hiding fields when radio buttons checked

	jQuery(document).on('change', '.video-container', function () {
	    if (jQuery('#category_video_youtube').prop('checked')) {
	        jQuery('.form-field.video').hide()
	        jQuery('.form-field.youtube').show('slow')
	    }else if (jQuery('#category_video').prop('checked')) {
	        jQuery('.form-field.youtube').hide()
	        jQuery('.form-field.video').show('slow')
	    }
	})
});
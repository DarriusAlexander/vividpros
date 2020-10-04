jQuery( document ).ready( function() {

	// Disable the navigation top and bottom lines, when there is no prev and next nav
	if( ! jQuery.trim( jQuery( '.tribe-events-nav-previous' ).html() ).length && ! jQuery.trim( jQuery( '.tribe-events-nav-next' ).html() ).length ) {
		jQuery( '.tribe-events-sub-nav' ).parent().hide();
	}

	jQuery( '.fusion-tribe-has-featured-image' ).each(function() {
		var height = jQuery(this).parent().height();
		jQuery(this).find('.tribe-events-event-image').css('height', height);
	});

	jQuery( window ).on( 'resize', function() {
		jQuery( '.fusion-tribe-has-featured-image' ).each(function() {
			jQuery(this).find('.tribe-events-event-image').css('height', 'auto');
			var height = jQuery(this).parent().height();
			jQuery(this).find('.tribe-events-event-image').css('height', height);
		});
	});
});

jQuery( window ).load(function() {
	// Equal Heights Elements
	jQuery( '.fusion-events-shortcode' ).each( function() {
		jQuery( this ).find('.fusion-events-meta' ).equalHeights();
	});

	jQuery( window ).on( 'resize', function() {
		jQuery( '.fusion-events-shortcode' ).each( function() {
			jQuery( this ).find( '.fusion-events-meta' ).equalHeights();
		});
	});
});

jQuery( document ).ajaxComplete( function() {
	jQuery( '.fusion-tribe-has-featured-image' ).each(function() {
		var height = jQuery(this).parent().height();
		jQuery(this).find('.tribe-events-event-image').css('height', height);
	});

	jQuery( this ).find( '.post' ).each(function() {
		jQuery( this ).find( '.fusion-post-slideshow' ).flexslider();
		jQuery( this ).find( '.full-video, .video-shortcode, .wooslider .slide-content' ).fitVids();
	});

	jQuery( '#tribe-events .fusion-blog-layout-grid' ).isotope();
	jQuery( window ).trigger( 'resize' );
});

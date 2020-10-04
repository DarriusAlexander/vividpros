(function ($) {

	$( 'body' ).on( 'click', 'a.scroll-next-row', function() {
		var $this = $( this ),
        row = $this.closest( '.themify_builder_row' ).next();
        if( row.length ) {
			$( 'body, html' ).animate( {
				scrollTop: row.offset().top
			}, 800, function(){
				if( $( '#headerwrap.fixed-header' ).length ) {
					$( 'body, html' ).animate( {
						scrollTop: row.offset().top - $( '#headerwrap.fixed-header' ).outerHeight()
					}, 200 );
				}
			} );
        }

		$( 'body' ).trigger( 'builder_button_scroll_to_next_row', [$this] );

		return false;
	} )
        .on( 'click', 'a.modules-reveal', function(){
		var $this = $( this ),
                    modules = $this.closest( '.module' ).nextAll();
		modules.fadeIn();

		/* if there are Map modules that need refreshing, SO #19245804 */
		modules.find( '.map-container' ).each(function(){
			if( typeof $( this ).data( 'gmap_object' ) == 'object' ) {
				google.maps.event.trigger( $( this ).data( 'gmap_object' ), 'resize' );
			}
		});

		if( $this.data( 'behavior' ) == 'toggle' ) {
			$this.addClass( 'modules-show-less' )
				.removeClass( 'modules-reveal' )
				.find( 'span' )
					.text( $this.data( 'lesslabel' ) );
		} else {
			$this.fadeOut( 'slow' );
		}

		$( 'body' ).trigger( 'builder_button_reveal_modules', [$this] );
		Themify.triggerEvent( window, 'resize' );

		return false;
	} )
        .on( 'click', 'a.modules-show-less', function(){
		var $this = $( this ),
			modules = $this.closest( '.module' ).nextAll();
		modules.fadeOut();
		$this.addClass( 'modules-reveal' )
			.removeClass( 'modules-show-less' )
			.find( 'span' )
				.text( $this.data( 'label' ) );

		$( 'body' ).trigger( 'builder_button_show_less', [$this] );
		return false;
	} );

	$(document).ready(function(){
		if ( $('.module-button .themify_lightbox').length>0 ) {
			Themify.InitGallery();
		}

		$('.builder_button').on({
                    mouseenter: function () {
			var $hover = $(this).data('hover');
			if($hover){
				$(this).removeClass($(this).data('remove'));
				$(this).addClass($hover);
			}
		},
		mouseleave: function () {
			var $hover = $(this).data('hover');
			if($hover){
				$(this).removeClass($hover);
				$(this).addClass($(this).data('remove'));
			}
		}
		});
	});
}(jQuery));
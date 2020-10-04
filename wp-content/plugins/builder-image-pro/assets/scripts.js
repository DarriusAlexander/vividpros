(function ($) {

	function flip_effect( el, side ) {
		side = side || ( el.hasClass( 'image-pro-flip' ) ? 'front' : 'back' );
		if( side == 'front' ) {
			el.removeClass( 'image-pro-flip' );
			window.setTimeout( function(){ el.removeClass( 'image-pro-flipped' ); }, 1000 );
		} else {
			el.addClass( 'image-pro-flip' );
			window.setTimeout( function(){ el.addClass( 'image-pro-flipped' ); }, 1000 );
		}
	}

	$( 'body' )
	.on( 'mouseenter', '.module-pro-image', function(){
		var $this = $( this ),
			entrance_effect = $this.data( 'entrance-effect' );

		if( entrance_effect == 'flip-horizontal' || entrance_effect == 'flip-vertical' ) {
			flip_effect( $( this ), 'back' );
		} else if( entrance_effect == 'none' ) {
			$this.find( '.image-pro-overlay' )
				.css( 'visibility', 'visible' )
				.addClass( entrance_effect );
		} else {
			$this.find( '.image-pro-overlay' )
				.css( 'visibility', 'visible' )
				.removeClass( $this.data( 'exit-effect' ) )
				.addClass( 'wow animated ' + entrance_effect );
		}
	} )
	.on( 'mouseleave', '.module-pro-image', function(){
                var $this = $( this ),
                    entrance_effect = $this.data( 'entrance-effect' );
                if($('.mfp-wrap').length>0){
                    setTimeout(function(){
                            $this.find( '.image-pro-overlay' ).removeAttr('style').removeClass('wow '+ entrance_effect );
                    },1000);
                    return false;
                }
		if( entrance_effect == 'flip-horizontal' || entrance_effect == 'flip-vertical' ) {
			flip_effect( $this, 'front' );
		} else if( entrance_effect == 'none' ) {
			$this.find( '.image-pro-overlay' )
				.css( 'visibility', 'visible' )
				.addClass( entrance_effect );
		} else {
			$this.find( '.image-pro-overlay' )
				.removeClass( entrance_effect )
				.addClass( 'wow animated ' + $this.data( 'exit-effect' ) );
		}
	} );


	function builder_image_pro_init() {
            if ('undefined' !== typeof $.fn.magnificPopup & $( '.module-pro-image .themify_lightbox' ).length > 0) {
                Themify.InitGallery();
            }
	}
	$(document).ready(builder_image_pro_init);
	$( 'body' ).on( 'builder_load_module_partial builder_toggle_frontend', builder_image_pro_init );
}(jQuery));

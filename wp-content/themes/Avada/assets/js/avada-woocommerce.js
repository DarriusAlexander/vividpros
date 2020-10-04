/**
 * WooCommerce Quanity buttons add-back
 */
jQuery(
    function( $ ) {
        if ( typeof js_local_vars.woocommerce_23 !== 'undefined' ) {
            var $testProp = $( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).find( 'qty' );
            if ( $testProp && $testProp.prop( 'type' ) != 'date' ) {
                // Quantity buttons
                $( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).addClass( 'buttons_added' ).append( '<input type="button" value="+" class="plus" />' ).prepend( '<input type="button" value="-" class="minus" />' );

                // Target quantity inputs on product pages
                $( 'input.qty:not(.product-quantity input.qty)' ).each(
                    function() {

                        var min = parseFloat( $( this ).attr( 'min' ) );

                        if ( min && min > 0 && parseFloat( $( this ).val() ) < min ) {
                            $( this ).val( min );
                        }
                    }
                );

                $( document ).on(
                    'click', '.plus, .minus', function() {

                        // Get values
                        var $qty = $( this ).closest( '.quantity' ).find( '.qty' ),
                            currentVal = parseFloat( $qty.val() ),
                            max = parseFloat( $qty.attr( 'max' ) ),
                            min = parseFloat( $qty.attr( 'min' ) ),
                            step = $qty.attr( 'step' );

                        // Format values
                        if ( !currentVal || currentVal === '' || currentVal === 'NaN' ) currentVal = 0;
                        if ( max === '' || max === 'NaN' ) max = '';
                        if ( min === '' || min === 'NaN' ) min = 0;
                        if ( step === 'any' || step === '' || step === undefined || parseFloat( step ) === 'NaN' ) step = 1;

                        // Change the value
                        if ( $( this ).is( '.plus' ) ) {

                            if ( max && ( max == currentVal || currentVal > max ) ) {
                                $qty.val( max );
                            } else {
                                $qty.val( currentVal + parseFloat( step ) );
                            }

                        } else {

                            if ( min && ( min == currentVal || currentVal < min ) ) {
                                $qty.val( min );
                            } else if ( currentVal > 0 ) {
                                $qty.val( currentVal - parseFloat( step ) );
                            }

                        }

                        // Trigger change event
                        $qty.trigger( 'change' );
                    }
                );
            }
        }
    }
);

function fusionResizeCrossfadeImages( $parent ) {
	var $parent_height = $parent.height();

	$parent.find( 'img' ).each(
		function() {
			$img_height = jQuery( this ).height();

			if ( $img_height < $parent_height ) {
				jQuery( this ).css( 'margin-top', ( ( $parent_height - $img_height ) / 2 )  + "px" );
			}
		}
	);
}

function fusion_resize_crossfade_images_container( $container ) {
	var $biggest_height = 0;

	$container.find( 'img' ).each(
		function() {
			$img_height = jQuery( this ).height();

			if ( $img_height > $biggest_height ) {
				$biggest_height = $img_height;
			}
		}
	);

	$container.css( 'height', $biggest_height );
}

// Resize crossfade images and square to be the largest image and also vertically centered
jQuery( window ).load(
    function() {
        jQuery( window ).resize(
            function() {
				jQuery( '.crossfade-images' ).each(
					function() {
						fusion_resize_crossfade_images_container( jQuery( this ) );
						fusionResizeCrossfadeImages( jQuery( this ) );
					}
				);
            }
        );

        jQuery( '.crossfade-images' ).each(
            function() {
                fusion_resize_crossfade_images_container( jQuery( this ) );
                fusionResizeCrossfadeImages( jQuery( this ) );
            }
        );

		// Make the onsale badge also work on products without image
        jQuery( '.product-images' ).each(
            function() {
                if ( ! jQuery( this ).find( 'img' ).length && jQuery( this ).find( '.onsale' ).length ) {
					jQuery( this ).css( 'min-height', '45px' );
				}
            }
        );

        // Make sure the variation image is also changed in the thumbs carousel and for lightbox
		jQuery( '.variations_form' ).on( 'change', '.variations select', function( event ) {
			var $variations_form = jQuery( this ).parents( '.variations_form' );

			// Timeout needed to get updated image src attribute
			setTimeout( function() {
				var $slider_first_image = jQuery( '.images' ).find( '#slider img:eq(0)' ),
					$slider_first_image_parent_link = $slider_first_image.parent(),
					$slider_first_image_src = $slider_first_image.attr( 'src' ),
					$thumbs_first_image = jQuery( '.images' ).find( '#carousel img:eq(0)' );

				if ( $slider_first_image_parent_link && $slider_first_image_parent_link.attr( 'href' ) ) {
					$slider_first_image_src = $slider_first_image_parent_link.attr( 'href' );
				}

				$slider_first_image.parent().attr( 'href', $slider_first_image_src );


				// Refresh the lightbox
				$avada_lightbox.refresh_lightbox();

				$thumbs_first_image.attr( 'src', $slider_first_image_src );


				var $slider = jQuery( '.images #slider' ).data( 'flexslider' );
				if ( $slider ) {
					$slider.resize();
				}

				$variations_form.find( '.variations .single_variation_wrap .woocommerce-variation-description' ).remove();

			}, 1 );

			setTimeout( function() {
				$avada_lightbox.refresh_lightbox();

				var $slider = jQuery( '.images #slider' ).data( 'flexslider' );
				if ( $slider ) {
					$slider.resize();
				}
			}, 500 );

			setTimeout( function() {
				$avada_lightbox.refresh_lightbox();
			}, 1500 );
		});
    }
);

jQuery( document ).ready( function() {
	jQuery( '.add_to_cart_button' ).click(function(e) {
		var $add_to_cart_button = jQuery( this );

		$add_to_cart_button.closest( '.product' ).find( '.cart-loading' ).find( 'i' ).removeClass( 'fusion-icon-check-square-o' ).addClass( 'fusion-icon-spinner' );
		$add_to_cart_button.closest( '.product' ).find( '.cart-loading' ).fadeIn();
		setTimeout( function(){
			$add_to_cart_button.closest( '.product' ).find( '.cart-loading' ).find( 'i' ).hide().removeClass( 'fusion-icon-spinner' ).addClass( 'fusion-icon-check-square-o' ).fadeIn();
		}, 2000 );
	});

	jQuery('li.product').mouseenter(function() {
		if(jQuery(this).find('.cart-loading').find('i').hasClass('fusion-icon-check-square-o')) {
			jQuery(this).find('.cart-loading').fadeIn();
		}
	}).mouseleave(function() {
		if(jQuery(this).find('.cart-loading').find('i').hasClass('fusion-icon-check-square-o')) {
			jQuery(this).find('.cart-loading').fadeOut();
		}
	});


});
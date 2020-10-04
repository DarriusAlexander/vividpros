// Fade module for Slider Pro.
// 
// Adds the possibility to navigate through slides using a cross-fade effect.
;(function( window, $ ) {

	"use strict";

	var NS = 'TransitionEffects.' + $.SliderPro.namespace;

	var TransitionEffects = {

		// Reference to the original 'gotoSlide' method
		originalGotoSlideReference: null,

		initFade: function() {
			this.on( 'update.' + NS, $.proxy( this._TransitionEffectsOnUpdate, this ) );
		},

		// If fade is enabled, store a reference to the original 'gotoSlide' method
		// and then assign a new function to 'gotoSlide'.
		_TransitionEffectsOnUpdate: function() {
			this.originalGotoSlideReference = this.gotoSlide;
			this.gotoSlide = this._gotoSlide;
		},

		// Will replace the original 'gotoSlide' function by adding a cross-fade effect
		// between the previous and the next slide.
		_gotoSlide: function( index ) {
			if( $(window).data( 'isKeySliding' ) ) {
				return false;
			}
			
			if ( index === this.selectedSlideIndex ) {
				return;
			}
			
			$(window).data( 'isKeySliding', true );
			
			// If the slides are being swiped/dragged, don't use fade, but call the original method instead.
			// If not, which means that a new slide was selected through a button, arrows or direct call, then
			// use fade.
			if ( this.$slider.hasClass( 'sp-swiping' ) ) {
				this.originalGotoSlideReference( index );
			} else {
				var that = this,
					$nextSlide,
					$previousSlide,
					newIndex = index;

				// Loop through all the slides and overlap the the previous and next slide,
				// and hide the other slides.
				$.each( this.slides, function( index, element ) {
					var slideIndex = element.getIndex(),
						$slide = element.$slide;

					if ( slideIndex === newIndex ) {
						$slide.css({ 'opacity': 0, 'left': 0, 'top': 0, 'z-index': 20, 'visibility': 'visible' });
						$nextSlide = $slide;
					} else if ( slideIndex === that.selectedSlideIndex ) {
						$slide.css({ 'opacity': 1, 'left': 0, 'top': 0, 'z-index': 10 });
						$previousSlide = $slide;
					} else {
						$slide.css( 'visibility', 'hidden' );
					}
				});

				// Set the new indexes for the previous and selected slides
				this.previousSlideIndex = this.selectedSlideIndex;
				this.selectedSlideIndex = index;

				// Re-assign the 'sp-selected' class to the currently selected slide
				this.$slides.find( '.sp-selected' ).removeClass( 'sp-selected' );
				this.$slides.find( '.sp-slide' ).eq( this.selectedSlideIndex ).addClass( 'sp-selected' );
			
				// Rearrange the slides if the slider is loopable
				if ( that.settings.loop === true ) {
					that._updateSlidesOrder();
				}

				// Move the slides container so that the cross-fading slides (which now have the top and left
				// position set to 0) become visible and in the center of the slider.
				this._moveTo( this.visibleOffset, true );

				// Fade out the previous slide, if indicated, in addition to fading in the next slide
				// The previous slide is always faded out
				if ( this.settings.fadeOutPreviousSlide === true ) {
					this._transition_effect( $previousSlide, 'fadeOut', $nextSlide.data( 'duration' ) );
				}

				var _transition_callback = function() {
					
					// Reset the position of the slides and slides container
					that._resetSlidesPosition();

					// Wait before reposition is running
					var waitingTime = parseFloat( $nextSlide.data( 'duration' ) ) * 1000;

					setTimeout(function() {
						// After the animation is over, make all the slides visible again
						$.each( that.slides, function( index, element ) {
							var $slide = element.$slide;
							$slide.css({ 'visibility': '', 'opacity': '', 'z-index': '', 'transform' : '' });
						});

						// Fire the 'gotoSlideComplete' event
						that.trigger({ type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex });
						if ( $.isFunction( that.settings.gotoSlideComplete ) ) {
							that.settings.gotoSlideComplete.call( that, { type: 'gotoSlideComplete', index: index, previousIndex: that.previousSlideIndex } );
						}

						/* After the animation is done, recalculate the heights */
						if ( that.settings.autoHeight === true ) {
							that._resizeHeight();
						}
					}, waitingTime );
				};

				/**
				 * This is where magic happens.
				 * Apply the transition effect to next slide
				 */
				var transition = $nextSlide.data( 'transition' );
				var duration = $nextSlide.data( 'duration' );
				this._transition_effect( $nextSlide, transition, duration, _transition_callback );

				if ( this.settings.autoHeight === true ) {
					this._resizeHeight();
				}

				// Fire the 'gotoSlide' event
				this.trigger({ type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				if ( $.isFunction( this.settings.gotoSlide ) ) {
					this.settings.gotoSlide.call( this, { type: 'gotoSlide', index: index, previousIndex: this.previousSlideIndex });
				}
			}
		},

		// slide effect
		_transition_effect: function( target, effect, duration, callback ) {
			var that = this;
			var sp_mask = target.closest( '.sp-mask' );
			var initial_css = {}, // CSS properties applied before transition effect
				css = {}; // CSS properties to make the transition to

			if( effect == 'slideTop' ) {
				initial_css = { opacity : 1, top : '-' + sp_mask.height() + 'px' };
				css = { top : 0 };
			} else if( effect == 'slideBottom' ) {
				initial_css = { opacity : 1, top : sp_mask.height() + 'px' };
				css = { top : 0 };
			} else if( effect == 'slideLeft' ) {
				initial_css = { opacity : 1, left : '-' + sp_mask.width() + 'px' };
				css = { left : 0 };
			} else if( effect == 'slideRight' ) {
				initial_css = { opacity : 1, left : sp_mask.width() + 'px' };
				css = { left : 0 };
			} else if( effect == 'slideTopFade' ) {
				initial_css = { top : '-' + sp_mask.height() + 'px' };
				css = { top : 0, opacity : 1 };
			} else if( effect == 'slideBottomFade' ) {
				initial_css = { top : sp_mask.height() + 'px' };
				css = { top : 0, opacity : 1 };
			} else if( effect == 'slideLeftFade' ) {
				initial_css = { left : '-' + sp_mask.width() + 'px' };
				css = { left : 0, opacity : 1 };
			} else if( effect == 'slideRightFade' ) {
				initial_css = { left : sp_mask.width() + 'px' };
				css = { left : 0, opacity : 1 };
			} else if( effect == 'zoomOut' ) {
				initial_css[that.vendorPrefix + 'transform'] = 'scale(2)';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
			} else if( effect == 'zoomTop' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = '-' + sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect == 'zoomBottom' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'top' ] = sp_mask.height() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'top' ] = 0;
			} else if( effect == 'zoomLeft' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = '-' + sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect == 'zoomTop' ) {
				initial_css[ that.vendorPrefix + 'transform' ] = 'scale(2)';
				initial_css[ 'left' ] = sp_mask.width() + 'px';
				css[ 'opacity' ] = 1;
				css[ that.vendorPrefix + 'transform' ] = 'scale(1)';
				css[ 'left' ] = 0;
			} else if( effect == 'fadeOut' ) {
				initial_css[ 'opacity' ] = 1;
				css[ 'opacity' ] = 0;
			} else { // fadeIn, as fallback
				var css = { opacity : 1 };
			}
			target.css( initial_css );

			// Use CSS transitions if they are supported. If not, use JavaScript animation.
			if ( this.supportedAnimation === 'css-3d' || this.supportedAnimation === 'css-2d' ) {

				// There needs to be a delay between the moment the opacity is set
				// and the moment the transitions starts.
				setTimeout(function(){
					css[ that.vendorPrefix + 'transition' ] = 'all ' + duration + 's';
					target.css( css );
				}, 100 );

				target.on( this.transitionEvent, function( event ) {
					if ( event.target !== event.currentTarget ) {
						return;
					}
					
					target.off( that.transitionEvent );
					target.css( that.vendorPrefix + 'transition', '' );

					if ( typeof callback === 'function' ) {
						callback();
					}
				});
			} else {
				target.stop().animate( css, duration, function() {
					if ( typeof callback === 'function' ) {
						callback();
					}
				});
			}
		},

		// Destroy the module
		destroyTransitionEffects: function() {
			this.off( 'update.' + NS );

			if ( this.originalGotoSlideReference !== null ) {
				this.gotoSlide = this.originalGotoSlideReference;
			}
		}
	};

	$.SliderPro.addModule( 'TransitionEffects', TransitionEffects );

})( window, jQuery );

// Custom autoplay module for Slider Pro.
// 
// Adds automatic navigation through the slides by calling the
// 'nextSlide' or 'previousSlide' methods at certain time intervals.
;(function( window, $ ) {

	"use strict";
	
	var NS = '_Autoplay.' + $.SliderPro.namespace;

	var _Autoplay = {

		_autoplayTimer: null,

		_isTimerRunning: false,

		_isTimerPaused: false,

		initAutoplay: function() {
			this.on( 'update.' + NS, $.proxy( this._autoplayOnUpdate, this ) );
		},

		// Start the autoplay if it's enabled, or stop it if it's disabled but running 
		_autoplayOnUpdate: function( event ) {
			if ( this.settings._autoplay === true ) {
				this.on( 'gotoSlide.' + NS, $.proxy( this._autoplayOnGotoSlide, this ) );
				this.on( 'mouseenter.' + NS, $.proxy( this._autoplayOnMouseEnter, this ) );
				this.on( 'mouseleave.' + NS, $.proxy( this._autoplayOnMouseLeave, this ) );

				this.startAutoplay();
			} else {
				this.off( 'gotoSlide.' + NS );
				this.off( 'mouseenter.' + NS );
				this.off( 'mouseleave.' + NS );

				this.stopAutoplay();
			}
		},

		// Restart the autoplay timer when a new slide is selected
		_autoplayOnGotoSlide: function( event ) {
			// stop previous timers before starting a new one
			if ( this._isTimerRunning === true ) {
				this.stopAutoplay();
			}
			
			if ( this._isTimerPaused === false ) {
				this.startAutoplay();
			}
		},

		// Pause the autoplay when the slider is hovered
		_autoplayOnMouseEnter: function( event ) {
			if ( this._isTimerRunning && ( this.settings._autoplayOnHover === 'pause' || this.settings._autoplayOnHover === 'stop' ) ) {
				this.stopAutoplay();
				this._isTimerPaused = true;

				/* hide the timer bar when autoplay stops */
				if( this.settings._autoplayOnHover === 'stop' ) {
					this.$slider.find( '.bsp-timer-bar' ).fadeOut();
				}
			}
		},

		// Start the autoplay when the mouse moves away from the slider
		_autoplayOnMouseLeave: function( event ) {
			if ( this.settings._autoplay === true && this._isTimerRunning === false && this.settings._autoplayOnHover !== 'stop' ) {
				this.startAutoplay();
				this._isTimerPaused = false;
			}
		},

		// Starts the autoplay
		startAutoplay: function() {
			var that = this;

			this._isTimerRunning = true;
			if( this.settings.timer_bar ) {
				that.$slider.find( '.bsp-timer-bar' ).css( 'width', '0' ).animate( { width: '100%' }, {
					duration : this.settings._autoplayDelay
				} );
			}

			this._autoplayTimer = setTimeout(function() {
				if ( that.settings._autoplayDirection === 'normal' ) {
					that.nextSlide();
				} else if ( that.settings._autoplayDirection === 'backwards' ) {
					that.previousSlide();
				}
			}, this.settings._autoplayDelay );
		},

		// Stops the autoplay
		stopAutoplay: function() {
			this._isTimerRunning = false;
			this._isTimerPaused = false;

			if( this.settings.timer_bar ) {
				this.$slider.find( '.bsp-timer-bar' ).stop();
			}
			clearTimeout( this._autoplayTimer );
		},

		// Destroy the module
		destroyAutoplay: function() {
			clearTimeout( this._autoplayTimer );
			this.$slider.find( '.bsp-timer-bar' ).remove();

			this.off( 'update.' + NS );
			this.off( 'gotoSlide.' + NS );
			this.off( 'mouseenter.' + NS );
			this.off( 'mouseleave.' + NS );
		},

		_AutoplayDefaults: {
			// Indicates whether or not autoplay will be enabled
			_autoplay: true,

			// Sets the delay/interval at which the autoplay will run
			_autoplayDelay: 5000,

			// Indicates whether autoplay will navigate to the next slide or previous slide
			_autoplayDirection: 'normal',

			// Indicates if the autoplay will be paused or stopped when the slider is hovered.
			// Possible values are 'pause', 'stop' or 'none'.
			_autoplayOnHover: 'pause',

			timer_bar : false
		}
	};

	$.SliderPro.addModule( '_Autoplay', _Autoplay );
	
})(window, jQuery);

// Custom module to handle the resizing of the slider
;(function( window, $ ) {

	"use strict";
	
	var NS = 'Resizer.' + $.SliderPro.namespace;

	var Resizer = {

		original_width : '',
		
		original_height : '',

		initResizer: function() {
			this.original_width = this.$slider.closest( '.module-pro-slider' ).data( 'slider-width' );
			this.original_height = this.$slider.closest( '.module-pro-slider' ).data( 'slider-height' );

			if( this.settings.autoHeightOnReize == true ) {
				$( window ).on( 'resize.' + this.uniqueId + '.' + NS, $.proxy( this.resizerEvent, this ) );
				this.on( 'gotoSlideComplete.' + NS, $.proxy( function(){
					
				}, this ) );
			}
		},

		resizerEvent: function( event ) {
			var current_width = this.$slidesContainer.width();
			var new_height;

			/* default slider ratio */
			var height_ratio = 1.9;
			if( typeof builderSliderPro.height_ratio != 'undefined' ) {
				height_ratio = parseFloat( builderSliderPro.height_ratio );
			}

			/* if width and height are set, use that to calculate the new height ratio */
			if( this.original_width != '' && this.original_height != '' ) {
				height_ratio = this.original_width / this.original_height;
			}
			/* if only height is set, use that to calculate the new height ratio */
			else if( this.original_height != '' ) {
				height_ratio = current_width / this.original_height;
			}

			/* calculate new height based on the width */
			new_height = current_width / height_ratio;
			this.settings.height = new_height;

			this.resize();
		},

		// Destroy the module
		destroyResizer: function() {
			$( window ).off( 'resize.' + this.uniqueId + '.' + NS, $.proxy( this.resizerEvent, this ) );
		},

		ResizerDefaults: {
			autoHeightOnReize : false
		}
	};

	$.SliderPro.addModule( 'Resizer', Resizer );
	
})(window, jQuery);

// handle the display of videos in slides
;(function( window, $ ) {

	"use strict";
	
	var NS = 'BSP_Video.' + $.SliderPro.namespace;

	var BSP_Video = {

		initBSP_Video: function() {
			this.on( 'init.' + NS, $.proxy( this.video_handler, this ) );
			this.on( 'gotoSlideComplete.' + NS, $.proxy( this.video_handler, this ) );
		},

		video_handler: function( event ) {
			this.$slides.find( '.bsp-video-iframe' ).attr( 'src', '' );
			var $selected = this.$slider.find( '.sp-selected' );
			$selected.find( '.bsp-video-iframe' ).attr( 'src', $selected.find( '.bsp-video' ).attr( 'data-src' ) );
		},

		// Destroy the module
		destroyBSP_Video: function() {
			$( window ).off( 'gotoSlideComplete.' + this.uniqueId + '.' + NS, $.proxy( this.video_handler, this ) );
		},

		BSP_VideoDefaults: {}
	};

	$.SliderPro.addModule( 'BSP_Video', BSP_Video );
	
})(window, jQuery);
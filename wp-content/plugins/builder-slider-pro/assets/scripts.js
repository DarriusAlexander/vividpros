(function ($) {

         if(!$.fn.themifyBuilderImagesLoaded){
            $.fn.themifyBuilderImagesLoaded = function (callback) {
                    var elems = this.filter('img'),
                                    len = elems.length,
                                    blank = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

                    elems.bind('load.imgloaded', function () {
                            if (--len <= 0 && this.src !== blank) {
                                    elems.unbind('load.imgloaded');
                                    callback.call(elems, this);
                            }
                    }).each(function () {
                            // cached images don't fire load sometimes, so we reset src.
                            if (this.complete || this.complete === undefined) {
                                    var src = this.src;
                                    // webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
                                    // data uri bypasses webkit log warning (thx doug jones)
                                    this.src = blank;
                                    this.src = src;
                            }
                    });

                    return this;
            };
        }
        
	function do_pro_slider(e,$el) {
                var $elements = '';
		if(e && e.type==='builder_load_module_partial'){
			$el = $($el);
			if($el.data('mod-name')==='pro-slider'){
                            $elements = $el.find( '.module.module-pro-slider' );
			}
			else{
                            return false;
			}
		}
		else{
                    $elements = $( '.module.module-pro-slider' );
		}
                
                Themify.LoadAsync(builderSliderPro.url+'jquery.sliderPro.min.js',function(){
                    Themify.LoadAsync(builderSliderPro.url+'sliderPro.helpers.js',callback, '1.2.1', null, function(){
                            return ($.inArray('TransitionEffects', $.SliderPro.modules)!==-1);
                    } );
                    }, '1.2.1', null, function(){
                        return ('undefined' !== typeof $.fn.sliderPro);
                } );
               
		function callback(){
                    
                    function call_slider($this,config){
                            $this.find( '.slider-pro' )
                                    .sliderPro( config )
                                    .css( 'visibility', 'visible' )
                                    .trigger( 'resize' );
                    }
                    $elements.each(function(){
                            if($(this).find('.sp-slides-container').length>0){//temprorary solution for responsive live preview, after iframe optimization must be deleted
                                var slides = $(this).find('.sp-slides');
                                slides.find('.sp-slide').each(function(){
                                        $(this)
                                        .removeAttr('data-index data-init data-loaded')
                                        .data({'index':'','init':'','loaded':''});
                                        var bg= $(this).css('background-image');
                                        $(this).removeAttr('style');
                                        if(bg){
                                            $(this).css('background-image',bg);
                                        }
                                });
                                slides.find('.sp-layer').removeAttr('style').data('layer-init','').removeAttr('data-layer-init');
                                $(this).html('<div class="themify_builder_slider_loader"></div><div class="slider-pro" style="visibility: hidden;"><div class="sp-slides">'+slides.html()+'</div></div>');
                            }
                            var $this = $( this ),
                            config = {
                                    buttons: !$this.hasClass( 'pager-none' ) && !$this.hasClass( 'pager-type-thumb' ),
                                    arrows : true,
                                    responsive : true,
                                    thumbnailWidth: $this.data( 'thumbnail-width' ),
                                    thumbnailHeight: $this.data( 'thumbnail-height' ),
                                    width : $this.data( 'slider-width' ) != '' ? parseInt( $this.data( 'slider-width' ) ) : '100%', // set default slider width to 100%
                                    // forceSize : 'fullWidth', // force the slider to span the width of the page
                                    height : $this.data( 'slider-height' ) != '' ? parseInt( $this.data( 'slider-height' ) ) : 400, // set default slider height to 400
                                    fadeOutPreviousSlide: false,		
                                    touchSwipe : $( 'body' ).hasClass( 'builder-is-touch' ), // on touch devices, enable the touchSwipe
                                    init:function(){
                                            $this.find('.themify_builder_slider_loader').remove();
                                    },
                                    gotoSlideComplete: function( event ) {
                    					$(window).data( 'isKeySliding', false );
                    				}
                            };

                            /* custom autoplay module settings */
                            config.autoHeightOnReize = true;
                            config._autoplay = $this.data( 'autoplay' ) !== 'off';
                            config._autoplayDelay = $this.data( 'autoplay' );
                            config.timer_bar = $this.data( 'timer-bar' ) == 'yes';
                            config._autoplayOnHover = $this.data( 'hover-pause' );
                            if( $this.data( 'timer-bar' ) == 'yes' ) {
                                    $( '.slider-pro', $this ).prepend( '<div class="bsp-timer-bar" />' );
                            }

                            /* do not edit these configurations */
                            config.autoplay = false; // disable the default Autoplay module
                            config.autoHeight = false;
                            config.thumbnailTouchSwipe = true; // this is required for the thumbnail click action to work
                            var $images = $this.find('img');
                            if($images.length>0){
                                    $images.themifyBuilderImagesLoaded(function () {
                                            call_slider($this,config);
                                    });
                            }
                            else{
                                call_slider($this,config);
                            }
                    });
                }
	}

	$(document).ready(function(){
            do_pro_slider();
        });
	$( 'body' ).on( 'builder_load_module_partial builder_toggle_frontend', do_pro_slider );

}(jQuery));
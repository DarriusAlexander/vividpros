;
(function($, window, document, undefined){

    "use strict";

    var InfiniteBgFront = {
        init: function() {
            var self = InfiniteBgFront;
            self.bindEvents();
        },

        bindEvents: function() {
            var self = InfiniteBgFront;
            $(document).on('tfb.live_styling.after_create', self.bindLiveStyling)
            .on('tb:row:create', function(e, $el, $rawHtml ) {
                var $infiniteBGStyle = $rawHtml.find('.themify_builder_infinite_bg');
                if ( $infiniteBGStyle.length ) {
                    $el.find('.row_inner').prepend( $infiniteBGStyle );
                }
            });
        },

        removeInfiniteBg: function($component) {
            var componentType = ThemifyBuilderCommon.getComponentType($component);

            var $infiniteBg = null;

            if (componentType === 'row') {
                $infiniteBg = $component.find('.row_inner').first().children('.themify_builder_infinite_bg');
            }

            $infiniteBg.remove();
        },

        bindLiveStyling: function(e, liveStylingInstance) {
            var self, getInfiniteBg, getOptions, insertToHTML, shouldContinue;

            self = InfiniteBgFront;

            getInfiniteBg = function(options) {
                var $elmt = liveStylingInstance.$liveStyledElmt;

                return $.post(
                    themifyBuilder.ajaxurl,
                    {
                        nonce : themifyBuilder.tfb_load_nonce,
                        action: 'tfb_infinite_bg_live_styling',
                        tfb_infinite_background_data: $.extend({},
                            options,
                            {
                                'row_order': $elmt.attr('data-cid'),
                                'builder_id': $elmt.attr('data-cid')
                            }
                        )
                    }
                );
            };

            getOptions = function() {
                return {
                    type: $('input[name="row_scrolling_background"]:checked').val(),
                    width: $('#row_scrolling_background_width').val(),
                    height: $('#row_scrolling_background_height').val(),
                    speed: $('#row_scrolling_background_speed').val()
                };
            };

            insertToHTML = function($infiniteBg) {
                var $elmt = liveStylingInstance.$liveStyledElmt;
                if($elmt){
                    var type = ThemifyBuilderCommon.getComponentType($elmt);
                    if (type === 'row') {
                        $elmt.find('.row_inner').prepend($infiniteBg);
                    }
                }
            };

            shouldContinue = function() {
                var options = getOptions();

                if (options.type === 'disable') {
                    return false;
                }

                if (options.type === 'bg-scroll-horizontally' && !options.width.length) {
                    return false;
                }

                if (options.type === 'bg-scroll-vertically' && !options.height.length) {
                    return false;
                }

                return true;
            };

            liveStylingInstance.$context.on(
                'change',
                'input[name="row_scrolling_background"],' +
                '#row_scrolling_background_speed,' +
                '#row_scrolling_background_width,' +
                '#row_scrolling_background_height',
                function() {
                    self.removeInfiniteBg(liveStylingInstance.$liveStyledElmt);

                    if (!shouldContinue()) {
                        return;
                    }

                    getInfiniteBg(getOptions()).done(function(infiniteBg) {
                        if (infiniteBg.length < 10) {
                            return;
                        }

                        var $infiniteBg = $(infiniteBg);

                        insertToHTML($infiniteBg);
                    });
            });
        }
    };

    InfiniteBgFront.init();

}(jQuery, window, document));

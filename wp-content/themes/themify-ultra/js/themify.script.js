/* Themify Theme Scripts - https://themify.me/ */
(function ($) {
    'use strict';
    $(function() {
        var $sections = $('.type-section'),
                usesRows = !$sections.length,
                isFullPageScroll = !Themify.is_builder_active && themifyScript.fullPageScroll && Themify.body[0].classList.contains('full-section-scrolling'),
                sectionClass = '.section-post:not(.section-post-slide)',
                is_horizontal_scrolling = isFullPageScroll && Themify.body[0].classList.contains('full-section-scrolling-horizontal'),
                slideClass = '.module_row_slide',
                sectionsWrapper = 'div:not(.module-layout-part) > #loops-wrapper',
                fixedHeader = Themify.body[0].classList.contains('fixed-header'),
                wowInit2;

        // Setup variables if it uses Builder rows instead of section post type
        if (isFullPageScroll) {
            if (usesRows) {
                isFullPageScroll = $('.themify_builder').length > 0;
                sectionClass = '.module_row:not('+slideClass+')';
                sectionsWrapper = 'div:not(.module-layout-part) > .themify_builder_content:not(.not_editable_builder)';
            }
            isFullPageScroll && updateFullPage();
        }

        // Remove non visible rows
        function updateFullPage() {
            var rows = usesRows?$(sectionsWrapper+'>.module_row') : $(sectionsWrapper + '>' + sectionClass),
                    bp = themifyScript.responsiveBreakpoints || {},
                    winWidth = window.innerWidth,
                    bpRange = {
                        desktop: winWidth >= bp.tablet_landscape,
                        tablet: winWidth < bp.tablet_landscape && winWidth >= bp.mobile,
                        mobile: winWidth <= bp.mobile
                    };

            rows.each(function () {
                var $el = $(this),
                        cl = this.classList;

                if ($el.is(':hidden')) {
                    $el.remove();
                } else if (cl.contains('hide-desktop') || cl.contains('hide-tablet') || cl.contains('hide-mobile')) {
                    for (var key in bpRange) {
                        bpRange[key] === true && cl.contains('hide-' + key) && $el.remove();
                    }
                }
            });
            // Set default row column alignment
            window.top._rowColAlign = 'col_align_middle';
        }

        // Fixed Header /////////////////////////
        var FixedHeader = {
            headerHeight: 0,
            hasHeaderSlider: false,
            headerSlider: false,
            $pageWrap: $('#pagewrap'),
            $headerWrap: $('#headerwrap'),
            $window: $(window),
            stickyHeader: themifyScript.sticky_header,
            init: function () {
                if (!Themify.is_builder_active) {
                    var _this = this;

                    _this.calculateHeaderHeight();

                    if (Themify.body.hasClass('revealing-header') && 'undefined' !== typeof _this.$headerWrap[0]) {
                        this.headerRevealing();
                    }
                    if (fixedHeader) {
                        if (isFullPageScroll) {
                            Themify.body.on('themify_onepage_afterload', function (event, $section, section_id) {
                                var $is_scroll = $(slideClass + '.active', $('.section-container.active')).closest('.section-container').index() > 0;
                                _this.activate($is_scroll);

                                if ($is_scroll) {
                                    var $height = _this.headerHeight;

                                    _this.calculateHeaderHeight();
                                    $height != _this.headerHeight && _this.updatePageOffset()
                                }
                            });
                        } else {
                            _this.activate(false);
                            this.$window.on('scroll touchstart.touchScroll touchmove.touchScroll', function (e) {
                                _this.activate(false);
                            });
                        }

                        this.$window.one('load',function () {
                                    _this.calculateHeaderHeight();
                                    _this.updatePageOffset();

                                    // Fix async custom styles
                                    setTimeout(function () {
                                        _this.calculateHeaderHeight();
                                        _this.updatePageOffset();
                                    }, 400);
                                })
                                .on('tfsmartresize', function (e) {
                                    if (this.loaded === true && (Themify.w !== e.w || Themify.h !== e.h)) {
                                        setTimeout(function () {
                                            _this.calculateHeaderHeight();
                                            _this.updatePageOffset();
                                        }, 400);
                                    }
                                });

                        if ($('#gallery-controller').length > 0)
                            _this.hasHeaderSlider = true;

                        if (_this.stickyHeader) {
                            var img = '<img id="sticky_header_logo" src="' + _this.stickyHeader.src + '"';
                            img += '/>';
                            $('#site-logo a').prepend(img);
                        }

                        Themify.body.on('announcement_bar_position announcement_bar_scroll_on_after announcementBarUpdate', _this.calculateHeaderHeight.bind(_this));
                    }
                }
            },
            headerRevealing: function () {
                var direction = 'down',
                        previousY = 0,
                        _this = this,
                        onScroll = function () {
                            if(previousY === window.scrollY){
                                return;
                            }
                            direction = previousY < window.scrollY ? 'down' : 'up';
                            previousY = window.scrollY;
                            if ('up' === direction || 0 === previousY) {
                                if (_this.$headerWrap.hasClass('hidden')) {
                                    _this.$headerWrap.css('top', '').removeClass('hidden');
                                }
                            } else if (0 < previousY && !_this.$headerWrap.hasClass('hidden')) {
                                _this.$headerWrap.css('top', -_this.$headerWrap.outerHeight()).addClass('hidden');
                            }
                        };
                this.$window.on('scroll touchstart.touchScroll touchmove.touchScroll', onScroll);
                onScroll();
            },
            activate: function ($hard) {
				if( $hard || this.$window.scrollTop() >= this.headerHeight ) {
						! this.$headerWrap.hasClass( 'fixed-header' ) && this.scrollEnabled();
				} else if ( this.$headerWrap.hasClass( 'fixed-header' ) ) {
						this.scrollDisabled();
				}
            },
            scrollDisabled: function () {
                this.$headerWrap.removeClass('fixed-header');
                $('#header').removeClass('header-on-scroll');
                Themify.body.removeClass('fixed-header-on');

                /**
                 * force redraw the header
                 * required in order to calculate header height properly after removing fixed-header classes
                 */
                this.$headerWrap.hide();
                this.$headerWrap[0].offsetHeight;
                this.$headerWrap.show();
                this.calculateHeaderHeight();
                this.updatePageOffset();
                this.triggerHeaderSlider();


            },
            scrollEnabled: function () {
                this.$headerWrap.addClass('fixed-header');
                $('#header').addClass('header-on-scroll');
                Themify.body.addClass('fixed-header-on');
                this.triggerHeaderSlider();

                this.updatePageOffset();
            },
            triggerHeaderSlider: function () {
                if (this.hasHeaderSlider && 'object' === typeof this.$headerWrap.data('backstretch')) {
                    this.$headerWrap.data('backstretch').resize();
                    $('#gallery-controller .slides').trigger('next');
                }
            },
            calculateHeaderHeight: function () {
                var offset = this.$headerWrap.css('position') === 'fixed' ? $('body').offset().top : '';
                this.headerHeight = this.$headerWrap.outerHeight(true) - (offset ? parseInt(offset) : 0);
                this.$headerWrap.css('margin-top', offset);
            },
            updatePageOffset: function () {
                if(!is_horizontal_scrolling) {
                this.$pageWrap.css('paddingTop', Math.floor(this.headerHeight));
            }
            }
        };
        FixedHeader.init();

        // Initialize carousels //////////////////////////////
        var ThemifySlider = {
            recalcHeight: function (items, $obj) {
                var heights = [], height;
                $.each(items, function () {
                    heights.push($(this).outerHeight(true));
                });
                height = Math.max.apply(Math, heights);
                $obj.closest('.carousel-wrap').find('.caroufredsel_wrapper, .slideshow').each(function () {
                    $(this).outerHeight(height);
                });
            },
            didResize: false,
            createCarousel: function (obj) {
                var self = this;
                obj.each(function () {
                    var $this = $(this),
                        id=$this.data('id');
                    $this.carouFredSel({
                        responsive: true,
                        prev: '#' + id + ' .carousel-prev',
                        next: '#' + id + ' .carousel-next',
                        pagination: {
                            container: '#' + id + ' .carousel-pager'
                        },
                        circular: true,
                        infinite: true,
                        swipe: true,
                        scroll: {
                            items: $this.data('scroll'),
                            fx: 'scroll',
                            duration: parseInt($this.data('speed'))
                        },
                        auto: {
                            play: ('off' !== $this.data('autoplay')),
                            timeoutDuration: 'off' !== $this.data('autoplay') ? parseInt($this.data('autoplay')) : 0
                        },
                        items: {
                            visible: {
                                min: 1,
                                max: $this.data('visible') ? parseInt($this.data('visible')) : 1
                            },
                            width: 222
                        },
                        onCreate: function (items) {
                            var $slideWrap = $this.closest('.slideshow-wrap');
                            $slideWrap.css({
                                'visibility': 'visible',
                                'height': 'auto'
                            });

                            self.recalcHeight(items.items, $this);
                            $(window).on('tfsmartresize', function () {
                                self.recalcHeight(items.items, $this);
                            });
                            setTimeout(function () {
                                $slideWrap.find('.carousel-nav-wrap').css('width', (parseInt($slideWrap.find('.carousel-pager').find('a').length) * 18) + 'px');
                            }, 200);
                        }
                    });
                });
            }
        };

        // Get builder rows anchor class to ID //////////////////////////////
        function getClassToId($section) {
            var classes = $section.prop('class').split(' '),
                    expr = new RegExp('^tb_section-', 'i'),
                    spanClass = null;
            for (var i = 0, len = classes.length; i < len; ++i) {
                if (expr.test(classes[i])) {
                    spanClass = classes[i];
                }
            }

            return spanClass === null?'':spanClass.replace('tb_section-', '');
        }
        // Create fullpage scrolling //////////////////////////////
        function createFullScrolling() {

            var $body = Themify.body,
                    initFullPage = false,
                    $wrapper = $(sectionsWrapper),
                    autoScrolling = !(!usesRows && '' != themifyScript.hash.replace('#', '')),
                    currentHash = themifyScript.hash.replace('#', ''),
                    isParralax = $body[0].classList.contains('section-scrolling-parallax-enabled'),
                    rows = document.getElementsByClassName('module_row')[0],
                    $sectionClass = null,
                    slideCl = slideClass.replace('.', ''),
                    sectionAnchors = [],
                    items = null;

            if (rows !== undefined) {
                    var temp = document.getElementsByClassName(slideCl)[0];//don't remove this will break horizontal scrolling
                    if (temp !== undefined) {
                        temp.classList.remove(slideCl);
                    }
                    temp = null;
                    var $sectionClass = $(sectionsWrapper + '>' + sectionClass);
                $sectionClass.each( function () {
                    var $current = $( this ),
                        f = document.createDocumentFragment(),
                        wrap = document.createElement( 'div' ),
                        cl = this.classList,
                        section_anchor = '';
                    for ( var i = cl.length - 1; i > -1; --i ) {
                        if ( cl[i].indexOf( 'tb_section-' ) === 0 ) {
                            section_anchor = getClassToId( $current );
                            break;
                        }
                    }
                    section_anchor = '1' === $current.attr( 'data-hide-anchor' ) ? '' : section_anchor;
                    sectionAnchors.push( section_anchor );
                    while ( true ) {
                        var next = $current.next()[0];
                        if ( next !== undefined && next.classList.contains( slideCl ) ) {
                            f.appendChild( next );
                        } else {
                            break;
                        }
                    }
                    wrap.className = 'section-container';
                    for ( var i = cl.length - 1; i > -1; --i ) {
                        if ( cl[i] !== 'fullwidth' && cl[i] !== 'fullcover' && cl[i].indexOf( 'module_row_' ) !== 0 && cl[i].indexOf( 'tb_' ) !== 0 ) {
                            wrap.className += ' ' + cl[i];
                        }
                    }
                    cl.add( slideCl );
                    this.parentNode.insertBefore( wrap, this );
                    wrap.appendChild( this );
                    wrap.appendChild( f );
                    wrap.style['display'] = 'block';
                } );

                items = document.getElementsByClassName(slideCl);
                for (var i = items.length - 1; i > -1; --i) {
                    items[i].parentNode.style['display'] = 'none';
                    var inner = items[i].getElementsByClassName('row_inner'),
                            $this = $(items[i]),
                            paddingTop = $this.css('padding-top').replace(/%/g, 'vh'),
                            paddingBottom = $this.css('padding-bottom').replace(/%/g, 'vh');
                    for (var j = inner.length - 1; j > -1; --j) {
                        inner[j].style['paddingTop'] = paddingTop;
                        inner[j].style['paddingBottom'] = paddingBottom;
                    }
                    items[i].style['paddingTop'] = items[i].style['paddingBottom'] = 0;
                    items[i].parentNode.style['display'] = '';
                }
                    }
            var menu=document.getElementById('main-nav'),
                $menu=$(menu);
            if ( usesRows && items!==null && menu!==null) {
                for(var i=items.length-1;i>-1;--i){
                    var slide_id=null,
                        cl = items[i].classList,
                        $this=$(items[i]);
                    for(var j=cl.length-1;j>-1;--j){
                        if(cl[j].indexOf('tb_section-')===0){
                            slide_id = getClassToId($this);
                            break;
                        }
                    }
                    if(slide_id===null){
                        slide_id=items[i].id;
                    }
                    if(slide_id){
                        var $aSectionHref = menu.querySelector('a[href$="#' + slide_id + '"]');
                        if ($aSectionHref!==null) {
                            var section_id = $this.closest('.module_row').data('anchor');
                            if(section_id){
                                $($aSectionHref).attr('href', '#' + section_id + '/' + slide_id).closest('li').attr('data-menuanchor', section_id + '/' + slide_id);
                            }
                        }
                    }
                }
            }
            $wrapper.fullpage({
                resize: false,
                sectionSelector: '.section-container',
                slideSelector: slideClass,
                anchors: sectionAnchors,
                scrollOverflow: true,
                navigation: true,
                lockAnchors: true,
                verticalCentered: true,
                autoScrolling: autoScrolling,
                menu: menu!==null?'#'+menu.id:'',
				/* horizontal scrolling is only disabled on vertical-direction pages with Snake-style scrolling disabled */
                scrollHorizontally: ( $body[0].classList.contains( 'full-section-scrolling-single' ) && ! $body[0].classList.contains( 'full-section-scrolling-horizontal' ) ) ? false : true,
                scrollHorizontallyKey: 'QU5ZX1UycmMyTnliMnhzU0c5eWFYcHZiblJoYkd4NWhLbA==',
                slidesNavigation: true,
                parallax: isParralax,
                parallaxKey: 'QU5ZX0FodGNHRnlZV3hzWVhnPXY1bA==',
                parallaxOptions: {
                    type: 'reveal',
                    percentage: 62,
                    property: 'translate'
                },
                scrollOverflowOptions: {
                    hideScrollbars: true,
                    preventDefault: false
                },
                afterRender: function () {
                    if (!autoScrolling) { // hack deep linking not working when use section row
                        $.fn.fullpage.setAutoScrolling(true);
                    }
                    var $section = $(slideClass + '.active, .section', $('.section-container.active')),
                            section_id = usesRows && $section.is('[class*="tb_section-"]') ? getClassToId($section) : $section.prop('id'),
                            $aSectionHref = $menu.find('a[href$="#' + section_id + '"]');


                    if ('undefined' !== typeof ThemifyBuilderModuleJs && 'undefined' !== typeof wowInit2 && null !== ThemifyBuilderModuleJs.wow) {
                        if (is_horizontal_scrolling) {
                            ThemifyBuilderModuleJs.wow.stop();
                            $body.triggerHandler( 'themify_onepage_afterload', [ $section, section_id ] );
                        }
                    }

                    if (usesRows) {
                        var extraEmptyRow = $('#fp-nav').find('li').get($('.module_row').length);
                        if ('undefined' !== typeof extraEmptyRow) {
                            extraEmptyRow.hide();
                        }
                    }

                    if ($aSectionHref.length > 0) {
                        $aSectionHref.closest('li').addClass('current_page_item').siblings().removeClass('current_page_item current-menu-item');
                    } else {
                        $menu.find('li').removeClass('current_page_item current-menu-item');
                    }

                    $body.on({
                        mouseenter: function () {
                            var t = $(this).find('a').attr('href').replace('#', '');
                            if (t.length>0) {
                                $('<div class="multiscroll-tooltip">' + t + "</div>").hide().appendTo($(this)).fadeIn(200);
                            }
                        },
                        mouseleave: function () {
                            $(this).find(".multiscroll-tooltip").fadeOut(200, function () {
                                $(this).remove();
                            });
                        }
                    }, "#fp-nav li");

                    var coverSelectors = '.builder_row_cover, .row-slider, .column-slider, .subrow-slider',
                            rowCovers = $(sectionClass).find('.fp-tableCell, .fp-scrollable, .fp-scroller').children(coverSelectors);

                    if (rowCovers.length) {
                        rowCovers.each(function () {
                            var row = $(this).closest('.module_row');
                            !row.is(coverSelectors) && row.prepend(this);
                        });
                    }

                    $body.triggerHandler('themify_onepage_after_render', [$section, section_id]);

                    function backgroundImage() {
                        $(slideClass).each(function () {
                            var $fpBackground = $('<div>');
                            $fpBackground.addClass('fp-bg')
                                    .css({
                                        'background-image': $(this).css('background-image'),
                                        /**
                                         * Note: Builder row overlay and background video are at z-index 0
                                         */
                                        'z-index': 0,
                                    });
                            $(this).css('background-image', 'none').prepend($fpBackground);
                        });
                    }

                    if (isParralax) {
                        if (document.querySelector(slideClass+'[data-fullwidthvideo]') !== null) {
                            $body.one('tb_bigvideojs_loaded', backgroundImage);
                        } else {
                            backgroundImage();
                        }
                    }


                },
                afterLoad: function (anchorLink, index) {
				  

                    var $section = $(sectionClass + '.active', $(sectionsWrapper)),
                        section_id = usesRows && $section.is('[class*="tb_section-"]') ? getClassToId($section) : $section.prop('id');
                        if(!section_id){
                           section_id = $section.data('anchor');
                        }
                        var $aSectionHref = $menu.find('a[href$="#' + section_id + '"]');
                        if($aSectionHref.length===0){
                            $aSectionHref = $menu.find('a[href$="#' + section_id + '/' + $section.find(slideClass + '.active').data('anchor') + '"]');
                        }

                    if ('undefined' !== typeof ThemifyBuilderModuleJs && 'undefined' !== typeof wowInit2 && null !== ThemifyBuilderModuleJs.wow) {
                        ThemifyBuilderModuleJs.wow.stop();
                    }

                    if ($aSectionHref.length > 0) {
                        $aSectionHref.closest('li').addClass('current_page_item').siblings().removeClass('current_page_item current-menu-item');
                    } else {
                        $menu.find('li').removeClass('current_page_item current-menu-item');
                    }
                    if (section_id) {
                            var new_hash = is_horizontal_scrolling ? section_id + '/' + section_id : section_id;
                            if (initFullPage && currentHash !== section_id) {
                                    history.pushState(null, null, '#' + new_hash);
                            } else {
                               history.replaceState(null, null, '#' + new_hash);
                            }
                    } else {
                            history.replaceState(null, null, location.pathname);
                    }

                    initFullPage = true;

                    $body.triggerHandler('themify_onepage_afterload', [$section, section_id]);

                    if (fixedHeader && index != 1) {
                        !FixedHeader.$headerWrap.hasClass('fixed-header') && FixedHeader.scrollEnabled();
                    }

                    /* resume the row video background if it exists */
					$section.find('.tf-video').each(function () {
						if (typeof this.play === 'function') {
							this.play();
						}
					});
                },
                onLeave: function (index, nextIndex, direction) {

                    $body.removeClass('fullpagescroll-up fullpagescroll-down').addClass('fullpagescroll-' + direction);

                    // when lightbox is active, prevent scrolling the page
                    if ($body.find('> .mfp-wrap').length > 0) {
                        return false;
                    }

                    var $rows = usesRows ? $(sectionsWrapper).children('.section-container') : $(sectionsWrapper).find(sectionClass);
                    if ($rows.length > 0) {

                        if (index > 0 && nextIndex > 0) {
                            var sectionIndex = index;
                            if ('up' === direction) {
                                for (sectionIndex = index; sectionIndex >= nextIndex; sectionIndex--) {
                                    $rows.eq(sectionIndex - 1).find('.module_row').css('visibility', 'visible');
                                }
                            } else {
                                for (sectionIndex = index; sectionIndex <= nextIndex; sectionIndex++) {
                                    $rows.eq(sectionIndex - 1).find('.module_row').css('visibility', 'visible');
                                }
                            }

                        }
                    }
                },
                afterSlideLoad: function (section, origin, destination, direction) {
                    var $aSectionHref = $menu.find('a[href$="#' + section + '/' + destination + '"]');
                    if ($aSectionHref.length===0) {
                        $aSectionHref = $menu.find('a[href$="#' + destination + '/' + destination + '"]');
                        if ($aSectionHref.length===0) {
                             $aSectionHref = $menu.find('a[href$="#' + destination + '"]');
                        }
                    }
                    if ($aSectionHref.length > 0) {
                        $aSectionHref.closest('li').addClass('current_page_item').siblings().removeClass('current_page_item current-menu-item');
                    } else {
                        $menu.find('li').removeClass('current_page_item current-menu-item');
                    }
                    if (typeof destination === 'string') {
                        history.replaceState(null, null, '#' + (section !== '' ? (section + '/' + destination) : destination));
                    } else {
                        history.replaceState(null, null, location.pathname);
                    }
                    /* resume the row video background if it exists */
                    var $section = $(sectionClass + '.active', $(sectionsWrapper));
                        $section.find('.tf-video').each(function () {
                            if (typeof this.play === 'function') {
                                this.play();
                            }
                    });
                    $body.triggerHandler( 'themify_onepage_afterload', [ $section, $section.data( 'anchor' ) ] );
                },
                onSlideLeave: function (anchorLink, index, slideIndex, direction, nextSlideIndex, nextSlide) {
                    var $slides = $('.section-container').find(slideClass);
                    $body.triggerHandler('themify_onepage_slide_onleave', [$slides.eq(nextSlideIndex)]);

                    var i = slideIndex;
                    if ('left' === direction) {
                        for (i = slideIndex; i > nextSlideIndex; --i) {
                            $slides.eq(i - 1).css('visibility', 'visible');
                        }
                    } else if ('right' === direction) {
                        for (i = slideIndex; i < nextSlideIndex; ++i) {
                            $slides.eq(i + 1).css('visibility', 'visible');
                        }
                    }
                }
            });

        }



        // Infinite Scroll ///////////////////////////////
        function doInfinite($container, selector) {
            Themify.infinity($container[0],{
                append: selector, // selector for all items you'll retrieve
                scrollToNewOnLoad:themifyScript.scrollToNewOnLoad,
                scrollThreshold: 'auto' !== themifyScript.autoInfinite?false:$('#footerwrap').height(),
                history: !themifyScript.infiniteURL?false:'replace',
                button:$('#load-more a')[0]
            });
        }

        var $body = Themify.body,
            bodyCl=$body[0].classList,
            $header = $('#header'),
            $header_icons = $('.header-icons'),
            $menu_icon = $('#menu-icon'),
            $cart_icon = $('#cart-icon'),
            $icon = $('.cart-icon'),
            $iconClone = $icon.clone(),
            moveCartIcon = function () {
                if ($icon.length) {
                    var iconContainer = null;
                    if ($body.is('.header-leftpane, .header-rightpane')) {
                        iconContainer = $('.social-widget');
                    } else if ($header_icons.is(':visible')) {
                        iconContainer = $header_icons;
                    }
                    if (iconContainer && !$iconClone.data('icon-moved')) {
                        if($body.hasClass('slide-cart')){
                            $iconClone.themifySideMenu({
                                panel: '#slide-cart',
                                close: '#cart-icon-close'
                            });
                        }
                        $iconClone.data('icon-moved', 1).appendTo(iconContainer);
                    }

                    $iconClone.toggle(!!iconContainer);
                    $icon.toggle(!iconContainer);
                }
            };

        // Move cart icon
        moveCartIcon();
        $(window).on('tfsmartresize orientationchange', moveCartIcon);

        ///// Header Top Widget 
        var header_top_widget_content = $('.header-top-widgets .header-widget').wrap('</p>').parent().html();
        if (header_top_widget_content != undefined) {
            $('.header-top-widgets #headerwrap').prepend("<div class='header-widget-full clearfix'><div class='header-widget-inner'>" + header_top_widget_content + "</div></div>");
        }
        header_top_widget_content=null;
		
        /////////////////////////////////////////////
        // Initialize Packery Layout and Filter
        /////////////////////////////////////////////
        Themify.isoTop('.masonry.loops-wrapper,.post-filter+.loops-wrapper');
        if (themifyScript.shop_masonry === 'yes') {
                // add grid{n} classes, required for masonry
				$( '.woocommerce.archive #content ul.products' ).addClass( function( index, classes ) {
					return classes.replace( /columns-(\d)/, 'grid$1' );
				} );
                Themify.isoTop('.woocommerce.archive #content ul.products',{itemSelector: '.product'});
        }
        Themify.isoTop('.packery-gallery.gallery-wrapper',{layoutMode:'packery','gutter':false,'columnWidth':false, itemSelector: '.item'});
        /////////////////////////////////////////////
        // Scroll to top
        /////////////////////////////////////////////
        var $back_top = $('.back-top');
        if ($back_top.length > 0) {
            if (!isFullPageScroll && $back_top.hasClass('back-top-float')) {
                $(window).on('scroll touchstart.touchScroll touchmove.touchScroll', function () {
                    if (window.scrollY < 10) {
                        $back_top.addClass('back-top-hide');
                    } else {
                        $back_top.removeClass('back-top-hide');
                    }
                });

            }
            $back_top.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (isFullPageScroll) {
                    $('#footerwrap').toggleClass('expanded');
                }
                else {
                    Themify.scrollTo();
                }
            });
        }
        function toggleMobileSidebar() {
            var item = $('.toggle-sticky-sidebar'),
                sidebar = $("#sidebar");
            item.on('click', function () {
                if (item.hasClass('open-toggle-sticky-sidebar')) {
                    item.removeClass('open-toggle-sticky-sidebar').addClass('close-toggle-sticky-sidebar');
                    sidebar.addClass('open-mobile-sticky-sidebar');
                } else {
                    item.removeClass('close-toggle-sticky-sidebar').addClass('open-toggle-sticky-sidebar');
                    sidebar.removeClass('open-mobile-sticky-sidebar');
                }
            });
        }
        
        function bodyOverlay(){
            var $overlay = $('<div class="body-overlay">');
            $body.append($overlay).on('sidemenushow.themify', function () {
                $overlay.addClass('body-overlay-on');
            }).on('sidemenuhide.themify', function () {
                $overlay.removeClass('body-overlay-on');
            }).on('click.themify touchend.themify', '.body-overlay', function () {
                $menu_icon.themifySideMenu('hide');
                $cart_icon.themifySideMenu('hide');
            });
            $(window).on('tfsmartresize',function () {
                if ($('#mobile-menu').hasClass('sidemenu-on') && $menu_icon.is(':visible')) {
                    $overlay.addClass('body-overlay-on');
                } else {
                    $overlay.removeClass('body-overlay-on');
                }
            });
        }
        toggleMobileSidebar();
       
        /////////////////////////////////////////////
        // Toggle main nav on mobile
        /////////////////////////////////////////////
        if (Themify.isTouch && typeof $.fn.themifyDropdown !== 'function') {
            Themify.LoadAsync(themify_vars.url + '/js/themify.dropdown.js', function () {
                $('#main-nav').themifyDropdown();
            });
        }
        var sideMenu='right';
        if(bodyCl.contains('header-slide-out') || bodyCl.contains('header-rightpane') || bodyCl.contains('header-minbar') || bodyCl.contains('header-leftpane')){
            if(bodyCl.contains('header-leftpane') || bodyCl.contains('header-minbar')){
                sideMenu='left';
            }
            if (bodyCl.contains('header-leftpane') || bodyCl.contains('header-rightpane')) {
                bodyOverlay();
            }
            if (!Themify.isTouch && ($header.length || bodyCl.contains('header-slide-out'))) {
                var nicescrollMenu = function () {
                    var $niceScrollTarget = $header;
                    if (bodyCl.contains('header-slide-out') || bodyCl.contains('header-minbar')) {
                        $niceScrollTarget = $('#mobile-menu');
                    }
                    $niceScrollTarget.niceScroll();
                    $body.on('sidemenushow.themify', function () {
                        setTimeout(function () {
                                $niceScrollTarget.getNiceScroll().resize();
                        }, 200);
                    });
                };
                if (typeof $.fn.niceScroll !== 'function') {
                    Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.nicescroll.min.js', nicescrollMenu, null, null, function () {
                        return ('undefined' !== typeof $.fn.niceScroll);
                    });
                } else {
                    nicescrollMenu();
                }
            }
        } 
        else {
            bodyOverlay();
        }
        /////////////////////////////////////////////
        // Side Menu for all other header designs
        /////////////////////////////////////////////
        $menu_icon.themifySideMenu({
            close: '#menu-icon-close',
            'side':sideMenu
        });
        if (!bodyCl.contains('header-overlay')) {

            var expand=undefined!=themifyScript.m_m_expand?' toggle-on':'';
            $("#main-nav > li.menu-item-has-children > a, #main-nav > li.page_item_has_children > a").after(
                '<span class="child-arrow'+ expand +'"></span>'
                    );
            $("#main-nav ul li.menu-item-has-children > a, #main-nav ul li.page_item_has_children > a").after(
                '<span class="child-arrow"></span>'
            );
            $('#main-nav .child-arrow,#main-nav a').on('click',function (e) {
                var toggle = true,
                        item = $(this);
                if (this.tagName === 'A') {
                    if ((item.attr('href') === '#' || item.parent('.themify_toggle_dropdown').length>0) && item.next('.child-arrow').length > 0) {
                        item = item.next('.child-arrow');
                    }
                    else {
                        toggle = false;
                    }
                }
                if (toggle) {
                    e.preventDefault();
                    if(undefined!=themifyScript.m_m_toggle){
                        item.closest('li').siblings().find('.toggle-on').toggleClass('toggle-on');
                    }
                    item.toggleClass('toggle-on');
                }
            });

        }
        else{
            var $sideMenuWrap = $('#mobile-menu');
            $sideMenuWrap.wrapInner('<div class="overlay-menu-sticky"><div class="overlay-menu-sticky-inner"></div></div>');
            /* in Overlay header style, when a menu item is clicked, close the overlay */
            $( '#main-nav a' ).on('click', function() {
                    if ( bodyCl.contains( 'mobile-menu-visible' ) ) {
                            $( '#menu-icon' ).click();
                    }
            } );
        }
		
        if(isFullPageScroll && bodyCl.contains('query-section')){
			var startX,
                startY,
				getCoord=function (e, c) {
					return /touch/.test(e.type) ? (e.originalEvent || e).changedTouches[0]['page' + c] : e['page' + c];
				}
                $body.one('themify_fullpage_afterload', function () {
						$body.on('touchstart', 'a[href*="#"], area[href*="#"]', function (e) {
							e.stopPropagation();
							startX = getCoord(e, 'X');
							startY = getCoord(e, 'Y');
						});
                        $body.on('click touchend', 'a[href*="#"]:not([href="#"])', function (e) {
								/* on touch devices ensure visitor means to "tap" the link rather than sliding over it */
								if (/touch/.test(e.type)) {
									if (!(Math.abs(getCoord(e, 'X') - startX) < 20 && Math.abs(getCoord(e, 'Y') - startY) < 20)) {
										return;
									}
								}
                                var  slide_id = $(this).prop('hash'),
                                slideNoHashWithSlash = slide_id.replace(/#/, '' ).split('/'),
                                slideNoHash = slideNoHashWithSlash[slideNoHashWithSlash.length-1],
                                sectionEl = usesRows ? '.tb_section-' + slideNoHash + ':not(' + sectionClass + ')' : slide_id,
                                $sectionEl= $(sectionEl);
                                if ($sectionEl.length) {
                                        e.preventDefault();
                                        var slide_index = $sectionEl.index();
                                                $.fn.fullpage.moveTo($sectionEl.closest('.section-container').index()+1, slide_index)
                                                $sectionEl.css('visibility', 'visible');
                                }
                        });
                });
        }
        if (bodyCl.contains('header-bottom')) {
            $("#footer").after("<a class='footer-tab' href='#'></a>");
            $(".footer-tab").click(function (e) {
                e.preventDefault();
                $('#footerwrap').toggleClass('expanded');
            });
            $("#footer .back-top").detach().appendTo('#pagewrap');
        }
        /////////////////////////////////////////////
        // Slide cart icon
        /////////////////////////////////////////////
        $('a[href="#slide-cart"]').themifySideMenu({
            panel: '#slide-cart',
            close: '#cart-icon-close'
        });

        /////////////////////////////////////////////
        // Add class "first" to first elements
        /////////////////////////////////////////////
        $('.highlight-post:odd').addClass('odd');

        if (!Themify.isTouch && Themify.w > 1200) {
            var items = $(".header-horizontal .header-widget, .header-top-bar .header-widget, .boxed-compact .header-widget, .header-stripe .header-widget");
            if(items.length>0){
                var nicescrollHeaderStuff = function () {
                    // NiceScroll Initialized Default
                    items.niceScroll();
                    items=null;
                };
                if (typeof $.fn.niceScroll !== 'function') {
                    Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.nicescroll.min.js', nicescrollHeaderStuff, null, null, function () {
                        return ('undefined' !== typeof $.fn.niceScroll);
                    });
                } else {
                    nicescrollHeaderStuff();
                }
            }
        }

        var $headerWidgets = $('.header-horizontal, .header-top-bar, .boxed-compact, .header-stripe').find('.header-widget');
        if ($headerWidgets.length > 0) {
            // Header Horizontal, Header Topbar, Boxed Compact Add pull down wrapper
            $('.header-horizontal #main-nav, .header-top-bar #main-nav, .boxed-compact #main-nav, .header-stripe #main-nav').after($('<a href="#" class="pull-down">'));

            // Pull Down onclick Header Horizontal, Header Topbar, Boxed Compact Only
            $('.pull-down').on('click', function (e) {
                if (!Themify.isTouch) {
                    if (typeof $.fn.niceScroll !== 'function') {
                        Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.nicescroll.min.js', function () {
                            $headerWidgets.getNiceScroll().resize();
                        }, null, null, function () {
                            return ('undefined' !== typeof $.fn.niceScroll);
                        });
                    } else {
                        $headerWidgets.getNiceScroll().resize();
                    }
                }
                $('#header').toggleClass('pull-down-close');
                $headerWidgets.slideToggle('fast', function () {
                    $('#pagewrap').css('paddingTop', $('#headerwrap').outerHeight(true));
                });
                e.preventDefault();
            });
        }


        /////////////////////////////////////////////
        // Make overlay clickable
        /////////////////////////////////////////////
        $body.on('click', '.loops-wrapper.grid4.polaroid .post-image + .post-content, .loops-wrapper.grid3.polaroid .post-image + .post-content, .loops-wrapper.grid2.polaroid .post-image + .post-content, .loops-wrapper.grid4.overlay .post-image + .post-content, .loops-wrapper.grid3.overlay .post-image + .post-content, .loops-wrapper.grid2.overlay .post-image + .post-content, .loops-wrapper.grid4.flip .post-image + .post-content, .loops-wrapper.grid3.flip .post-image + .post-content, .loops-wrapper.grid2.flip .post-image + .post-content', function () {
            var $link = $(this).closest('.post').find('a[data-post-permalink]');
            if ($link.attr('href') && !$link.hasClass('themify_lightbox')) {
                window.location = $link.attr('href');
            }
        });

        /////////////////////////////////////////////
        // Carousel initialization
        /////////////////////////////////////////////

        
        $('.loops-wrapper.slider').each(function (i) {
            var $self = $(this),
                dataID = $self.prop('id');
            if (!dataID) {
                dataID = 'loops-wrapper-' + i;
                // If this doesn't have an id, set dummy id
                $self.attr('id', dataID);
            }
            var slideShow=$self.addClass('slideshow-wrap').find('.slideshow');
            if (slideShow.length === 0) {
                $self.wrapInner('<div class="slideshow" data-id="' + dataID + '" data-autoplay="off" data-speed="1000" data-effect="scroll" data-visible="3" />');
            } else {
                slideShow.attr('data-id', dataID);
            }
        });
        var slideShow= $('.slideshow:not(body)');
        if (slideShow.length > 0) {
            if (!$.fn.carouFredSel) {
                Themify.LoadAsync(themify_vars.url + '/js/carousel.min.js', function () {
                    ThemifySlider.createCarousel(slideShow);
                    slideShow=null;
                }, null, null, function () {
                    return typeof $.fn.carouFredSel !== 'undefined';
                });
            }
            else {
                ThemifySlider.createCarousel(slideShow);
                slideShow=null;
            }
        }
        var $headerwrap = $('#headerwrap');
        $body.on('announcement_bar_position announcement_bar_scroll_on_after', function (e, el) {
            $('#pagewrap').css('paddingTop', Math.floor($headerwrap.outerHeight(true)));
        }).on('announcement_bar_position', function (e, el) {
            if ($(this).hasClass('header-minbar')) {
                var w = $headerwrap.width();
                el.css({'left': w - Math.abs(parseInt($headerwrap.css('left'), 10)), 'right': w - Math.abs(parseInt($headerwrap.css('right'), 10))});
            }
        });

        var initResize = null,
            nicescrollMobileInit=null,
            condition = bodyCl.contains('header-top-bar') || bodyCl.contains('header-horizontal') || bodyCl.contains('header-top-widgets') || bodyCl.contains('boxed-compact') || bodyCl.contains('header-stripe') || bodyCl.contains('header-magazine');
        $(window).on('tfsmartresize',function (e) {
            // Reset NiceScroll Resize
            if (e.w < 1200 && !Themify.isTouch) {
                var selector = '.header-horizontal .header-widget, .header-top-bar .header-widget, .boxed-compact .header-widget, .header-stripe .header-widget';
                if (document.querySelector(selector)!==null) {
                    var nicescrollItems = $(selector);
                    if (typeof $.fn.niceScroll !== 'function') {
                            Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.nicescroll.min.js', function () {
                                    nicescrollItems.getNiceScroll().remove();
                            }, null, null, function () {
                                    return ('undefined' !== typeof $.fn.niceScroll);
                            });
                    } else {
                        nicescrollItems.getNiceScroll().remove();
                    }
                    nicescrollItems.attr('style','');
                }
            }
            /////////////////////////////////////////////
            // Mega menu width
            /////////////////////////////////////////////
            /* Adjust for scroll bar width */
            if (condition===true) {
                var megaItems =  $('#main-nav li.has-mega-column > ul,#main-nav li.has-mega-sub-menu > .mega-sub-menu');
                if (e.w > tf_mobile_menu_trigger_point) {
                    megaItems.css('width', $('#header').width());
                } else {
                    megaItems.removeAttr('style');
                }
            }
            /////////////////////////////////////////////
            // Nicescroll for mobile menu
            /////////////////////////////////////////////
            if (initResize===null && nicescrollMobileInit!==null && document.querySelector('.mobile_menu_active')!==null) {
                initResize = true;
                nicescrollMobileInit();
            }
        });

        /////////////////////////////////////////////
        // Header Overlay toggle-able dropdown
        /////////////////////////////////////////////
        // Set Dropdown Arrow
        $(".header-overlay #main-nav li.menu-item-has-children > a, .header-overlay #main-nav li.page_item_has_children > a").after(
                "<span class='child-arrow'></span>"
                );
        $('.header-overlay #main-nav li.menu-item-has-children > .child-arrow, .header-overlay #main-nav li.page_item_has_children > .child-arrow').on('click',function () {
            $(this).toggleClass('toggle-on').next('div, ul').toggle('fast');
            return true;
        });
        
        if (!Themify.isTouch && document.querySelector('.mobile_menu_active')!==null && bodyCl.contains('header-overlay')) {
            nicescrollMobileInit = function () {
                var nicescrollMobile = function(){
                    var $niceScrollTarget = $('#mobile-menu');
                    $niceScrollTarget.niceScroll();
                    $body.on('sidemenushow.themify', function () {
                        setTimeout(function () {
                            $niceScrollTarget.getNiceScroll().resize();
                        }, 200);
                    });
                };
                if (typeof $.fn.niceScroll !== 'function') {
                    Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.nicescroll.min.js', nicescrollMobile, null, null, function () {
                        return ('undefined' !== typeof $.fn.niceScroll);
                    });
                } else {
                    nicescrollMobile();
                }
            };
            nicescrollMobileInit();
        }

        /////////////////////////////////////////////
        // One Page Scroll
        /////////////////////////////////////////////
        if (isFullPageScroll && typeof $.fn.fullpage === 'undefined') {
            Themify.LoadAsync(themifyScript.themeURI + "/js/jquery.fullpage.extensions.min.js", function () {
                $body.triggerHandler('themify_fullpage_afterload');
            }, null, null, function () {
                return "undefined" !== typeof $.fn.fullpage;
            });
        }
        if (isFullPageScroll && $body.hasClass('query-section')) {
            themifyScript.hash = window.location.hash.replace('#', '').replace('!/', '');
            if ('undefined' !== typeof $.fn.themifyScrollHighlight) {
                $body.on('scrollhighlight.themify', function (e, section) {
                    if ('undefined' != typeof section && '' != section) {
                        $('#fp-nav').find('li').eq($('.tb_section-' + section.replace('#', '')).index()).find('a').trigger('click');
                    }
                });
                $(window).triggerHandler('scroll');
            }
            // Get rid of wow js animation since animation is managed with fullpage js
            var callbackTimer = setInterval(function () {
                if ('undefined' !== typeof ThemifyBuilderModuleJs) {
                    clearInterval(callbackTimer);
                    ThemifyBuilderModuleJs.wowInit();
                    wowInit2 = ThemifyBuilderModuleJs.wowInit;
                    ThemifyBuilderModuleJs.wowInit = function () {
                    };
                }
            }, 100);
            $body.one('themify_fullpage_afterload', function () {
                var wowCallbackTimer = setInterval(function () {
                    if ('undefined' !== typeof ThemifyBuilderModuleJs && 'undefined' !== typeof wowInit2 && null !== ThemifyBuilderModuleJs.wow) {
                        clearInterval(wowCallbackTimer);
                        ThemifyBuilderModuleJs.wow.stop();
                        wowInit2();
                        setTimeout(createFullScrolling, 100);
                    } else {
                        clearInterval(wowCallbackTimer);
                        createFullScrolling();
                    }
                }, 100);
            });
           if(Themify.is_builder_active && typeof $.fn.fullpage === 'undefined'){
                    Themify.LoadAsync(themifyScript.themeURI + '/js/jquery.fullpage.extensions.min.js', function () {
                        $body.trigger('themify_fullpage_afterload');
                        $.fn.fullpage.destroy('all');
                    }, null, null, function () {
                    return "undefined" !== typeof $.fn.fullpage;
                    });
                }
            $body.on('themify_onepage_afterload', function (e, $panel) {
                var $slide = $(sectionClass + '.active', $(sectionsWrapper)).find(slideClass + '.active');
                // Trigger wow display for elements in this panel
                if (tbLocalScript && tbLocalScript.animationInviewSelectors && typeof ThemifyBuilderModuleJs !== 'undefined' && ThemifyBuilderModuleJs.wow) {
                    $(tbLocalScript.animationInviewSelectors).each(function (i, selector) {
                        $(selector, $slide).each(function () {
                            ThemifyBuilderModuleJs.wow.show(this);
                        });
                    });
                }
            }).on('themify_onepage_afterload themify_onepage_after_render', function (event, $section, section_id) {
                if ($.fn.waypoint) {
                    Waypoint.refreshAll();
                }
                if ('undefined' !== typeof ThemifyBuilderModuleJs && ThemifyBuilderModuleJs.wow !== null && typeof ThemifyBuilderModuleJs.wow.scrollHandler() === 'boolean') {
                    ThemifyBuilderModuleJs.wow.scrollHandler();
                }

            });
        }
        
        //Fix overlay style regardless the featured image position above/below post title
        $('.loops-wrapper.overlay .post').each(function () {
            $(this).find('.post-image').insertBefore($(this).find('.post-content'));
        });

        /////////////////////////////////////////////
        // Mega Menu
        /////////////////////////////////////////////
        if (document.querySelector('.has-mega-sub-menu')!==null) {
            var megaMenuInit = function () {
                    /* add required wrappers for mega menu items */
                    $('.has-mega-sub-menu').each(function () {
                        $(this).find('> ul').removeAttr('class')
                            .wrap('<div class="mega-sub-menu sub-menu" />')
                            .after('<div class="mega-menu-posts" />')
                            .find('li.menu-item-type-taxonomy') // only taxonomy terms can display mega posts
                            .addClass('mega-link');
                                        $(this).ThemifyMegaMenu({
                                                        events: themifyScript.events
                                        });
                    });
            };
            if (typeof $.fn.ThemifyMegaMenu !== 'function') {
                Themify.LoadAsync(themifyScript.themeURI + '/themify/megamenu/js/themify.mega-menu.js', megaMenuInit);
            } else {
                megaMenuInit();
            }
        }
        // WINDOW RESIZE
        $(window).on('tfsmartresize',function (e) {
            if (bodyCl.contains('header-menu-split')) {
                if ($('#menu-icon').is(':visible')) {
                    if ($('.header-bar').find('#site-logo').length == 0) {
                        $('#site-logo').prependTo('.header-bar');
                    }
                } else if ($('.themify-logo-menu-item').find('#site-logo').length === 0) {
                    $('.themify-logo-menu-item').append($('.header-bar').find('#site-logo'));
                }
            }
            if (e.w > tf_mobile_menu_trigger_point) {
                if (bodyCl.contains('header-magazine')) {
                    $('#headerwrap').css({
                        'paddingBottom': $('.navbar-wrapper').outerHeight()
                    });
                }
                if (bodyCl.contains('header-classic')) {
                        $('#headerwrap').css({
                            'paddingBottom': $('.navbar-wrapper').outerHeight()
                        });
                    if ($('.navbar-wrapper').find('.navbar-wrapper-inner').length == 0) {
                        $('.navbar-wrapper').wrapInner('<div class="navbar-wrapper-inner"></div>');
                    }
                }
            }

        }).one('load',function () {
            var $body = Themify.body;
            ///////////////////////////////////////////
            // Initialize infinite scroll
            ///////////////////////////////////////////
            if(themifyScript.infiniteEnable){
                if ($body.hasClass('woocommerce') && $body.hasClass('archive')) {
                    doInfinite($('#content ul.products'), '#content .product');
                } else {
                    doInfinite($('#loops-wrapper'), '#loops-wrapper .post');
                }
            }
            ///////////////////////////////////////////
            // Header Video
            ///////////////////////////////////////////
            var $header = $('#headerwrap'),
                $videos = $header.find('[data-fullwidthvideo]');
            if ($header.data('fullwidthvideo')) {
                $videos = $videos.add($header);
            }
            function ThemifyBideo() {
                var init = true,
                        $fixed = $header.hasClass('fixed-header');
                if ($fixed) {
                    $header.removeClass('fixed-header');
                }
                $videos.each(function (i) {
                    var url = $(this).data('fullwidthvideo');
                    if (url) {
                        var options = {
                            url: url,
                            doLoop: true,
                            ambient: true,
                            id: i
                        };
                        if (init && $fixed) {
                            init = false;
                            options['onload'] = function () {
                                $header.addClass('fixed-header');
                            }
                        }
                        $(this).ThemifyBgVideo(options);
                    }
                });
            }
            if($videos.length > 0){
                if (!Themify.isTouch) {
                    if (typeof $.fn.ThemifyBgVideo === 'undefined') {
                        Themify.LoadAsync(
                                themify_vars.url + '/js/bigvideo.js',
                                ThemifyBideo,
                                null,
                                null,
                                function () {
                                    return ('undefined' !== typeof $.fn.ThemifyBgVideo);
                                }
                        );
                    }
                    else {
                        ThemifyBideo();
                    }
                }
               else{
                    $videos.each(function (key) {
                        var videoSrc = $(this).data('fullwidthvideo');
                        if (videoSrc && videoSrc.indexOf('.mp4') >= 0 && videoSrc.indexOf(window.location.hostname) >= 0) {
                            $(this).addClass('themify-responsive-video-background');
                            var videoEl = $('<div class="header-video-wrap">'
                                    + '<video class="responsive-video header-video video-' + key + '" muted="true" autoplay="true" loop="true" playsinline="true" >' +
                                    '<source src="' + videoSrc + '" type="video/mp4">' +
                                    '</video></div>')
                            videoEl.prependTo($(this));
                        }
                    });
                }
            }

            /////////////////////////////////////////////
            // Entry Filter Layout
            /////////////////////////////////////////////
            // Edge menu
           
            $('#main-nav li:has(ul), #footer-nav li:has(ul)').on('mouseenter dropdown_open', function (e) {
                    /* prevent "edge" classname being removed by mouseleave event when flipping through menu items really fast */
                    window.clearTimeout($(this).data('edge_menu_t'));

                    var elm = $('ul:first', this),
                                    l = elm.offset().left,
                                    w = elm.width(),
                                    docW = $(window).width(),
                                    isEntirelyVisible = (l + w <= docW);

                    if (!isEntirelyVisible) {
                            $(this).addClass('edge');
                    }
            })
            .on('mouseleave dropdown_close', function () {
                    var $this = $(this),
                    t = setTimeout(function () {
                            $this.removeClass('edge');
                    }, 300);
                    $this.data('edge_menu_t', t);
            });
            if (isFullPageScroll && $body.hasClass('query-section')) {
                // Hack Chrome browser doesn't autoplay the video background
                $body.on('themify_onepage_after_render', function () {
                    $.each(tbLocalScript.animationInviewSelectors, function (index, selector) {
                        $(selector).css('visibility', 'hidden');
                    });

                    // Section deep linking
                    if (window.location.hash) {
                        setTimeout(function () {
                            var hashSection = themifyScript.hash;
                            hashSection = hashSection.indexOf('/') != -1 ? hashSection.substring(0, hashSection.indexOf('/')) : hashSection;
                            if ('' != hashSection && '#' != hashSection) {
                                var $sectionEl = usesRows ? $('.tb_section-' + hashSection) : $('#' + hashSection);
                                if ($sectionEl.length > 0) {
                                    $.fn.fullpage.moveTo($sectionEl.closest('.section-container').index() + 1, $sectionEl.index());
                                    if (typeof ThemifyBuilderModuleJs !== 'undefined' && ThemifyBuilderModuleJs.wow) {
                                        $(tbLocalScript.animationInviewSelectors).each(function (i, selector) {
                                            $(selector, $sectionEl).addBack().each(function () {
                                                ThemifyBuilderModuleJs.wow.show(this);
                                            });
                                        });
                                    }
                                }
                            }
                        }, 1500);
                    }
                });
                // Make row backgrounds visible.
                $('.module_row').css('visibility', 'visible');
            }

            // remove item ajax
            $(document).on('click', '.remove-item-js', function (e) {
                e.preventDefault();
                // AJAX add to cart request
                var $thisbutton = $(this),
                        data = {
                            action: 'theme_delete_cart',
                            remove_item: $thisbutton.attr('data-product-key')
                        },
                $addedButton = $body.find('.ajax_add_to_cart '),
                        removedURL = $thisbutton.parent().find('.product-title a').attr('href');

                $thisbutton.addClass('themify_spinner');
                // Ajax action
                $.post(woocommerce_params.ajax_url, data, function (response) {
                    var fragments = response.fragments,
                            cart_hash = response.cart_hash;

                    // Changes button classes
                    if ($thisbutton.parent().find('.added_to_cart').length === 0)
                        $thisbutton.addClass('added');

                    // Replace fragments
                    if (fragments) {
                        $.each(fragments, function (key, value) {
                            $(key).addClass('updating').replaceWith(value);
                        });
                        if (!$(fragments['#shopdock-ultra']).find('.cart-total').length) {
                            $('#cart-icon-close').trigger('click');
                        }
                    }
                    if ($addedButton.length) {
                        $addedButton.each(function () {
                            if ($(this).hasClass('added') && $(this).closest('.post-content').find('[href="' + removedURL + '"]').length) {
                                $(this).removeClass('added').siblings('.added_to_cart').remove();
                            }
                        });
                    }
                    // Trigger event so themes can refresh other areas
                    $body.triggerHandler('removed_from_cart', [fragments, cart_hash]);
                    $thisbutton.removeClass('themify_spinner');

                });
            });
        });

        // Revealing footer
        var revealingFooter = function () {
            var currentColor, contentParents, isSticky,
                    $footer = $('#footerwrap'),
                    $footerInner = $footer.find('#footer'),
                    footerHeight = $footer.innerHeight(),
                    $content = $('#body'),
                    resizeCallback = function () {
                        footerHeight = $footer.innerHeight();
                        !isSticky && $footer.parent().css('padding-bottom', footerHeight);
                    },
                    scrollCallback = function () {
                        var contentPosition = $content.get(0).getBoundingClientRect(),
                                footerVisibility = window.innerHeight - contentPosition.bottom;

                        $footer.toggleClass('active-revealing', contentPosition.top < 0);

                        if (footerVisibility >= 0 && footerVisibility <= footerHeight) {
                            $footerInner.css('opacity', footerVisibility / footerHeight + 0.2);
                        } else if (footerVisibility > footerHeight) {
                            $footerInner.css('opacity', 1);
                        }
                    };

            if (!$footer.length && !$content.length)
                return;

            // Check for content background
            contentParents = $content.parents();

            if (contentParents.length) {
                $content.add(contentParents).each(function () {
                    if (!currentColor) {
                        var elColor = $(this).css('background-color');
                        if (elColor && elColor !== 'transparent' && elColor !== 'rgba(0, 0, 0, 0)') {
                            currentColor = elColor;
                        }
                    }
                });
            }

            $content.css('background-color', currentColor || '#ffffff');

            // Sticky Check
            isSticky = $footer.css('position') === 'sticky';
            Themify.body.toggleClass('no-css-sticky', !isSticky);

            resizeCallback();
            scrollCallback();
            $(window).on('tfsmartresize', resizeCallback).on('scroll', scrollCallback);
        };

        if (Themify.body.hasClass('revealing-footer')) {

            var backToTopButton = $('.back-top.back-top-float');
            revealingFooter();

            if (backToTopButton.length) {

                $('#footerwrap').before(backToTopButton);
            }
        }

        /* COMMENT FORM ANIMATION */
        $('input, textarea').on('focus',function () {
            $(this).parents('#commentform p').addClass('focused');
        }).on('blur',function () {
            var inputValue = $(this).val();
            if (inputValue == "") {
                $(this).removeClass('filled');
                $(this).parents('#commentform p').removeClass('focused');
            } else {
                $(this).addClass('filled');
            }
        });

        $body.on('added_to_cart', function (e) {

            var cartButton = $('.cart-icon');
            if (cartButton.hasClass('empty-cart')) {
                cartButton.removeClass('empty-cart');
            }

        })
                .on('removed_from_cart', function (e) {

                    var cartButton = $('.cart-icon');
                    if (!cartButton.hasClass('empty-cart') && parseInt($('#cart-icon span').text()) <= 0) {
                        cartButton.addClass('empty-cart');
                    }
                });

});

    $(window).one('load',function () {


        /////////////////////////////////////////////
        // Search Form							
        /////////////////////////////////////////////
        var $search = $('#search-lightbox-wrap');
        if ($search.length > 0) {
            var cache = [],
                    xhr,
                    $input = $search.find('#searchform input'),
                    $result_wrapper = $search.find('.search-results-wrap');
            $('.search-button, #close-search-box').on('click', function (e) {
                e.preventDefault();
                if ($input.val().length) {
                    $search.addClass('search-active');
                } else {
                    $search.removeClass('search-active')
                }
                if ($(this).hasClass('search-button')) {
                    $search.fadeIn(function () {
                        $input.focus();
                        Themify.body.css('overflow-y', 'hidden');
                    });
                    Themify.body.addClass('searchform-slidedown');
                }
                else {
                    if (xhr) {
                        xhr.abort();
                    }
                    $search.fadeOut();
                    Themify.body.css('overflow-y', 'visible').removeClass('searchform-slidedown');
                }
            });

            $result_wrapper.on('click', '.search-option-tab a', function (e) {
                e.preventDefault();
                var $href = $(this).attr('href').replace('#', '');
                if ($href === 'all') {
                    $href = 'item';
                }
                else {
                    $result_wrapper.find('.result-item').stop().fadeOut();
                }
                if ($('#result-link-' + $href).length > 0) {
                    $('.view-all-button').hide();
                    $('#result-link-' + $href).show();
                }
                $result_wrapper.find('.result-' + $href).stop().fadeIn();
                $(this).closest('li').addClass('active').siblings('li').removeClass('active');
            });
            $input.prop('autocomplete', 'off').on('keyup', function (e) {
                if ($input.val().length > 0) {
                    $search.addClass('search-active');
                } else {
                    $search.removeClass('search-active');
                }
                function set_active_tab(index) {
                    if (index < 0) {
                        index = 0;
                    }
                    $result_wrapper.find('.search-option-tab li').eq(index).children('a').trigger('click');
                    $result_wrapper.show();
                }
                if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || e.keyCode === 8 || e.keyCode === 229) {
                    var $v = $.trim($(this).val());
                    if ($v) {
                        if (cache[$v]) {
                            var $tab = $result_wrapper.find('.search-option-tab li.active').index();
                            $result_wrapper.hide().html(cache[$v]);
                            set_active_tab($tab);
                            return;
                        }
                        setTimeout(function () {
                            $v = $.trim($input.val());
                            if (xhr) {
                                xhr.abort();
                            }
                            if (!$v) {
                                $result_wrapper.html('');
                                return;
                            }

                            xhr = $.ajax({
                                url: themifyScript.ajax_url,
                                type: 'POST',
                                data: {'action': 'themify_search_autocomplete', 'term': $v},
                                beforeSend: function () {
                                    $search.addClass('themify-loading');
                                    $result_wrapper.html('<span class="themify_spinner"></span>');
                                },
                                complete: function () {
                                    $search.removeClass('themify-loading');
                                },
                                success: function (resp) {
                                    if (!$v) {
                                        $result_wrapper.html('');
                                    }
                                    else if (resp) {
                                        var $tab = $result_wrapper.find('.search-option-tab li.active').index();
                                        $result_wrapper.hide().html(resp);
                                        set_active_tab($tab);
                                        $result_wrapper.find('.search-option-tab li.active')
                                        cache[$v] = resp;
                                    }
                                }
                            });
                        }, 100);
                    }
                    else {
                        $result_wrapper.html('');
                    }
                }
            });
        }
        if ('1' === themifyScript.pageLoaderEffect || Themify.body.hasClass('full-section-scrolling')) {

            Themify.body.addClass('ready-view').removeClass('hidden-view');

            $('.section_loader').fadeOut(500, function () {
                if ('undefined' !== typeof ThemifyBuilderModuleJs && 'undefined' !== typeof ThemifyBuilderModuleJs.wowInit) {
                    ThemifyBuilderModuleJs.wowInit(false,true);
                }
            });
        }

        /**
         * Called when user navigates away of the current view.
         * Publicly accessible through themifyScript.onBrowseAway
         */
        themifyScript.onBrowseAway = function (e) {

            if (e.target.activeElement.tagName === 'BODY'
                    || ($(e.target.activeElement).attr('id') == 'tb_toolbar')
                    || $(e.target.activeElement).closest('#tb_toolbar').length)
                return;

            if (Themify.body.hasClass('ready-view')) {
                Themify.body.addClass('hidden-view').removeClass('ready-view');
            } else {
                Themify.body.addClass('hidden-view');
            }
        };

        if ('1' === themifyScript.pageLoaderEffect || Themify.body.hasClass('full-section-scrolling')) {
            window.addEventListener('beforeunload', themifyScript.onBrowseAway);
        }
    });

})(jQuery);

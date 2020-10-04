(function( $ ){

	// load more button
	$( 'body' ).on( 'click', '.infinite-posts-load-more', function(e){
		e.preventDefault();
		var $this = $( this ),
			$module = $this.closest( '.module-infinite-posts' ),
			$posts_container = $module.find( '.builder-infinite-posts-wrap' ),
			module_id = '#' + $module.attr( 'id' );

		if( ! $module.hasClass( 'loading-posts' ) ) {
			$module.addClass( 'loading-posts' );
			$.get( $this.attr( 'href' ), function( data ) {
				var $data = $( data );
				if( $data.find( module_id + ' .builder-infinite-posts-wrap > .post' ).length > 0 ) {
					if( $module.hasClass( 'masonry-enabled' ) ) {
						var items = $data.find( module_id + ' .builder-infinite-posts-wrap > .post' );
						items.imagesLoaded(function(){
							$posts_container.append( items ).isotope( 'appended', items ).isotope( 'layout' );
						});
					} else {
						$data.find( module_id + ' .builder-infinite-posts-wrap > .post' ).appendTo( $posts_container );
					}
				}

				$( 'body' ).trigger( 'themify_builder_infinite_posts_load_more', [$this, $data] );

				// there's more pages to show?
				if( $data.find( module_id + ' .infinite-posts-load-more' ).length > 0 ) {
					$this.attr( 'href', $data.find( module_id + ' .infinite-posts-load-more' ).attr( 'href' ) ); // swap the path for the new page
				} else {
					$this.fadeOut(); // no more pages, hide the button
				}
			} ).always( function(){
				$module.removeClass( 'loading-posts' );
			} );
		}
	} );

	// load InfiniteScroll library and do the magic
	function do_infinite_posts() {
		if( $( 'div.module-infinite-posts.pagination-infinite-scroll' ).length > 0 ) {
			if ( 'undefined' == typeof $.fn.infinitescroll ) {
				Themify.LoadAsync( builderInfinitePosts.url + 'assets/jquery.infinitescroll.min.js', function(){
					do_infinite();
				},
                                null,
                                null,
                                function(){
                                    return typeof  $.fn.infinitescroll!=='undefined';
                                });
			} else {
				do_infinite();
			}
		}
		if( $( 'div.module-infinite-posts.masonry-enabled' ).length > 0 ) {
			if ( 'undefined' == typeof $.fn.isotope ) {
				Themify.LoadAsync( builderInfinitePosts.url + 'assets/jquery.isotope.min.js', function(){
					do_isotope();
				},
                                null,
                                null,
                                function(){
                                    return typeof  $.fn.isotope!=='undefined';
                                } );
			} else {
				do_isotope();
			}
		}
	}

	function do_isotope() {
		$( 'div.module-infinite-posts.masonry-enabled .builder-infinite-posts-wrap' ).each(function(){
			var $this = $( this );
			$this.imagesLoaded(function(){
				if( ! $this.find( '> .infinite-post-grid-sizer' ).length ) {
					$this.prepend( '<div class="infinite-post-grid-sizer"></div><div class="infinite-post-gutter-sizer"></div>' );
				}
				$this.isotope({
					masonry: {
						columnWidth: '.infinite-post-grid-sizer',
						gutter: '.infinite-post-gutter-sizer'
					},
					itemSelector: '.post'
				})
				.addClass( 'masonry-done' )
				.isotope( 'once', 'layoutComplete', function() {
					$(window).trigger( 'resize' );
				});
			});
		});
	}

	function do_infinite() {
		$('.module.module-infinite-posts.pagination-infinite-scroll').each(function(){
			var $this = $( this ),
				id = '#' + $this.attr( 'id' );

			$this.find( '.builder-infinite-posts-wrap' ).infinitescroll({
				navSelector : id + ' .pagenav',
				nextSelector : id + ' .pagenav span.current + a',
				itemSelector : id + ' .builder-infinite-posts-wrap > .post',
				donetext : '',
				loading : { img: builderInfinitePosts.loading_image },
				bufferPx : builderInfinitePosts.bufferPx,
				behavior : '',
				path : function( i ) {
					return $this.data( 'url' ).replace( /tb_infinite=0/, 'tb_infinite=' + i )
				},
				state: {
					currPage : parseInt( $this.data( 'current-page' ) )
				},
				pixelsFromNavToBottom : builderInfinitePosts.pixelsFromNavToBottom
			}, function( newElements ){
				$( '#infscr-loading' ).remove();

				// if masonry layout is enabled, redo the masonry layout
				if( $this.hasClass( 'masonry-enabled' ) ) {
					$this.find( '.builder-infinite-posts-wrap' ).isotope( 'appended', newElements ).isotope( 'layout' );
				}

				$( 'body' ).trigger( 'infiniteloaded.themify', [newElements, $this] );
			});
		});
	}

	$( 'body' ).on( 'builder_load_module_partial builder_toggle_frontend', do_infinite_posts );
	$( window ).bind( 'load', do_infinite_posts );
})( jQuery );
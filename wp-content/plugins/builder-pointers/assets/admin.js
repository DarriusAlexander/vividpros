jQuery(function($){
	var $blobs, $add_new, $preview;

	$( 'body' ).on( 'editing_module_option', function(e){
		if( ! $( '#blobs_showcase' ).length > 0 ) return;
		$blobs = $( '#blobs_showcase' );
		$add_new = $blobs.next( 'p.add_new' ).find('a');
		$preview = $( '#pointers-preview' );

		update_image();
		make_preview();
	} )
	.on( 'change', '#themify_builder_options_setting #image_url, #themify_builder_options_setting #image_width, #themify_builder_options_setting #image_height', update_image )
	.on( 'mousedown touchstart', '#pointers-preview .blob', blob_edit )
	.on( 'click touchstart', '#pointers-preview img', add_new_blob )
	.on( 'click touchstart', '#pointers .blob-delete', blob_delete );

	function make_preview() {
		// display previously added blobs
		$blobs.find( '> .tb_repeatable_field' ).each(function(i, v){
			if( $(this).find('[name="left"]').val() !== '' ) {
				add_blob( $(this).find('[name="left"]').val(), $(this).find('[name="top"]').val(), ++i );
			}
		});
	}

	function remove_pointers() {
		$blobs.find( '.blob' ).remove();
	}

	function update_image() {
		/* early call, return back until the edit window is initialized */
		if( $preview == undefined )
			return;

		var url = $( '#image_url', '#themify_builder_options_setting' ).val();
		if( '' == url ) {
			$( '#pointers' ).hide();
		} else {
			$.ajax({
				url : themifyBuilder.ajaxurl,
				method : 'POST',
				data : {
					action : 'builder_pointers_get_image',
					'pointers_image' : url,
					'pointers_width' : $( '#image_width', '#themify_builder_options_setting' ).val(),
					'pointers_height' : $( '#image_height', '#themify_builder_options_setting' ).val(),
				},
				beforeSend : function(){
					$preview.find( '.loading' ).fadeIn();
				},
				success : function( data ) {
					$( '#pointers' ).show();
					$preview.find( 'img' ).remove();
					$preview.prepend( data );
				},
				complete : function(){
					$preview.find( '.loading' ).fadeOut();
				}
			});
		}
	}

	function add_blob( left, top, id ) {
		return $( '<div class="blob" data-id="'+ id +'" style="top: '+ top +'%; left: '+ left +'%;"><div class="blob-icon"></div></div>' ).appendTo( $preview )
			.draggable({
				stop : function(e){
					var thiz = $( this );
					var top_percent = ( parseFloat( thiz.css('top') ) * 100 ) / $preview.height(),
						left_percent = ( parseFloat( thiz.css('left') ) * 100 ) / $preview.width(),
						row = $blobs.find( '.tb_repeatable_field:nth-child('+ thiz.data( 'id' ) +')' );
					$( '[name="left"]', row ).val( left_percent );
					$( '[name="top"]', row ).val( top_percent );
				}
			});
	}

	function add_new_blob(e) {
		var top_percent = ( e.offsetY * 100 ) / $( this ).height();
		var left_percent = ( e.offsetX * 100 ) / $( this ).width();
		var id = $blobs.find( '.tb_repeatable_field' ).length + 1;
		$add_new.click();
		add_blob( left_percent, top_percent, id )
			.trigger( 'mousedown' ); // show options for the pointer
		var row = $blobs.find( '.tb_repeatable_field:last-child' );
		$( '[name="left"]', row ).val( left_percent );
		$( '[name="top"]', row ).val( top_percent );
	}

	function blob_edit() {
		var id = $( this ).data( 'id' );
		$preview.find( '.blob' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
		var row = $blobs.show().find('.tb_repeatable_field').hide().filter( ':nth-child(' + id + ')' ).show();
		if( row.find( '.blob-delete' ).length == 0 ) {
			row.append( '<a class="blob-delete" href="#"><i class="fa fa-close"></i></a>' );
		}

		// UI patch: mark the active choice for the Direction option.
		row.find( '.tf-radio-choice input[checked]' ).trigger( 'click' );

		return false;
	}

	function blob_delete() {
		var row = $( this ).closest( '.tb_repeatable_field' ),
			index = row.index() + 1;
		$preview.find( '.blob[data-id="'+ index +'"]' ).remove();
		row.remove();

		// redo the preview, to correct the indexes
		remove_pointers();
		make_preview();

		return false;
	}
});
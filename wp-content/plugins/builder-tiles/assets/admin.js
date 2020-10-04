jQuery(function($){

	$( 'body' ).on( 'editing_module_option', function(e){
		if( ! $('#themify_builder_tabs_tile').length > 0 ) return;

		// layout controls
		$( '#type_front a, #type_back a' ).click(function(){
			var thiz = $(this),
				id = thiz.attr( 'id' );

			thiz.closest( '.themify_builder_tab' )
				.find( '> .tf-tile-options' ).hide()
				.filter( '.tf-tile-options-' + id ).show()
		}).filter( '.selected' ).click();
	});

});
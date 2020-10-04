(function ($) {
   
    function create_stories() {
            function callback(){
                var builder_timeline_data = [];
                $( '.timeline-embed' ).each(function(){
                    builder_timeline_data[$(this).data('id')] =JSON.parse(window.atob($(this).data('data')));
                });
                $( '.module.module-timeline.layout-graph').each(function(){
			if( $( this ).find( '.storyjs-embed' ).length == 0 ) {
                            var id = $( this ).attr( 'id' ).trim(),
                                source = builder_timeline_data[id],
                                embed = $( this ).find( '.timeline-embed' ),
                                config = embed.data( 'config' );
                                config.source = source;
                                createStoryJS( config );
                        }
		});
            }
            if($( '.module.module-timeline.layout-graph' ).length>0){
                if(typeof createStoryJS==='undefined'){
                    Themify.LoadAsync(builder_timeline.url+'knight-lab-timelinejs/js/storyjs-embed.js', callback, '2.33.1', null, function(){
                            return ('undefined' !== typeof createStoryJS);
                    });
                }
                else{
                    callback();
                }
            }
		
	}

	$( window ).on( 'load', create_stories );
	$( 'body' ).on( 'builder_load_module_partial builder_toggle_frontend', create_stories );
}(jQuery));
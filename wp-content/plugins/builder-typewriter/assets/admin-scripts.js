(function($, window, document, undefined) {
	"use strict";

	var BuilderTypewriterAdmin = {
		init: function() {
			var self = BuilderTypewriterAdmin;
			self.bindEvents();
		},

		bindEvents: function() {
			var self = BuilderTypewriterAdmin;
			$(document).on('tfb.live_styling.after_create', self.bindLiveStyling);
		},

		bindLiveStyling: function(e, liveStylingInstance) {
			var liveStylingCallback = function() {
				var activeTabId = $(this).attr('href');

				if (activeTabId === '#themify_builder_options_styling') {
					if ($('a[href="#tf_module-styling_typewriter"]', liveStylingInstance.$context).parent().hasClass('ui-state-active')) {
						setTimeout(function() {
							ThemifyBuilderCommon.Lightbox.showPreviewBtn();
						}, 50);
					}
				} else if (activeTabId === '#tf_module-styling_typewriter') {
					ThemifyBuilderCommon.Lightbox.showPreviewBtn();
				} else {
					ThemifyBuilderCommon.Lightbox.hidePreviewBtn();
				}
			};

			liveStylingInstance.$context.on(
				'click',
				'a[href="#themify_builder_options_styling"],' +
				'a[href="#tf_module-styling_general"],' +
				'a[href="#tf_module-styling_typewriter"]',
				liveStylingCallback
			);
		}
	};

	BuilderTypewriterAdmin.init();
}(jQuery, window, document));

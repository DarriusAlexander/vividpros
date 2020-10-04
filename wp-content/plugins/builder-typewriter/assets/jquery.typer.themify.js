/*
 * jQuery Plugin Typer (Typewriter) by Themify.me
 * 
 * Fork from: https://github.com/kontur/jquery.typer.js
 * jQuery plugin boilerplate: https://github.com/jquery-boilerplate/jquery-boilerplate
 */

;(function($, window, document, undefined) {

	'use strict';

	var pluginName = 'typer',
		defaults = {
			highlightSpeed: 20, // ms
			typeSpeed: 100, // ms
			clearDelay: 500, // ms
			typeDelay: 200, // ms
			typerInterval: 2000, // ms
			highlightEverything: true, // true/false
			typerDataAttr: 'data-typer-targets',
			backgroundColor: 'auto', // CSS background-color
			highlightColor: 'auto', // CSS color
			typerOrder: 'sequential', // sequential/random
			inlineHighlightStyle: true // write inline span style
		};

	function Plugin(element, options) {
		this._defaults = defaults;
		this._name = pluginName;
		this.element = element;
		this.settings = $.extend({}, defaults, options);
		this.init();
	}

	$.extend(Plugin.prototype, {
		init: function() {
			var self;

			if (typeof $(this.element).attr(this.settings.typerDataAttr) === 'undefined') {
				return;
			}

			this.last = 0;
			this.typeWithAttribute();

			self = this;
			this.intervalHandle = setInterval(
				function() {
					self.typeWithAttribute.call(self);
				},
				this.settings.typerInterval
			);
		},

		destroy: function() {
			clearInterval(this.intervalHandle);
			$(this.element).removeData('plugin_'+ pluginName);
		},

		clearData: function() {
			this.highlightPosition = null;
			this.leftStop = null;
			this.rightStop = null;
			this.text = null;
			this.typing = null;
		},

		typeWithAttribute: function() {
			var $e = $(this.element),
				targets;

			if (typeof this.settings === 'undefined') {
				this.destroy();
				return;
			}

			if (this.typing) {
				return;
			}

			try {
				targets = JSON.parse($e.attr(this.settings.typerDataAttr)).targets;
			} catch (e) {}

			if (typeof targets === 'undefined') {
				targets = $.map($e.attr(this.settings.typerDataAttr).split(','), function(e) {
					return $.trim(e);
				});
			}

			if (this.settings.typerOrder === 'random') {
				this.typeTo(targets[Math.floor(Math.random() * targets.length)]);
			}
			else if (this.settings.typerOrder === 'sequential') {
				this.typeTo(targets[this.last]);
				this.last = (this.last < targets.length - 1) ? this.last + 1 : 0;
			}
			else {
				this.destroy();
				return;
			}
		},

		typeTo: function(newString) {
			newString = this.decodeEntities(newString);

			var $e = $(this.element),
				currentText = $e.text(),
				i = 0,
				j = 0;

			if (currentText === newString) {
				return;
			}

			this.typing = true;

			if (this.settings.highlightEverything !== true) {
				while (currentText.charAt(i) === newString.charAt(i)) {
					i++;
				}

				while (this.rightChars(currentText, j) === this.rightChars(newString, j)) {
					j++;
				}
			}

			if (typeof newString !== 'undefined') {
				newString = newString.substring(i, newString.length - j + 1);
			}

			this.oldLeft = currentText.substring(0, i);
			this.oldRight = this.rightChars(currentText, j - 1);
			this.leftStop = i;
			this.rightStop = currentText.length - j;
			this.text = newString;

			this.highlight();
		},

		highlight: function() {
			var self,
				$e = $(this.element),
				text,
				leftText,
				highlightedText,
				rightText;

			if (typeof this.settings === 'undefined') {
				this.destroy();
				return;
			}

			if (! this.isNumber(this.highlightPosition)) {
				this.highlightPosition = this.rightStop + 1;
			}

			if (this.highlightPosition <= this.leftStop) {
				self = this;
				setTimeout(
					function() {
						self.clearText.call(self);
					},
					this.settings.clearDelay
				);

				return;
			}

			text = $e.text();
			leftText = text.substring(0, this.highlightPosition - 1);
			highlightedText = text.substring(this.highlightPosition - 1, this.rightStop + 1);
			rightText = text.substring(this.rightStop + 1);

			if (leftText == '' && highlightedText == '' && rightText == '') {
				$e.hide();
			}
			var highlighted = this.spanWithColor(
						this.settings.highlightColor === 'auto' ? $e.css('background-color') : this.settings.highlightColor,
						this.settings.backgroundColor === 'auto' ? $e.css('color') : this.settings.backgroundColor,
						highlightedText
			);
                        $e.html(leftText+highlighted+rightText);
			this.highlightPosition -= 1;

			self = this;
			setTimeout(
				function() {
					self.highlight.call(self);
				},
				this.settings.highlightSpeed
			);
		},

		type: function() {
			var self,
				$e = $(this.element),
				text;

			if (! this.text || this.text.length === 0) {
				this.clearData();
				return;
			}

			text = this.oldLeft + this.text.charAt(0) + this.oldRight;
			$e.html(text);

			if (text.length === 1) {
				$e.show();
			}

			this.oldLeft = this.oldLeft + this.text.charAt(0);
			this.text = this.text.substring(1);

			self = this;
			setTimeout(
				function() {
					self.type.call(self);
				},
				this.settings.typeSpeed
			);
		},

		clearText: function() {
			var self;

			$(this.element).find('span').remove();

			self = this;
			setTimeout(
				function() {
					self.type.call(self);
				},
				this.settings.typeDelay
			);
		},

		spanWithColor: function(color, backgroundColor,txt) {
			if ( this.settings.inlineHighlightStyle ) {
				return '<span style="color:'+color+';background-color:'+backgroundColor+';">'+txt+'</span>';
			} else {
				return '<span>'+txt+'</span>';
			}
		},

		isNumber: function(number) {
			return ! isNaN(parseFloat(number)) && isFinite(number);
		},

		rightChars: function(text, number) {
			if (number <= 0) {
				return '';
			}

			else if (number > text.length) {
				return text;
			}

			else {
				return text.substring(text.length, text.length - number);
			}
		},

		decodeEntities: (function() {
			var element = document.createElement('div');

			function decodeHTMLEntities(str) {
				if (str && typeof str === 'string') {
					str = escape(str).replace(/%26/g,'&').replace(/%23/g,'#').replace(/%3B/g,';');
					element.innerHTML = str;
					
					if (element.innerText) {
						str = element.innerText;
						element.innerText = '';
					} else {
						str = element.textContent;
						element.textContent = '';
					}
				}

				return unescape(str);
			}

			return decodeHTMLEntities;
		})()
	});

	$.fn[pluginName] = function(options) {
		return this.each(function() {
			if (! $.data(this, 'plugin_'+ pluginName)) {
				$.data(this, 'plugin_'+ pluginName, new Plugin(this, options));
			}
		});
	};

})(jQuery, window, document);

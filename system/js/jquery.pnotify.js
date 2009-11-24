/*
 * jQuery Pines Notify (pnotify) Plugin 1.0
 *
 * Copyright (c) 2009 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
	var first_top;
	$.extend({
		pnotify_remove_all: function () {
			var body = $("body");
			var body_data = body.data("pnotify");
			$.each(body_data, function(){
				if (this.pnotify_remove)
					this.pnotify_remove();
			});
		},
		pnotify_position_all: function () {
			var body = $("body");
			var next = first_top;
			var body_data = body.data("pnotify");
			$.each(body_data, function(){
				var pos = this.position();
				if (next) {
					if (pos.top > next) {
						this.animate({top: next+"px"}, {duration: 500, queue: false});
					} else {
						this.css("top", next+"px");
					}
				}
				if (!first_top)
					next = (first_top = pos.top);
				if (this.css("display") != "none") {
					next += this.height() + 10;
				}
			});
		},
		pnotify: function(options) {
			var body = $("body");
			// Build main options.
			var opts;
			if (typeof options == "string") {
				opts = $.extend({}, $.pnotify.defaults);
				opts.pnotify_text = options;
			} else {
				opts = $.extend({}, $.pnotify.defaults, options);
			}
			var pnotify = $("<div />").addClass("ui-widget ui-helper-clearfix ui-pnotify");
			pnotify.container = $("<div />").addClass("ui-corner-all ui-pnotify-container");
			pnotify.append(pnotify.container);

			pnotify.pnotify_version = "1.0.0"

			pnotify.pnotify_display = function() {
				if (pnotify.parent().get()) {
					body.append(pnotify);
				}
				$.pnotify_position_all();
				// First show it, then set its opacity to 0, then fade into the set opacity.
				pnotify.show().fadeTo(0, 0).fadeTo(opts.pnotify_fade_speed, opts.pnotify_opacity);
			}

			pnotify.pnotify_remove = function() {
				if (pnotify.timer) {
					window.clearTimeout(pnotify.timer);
					pnotify.timer = null;
				}
				pnotify.fadeOut(opts.pnotify_fade_speed, function(){
					$.pnotify_position_all();
					if (opts.pnotify_remove)
						pnotify.remove();
				});
			}

			pnotify.pnotify_cancel_remove = function() {
				if (pnotify.timer) {
					window.clearTimeout(pnotify.timer);
				}
			}

			pnotify.pnotify_queue_remove = function() {
				pnotify.pnotify_cancel_remove();
				pnotify.timer = window.setTimeout(function(){
					pnotify.pnotify_remove();
				}, (isNaN(opts.pnotify_delay) ? 0 : opts.pnotify_delay));
			}

			if (opts.pnotify_type == "error") {
				pnotify.container.addClass("ui-state-error");
			} else {
				pnotify.container.addClass("ui-state-highlight");
			}

			if (opts.pnotify_closer) {
				var closer = $("<div />").addClass("ui-pnotify-closer").css("cursor", "pointer");
				closer.append($("<span />").addClass("ui-icon ui-icon-circle-close"));
				closer.click(function(){
					pnotify.pnotify_remove();
				});
				closer.hide();
				pnotify.container.append(closer);
				pnotify.hover(function(){
					closer.show();
				}, function(){
					closer.hide();
				});
			}

			if (typeof opts.pnotify_title == "string") {
				var title = $("<span />").addClass("ui-pnotify-title");
				title.html(opts.pnotify_title);
				pnotify.container.append(title);
			}

			if (typeof opts.pnotify_text == "string") {
				var text = $("<span />").addClass("ui-pnotify-text");
				text.html(opts.pnotify_text);
				pnotify.container.append(text);
			}

			if (typeof opts.pnotify_width == "string") {
				pnotify.css("width", opts.pnotify_width);
			}

			if (typeof opts.pnotify_min_height == "string") {
				pnotify.container.css("min-height", opts.pnotify_min_height);
			}

			pnotify.hide();
			
			var body_data = body.data("pnotify");
			if (typeof body_data != "object")
				body_data = Array();
			body_data = $.merge(body_data, [pnotify]);
			body.data("pnotify", body_data);

			pnotify.pnotify_display();

			if (opts.pnotify_hide) {
				pnotify.pnotify_queue_remove();
				pnotify.mouseenter(function(){
					pnotify.stop();
					pnotify.fadeTo("fast", opts.pnotify_opacity);
					pnotify.pnotify_cancel_remove();
				}).mouseleave(function(){
					pnotify.pnotify_queue_remove();
					$.pnotify_position_all();
				});
			}

			return pnotify;
		}
	});

	$.pnotify.defaults = {
		// Width of the notice.
		pnotify_width: "300px",
		// Minimum height of the notice. It will expand to fit content.
		pnotify_min_height: "16px",
		// Speed at which the notice fades in and out. "slow", "def", "fast" or number of milliseconds.
		pnotify_fade_speed: "slow",
		// Opacity to fade to.
		pnotify_opacity: 1,
		// Provide a button for the user to manually close a notice.
		pnotify_closer: true,
		// After a delay, make the notice disappear.
		pnotify_hide: true,
		// Delay in milliseconds before the notice disappears.
		pnotify_delay: 8000,
		// Remove the notice from the DOM after it disappears.
		pnotify_remove: true
	};
})(jQuery);
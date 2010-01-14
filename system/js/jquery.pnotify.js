/*
 * jQuery Pines Notify (pnotify) Plugin 1.0
 *
 * Copyright (c) 2009 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
	var first_top, first_right, history_handle_top, timer;
	$.extend({
		pnotify_remove_all: function () {
			var body = $("body");
			var body_data = body.data("pnotify");
			/* POA: Added null-check */
			if (body_data && body_data.length) {
				$.each(body_data, function(){
					if (this.pnotify_remove)
						this.pnotify_remove();
				});
			}
		},
		pnotify_position_all: function () {
			timer = null;
			var body = $("body");
			var nexttop = first_top;
			var nextright = first_right;
			var addwidth = 0;
			var body_data = body.data("pnotify");
			$.each(body_data, function(){
				if (this.css("display") != "none") {
					var postop, posright, animate = {};
					// Calculate the top value, disregarding the scroll, since position=fixed.
					postop = this.offset().top - $(window).scrollTop();
					// Remember the topmost position, so the first visible notice goes there.
					if (typeof first_top == "undefined") {
						first_top = postop;
						nexttop = first_top;
					}
					if (typeof window.innerHeight != "undefined") {
						posright = parseInt(this.css("right"));
						if (isNaN(posright))
							posright = 18;
						// Remember the rightmost position, so the first visible notice goes there.
						if (typeof first_right == "undefined") {
							first_right = posright;
							nextright = first_right;
						}
						// Check that we're not below the bottom of the page.
						if (nexttop + this.height() > window.innerHeight) {
							// If we are, we need to go back to the top, and over to the left.
							nexttop = first_top;
							nextright += addwidth + 10;
							addwidth = 0;
						}
						// Animate if we're moving to the right.
						if (nextright < posright) {
							animate.right = nextright+"px";
						} else {
							this.css("right", nextright+"px");
						}
						// Keep track of the widest notice in the column, so we can push the next column.
						if (this.outerWidth(true) > addwidth)
							addwidth = this.width();
					}
					if (nexttop) {
						// Animate if we're moving up or to the right.
						if (postop > nexttop || animate.right) {
							animate.top = nexttop+"px";
						} else {
							this.css("top", nexttop+"px");
						}
					}
					if (animate.top || animate.right)
						this.animate(animate, {duration: 500, queue: false});
					nexttop += this.height() + 10;
				}
			});
		},
		pnotify: function(options) {
			var body = $("body");
			var closer;
			
			// Build main options.
			var opts;
			if (typeof options == "string") {
				opts = $.extend({}, $.pnotify.defaults);
				opts.pnotify_text = options;
			} else {
				opts = $.extend({}, $.pnotify.defaults, options);
			}

			if (opts.pnotify_before_init) {
				if (opts.pnotify_before_init(opts) === false)
					return null;
			}
			
			var pnotify = $("<div />").addClass("ui-widget ui-helper-clearfix ui-pnotify");
			pnotify.opts = opts;
			pnotify.container = $("<div />").addClass("ui-corner-all ui-pnotify-container");
			pnotify.append(pnotify.container);

			pnotify.pnotify_version = "1.0.0";

			pnotify.pnotify = function(options) {
				// Update the notice.
				var old_opts = opts;
				if (typeof options == "string") {
					opts.pnotify_text = options;
				} else {
					opts = $.extend({}, opts, options);
				}
				if (typeof opts.pnotify_title == "string") {
					pnotify.title_container.html(opts.pnotify_title).show("fast");
				} else if (opts.pnotify_title === false) {
					pnotify.title_container.hide("fast");
				}
				if (typeof opts.pnotify_text == "string") {
					if (opts.pnotify_insert_brs)
						opts.pnotify_text = opts.pnotify_text.replace("\n", "<br />");
					pnotify.text_container.html(opts.pnotify_text).show("fast");
				} else if (opts.pnotify_text === false) {
					pnotify.text_container.hide("fast");
				}
				pnotify.pnotify_history = opts.pnotify_history;
				if (opts.pnotify_type != old_opts.pnotify_type) {
					// Change to the new type.
					if (opts.pnotify_type == "error") {
						pnotify.container.addClass("ui-state-error").removeClass("ui-state-highlight");
					} else if (opts.pnotify_type == "notice") {
						pnotify.container.addClass("ui-state-highlight").removeClass("ui-state-error");
					}
				}
				if ((opts.pnotify_notice_icon != old_opts.pnotify_notice_icon && opts.pnotify_type == "notice") ||
					(opts.pnotify_error_icon != old_opts.pnotify_error_icon && opts.pnotify_type == "error") ||
					(opts.pnotify_type != old_opts.pnotify_type)) {
					// Remove any old icon.
					pnotify.container.find(".ui-pnotify-icon").remove();
					if ((opts.pnotify_notice_icon && opts.pnotify_type == "notice") || (opts.pnotify_error_icon && opts.pnotify_type == "error")) {
						// Build the new icon.
						var icon = $("<div />").addClass("ui-pnotify-icon");
						icon.append($("<span />").addClass(opts.pnotify_type == "notice" ? opts.pnotify_notice_icon : opts.pnotify_error_icon));
						pnotify.container.prepend(icon);
					}
				}
				// Update the width.
				if (opts.pnotify_width != old_opts.pnotify_width && typeof opts.pnotify_width == "string")
					pnotify.animate({width: opts.pnotify_width});
				// Update the minimum height.
				if (opts.pnotify_min_height != old_opts.pnotify_min_height && typeof opts.pnotify_min_height == "string")
					pnotify.container.animate({minHeight: opts.pnotify_min_height});
				// Update the opacity.
				if (opts.pnotify_opacity != old_opts.pnotify_opacity)
					pnotify.fadeTo(opts.pnotify_animate_speed, opts.pnotify_opacity);
				if (!opts.pnotify_hide) {
					pnotify.pnotify_cancel_remove();
				} else if (!old_opts.pnotify_hide) {
					pnotify.pnotify_queue_remove();
				}
				pnotify.opts = opts;
				pnotify.pnotify_queue_position();
				return pnotify;
			};

			pnotify.pnotify_init = function() {
				body.append(pnotify);

				// Add events to stop fading when the user mouses over.
				if (opts.pnotify_hide) {
					pnotify.mouseenter(function(){
						pnotify.stop();
						pnotify.fadeTo("fast", opts.pnotify_opacity);
						pnotify.pnotify_cancel_remove();
					}).mouseleave(function(){
						pnotify.pnotify_queue_remove();
						$.pnotify_position_all();
					});
				}

				if (opts.pnotify_closer) {
					if (closer) closer.remove();
					closer = $("<div />").addClass("ui-pnotify-closer").css("cursor", "pointer");
					closer.append($("<span />").addClass("ui-icon ui-icon-circle-close"));
					closer.click(function(){
						pnotify.pnotify_remove();
					});
					closer.hide();
					pnotify.container.prepend(closer);
					pnotify.hover(function(){
						closer.show();
					}, function(){
						closer.hide();
					});
				}
			};

			pnotify.pnotify_queue_position = function() {
				if (timer)
					clearTimeout(timer);
				timer = setTimeout($.pnotify_position_all, 10);
			};

			pnotify.pnotify_display = function() {
				if (pnotify.parent().get())
					pnotify.pnotify_init();
				if (opts.pnotify_before_open) {
					if (opts.pnotify_before_open(pnotify) === false)
						return;
				}
				pnotify.pnotify_queue_position();
				// First show it, then set its opacity, then hide it.
				pnotify.show().fadeTo(0, opts.pnotify_opacity).hide();
				pnotify.animate_in(function(){
					if (opts.pnotify_after_open)
						opts.pnotify_after_open(pnotify);
				
					// Now set it to hide.
					if (opts.pnotify_hide)
						pnotify.pnotify_queue_remove();
				});
			};

			pnotify.pnotify_remove = function() {
				if (pnotify.timer) {
					window.clearTimeout(pnotify.timer);
					pnotify.timer = null;
				}
				if (opts.pnotify_before_close) {
					if (opts.pnotify_before_close(pnotify) === false)
						return;
				}
				pnotify.animate_out(function(){
					if (opts.pnotify_after_close) {
						if (opts.pnotify_after_close(pnotify) === false)
							return;
					}
					pnotify.pnotify_queue_position();
					if (opts.pnotify_remove)
						pnotify.remove();
				});
			};

			pnotify.animate_in = function(callback){
				var animation;
				if (typeof opts.pnotify_animation.effect_in != "undefined") {
					animation = opts.pnotify_animation.effect_in;
				} else {
					animation = opts.pnotify_animation;
				}
				if (animation == "none") {
					pnotify.show();
					callback();
				} else if (animation == "show") {
					pnotify.show(opts.pnotify_animate_speed, callback);
				} else if (animation == "fade") {
					pnotify.show().fadeTo(0, 0).fadeTo(opts.pnotify_animate_speed, opts.pnotify_opacity, callback);
				} else if (animation == "slide") {
					pnotify.slideDown(opts.pnotify_animate_speed, callback);
				} else if (typeof animation == "function") {
					animation("in", callback, pnotify);
				} else {
					if (pnotify.effect)
						pnotify.effect(animation, {}, opts.pnotify_animate_speed, callback);
				}
			};

			pnotify.animate_out = function(callback){
				var animation;
				if (typeof opts.pnotify_animation.effect_out != "undefined") {
					animation = opts.pnotify_animation.effect_out;
				} else {
					animation = opts.pnotify_animation;
				}
				if (animation == "none") {
					pnotify.hide();
					callback();
				} else if (animation == "show") {
					pnotify.hide(opts.pnotify_animate_speed, callback);
				} else if (animation == "fade") {
					pnotify.fadeOut(opts.pnotify_animate_speed, callback);
				} else if (animation == "slide") {
					pnotify.slideUp(opts.pnotify_animate_speed, callback);
				} else if (typeof animation == "function") {
					animation("out", callback, pnotify);
				} else {
					if (pnotify.effect)
						pnotify.effect(animation, {}, opts.pnotify_animate_speed, callback);
				}
			};

			pnotify.pnotify_cancel_remove = function() {
				if (pnotify.timer)
					window.clearTimeout(pnotify.timer);
			};

			pnotify.pnotify_queue_remove = function() {
				pnotify.pnotify_cancel_remove();
				pnotify.timer = window.setTimeout(function(){
					pnotify.pnotify_remove();
				}, (isNaN(opts.pnotify_delay) ? 0 : opts.pnotify_delay));
			};

			if (opts.pnotify_type == "error") {
				pnotify.container.addClass("ui-state-error");
			} else if (opts.pnotify_type == "notice") {
				pnotify.container.addClass("ui-state-highlight");
			}

			if ((opts.pnotify_notice_icon && opts.pnotify_type == "notice") || (opts.pnotify_error_icon && opts.pnotify_type == "error")) {
				var icon = $("<div />").addClass("ui-pnotify-icon");
				icon.append($("<span />").addClass(opts.pnotify_type == "notice" ? opts.pnotify_notice_icon : opts.pnotify_error_icon));
				pnotify.container.append(icon);
			}

			pnotify.title_container = $("<span />").addClass("ui-pnotify-title");
			pnotify.title_container.html(opts.pnotify_title);
			pnotify.container.append(pnotify.title_container);
			if (typeof opts.pnotify_title != "string")
				pnotify.title_container.hide();

			pnotify.text_container = $("<span />").addClass("ui-pnotify-text");
			if (opts.pnotify_insert_brs && typeof opts.pnotify_text == "string")
				opts.pnotify_text = opts.pnotify_text.replace("\n", "<br />");
			pnotify.text_container.html(opts.pnotify_text);
			pnotify.container.append(pnotify.text_container);
			if (typeof opts.pnotify_text != "string")
				pnotify.text_container.hide();

			if (typeof opts.pnotify_width == "string")
				pnotify.css("width", opts.pnotify_width);

			if (typeof opts.pnotify_min_height == "string")
				pnotify.container.css("min-height", opts.pnotify_min_height);

			pnotify.pnotify_history = opts.pnotify_history;

			pnotify.hide();
			
			var body_data = body.data("pnotify");
			if (typeof body_data != "object")
				body_data = Array();
			body_data = $.merge(body_data, [pnotify]);
			body.data("pnotify", body_data);

			if (opts.pnotify_after_init)
				opts.pnotify_after_init(pnotify);

			pnotify.pnotify_display();

			if (opts.pnotify_history) {
				var body_history = body.data("pnotify_history");
				if (typeof body_history == "undefined") {
					body_history = $("<div />").addClass("ui-pnotify-history-container ui-state-default ui-corner-bottom");
					body.append(body_history);
					
					body_history.append($("<div>Redisplay</div>").addClass("ui-pnotify-history-header"));
					body_history.append($("<button>All</button>").addClass("ui-pnotify-history-all ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).click(function(){
						$.each(body_data, function(){
							if (this.pnotify_history && this.pnotify_display)
								this.pnotify_display();
						});
					}));
					body_history.append($("<button>Last</button>").addClass("ui-pnotify-history-last ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).click(function(){
						var i = 1;
						while (!body_data[body_data.length - i] || !body_data[body_data.length - i].pnotify_history) {
							if (body_data.length - i === 0)
								return;
							i++;
						}
						if (body_data[body_data.length - i].pnotify_display)
							body_data[body_data.length - i].pnotify_display();
					}));

					var handle = $("<span></span>").addClass("ui-pnotify-history-pulldown ui-icon ui-icon-grip-dotted-horizontal").mouseenter(function(){
						body_history.animate({top: "0"}, {duration: 100, queue: false});
					});
					body_history.append(handle);
					history_handle_top = handle.offset().top + 2;

					body_history.mouseleave(function(){
						body_history.animate({top: "-"+history_handle_top+"px"}, {duration: 100, queue: false});
					});
					body_history.css({top: "-"+history_handle_top+"px"});

					body.data("pnotify_history", body_history);
				}
			}

			return pnotify;
		}
	});

	$.pnotify.defaults = {
		// Display a pull down menu to redisplay previous notices, and place this notice in the history.
		pnotify_history: true,
		// Width of the notice.
		pnotify_width: "300px",
		// Minimum height of the notice. It will expand to fit content.
		pnotify_min_height: "16px",
		// Type of the notice. "notice" or "error".
		pnotify_type: "notice",
		// The icon class to use if type is notice.
		pnotify_notice_icon: "ui-icon ui-icon-info",
		// The icon class to use if type is error.
		pnotify_error_icon: "ui-icon ui-icon-alert",
		// The animation to use when displaying and hiding the notice. "none", "show", "fade", and "slide" are built in to jQuery. Others require jQuery UI. Use an object with effect_in and effect_out to use different effects.
		pnotify_animation: "fade",
		// Speed at which the notice fades or animates in and out. "slow", "def" or "normal", "fast" or number of milliseconds.
		pnotify_animate_speed: "slow",
		// Opacity to fade to.
		pnotify_opacity: 1,
		// Provide a button for the user to manually close a notice.
		pnotify_closer: true,
		// After a delay, make the notice disappear.
		pnotify_hide: true,
		// Delay in milliseconds before the notice disappears.
		pnotify_delay: 8000,
		// Remove the notice from the DOM after it disappears.
		pnotify_remove: true,
		// Change new lines to br tags.
		pnotify_insert_brs: true
	};
})(jQuery);
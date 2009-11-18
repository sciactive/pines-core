/*
 * jQuery Pines Grid (pgrid) Plugin 1.0
 *
 * Copyright (c) 2009 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *	  http://www.gnu.org/licenses/agpl.html
 */

(function($) {
	$.fn.pgrid_add = function(rows) {
		this.each(function(){
			if (!this.pines_grid)
				return;
			var pgrid = this.pines_grid;
			var new_rows = false;
			$.each(rows, function(){
				var cur_row = this;
				var jq_row = $("<tr />").attr("title", cur_row.key).addClass(cur_row.classes).each(function(){
					var this_row = $(this);
					$.each(cur_row.values, function(){
						this_row.append($("<td>"+this+"</td>"));
					});
				});
				pgrid.find("tbody tr.ui-pgrid-table-row-spacer").before(jq_row);
				// Gather all the rows.
				if (new_rows) {
					new_rows = new_rows.add(jq_row);
				} else {
					new_rows = jq_row;
				}
			});
			// The rows need to be initialized after they've all been added, for child indentation.
			pgrid.init_rows(new_rows);

			pgrid.do_col_hiding(true);
			pgrid.do_sort(false, true);
			pgrid.do_filter(false, true);
			pgrid.paginate(true);
			pgrid.make_page_buttons();
			pgrid.update_selected();
		});
		return this;
	};
	$.fn.pgrid_delete = function(keysorrows) {
		if (keysorrows) {
			this.each(function(){
				var pgrid = this.pines_grid;
				if (!pgrid)
					return;
				if (keysorrows.jquery) {
					keysorrows.each(function(){
						pgrid.mark_for_delete_recursively($(this));
					});
				} else {
					$.each(keysorrows, function(){
						var cur_keyorrow = this;
						if (typeof cur_keyorrow == "object") {
							pgrid.mark_for_delete_recursively($(this));
						} else {
							pgrid.find("tbody tr[title="+cur_keyorrow+"]:not(.ui-pgrid-table-row-spacer)").each(function(){
								pgrid.mark_for_delete_recursively($(this));
							});
						}

					});
				}
				// Delete the rows we just marked for deletion.
				// Marking them for deletion first prevents errors when selecting children.
				pgrid.delete_marked();
			});
		} else {
			var pgrid = this.closest(".ui-pgrid-table").get(0).pines_grid;
			if (!pgrid)
				return this;
			this.each(function(){
				pgrid.mark_for_delete_recursively($(this));
			});
			pgrid.delete_marked();
		}
		return this;
	};
	$.fn.pgrid_export_rows = function(rows) {
		var return_array = [];
		if (!rows) {
			rows = this;
		}
		$.each(rows, function(){
			var cur_row = $(this);
			var value_array = [];
			cur_row.children("td:not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)").each(function(){
				value_array = $.merge(value_array, [$(this).text()]);
			});
			return_array = $.merge(return_array, [{
				key: cur_row.attr("title"),
				classes: cur_row.attr("class").replace(/ui-[a-z0-9 -]+/, ""),
				values: value_array
			}]);
		});
		return return_array;
	};
	$.fn.pgrid_select_rows = function(rows) {
		var pgrid = null;
		if (!rows) {
			rows = this;
			pgrid = rows.closest(".ui-pgrid-table").get(0).pines_grid;
		} else {
			pgrid = this;
		}
		if (!pgrid)
			return this;
		if (pgrid.pgrid_select) {
			if ((pgrid.find("tr.ui-pgrid-table-row-selected").length || rows.length > 1) && !pgrid.pgrid_multi_select)
				return this;
			$.each(rows, function(){
				var cur_row = $(this);
				cur_row.addClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-active");
			});
		}
		return this;
	};
	$.fn.pgrid_deselect_rows = function(rows) {
		var pgrid = null;
		if (!rows) {
			rows = this;
			pgrid = rows.closest(".ui-pgrid-table").get(0).pines_grid;
		} else {
			pgrid = this;
		}
		if (!pgrid)
			return this;
		if (pgrid.pgrid_select) {
			$.each(rows, function(){
				var cur_row = $(this);
				cur_row.removeClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").removeClass("ui-state-active");
			});
		}
		return this;
	};
	$.fn.pgrid_get_all_rows = function() {
		var return_rows = false;
		this.each(function(){
			if (!this.pines_grid.jquery)
				return;
			var pgrid = this.pines_grid;
			if (return_rows) {
				return_rows = return_rows.add(pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer)"));
			} else {
				return_rows = pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer)");
			}
		});
		return return_rows;
	};
	$.fn.pgrid_get_selected_rows = function() {
		var return_rows = false;
		this.each(function(){
			if (!this.pines_grid.jquery)
				return;
			var pgrid = this.pines_grid;
			if (return_rows) {
				return_rows = return_rows.add(pgrid.find("tbody tr.ui-pgrid-table-row-selected"));
			} else {
				return_rows = pgrid.find("tbody tr.ui-pgrid-table-row-selected");
			}
		});
		return return_rows;
	};
	$.fn.pgrid_add_descendent_rows = function() {
		var rows = $(this);
		this.each(function(){
			var cur_row = $(this);
			
			if (cur_row.hasClass("parent")) {
				var children = cur_row.siblings("."+cur_row.attr("title"));
				rows = rows.add(children.pgrid_add_descendent_rows());
			}
		});
		return rows;
	};
	$.fn.pgrid_export_state = function() {
		var pgrid = this.get(0).pines_grid;
		if (pgrid.jquery)
			return pgrid.export_state();
		return false;
	};
	$.fn.pgrid_import_state = function(state) {
		this.each(function(){
			if (!this.pines_grid) return;
			this.pines_grid.import_state(state);
		});
		return this;
	};

	$.fn.pgrid = function(options) {
		// Build main options before element iteration.
		var opts = $.extend({}, $.fn.pgrid.defaults, options);

		// Iterate and gridify each matched element.
		var all_elements = this;
		all_elements.each(function() {
			var pgrid = $(this);
			
			// Check for the pgrid class. If it has it, we've already gridified this table.
			if (pgrid.hasClass("ui-pgrid-table")) return true;
			// Add the pgrid class.
			pgrid.addClass("ui-pgrid-table");
			/* This could be used to properly size the table in non-mozilla browsers.
			 *
			 * pgrid.wrap($("<div />"));
			 * var head_section = pgrid.children("thead");
			 * pgrid.before(head_section);
			 * head_section.wrap($("<table />").addClass("ui-pgrid-table"));
			 * pgrid = pgrid.parent();
			 */
			
			pgrid.extend(pgrid, opts);

			pgrid.pgrid_pages = null;
			pgrid.pgrid_widget = $("<div />");
			pgrid.pgrid_table_container = $("<div />");
			pgrid.pgrid_header_select = $("<div />");

			// All arrays and objects in our options need to be copied,
			// since they just have a pointer to the defaults if we don't.
			pgrid.pgrid_toolbar_contents = pgrid.pgrid_toolbar_contents.slice();
			pgrid.pgrid_hidden_cols = pgrid.pgrid_hidden_cols.slice();

			// If we're running on a browser that doesn't have an indexOf
			// function on the array object, create one, so we can hide columns.
			if (!pgrid.pgrid_hidden_cols.indexOf) {
				//This prototype is provided by the Mozilla foundation and
				//is distributed under the MIT license.
				//http://www.ibiblio.org/pub/Linux/LICENSES/mit.license
				pgrid.pgrid_hidden_cols.indexOf = function(elt /*, from*/) {
					var len = this.length >>> 0;

					var from = Number(arguments[1]) || 0;
					from = (from < 0) ? Math.ceil(from) : Math.floor(from);
					if (from < 0)
						from += len;

					for (; from < len; from++) {
						if (from in this && this[from] === elt)
							return from;
					}
					return -1;
				};
			}

			// Export the current state of the grid.
			pgrid.export_state = function() {
				return {
					pgrid_page: pgrid.pgrid_page,
					pgrid_perpage: pgrid.pgrid_perpage,
					pgrid_filter: pgrid.pgrid_filter,
					pgrid_hidden_cols: pgrid.pgrid_hidden_cols.slice(),
					pgrid_sort_col: pgrid.pgrid_sort_col,
					pgrid_sort_ord: pgrid.pgrid_sort_ord
				};
			};

			// Return the grid to a provided state.
			pgrid.import_state = function(state) {
				if (typeof state.pgrid_page !== undefined)
					pgrid.pgrid_page = state.pgrid_page;
				if (typeof state.pgrid_perpage !== undefined)
					pgrid.pgrid_perpage = state.pgrid_perpage;
				if (typeof state.pgrid_filter !== undefined)
					pgrid.pgrid_filter = state.pgrid_filter;
				if (typeof state.pgrid_hidden_cols !== undefined)
					pgrid.pgrid_hidden_cols = state.pgrid_hidden_cols.slice(0);
				if (typeof state.pgrid_sort_col !== undefined)
					pgrid.pgrid_sort_col = state.pgrid_sort_col;
				if (typeof state.pgrid_sort_ord !== undefined)
					pgrid.pgrid_sort_ord = (state.pgrid_sort_ord != "desc" ? "asc" : "desc");
				// Filter need to come first, because pagination ignores disabled records.
				pgrid.do_filter(pgrid.pgrid_filter);
				pgrid.do_sort();
				pgrid.do_col_hiding();
				if (pgrid.pgrid_footer && pgrid.pgrid_filtering) {
					footer.find(".ui-pgrid-footer-filter-container span:first-child input").val(pgrid.pgrid_filter);
				}
				if (pgrid.pgrid_footer && pgrid.pgrid_paginate) {
					footer.find(".ui-pgrid-footer-pager-container span:first-child input").val(pgrid.pgrid_perpage);
				}
			};

			// When the grid's state changes, call the provided function, passing the current state.
			pgrid.state_changed = function() {
				if (pgrid.pgrid_state_change) {
					return pgrid.pgrid_state_change(pgrid.export_state());
				}
				return null;
			};

			pgrid.pagestart = function() {
				// Go to the first page.
				pgrid.pgrid_page = 0;
				pgrid.paginate();
			};

			pgrid.pageprev = function() {
				// Go to the previous page.
				pgrid.pgrid_page--;
				if (pgrid.pgrid_page < 0)
					pgrid.pgrid_page = 0;
				pgrid.paginate();
			};

			pgrid.pagenext = function() {
				// Go to the next page.
				pgrid.pgrid_page++;
				if (pgrid.pgrid_page >= pgrid.pgrid_pages)
					pgrid.pgrid_page = pgrid.pgrid_pages - 1;
				pgrid.paginate();
			};

			pgrid.pageend = function() {
				// Go to the last page.
				pgrid.pgrid_page = pgrid.pgrid_pages - 1;
				pgrid.paginate();
			};

			pgrid.pagenum = function(pagenum) {
				// Change the current page.
				pgrid.pgrid_page = pagenum;
				if (pgrid.pgrid_page < 0)
					pgrid.pgrid_page = 0;
				if (pgrid.pgrid_page >= pgrid.pgrid_pages)
					pgrid.pgrid_page = pgrid.pgrid_pages - 1;
				pgrid.paginate();
			};

			pgrid.set_per_page = function(new_per_page) {
				// Change the records shown per page.
				pgrid.pgrid_page = 0;
				pgrid.pgrid_perpage = new_per_page;
				if (pgrid.pgrid_perpage == 0)
					pgrid.pgrid_perpage = 1;
				pgrid.paginate();
				pgrid.make_page_buttons();
			}

			pgrid.make_page_buttons = function() {
				// Make a button in the footer to jump to each page.
				if (pgrid.pgrid_paginate && pgrid.pgrid_footer) {
					pgrid.pgrid_widget.find(".ui-pgrid-footer .ui-pgrid-footer-pager-button-container").html("").each(function(){
						for (var cur_page = 0; cur_page < pgrid.pgrid_pages; cur_page++) {
							$(this).append($("<button>"+(cur_page+1)+"</button>").addClass("ui-state-default ui-corner-all").click(function(){
								pgrid.pagenum(parseInt($(this).text()) - 1);
								return false;
							}).hover(
								function(){
									$(this).addClass("ui-state-hover");
								},
								function(){
									$(this).removeClass("ui-state-hover");
								}
							));
						}
					});
				}
			};

			pgrid.hide_children = function(jq_rows) {
				// For each row, hide its children.
				jq_rows.each(function() {
					var cur_row = $(this);
					cur_row.siblings("."+cur_row.attr("title")).addClass("ui-pgrid-table-row-hidden").each(function(){
						// And its descendants, if it's a parent.
						var this_row = $(this);
						if (this_row.hasClass("parent"))
							pgrid.hide_children(this_row);
					});
				});
			};

			pgrid.show_children = function(jq_rows) {
				// For each row, unhide its children. (If it's expanded.)
				jq_rows.each(function() {
					var cur_row = $(this);
					// If this row is expanded, its children should be shown.
					if (cur_row.hasClass("ui-pgrid-table-row-expanded")) {
						cur_row.siblings("."+cur_row.attr("title")).removeClass("ui-pgrid-table-row-hidden").each(function(){
							// And its descendants.
							pgrid.show_children($(this));
						});
					}
				});
			};

			pgrid.mark_for_delete_recursively = function(jq_rows) {
				// For each row, mark its children.
				jq_rows.each(function() {
					var cur_row = $(this);
					cur_row.siblings("."+cur_row.attr("title")).each(function(){
						// And its descendants, if it's a parent.
						var this_row = $(this);
						if (this_row.hasClass("parent")) {
							pgrid.mark_for_delete_recursively(this_row);
						} else {
							this_row.addClass("ui-pgrid-table-row-marked-for-deletion");
						}
					});
					// Then itself.
					cur_row.addClass("ui-pgrid-table-row-marked-for-deletion");
				});
			};

			pgrid.delete_marked = function() {
				pgrid.find("tbody tr.ui-pgrid-table-row-marked-for-deletion").remove();
				pgrid.do_sort(false, true);
				pgrid.do_filter(false, true);
				pgrid.paginate(true);
				pgrid.make_page_buttons();
				pgrid.update_selected();
			};

			pgrid.paginate = function(loading) {
				if (pgrid.pgrid_paginate) {
					// Hide all rows.
					pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer, .ui-helper-hidden)").addClass("ui-pgrid-table-row-hidden");
					var elements = pgrid.find("tbody tr:not(.child, .ui-pgrid-table-row-spacer, .ui-helper-hidden)");
					// Calculate the total number of pages.
					pgrid.pgrid_pages = Math.ceil(elements.length / pgrid.pgrid_perpage);

					// If the current page is past the last page, set it to the last page,
					// and if it's before the first page, set it to the first page.
					if (pgrid.pgrid_page + 1 > pgrid.pgrid_pages) {
						pgrid.pgrid_page = pgrid.pgrid_pages - 1;
					} else if ((pgrid.pgrid_page == -1) && (pgrid.pgrid_pages > 0)) {
						pgrid.pgrid_page = 0;
					}

					// Select all the rows on the current page.
					var elempage = elements.slice(pgrid.pgrid_page * pgrid.pgrid_perpage, (pgrid.pgrid_page * pgrid.pgrid_perpage) + pgrid.pgrid_perpage);
					// Unhide them.
					elempage.removeClass("ui-pgrid-table-row-hidden");
					// And their children.
					pgrid.show_children(elempage);
					// Update the page number and count in the footer.
					if (pgrid.pgrid_footer) {
						pgrid.pgrid_widget.find(".ui-pgrid-footer .page_number").html(pgrid.pgrid_page+1).end().find(".page_total").html(pgrid.pgrid_pages);
					}
				}
				// Restripe the rows, since they may have changed. (Even if pagination isn't enabled.)
				pgrid.do_stripes();
				// The grid's state has probably changed.
				if (!loading) pgrid.state_changed();
			};

			pgrid.do_filter = function(filter, loading) {
				// Filter if filtering is allowed, or if this is an initial filter.
				if (pgrid.pgrid_filtering || loading) {
					if (typeof filter == "string")
						pgrid.pgrid_filter = filter;
					if (pgrid.pgrid_filter.length > 0) {
						var filter_arr = pgrid.pgrid_filter.toLowerCase().split(" ");
						var i;
						// Disable all rows, then iterate them and match.
						pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer)").addClass("ui-helper-hidden").each(function(){
							var cur_row = $(this);
							var cur_text = "";
							// Add spaces between cell values, so they're not falsely matched.
							// ex: "mest" would match the cells "James" and "Teacher" if they were concatenated.
							cur_row.contents().each(function(){
								cur_text += " "+$(this).text().toLowerCase();
							});
							var match = true;
							// Go through each search term and if any doesn't match, flag the row as a non-match.
							for (i in filter_arr) {
								if (cur_text.indexOf(filter_arr[i]) == -1) {
									match = false;
								}
							}
							// If all search terms were found, enable the row and its parents.
							if (match) {
								cur_row.removeClass("ui-helper-hidden");
								pgrid.enable_parents(cur_row);
							}
						});
					} else {
						// If the user enters nothing, all records should be shown.
						pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer)").removeClass("ui-helper-hidden");
					}
					// Only do this if we're not loading, to speed up initialization.
					if (!loading) {
						// Paginate, since we may have disabled rows.
						pgrid.paginate();
						// Make new page buttons, since the enabled record total may have changed.
						pgrid.make_page_buttons();
						// Update the selected items, and the record counts.
						pgrid.update_selected();
					}
				}
			};

			pgrid.enable_parents = function(jq_rows) {
				// For each row, enable all the rows ancestors.
				jq_rows.each(function() {
					var cur_row = $(this);
					if (cur_row.hasClass("child")) {
						// Go through each parent to check if it's the row's parent.
						cur_row.siblings(".parent").each(function(){
							var cur_test_row = $(this);
							if (cur_row.hasClass(cur_test_row.attr("title"))) {
								cur_test_row.removeClass("ui-helper-hidden");
								// Enable this row's ancestors too.
								pgrid.enable_parents(cur_test_row);
							}
						});
					}
				});
			};

			pgrid.do_stripes = function() {
				// Add striping to odd rows. (Disregarding hidden rows.)
				if (pgrid.pgrid_stripe_rows) {
					pgrid.find("tbody tr td").removeClass("ui-pgrid-table-row-striped");
					pgrid.find("tbody tr:not(.ui-pgrid-table-row-hidden, .ui-pgrid-table-row-spacer, .ui-helper-hidden):odd td:not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-pgrid-table-row-striped");
				}
			};

			pgrid.do_sort = function(column_class, loading) {
				if (pgrid.pgrid_sortable) {
					// If they click the header again, change to descending order.
					if (pgrid.pgrid_sort_col == column_class && !loading) {
						if (pgrid.pgrid_sort_ord == "asc") {
							pgrid.pgrid_sort_ord = "desc";
						} else {
							pgrid.pgrid_sort_ord = "asc";
						}
					} else {
						if (column_class) {
							pgrid.pgrid_sort_col = column_class;
							pgrid.pgrid_sort_ord = "asc";
						}
					}

					// Stylize the currently sorted column.
					pgrid.find("tbody td").removeClass("ui-pgrid-table-cell-sorted");
					var cols = pgrid.find("tbody td."+pgrid.pgrid_sort_col).addClass("ui-pgrid-table-cell-sorted");

					// Stylize the currently sorted column header. (According to order.)
					pgrid.find("thead th span.ui-icon")
					.removeClass("ui-pgrid-table-header-sorted-asc")
					.removeClass("ui-pgrid-table-header-sorted-desc")
					.removeClass("ui-icon-triangle-1-s")
					.removeClass("ui-icon-triangle-1-n");
					if (pgrid.pgrid_sort_ord == "asc") {
						pgrid.find("thead th."+pgrid.pgrid_sort_col+" span.ui-icon").addClass("ui-pgrid-table-header-sorted-desc ui-icon-triangle-1-n");
					} else {
						pgrid.find("thead th."+pgrid.pgrid_sort_col+" span.ui-icon").addClass("ui-pgrid-table-header-sorted-asc ui-icon-triangle-1-s");
					}

					// Is this column only numbers, or is there a string?
					var is_str = !!cols.contents().text().match(/[^0-9.,¤$€£¥]/);

					// Get all the rows.
					var jq_rows = pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer)");
					var rows = jq_rows.get();

					// Calculate their sort keys and store them in their DOM objects.
					$.each(rows, function(index, row) {
						row.sortKey = $(row).children("td."+pgrid.pgrid_sort_col).contents().text().toUpperCase(); //.replace("├ ", "").replace("└ ", "").toUpperCase();
						// If this column contains only numbers (currency formatting included), parse it as floats.
						if (!is_str) {
							// Strip non numerical characters except for the decimal separator. Replace that with a period, then parse it.
							row.sortKey = parseFloat(row.sortKey.replace((new RegExp("[^0-9"+pgrid.pgrid_decimal_sep+"]", "g")), "").replace(pgrid.pgrid_decimal_sep, "."));
						}
					});
					// Sort them by their keys.
					rows.sort(function(a, b) {
						if (a.sortKey < b.sortKey) return -1;
						if (a.sortKey > b.sortKey) return 1;
						return 0;
					});
					// Since we're prepending the rows to the tbody, we need to reverse the order if it's ascending.
					if (pgrid.pgrid_sort_ord == "asc") {
						rows.reverse();
					}
					// Insert the rows into the tbody in the correct order.
					$.each(rows, function(index, row) {
						pgrid.children('tbody').prepend(row);
					});
					// Place children under their parents.
					jq_rows.filter(".parent").each(function(){
						var cur_row = $(this);
						cur_row.after(cur_row.siblings("."+cur_row.attr("title")));
					});
					// Only do this if we're not loading, to speed up initialization.
					if (!loading) {
						// Paginate, since we changed the order.
						pgrid.paginate();
					}
				}
			};

			pgrid.update_selected = function() {
				if (pgrid.pgrid_select) {
					// Deselect any disabled or incorrect rows. They shouldn't be selected.
					pgrid.find("tbody tr.ui-helper-hidden.ui-pgrid-table-row-selected, tbody tr.ui-pgrid-table-row-spacer.ui-pgrid-table-row-selected").removeClass("ui-pgrid-table-row-selected").each(function(){
						$(this).children(":not(.ui-pgrid-table-cell-scrollspace)").removeClass("ui-state-active");
					});

					// Update the table footer.
					if (pgrid.pgrid_footer && pgrid.pgrid_count) {
						var selected_rows = pgrid.find("tbody tr.ui-pgrid-table-row-selected");
						pgrid.pgrid_widget.find(".ui-pgrid-footer .ui-pgrid-footer-count-select").html(selected_rows.length);
					}
				}
				pgrid.update_count();
			};

			pgrid.update_count = function() {
				// Update the table footer.
				if (pgrid.pgrid_footer && pgrid.pgrid_count) {
					var all_rows = pgrid.find("tbody tr:not(.ui-pgrid-table-row-spacer, .ui-helper-hidden)");
					pgrid.pgrid_widget.find(".ui-pgrid-footer .ui-pgrid-footer-count-total").html(all_rows.length);
				}
			};

			pgrid.do_col_hiding = function(loading) {
				var cur_col;
				pgrid.find("th:not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace), td:not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)").show();
				pgrid.pgrid_header_select.find("input").attr("checked", true);
				for (cur_col in pgrid.pgrid_hidden_cols) {
					pgrid.pgrid_header_select.find("input.ui-pgrid-table-col-hider-"+pgrid.pgrid_hidden_cols[cur_col]).removeAttr("checked");
					pgrid.find(".col_"+pgrid.pgrid_hidden_cols[cur_col]).hide();
				}
				// The grid's state has probably changed.
				if (!loading) pgrid.state_changed();
			};

			pgrid.hide_col = function(number) {
				if (pgrid.pgrid_hidden_cols.indexOf(number) == -1) {
					pgrid.pgrid_hidden_cols.push(number);
					pgrid.do_col_hiding();
				}
			};

			pgrid.show_col = function(number) {
				if (pgrid.pgrid_hidden_cols.indexOf(number) != -1) {
					pgrid.pgrid_hidden_cols.splice(pgrid.pgrid_hidden_cols.indexOf(number), 1);
					pgrid.do_col_hiding();
				}
			};

			pgrid.init_rows = function(jq_rows) {
				if (!jq_rows) return;
				// Add an expander and scrollspace column to the rows, add hover events, and give child rows indentation.
				jq_rows.each(function(){
					var cur_row = $(this);
					if (cur_row.hasClass("parent")) {
						var cur_left_padding = parseInt(cur_row.children("td:first-child").css("padding-left"));
						cur_row.siblings("."+cur_row.attr("title"))
						.children() //("td:first-child")
						.css("padding-left", (cur_left_padding+10)+"px");
						//.slice(0, -1)
						//.prepend("<span style=\"font-family: Arial, sans-serif; font-size: 85%; font-weight: lighter; vertical-align: top;\">├ </span>")
						//.end().slice(-1)
						//.prepend("<span style=\"font-family: Arial, sans-serif; font-size: 85%; font-weight: lighter; vertical-align: top;\">└ </span>");
					}
					cur_row.prepend("<td class=\"ui-pgrid-table-expander\"></td>")
					.append("<td class=\"ui-pgrid-table-cell-scrollspace\"></td>");
					// Add some coloring when hovering over rows.
					if (pgrid.pgrid_row_hover_effect) {
						// Can't use "hover" because of a bug in Firefox when the mouse moves onto a scrollbar.
						cur_row.mouseover(function(){
							cur_row.children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-hover");
						}).mouseout(function(){
							cur_row.children(":not(.ui-pgrid-table-cell-scrollspace)").removeClass("ui-state-hover");
						});
					}
					// Bind to click for selecting records. Double click for double click action.
					if (pgrid.pgrid_select) {
						cur_row.children(":not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)").click(function(e){
							var clicked_row = $(this).parent();
							if (!pgrid.pgrid_multi_select || (!e.ctrlKey && !e.shiftKey)) {
								clicked_row.siblings().removeClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").removeClass("ui-state-active");
							} else if (e.shiftKey) {
								var cur_row = clicked_row;
								while (cur_row.prev().length > 0 && !cur_row.prev().hasClass("ui-pgrid-table-row-selected")) {
									cur_row = cur_row.prev();
									cur_row.addClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-active");
								}
							}
							if (e.ctrlKey) {
								clicked_row.toggleClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").toggleClass("ui-state-active");
							} else {
								clicked_row.addClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-active");
							}
							pgrid.update_selected();
						}).dblclick(function(e){
							$(this).parent().addClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-active");
							if (pgrid.pgrid_double_click)
								pgrid.pgrid_double_click(e, pgrid.find("tbody tr.ui-pgrid-table-row-selected"));
							if (pgrid.pgrid_double_click_tb)
								pgrid.pgrid_double_click_tb();
						});
						// Prevent the browser from selecting text if the user is holding a modifier key.
						cur_row.mousedown(function(e){
							if (e.ctrlKey || e.shiftKey) {
								return false;
							}
							return true;
						});
					}
					// Get table cells ready.
					cur_row.find("td:not(.ui-pgrid-table-cell-scrollspace)").each(function(){
						var cur_cell = $(this);
						// Add some styling.
						cur_cell.addClass("ui-state-default");

						// Wrap cell contents in a div, so we can resize and sort them correctly.
						if (!cur_cell.hasClass("ui-pgrid-table-expander")) {
							// Calculate the current column number. Don't need to add 1 because of the expander column.
							var cur_col = cur_cell.prevAll().length;
							// Wrap the contents and add the column class to the cell.
							cur_cell.wrapInner($("<div />").addClass("ui-pgrid-table-cell-text")).addClass("col_"+cur_col);
							// If this table is resizable, we need its cells to have a width of 1px;
							if (pgrid.pgrid_resize_cols) {
								cur_cell.children(".ui-pgrid-table-cell-text").addClass("ui-pgrid-table-sized-cell");
							}
						}
					});
					if (cur_row.hasClass("child")) {
						// Hide children.
						cur_row.addClass("ui-pgrid-table-row-hidden");
					}
					// Bind to expander's click to toggle its children.
					if (cur_row.hasClass("parent")) {
						cur_row.children("td.ui-pgrid-table-expander").append($("<span />").addClass("ui-icon ui-icon-triangle-1-e")).click(function(){
							var cur_working_row = $(this).parent();
							if (cur_working_row.hasClass("ui-pgrid-table-row-expanded")) {
								cur_working_row.removeClass("ui-pgrid-table-row-expanded");
								$(this).children(".ui-icon").removeClass("ui-icon-triangle-1-s").addClass("ui-icon-triangle-1-e");
								pgrid.hide_children(cur_working_row);
							} else {
								cur_working_row.addClass("ui-pgrid-table-row-expanded");
								$(this).children(".ui-icon").removeClass("ui-icon-triangle-1-e").addClass("ui-icon-triangle-1-s");
								pgrid.show_children(cur_working_row);
							}
							pgrid.do_stripes();
						});
					}
				});
			};

			// Add the pgrid class to the container.
			pgrid.pgrid_widget.addClass("ui-pgrid ui-widget ui-widget-content ui-corner-all");
			// And the table container.
			pgrid.pgrid_table_container.addClass("ui-pgrid-table-container ui-widget-content");
			// Wrap the grid in the container.
			pgrid.wrap(pgrid.pgrid_widget);
			pgrid.wrap(pgrid.pgrid_table_container);
			// Refresh the jQuery objects.
			pgrid.pgrid_widget = pgrid.parent().parent();
			pgrid.pgrid_table_container = pgrid.parent();

			// Set the grid's height.
			pgrid.find("tbody").css("height", pgrid.pgrid_view_height);

			// Iterate column headers and make a checkbox to hide each one.
			pgrid.find("thead th").each(function(){
				var cur_header = $(this);
				// We add one to this because an expander column will be added.
				var cur_col = $(this).prevAll().length + 1;
				pgrid.pgrid_header_select.append($("<label></label>")
					.addClass("ui-state-default ui-corner-all")
					.append($("<input type=\"checkbox\" />").change(function(e){
						if (e.target.checked) {
							pgrid.show_col(cur_col);
						} else {
							pgrid.hide_col(cur_col);
						}
					}).addClass("ui-pgrid-table-col-hider-"+cur_col))
					.append(cur_header.text()));
			});
			// Add the header_select class;
			pgrid.pgrid_header_select.addClass("ui-pgrid-header-select ui-widget-header ui-corner-all");
			// Add a handler to hide the header selector.
			pgrid.pgrid_header_select.mouseenter(function(){
				$(this).mouseleave(function(){
					$(this).fadeOut("fast").unbind("mouseleave");
				});
			});

			// Wrap header contents in a div and provide a resizer.
			pgrid.find("thead tr th:not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)").each(function(){
				// Calculate the current column number.
				var cur_col = $(this).prevAll().length + 1;
				var resizing_header = false;
				var resizing_tempX = 0;
				var resizing_cur_bar;
				// Wrap the contents and add the column class to the cell.
				$(this).wrapInner($("<div />").addClass("ui-pgrid-table-header-text")).addClass("col_"+cur_col).append($("<span />").addClass("ui-icon"));
				// Make a sizer div to keep all header contents (text, sort icon, sizer handle) on one line.
				$(this).append($("<div style=\"clear: both; height: 0pt;\" />").addClass("ui-pgrid-table-header-sizer"))
				.each(function(){
					// Make sure the sizer div has a width.
					if ($(this).children(".ui-pgrid-table-header-text").width() + 18 >= $(this).width()) {
						// The resizer handle and sort icon need room, or they will be on a new line.
						$(this).children(".ui-pgrid-table-header-sizer").width($(this).width() + 21);
					} else {
						$(this).children(".ui-pgrid-table-header-sizer").width($(this).width() + 16);
					}
				});
				// Provide a column resizer, if set. The cleared div appended to the end will actually size the entire column.
				if (pgrid.pgrid_resize_cols) {
					$(this).children(".ui-pgrid-table-header-text").after($("<div />").addClass("ui-pgrid-table-header-sizehandle").addClass("ui-state-hover").mousedown(function(e){
						resizing_header = true;
						resizing_tempX = e.pageX;
						resizing_cur_bar = $(this).nextAll(".ui-pgrid-table-header-sizer");
						// Prevent the browser from selecting text while the user is resizing a header.
						return false;
					}));
					pgrid.mousemove(function(e){
						if (resizing_header) {
							var cur_width = resizing_cur_bar.width();
							var new_width = cur_width + e.pageX - resizing_tempX;
							resizing_cur_bar.width(new_width);
							resizing_tempX = e.pageX;
						}
					})
					.mouseup(function(){
						if (resizing_header) {
							var cur_width = resizing_cur_bar.width();
							var cur_parent_width = resizing_cur_bar.parent().width();
							if (cur_width < cur_parent_width)
								resizing_cur_bar.width(cur_parent_width);
							resizing_header = false;
						}
					});
				}
				// Bind to mouseup (not click) on the header to sort it.
				// If we bind to click, resizing_header will always be false.
				$(this).mouseup(function(){
					// If we're resizing, don't sort it.
					if (resizing_header) {
						resizing_header = false;
					} else {
						pgrid.do_sort("col_"+cur_col);
					}
				});

				// If this table is resizable, we need its cells to have a width of 1px;
				if (pgrid.pgrid_resize_cols) {
					$(this).children(".ui-pgrid-table-header-text").addClass("ui-pgrid-table-sized-cell");
				}
			});

			// Add an expander and scrollspace column to the header.
			pgrid.find("thead tr").addClass("ui-widget-header").each(function(){
				$(this).prepend($("<th class=\"ui-pgrid-table-expander\"><div style=\"width: 16px; visibility: hidden;\">+</div></th>").click(function(e){
					// Show the header selector.
					var offset = pgrid.pgrid_widget.offset();
					pgrid.pgrid_header_select.css({
						left: (e.pageX - offset.left - 5),
						top: (e.pageY - offset.top - 5)
					});
					pgrid.pgrid_header_select.fadeIn("fast");
				}).mouseover(function(){
					$(this).addClass("ui-icon ui-icon-triangle-1-s");
				}).mouseout(function(){
					$(this).removeClass("ui-icon").removeClass("ui-icon-triangle-1-s");
				})).append("<th class=\"ui-pgrid-table-cell-scrollspace\"><div style=\"width: 16px; visibility: hidden;\">+</div></th>");
			});

			// Initialize the rows.
			pgrid.init_rows(pgrid.find("tbody tr"));

			// Now that the column classes have been assigned and hiding/showing is done,
			// we can hide the default hidden columns.
			pgrid.do_col_hiding(true);
			// Now that it's ready, insert the header selector div in the container.
			pgrid.pgrid_widget.append(pgrid.pgrid_header_select);


			// This is used to keep the rows from being sized to fill the rest of the table.
			pgrid.children("tbody").append($("<tr><td></td></tr>").addClass("ui-pgrid-table-row-spacer"));

			/* -- Toolbar -- */
			if (pgrid.pgrid_toolbar) {
				var toolbar = $("<div />").addClass("ui-pgrid-toolbar ui-helper-clearfix");

				$.each(pgrid.pgrid_toolbar_contents, function(key, val){
					if (val.type == "button") {
						var cur_button = $("<div />").addClass("ui-pgrid-toolbar-button ui-state-default ui-corner-all").append(
							$("<div><span>"+val.text+"</span></div>").each(function(){
								if (val.extra_class)
									$(this).addClass(val.extra_class);
							})
							).click(function(e){
							var selected_rows = (val.return_all_rows ? pgrid.find("tbody tr:not(.ui-helper-hidden, .ui-pgrid-table-row-spacer)") : pgrid.find("tbody tr.ui-pgrid-table-row-selected"));
							if (!val.selection_optional && !val.select_all && !val.select_none && selected_rows.length == 0) {
								alert("Please make a selection before performing this operation.");
								return false;
							}
							if (!val.multi_select && !val.selection_optional && !val.select_all && !val.select_none && selected_rows.length > 1) {
								alert("Please choose only one item before performing this operation.");
								return false;
							}
							if (val.confirm) {
								if (val.return_all_rows) {
									if (!confirm("Are you sure you want to perform the operation \""+val.text+"\" on all items?")) {
										return false;
									}
								} else if (selected_rows.length == 0) {
									if (!confirm("Are you sure you want to perform the operation \""+val.text+"\"?")) {
										return false;
									}
								} else {
									if (!confirm("Are you sure you want to perform the operation \""+val.text+"\" on the "+selected_rows.length+" currently selected item(s)?")) {
										return false;
									}
								}
							}
							if (val.select_all) {
								if (pgrid.pgrid_select && pgrid.pgrid_multi_select) {
									pgrid.find("tbody tr:not(.ui-helper-hidden, .ui-pgrid-table-row-spacer)").addClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").addClass("ui-state-active");
									pgrid.update_selected();
								}
							}
							if (val.select_none) {
								if (pgrid.pgrid_select) {
									pgrid.find("tbody tr").removeClass("ui-pgrid-table-row-selected").children(":not(.ui-pgrid-table-cell-scrollspace)").removeClass("ui-state-active");
									pgrid.update_selected();
								}
							}
							if (val.click) {
								var row_data = "";
								if (val.pass_csv || val.pass_csv_with_headers) {
									// Pass a CSV of the selected rows, instead of a jQuery object.
									if (val.pass_csv_with_headers) {
										selected_rows = pgrid.find("thead tr").add(selected_rows);
									}
									selected_rows.each(function() {
										// Turn each cell into a CSV cell.
										$(this).children(":not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)").each(function(){
											row_data += "\""+$(this).contents().text().replace("\"", "\"\"")+"\"";
											// Add a comma, unless only the scrollspace column is left.
											if (!$(this).next().hasClass("ui-pgrid-table-cell-scrollspace"))
												row_data += ",";
										});
										// Add a new line after each row.
										row_data += "\n";
									});
								} else {
									// Pass a jQuery object of the selected rows.
									row_data = selected_rows;
								}
								return val.click(e, row_data);
							} else if (val.url) {
								var parsed_url = val.url;
								var cur_title = "";
								var cur_cols_text = [];
								selected_rows.each(function(){
									var cur_row = $(this);
									var cur_cells = cur_row.children(":not(.ui-pgrid-table-expander, .ui-pgrid-table-cell-scrollspace)");
									cur_title += (cur_title.length ? val.delimiter : "") + pgrid_encode_uri(cur_row.attr("title"));
									cur_cells.each(function(i){
										if (!cur_cols_text[i+1]) {
											cur_cols_text[i+1] = pgrid_encode_uri($(this).contents().text());
										} else {
											cur_cols_text[i+1] += val.delimiter + pgrid_encode_uri($(this).contents().text());
										}
									});
								});
								parsed_url = parsed_url.replace("#title#", cur_title);
								var i = 0;
								for (i in cur_cols_text) {
									parsed_url = parsed_url.replace("#col_"+i+"#", cur_cols_text[i]);
								}
								if (val.new_window) {
									return window.open(parsed_url);
								} else {
									return (window.location = parsed_url);
								}
							}
							return true;
						}).mousedown(function(){
							// Prevent text selection;
							return false;
						}).hover(
							function(e){
								$(this).addClass("ui-state-hover");
							},
							function(){
								$(this).removeClass("ui-state-hover");
							}
						);
						if (val.double_click) {
							pgrid.pgrid_double_click_tb = function() {
								cur_button.click();
							};
						}
						toolbar.append(cur_button);
					} else if (val.type == "separator") {
						toolbar.append(
							$("<div />").addClass("ui-pgrid-toolbar-sep ui-state-default")
							);
					}
				});

				pgrid.pgrid_widget.prepend(toolbar);
				toolbar.wrap($("<div />").addClass("ui-pgrid-toolbar-container ui-widget-header ui-corner-top"));
			}

			/* -- Footer -- */
			// Build a footer to place some utilities.
			if (pgrid.pgrid_footer) {
				var footer = $("<div />").addClass("ui-pgrid-footer ui-widget-header ui-corner-bottom ui-helper-clearfix");
				pgrid.pgrid_widget.append(footer);
			}

			// Provide filtering controls.
			if (pgrid.pgrid_filtering) {
				// Add filtering controls to the grid's footer.
				if (pgrid.pgrid_footer) {
					footer.append(
						$("<div />").addClass("ui-pgrid-footer-filter-container").each(function(){
							$(this).append($("<span>Filter: </span>").append(
								$("<input />").addClass("ui-state-default ui-corner-all").attr({
									type: "text",
									value: pgrid.pgrid_filter,
									size: "10"
								}).keyup(function(){
									pgrid.do_filter($(this).val());
								})
								).append(
								$("<button>X</button>").addClass("ui-state-default ui-corner-all").click(function(){
									$(this).prev("input").val("").keyup().focus();
								}).hover(
									function(){
										$(this).addClass("ui-state-hover");
									},
									function(){
										$(this).removeClass("ui-state-hover");
									}
								)));
						})
						);
				}
			}
			// Filter the grid.
			if (pgrid.pgrid_filter != "") {
				pgrid.do_filter(pgrid.pgrid_filter, true);
			}

			// Sort the grid.
			if (pgrid.pgrid_sort_col != false) {
				// Since the order is switched if the column is already set, we need to set the order to the opposite.
				// This also works as a validator. Anything other than "desc" will become "asc" when it's switched.
				if (pgrid.pgrid_sort_ord == "desc") {
					pgrid.pgrid_sort_ord = "asc";
				} else {
					pgrid.pgrid_sort_ord = "desc";
				}
				// Store the real sortable value in a temp val, and make sure it's true while we sort initially.
				var tmp_col_val = pgrid.pgrid_sortable;
				pgrid.pgrid_sortable = true;
				pgrid.do_sort(pgrid.pgrid_sort_col, true);
				// Restore the original sortable value. That way, you can decide to sort by a column, but not allow the user to change it.
				pgrid.pgrid_sortable = tmp_col_val;
			}

			// Paginate the grid.
			if (pgrid.pgrid_paginate) {
				// Add pagination controls to the grid's footer.
				if (pgrid.pgrid_footer) {
					footer.append(
						$("<div />").addClass("ui-pgrid-footer-pager-container").each(function(){
							$(this).append($("<span>Display #</span>").append(
								$("<input />").addClass("ui-state-default ui-corner-all").attr({
									type: "text",
									value: pgrid.pgrid_perpage,
									size: "1"
								}).change(function(){
									pgrid.set_per_page(Math.abs(parseInt($(this).val())));
									$(this).val(pgrid.pgrid_perpage);
								})
								).append(" "));
							$(this).append($("<button>&lt;&lt; Start</button>").addClass("ui-state-default ui-corner-all").click(function(){
								pgrid.pagestart();
							}).hover(
								function(){
									$(this).addClass("ui-state-hover");
								},
								function(){
									$(this).removeClass("ui-state-hover");
								}
							));
							$(this).append($("<button>&lt; Prev</button>").addClass("ui-state-default ui-corner-all").click(function(){
								pgrid.pageprev();
							}).hover(
								function(){
									$(this).addClass("ui-state-hover");
								},
								function(){
									$(this).removeClass("ui-state-hover");
								}
							));
							$(this).append($("<div />").addClass("ui-pgrid-footer-pager-button-container"));
							$(this).append($("<button>Next &gt;</button>").addClass("ui-state-default ui-corner-all").click(function(){
								pgrid.pagenext();
							}).hover(
								function(){
									$(this).addClass("ui-state-hover");
								},
								function(){
									$(this).removeClass("ui-state-hover");
								}
							));
							$(this).append($("<button>End &gt;&gt;</button>").addClass("ui-state-default ui-corner-all").click(function(){
								pgrid.pageend();
							}).hover(
								function(){
									$(this).addClass("ui-state-hover");
								},
								function(){
									$(this).removeClass("ui-state-hover");
								}
							));
							$(this).append($("<span> Page <span class=\"page_number\">1</span> of <span class=\"page_total\">1</span></span>"));
						})
						);
				}
				// Perform the pagination and update the controls' text.
				pgrid.paginate(true);
				// Make page buttons in the footer.
				if (pgrid.pgrid_footer) {
					pgrid.make_page_buttons();
				}
			}

			// Make selected and total record counters.
			if (pgrid.pgrid_footer) {
				if (pgrid.pgrid_count) {
					// Make a counter.
					footer.append(
						$("<div />").addClass("ui-pgrid-footer-count-container").each(function(){
							if (pgrid.pgrid_select) {
								$(this).append($("<span><span class=\"ui-pgrid-footer-count-select\">0</span> selected of </span>"));
							}
							$(this).append($("<span><span class=\"ui-pgrid-footer-count-total\">0</span> total.</span>"));
						})
						);
					// Update the selected and total count.
					pgrid.update_selected();
				}
			}

			// Save the pgrid object in the DOM, so we can access it.
			this.pines_grid = pgrid;
		});

		return all_elements;
	};

	var pgrid_encode_uri = function(text){
		if (encodeURIComponent) {
			return encodeURIComponent(text);
		} else {
			return escape(text);
		}
	};

	$.fn.pgrid.defaults = {
		// Use a custom class instead of "ui-pgrid". (Not implemented.)
		//pgrid_custom_class: null,
		// Show a toolbar.
		pgrid_toolbar: false,
		// Contents of the toolbar.
		pgrid_toolbar_contents: [],
		// Show a footer.
		pgrid_footer: true,
		// Include a record count in the footer.
		pgrid_count: true,
		// Allow selecting records.
		pgrid_select: true,
		// Allow selecting multiple records.
		pgrid_multi_select: true,
		// Double click action.
		pgrid_double_click: null,
		// Paginate the grid.
		pgrid_paginate: true,
		// Opening page.
		pgrid_page: 0,
		// Top-level entries per page.
		pgrid_perpage: 15,
		// Allow filtering.
		pgrid_filtering: true,
		// Default filter.
		pgrid_filter: "",
		// Hidden columns. (Columns start at one.)
		pgrid_hidden_cols: [],
		// Allow columns to be resized.
		pgrid_resize_cols: true,
		// Allow records to be sorted.
		pgrid_sortable: true,
		// The default sorted column. (false, or "col_1", etc.)
		pgrid_sort_col: false,
		// The default sort order. ("asc" or "desc")
		pgrid_sort_ord: "asc",
		// Decimal seperator. (Used during sorting of numbers.)
		pgrid_decimal_sep: ".",
		// Stripe alternating rows.
		pgrid_stripe_rows: true,
		// Add a hover effect to the rows.
		pgrid_row_hover_effect: true,
		// Height of the box (view) containing the entries. (Not the entire grid.) (Only works in Firefox.)
		pgrid_view_height: "360px",
		// State change. Gets called whenever the user changes the state of the grid. The state from export_state() will be passed.
		pgrid_state_change: null
	};
})(jQuery);
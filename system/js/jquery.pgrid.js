/*
 * jQuery Pines Grid (pgrid) Plugin 1.0
 *
 * Copyright (c) 2009 Hunter Perrin
 *
 * Licensed (along with all of Pines) under the GNU Affero GPL:
 *      http://www.gnu.org/licenses/agpl.html
 */

(function($) {
    $.fn.pgrid = function(options) {
        // Build main options before element iteration.
        var opts = $.extend({}, $.fn.pgrid.defaults, options);

        // Iterate and pgridify each matched element.
        var all_elements = this;
        all_elements.each(function() {
            var pgrid = $(this);
            
            // Check for the pgrid class. If it has it, we've already gridified this table.
            if (pgrid.hasClass("pgrid")) return true;
            // Add the pgrid class.
            pgrid.addClass("pgrid");
			/* This could be used to properly size the table in non-mozilla browsers.
			pgrid.wrap($("<div />"));
			var head_section = pgrid.children("thead");
			pgrid.before(head_section);
			head_section.wrap($("<table />").addClass("pgrid"));
			pgrid = pgrid.parent();
			*/
            
            pgrid.extend(pgrid, opts);

            pgrid.pgrid_pages = null;
            pgrid.pgrid_container = $("<div />");
            pgrid.pgrid_header_select = $("<div />");

			// All arrays and objects in our options need to be copied,
			// since they just have a pointer to the defaults if we don't.
			pgrid.pgrid_toolbar_contents = pgrid.pgrid_toolbar_contents.splice(0);
			pgrid.pgrid_hidden_cols = pgrid.pgrid_hidden_cols.splice(0);

			// If we're running on a browser that doesn't have an indexOf
			// function on the array object, create one, so we can hide columns.
			if (!pgrid.pgrid_hidden_cols.indexOf) {
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
            all_elements.export_state = function() {
                return {
                    pgrid_page: pgrid.pgrid_page,
                    pgrid_perpage: pgrid.pgrid_perpage,
                    pgrid_filter: pgrid.pgrid_filter,
                    pgrid_hidden_cols: pgrid.pgrid_hidden_cols.slice(0),
                    pgrid_sort_col: pgrid.pgrid_sort_col,
                    pgrid_sort_ord: pgrid.pgrid_sort_ord
                };
            };

			// Return the grid to a provided state.
            all_elements.import_state = function(state) {
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
                    footer.find(".filter span:first-child input").val(pgrid.pgrid_filter);
                }
                if (pgrid.pgrid_footer && pgrid.pgrid_paginate) {
                    footer.find(".pages span:first-child input").val(pgrid.pgrid_perpage);
                }
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
                    pgrid.parent().next().find(".page_buttons").html("").each(function(){
                        for (var cur_page = 0; cur_page < pgrid.pgrid_pages; cur_page++) {
                            $(this).append($("<button>"+(cur_page+1)+"</button>").click(function(){
                                pgrid.pagenum(parseInt($(this).text()) - 1);
                            }));
                        }
                    });
                }
            };

            pgrid.hide_children = function(jq_rows) {
                // For each row, hide its children.
                jq_rows.each(function() {
                    var cur_row = $(this);
                    cur_row.siblings("."+cur_row.attr("title")).addClass("hidden").each(function(){
                        // And its descendants, if its a parent.
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
                    if (cur_row.hasClass("expanded")) {
                        cur_row.siblings("."+cur_row.attr("title")).removeClass("hidden").each(function(){
                            // And its descendants.
                            pgrid.show_children($(this));
                        });
                    }
                });
            };

            pgrid.paginate = function() {
                if (pgrid.pgrid_paginate) {
                    // Hide all rows.
                    pgrid.find("tbody tr:not(.p_spacer, .disabled)").addClass("hidden");
                    var elements = pgrid.find("tbody tr:not(.child, .p_spacer, .disabled)");
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
                    elempage.removeClass("hidden");
                    // And their children.
                    pgrid.show_children(elempage);
                    // Update the page number and count in the footer.
                    if (pgrid.pgrid_footer) {
                        pgrid.parent().next().find(".page_number").html(pgrid.pgrid_page+1).end().find(".page_total").html(pgrid.pgrid_pages);
                    }
                }
                // Restripe the rows, since they may have changed. (Even if pagination isn't enabled.)
                pgrid.do_stripes();
            };

            pgrid.do_filter = function(filter) {
                if (pgrid.pgrid_filtering) {
                    pgrid.pgrid_filter = filter;
                    if (pgrid.pgrid_filter.length > 0) {
                        var filter_arr = pgrid.pgrid_filter.toLowerCase().split(" ");
                        var i;
                        // Disable all rows, then iterate them and match.
                        pgrid.find("tbody tr:not(.p_spacer)").addClass("disabled").each(function(){
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
                                cur_row.removeClass("disabled");
                                pgrid.enable_parents(cur_row);
                            }
                        });
                    } else {
                        // If the user enters nothing, all records should be shown.
                        pgrid.find("tbody tr:not(.p_spacer)").removeClass("disabled");
                    }
                    // Paginate, since we may have disabled rows.
                    pgrid.paginate();
                    // Make new page buttons, since the enabled record total may have changed.
                    pgrid.make_page_buttons();
                    // Update the selected items, and the record counts.
                    pgrid.update_selected();
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
                                cur_test_row.removeClass("disabled");
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
                    pgrid.find("tbody tr").removeClass("striped");
                    pgrid.find("tbody tr:not(.hidden, .p_spacer, .disabled):odd").addClass("striped");
                }
            };

            pgrid.do_sort = function(column_class) {
                if (pgrid.pgrid_sortable) {
                    // If they click the header again, change to descending order.
                    if (pgrid.pgrid_sort_col == column_class) {
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
                    pgrid.find("tbody td").removeClass("sorted");
                    var cols = pgrid.find("tbody td."+pgrid.pgrid_sort_col).addClass("sorted");

                    // Stylize the currently sorted column header. (According to order.)
                    pgrid.find("thead th").removeClass("sort-asc").removeClass("sort-desc");
                    pgrid.find("thead th."+pgrid.pgrid_sort_col).addClass("sort-"+pgrid.pgrid_sort_ord);

                    // Is this column only numbers, or is there a string?
                    var is_str = !!cols.contents().text().match(/[^0-9.,¤$€£¥]/);

                    // Get all the rows.
                    var jq_rows = pgrid.find("tbody tr:not(.p_spacer)");
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
                    // Paginate, since we changed the order.
                    pgrid.paginate();
                }
            };

            pgrid.update_selected = function() {
                if (pgrid.pgrid_select) {
                    // Deselect any disabled or incorrect rows. They shouldn't be selected.
                    pgrid.find("tbody tr.disabled.selected, tbody tr.p_spacer.selected").removeClass("selected");

                    // Update the table footer.
                    if (pgrid.pgrid_footer && pgrid.pgrid_count) {
                        var selected_rows = pgrid.find("tbody tr.selected");
                        pgrid.parent().next().find(".count_select").html(selected_rows.length);
                    }
                }
                pgrid.update_count();
            };

            pgrid.update_count = function() {
                // Update the table footer.
                if (pgrid.pgrid_footer && pgrid.pgrid_count) {
                    var all_rows = pgrid.find("tbody tr:not(.p_spacer, .disabled)");
                    pgrid.parent().next().find(".count_total").html(all_rows.length);
                }
            };

            pgrid.do_col_hiding = function() {
                var cur_col;
                pgrid.find("th:not(.expander, .scrollspace), td:not(.expander, .scrollspace)").show();
                pgrid.pgrid_header_select.find("input").attr("checked", true);
                for (cur_col in pgrid.pgrid_hidden_cols) {
                    pgrid.pgrid_header_select.find("input.col_hider_"+pgrid.pgrid_hidden_cols[cur_col]).removeAttr("checked");
                    pgrid.find(".col_"+pgrid.pgrid_hidden_cols[cur_col]).hide();
                }
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

            // Add the container class to its container.
            pgrid.pgrid_container.addClass("pgrid_container");
            // Wrap the grid in the container.
            pgrid.wrap(pgrid.pgrid_container);
            // Set the grid's height.
            pgrid.find("tbody").css("height", pgrid.pgrid_view_height);
            // Tried to work around non Mozilla browser table sizing, but didn't work and broke Firefox's.
            //pgrid.find("thead").wrap($("<div />").addClass("thead_container"));
            //pgrid.find("tbody").wrap($("<div />").addClass("tbody_container").css("height", pgrid.pgrid_view_height));
            // Iterate column headers and make a checkbox to hide each one.
            pgrid.find("thead th").each(function(){
                var cur_header = $(this);
                // We add one to this because an expander column will be added.
                var cur_col = $(this).prevAll().length + 1;
                pgrid.pgrid_header_select.append($("<label></label>")
                .append($("<input type=\"checkbox\" />").change(function(e){
                    if (e.target.checked) {
                        pgrid.show_col(cur_col);
                    } else {
                        pgrid.hide_col(cur_col);
                    }
                }).addClass("col_hider_"+cur_col))
                .append(cur_header.text()));
            });
            // Add the header_select class;
            pgrid.pgrid_header_select.addClass("header_select");
            // Add a handler to hide the header selector.
            pgrid.pgrid_header_select.mouseenter(function(){
                $(this).mouseleave(function(){
                    $(this).fadeOut("fast").unbind("mouseleave");
                });
            });

            // Add an expander and scrollspace column to the header.
            pgrid.find("thead tr").each(function(){
                $(this).prepend($("<th class=\"expander\"><div style=\"width: 19px; visibility: hidden;\">+</div></th>").click(function(e){
                    // Show the header selector.
                    pgrid.pgrid_header_select.css({left: (e.pageX-5), top: (e.pageY-5)});
                    pgrid.pgrid_header_select.fadeIn("fast");
                })).append("<th class=\"scrollspace\"><div style=\"width: 19px; visibility: hidden;\">+</div></th>")
                .mouseover(function(){
                    $(this).addClass("show_handle");
                }).mouseout(function(){
                    $(this).removeClass("show_handle");
                });
            });

            // Add an expander and scrollspace column to the rows, add hover events, and give child rows indentation.
            pgrid.find("tbody tr").each(function(){
                if ($(this).hasClass("parent")) {
                    var cur_left_padding = parseInt($(this).children("td:first-child").css("padding-left"));
                    $(this).siblings("."+$(this).attr("title"))
                    .children() //("td:first-child")
                    .css("padding-left", (cur_left_padding+10)+"px");
                    //.slice(0, -1)
                    //.prepend("<span style=\"font-family: Arial, sans-serif; font-size: 85%; font-weight: lighter; vertical-align: top;\">├ </span>")
                    //.end().slice(-1)
                    //.prepend("<span style=\"font-family: Arial, sans-serif; font-size: 85%; font-weight: lighter; vertical-align: top;\">└ </span>");
                }
                $(this).prepend("<td class=\"expander\"></td>")
                .append("<td class=\"scrollspace\"></td>");
                // Add some coloring when hovering over rows.
                if (pgrid.pgrid_row_hover_effect) {
                    $(this).mousemove(function(){
                        $(this).addClass("hover");
                    })
                    .mouseout(function(){
                        $(this).removeClass("hover");
                    });
                }
                // Bind to click for selecting records. Double click for double click action.
                if (pgrid.pgrid_select) {
                    $(this).children(":not(.expander, .scrollspace)").click(function(e){
                        var clicked_row = $(this).parent();
                        if (!pgrid.pgrid_multi_select || (!e.ctrlKey && !e.shiftKey)) {
                            clicked_row.siblings().removeClass("selected");
                        } else if (e.shiftKey) {
                            var cur_row = clicked_row;
                            while (cur_row.prev().length > 0 && !cur_row.prev().hasClass("selected")) {
                                cur_row = cur_row.prev();
                                cur_row.addClass("selected");
                            }
                        }
                        if (e.ctrlKey) {
                            clicked_row.toggleClass("selected");
                        } else {
                            clicked_row.addClass("selected");
                        }
                        pgrid.update_selected();
                    }).dblclick(function(e){
                        $(this).parent().addClass("selected");
                        if (pgrid.pgrid_double_click)
                            pgrid.pgrid_double_click(e, pgrid.find("tbody tr.selected"));
                        if (pgrid.pgrid_double_click_tb)
                            pgrid.pgrid_double_click_tb();
                    });
                    // Prevent the browser from selecting text if the user is holding a modifier key.
                    $(this).mousedown(function(e){
                        if (e.ctrlKey || e.shiftKey) {
                            return false;
                        }
                        return true;
                    });
                }
            });

            // Wrap header contents in a div and provide a resizer.
            pgrid.find("thead tr th:not(.expander, .scrollspace)").each(function(){
                // Calculate the current column number.
                var cur_col = $(this).prevAll().length;
                var resizing_header = false;
                var resizing_tempX = 0;
                var resizing_cur_bar;
                // Wrap the contents and add the column class to the cell.
                $(this).wrapInner($("<div />")).addClass("col_"+cur_col);
                // Provide a column resizer, if set. The cleared div appended to the end will actually size the entire column.
                if (pgrid.pgrid_resize_cols) {
                    $(this).append($("<div>|</div>").addClass("header_sizehandle").mousedown(function(e){
                            resizing_header = true;
                            resizing_tempX = e.pageX;
                            resizing_cur_bar = $(this).next();
                            // Prevent the browser from selecting text while the user is resizing a header.
                            return false;
                        })
                    )
                    .append($("<div style=\"clear: both; height: 1px;\" />").addClass("header_sizer"))
                    .each(function(){
                        // Make sure the resizer div has a width.
                        if ($(this).children(":first-child").width() + 2 >= $(this).width()) {
                            // The resizer handle needs room, or it will be on a new line.
                            $(this).children(":last-child").width($(this).width() + 5);
                        } else {
                            $(this).children(":last-child").width($(this).width());
                        }
                    });
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

                var header_text = $(this).children(":first-child");
                // Now that the size has been taken, we can add header_text, which will float it.
                header_text.addClass("header_text");
                // If this table is resizable, we need its cells to have a width of 1px;
                if (pgrid.pgrid_resize_cols) {
                    header_text.addClass("sized_cell");
                }
            });

            // Wrap cell contents in a div, so we can resize and sort them correctly.
            pgrid.find("tbody td:not(.expander, .scrollspace)").each(function(){
                // Calculate the current column number.
                var cur_col = $(this).prevAll().length;
                // Wrap the contents and add the column class to the cell.
                $(this).wrapInner($("<div />").addClass("cell_text")).addClass("col_"+cur_col);
                // If this table is resizable, we need its cells to have a width of 1px;
                if (pgrid.pgrid_resize_cols) {
                    $(this).children(":first-child").addClass("sized_cell");
                }
            });

            // Now that the column classes have been assigned and hiding/showing is done,
            // we can hide the default hidden columns.
            pgrid.do_col_hiding();
            // Now that it's ready, insert the header selector div in the container.
            pgrid.after(pgrid.pgrid_header_select);

            // Hide child entries.
            pgrid.find("tbody tr.child").addClass("hidden");
            // Bind to expander's click to toggle its children.
            pgrid.find("tbody tr.parent td.expander").click(function(){
                var cur_row = $(this).parent();
                if (cur_row.hasClass("expanded")) {
                    cur_row.removeClass("expanded");
                    pgrid.hide_children(cur_row);
                } else {
                    cur_row.addClass("expanded");
                    pgrid.show_children(cur_row);
                }
                pgrid.do_stripes();
            });

            // This is used to keep the rows from being sized to fill the rest of the table.
            pgrid.children("tbody").append($("<tr><td></td></tr>").addClass("p_spacer"));

            // Calculate the total number of pages.
            var elements = pgrid.find("tbody tr:not(.child, .p_spacer, .disabled)");
            pgrid.pgrid_pages = Math.ceil(elements.length / pgrid.pgrid_perpage);

            /* -- Toolbar -- */
            if (pgrid.pgrid_toolbar) {
                var toolbar = $("<div />").addClass("pgrid_toolbar").append(
                    $("<hr />").addClass("toolbar_clear")
                );

                $.each(pgrid.pgrid_toolbar_contents, function(i, val){
                    if (val.type == "button") {
                        var cur_button = $("<div />").addClass("tbutton").append(
                            $("<div><span>"+val.text+"</span></div>").each(function(){
                                if (val.extra_class)
                                    $(this).addClass(val.extra_class);
                            })
                        ).click(function(e){
                            var selected_rows = (val.return_all_rows ? pgrid.find("tbody tr:not(.disabled, .p_spacer)") : pgrid.find("tbody tr.selected"));
                            if (!val.selection_optional && !val.select_all && !val.select_none && selected_rows.length == 0) {
                                alert("Please make a selection before performing this operation.");
                                return false;
                            }
                            if (!val.multi_select && !val.selection_optional && !val.select_all && !val.select_none && selected_rows.length > 1) {
                                alert("Please choose only one item before performing this operation.");
                                return false;
                            }
                            if (val.confirm) {
                                if (!confirm("Are you sure you want to perform the operation \""+val.text+"\" on the "+selected_rows.length+" currently selected item(s)?")) {
                                    return false;
                                }
                            }
                            if (val.select_all) {
                                if (pgrid.pgrid_select && pgrid.pgrid_multi_select) {
                                    pgrid.find("tbody tr:not(.disabled, .p_spacer)").addClass("selected");
									pgrid.update_selected();
                                }
                            }
                            if (val.select_none) {
                                if (pgrid.pgrid_select && pgrid.pgrid_multi_select) {
                                    pgrid.find("tbody tr").removeClass("selected");
									pgrid.update_selected();
                                }
                            }
                            if (val.click) {
                                return val.click(e, selected_rows);
                            } else if (val.url) {
                                var parsed_url = val.url;
                                var cur_title = "";
                                var cur_cols_text = [];
                                selected_rows.each(function(){
                                    var cur_row = $(this);
                                    var cur_cells = cur_row.children(":not(.expander, .scrollspace)");
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
                                i = 0;
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
                            $(this).addClass("pushed");
                            // Prevent text selection;
                            return false;
                        }).mouseup(function(){
                            $(this).removeClass("pushed");
                        }).mouseover(function(e){
                            $(this).addClass("hover");
                        }).mouseout(function(){
                            $(this).removeClass("pushed").removeClass("hover");
                        });
                        if (val.double_click) {
                            pgrid.pgrid_double_click_tb = function() {
                                cur_button.click();
                            };
                        }
                        toolbar.append(cur_button);
                    } else if (val.type == "separator") {
                        toolbar.append(
                            $("<div />").addClass("tsep")
                        );
                    }
                });

                toolbar.append(
                    $("<hr />").addClass("toolbar_clear")
                );
                pgrid.parent().before(toolbar);
                toolbar.wrap($("<div />").addClass("pgrid_toolbar_container"));
            }

            /* -- Footer -- */
            // Build a footer to place some utilities.
            if (pgrid.pgrid_footer) {
                var footer = $("<div />").addClass("pgrid_footer").append(
                    $("<hr />").addClass("footer_clear")
                );
                pgrid.parent().after(footer);
            }

            // Filter the grid.
            if (pgrid.pgrid_filtering) {
                // Add filtering controls to the grid's footer.
                if (pgrid.pgrid_footer) {
                    footer.append(
                        $("<div />").addClass("filter").each(function(){
                            $(this).append($("<span>Filter: </span>").append(
                                $("<input />").attr({type: "text", value: pgrid.pgrid_filter, size: "10"}).keyup(function(){
                                    pgrid.do_filter($(this).val());
                                })
                            ).append(
                                $("<button>X</button>").click(function(){
                                    $(this).prev("input").val("").keyup().focus();
                                })
                            ));
                        })
                    );
                }
                // Perform the filtering.
                pgrid.do_filter(pgrid.pgrid_filter);
            }

            // Paginate the grid.
            if (pgrid.pgrid_paginate) {
                // Add pagination controls to the grid's footer.
                if (pgrid.pgrid_footer) {
                    footer.append(
                        $("<div />").addClass("pages").each(function(){
                            $(this).append($("<span>Display #</span>").append(
                                $("<input />").attr({type: "text", value: pgrid.pgrid_perpage, size: "1"}).change(function(){
                                    pgrid.set_per_page(Math.abs(parseInt($(this).val())));
                                    $(this).val(pgrid.pgrid_perpage);
                                })
                            ).append(" "));
                            $(this).append($("<button>&lt;&lt; Start</button>").click(function(){
                                pgrid.pagestart();
                            }));
                            $(this).append($("<button>&lt; Prev</button>").click(function(){
                                pgrid.pageprev()
                            }));
                            $(this).append($("<div />").addClass("page_buttons"));
                            $(this).append($("<button>Next &gt;</button>").click(function(){
                                pgrid.pagenext();
                            }));
                            $(this).append($("<button>End &gt;&gt;</button>").click(function(){
                                pgrid.pageend();
                            }));
                            $(this).append($("<span> Page <span class=\"page_number\">1</span> of <span class=\"page_total\">1</span></span>").click(function(){
                                pgrid.pageend();
                            }));
                        })
                    );
                }
                // Perform the pagination and update the controls' text.
                pgrid.paginate();
                // Make page buttons in the footer.
                if (pgrid.pgrid_footer) {
                    pgrid.make_page_buttons();
                }
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
                pgrid.do_sort(pgrid.pgrid_sort_col);
                // Restore the original sortable value. That way, you can decide to sort by a column, but not allow the user to change it.
                pgrid.pgrid_sortable = tmp_col_val;
            }

            // Make selected and total record counters.
            if (pgrid.pgrid_footer) {
                if (pgrid.pgrid_count) {
                    // Make a counter.
                    footer.append(
                        $("<div />").addClass("count_container").each(function(){
                            if (pgrid.pgrid_select) {
                                $(this).append($("<span><span class=\"count_select\">0</span> selected of </span>"));
                            }
                            $(this).append($("<span><span class=\"count_total\">0</span> total.</span>"));
                        })
                    );
                    // Update the selected and total count.
                    pgrid.update_selected();
                }
                footer.append(
                    $("<hr />").addClass("footer_clear")
                );
            }

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
        // Use a custom class instead of "pgrid". (Not implemented.)
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
        pgrid_perpage: 10,
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
        pgrid_view_height: "260px"
    };
})(jQuery);
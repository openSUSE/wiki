/**
 * sf_autocomplete()
 *
 * Javascript utitilies for the Semantic Forms extension, mostly related to
 * jQuery autocompletion.
 *
 * @author Sanyam Goyal
 * @author Yaron Koren
 */

function isEmpty(obj) {
    for(var i in obj) {
        return false;
    }
    return true;
}

function sf_autocomplete(input_name, container_name, values, api_url, data_type, delimiter, data_source) {
    var myServer = api_url;
    jQuery.noConflict();

/* extending jquery functions for custom highlighting */
     jQuery.ui.autocomplete.prototype._renderItem = function( ul, item) {

          var re = new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + this.term.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi");
          var loc = item.label.search(re);
	  if (loc >= 0) {
          	var t = item.label.substr(0, loc) + '<strong>' + item.label.substr(loc, this.term.length) + '</strong>' + item.label.substr(loc + this.term.length);
	} else {
		var t = item.label;
	}
          return jQuery( "<li></li>" )
              .data( "item.autocomplete", item )
              .append( " <a>" + t + "</a>" )
              .appendTo( ul );
      };

/* extending jquery functions  */
      jQuery.extend( jQuery.ui.autocomplete, {	
	filter: function(array, term) {
		var matcher = new RegExp("\\b"+ jQuery.ui.autocomplete.escapeRegex(term), "i" );
		return jQuery.grep( array, function(value) {
			return matcher.test( value.label || value.value || value );
		});
	}
    });


    if (values != null) {
            
   /* delimiter != '' means multiple autocomplete */

        if (delimiter != null) {

            jQuery(document).ready(function(){
                function split(val) {
                    return val.split(delimiter+" ");
                }
		function extractLast(term) {
			return split(term).pop();
		}

		jQuery("#" + input_name).autocomplete({
			minLength: 0,
			source: function(request, response) {

				response(jQuery.ui.autocomplete.filter(values, extractLast(request.term)));
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function(event, ui) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push("");
				this.value = terms.join(delimiter+" ");
				return false;
			}
		});              
            } );
            
        } else{
            jQuery(document).ready(function(){
                jQuery("#" + input_name).autocomplete({
			source:values
		});
            } ) ;
        }
    } else {
                     
        if (data_type == 'property')
            myServer += "?action=sfautocomplete&format=json&property=" + data_source;
        else if (data_type == 'relation')
            myServer += "?action=sfautocomplete&format=json&relation=" + data_source;
        else if (data_type == 'attribute')
            myServer += "?action=sfautocomplete&format=json&attribute=" + data_source;
        else if (data_type == 'category')
            myServer += "?action=sfautocomplete&format=json&category=" + data_source;
        else if (data_type == 'namespace')
            myServer += "?action=sfautocomplete&format=json&namespace=" + data_source;
        else if (data_type == 'external_url')
            myServer += "?action=sfautocomplete&format=json&external_url=" + data_source;                   
       
       if (delimiter != null) {
            
            jQuery(document).ready(function(){
                function split(val) {
			return val.split(delimiter + " ");
		}
		function extractLast(term) {
			return split(term).pop();
		}
                        jQuery("#" + input_name).autocomplete({
			source: function(request, response) {
				jQuery.getJSON(myServer, {
					substr: extractLast(request.term)
				}, function( data ) {
                            response(jQuery.map(data.sfautocomplete, function(item) {
                                return {
                                    value: item.title
                                }
                            }))

					});
			},
			search: function() {
				// custom minLength
				var term = extractLast(this.value);
				if (term.length < 1) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function(event, ui) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push("");
				this.value = terms.join(delimiter+" ");
				return false;
			}
		});	

              
            } );
        } else {
		jQuery(document).ready(function(){
		jQuery("#" + input_name).autocomplete({
			minLength: 1,
			source: function(request, response) {
				jQuery.ajax({
					url: myServer,
					dataType: "json",
					data: { 
                                            substr:request.term
                                            
                                        },
					success: function( data ) {
						response(jQuery.map(data.sfautocomplete, function(item) {
						return {
							value: item.title
						}
					}))
					}                                         
				});
			},                      
			open: function() {
				jQuery(this).removeClass("ui-corner-all").addClass("ui-corner-top");
			},
			close: function() {
				jQuery(this).removeClass("ui-corner-top").addClass("ui-corner-all");
			}
		});

            } );
        }
    }
};

/*
 * Functions for handling 'show on select' and 'show on check'
 */

// show the relevant div if any one of the relevant options are passed in
// to the relevant dropdown - otherwise, hide it
function showIfSelected(input_id, options_array, div_id) {
	the_input = document.getElementById(input_id);
	the_div = document.getElementById(div_id);
        for (var i in options_array) {
		if (the_input.value == options_array[i]) {
			the_div.style.display = 'inline';
			return;
		}
	}
	the_div.style.display = 'none';
}

// show the relevant div if any one of the relevant checkboxes are
// checked - otherwise, hide it
function showIfChecked(checkbox_inputs, div_id) {
	the_div = document.getElementById(div_id);
        for (var i in checkbox_inputs) {
		checkbox = document.getElementById(checkbox_inputs[i]);
		if (checkbox.checked) {
			the_div.style.display = 'inline';
			return;
		}
	}
	the_div.style.display = 'none';
}


/* extending jquery functions  */
    



(function(jQuery) {
     
	jQuery.widget("ui.combobox", {
		_create: function() {
			var self = this;
			var select = this.element.hide();
			var name= select[0].name;
			var id = select[0].id;
			var curval = select[0].options[0].value;
			var input = jQuery("<input id=\"" + id + "\" type=\"text\" name=\" " + name + " \" value=\"" + curval + "\">")
				.insertAfter(select)
				.autocomplete({
					source: function(request, response) {
						var matcher = new RegExp("\\b"+request.term, "i");
						response(select.children("option").map(function() {
							var text = jQuery(this).text();
							if (this.value && (!request.term || matcher.test(text))) {
								return {
									id: this.value,
									label: text.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + jQuery.ui.autocomplete.escapeRegex(request.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>"),
									value: text
								};
							}
						}));
					},
					delay: 0,
					change: function(event, ui) {
						if (!ui.item) {
							// remove invalid value, as it didn't match anything
							//jQuery(this).val("");
							return false;
						}
						select.val(ui.item.id);
						self._trigger("selected", event, {
							item: select.find("[value='" + ui.item.id + "']")
						});

					},
					minLength: 0
				})
			.addClass("ui-widget ui-widget-content ui-corner-left");
		jQuery("<button type=\"button\">&nbsp;</button>")
			.attr("tabIndex", -1)
			.attr("title", "Show All Items")
			.insertAfter(input)
			.button({
				icons: {
					primary: "ui-icon-triangle-1-s"
				},
				text: false
			}).removeClass("ui-corner-all")
			.addClass("ui-corner-right ui-button-icon")
			.click(function() {
				// close if already visible
				if (input.autocomplete("widget").is(":visible")) {
					input.autocomplete("close");
					return;
				}
				// pass empty string as value to search for, displaying all results
				input.autocomplete("search", "");
				input.focus();
			});
		}
	});

})(jQuery);


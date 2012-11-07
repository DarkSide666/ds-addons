$.each({

	menu_dropdown: function(options){

		var root = this.jquery; // menu container
    	var m = root.children().eq(0); // menu root element <ul>

	    // if menu already initialized, then just refresh it, otherwise - create it
    	if(m.hasClass("ui-menu")) {

	        m.menu('refresh');

	    } else {

        	m.menu($.extend({
	            position: {
                	using: function(pos, e){
                    	var ul_tag = m.menu("option","menus");

                    	// if horizontal menu, then fix position starting from 2nd level
                    	if(root.hasClass("atk-menu-dropdown-hor")){
	                        if( $(this).parents(ul_tag).eq(0).is(m) ) {
                            	pos.top = pos.top + e.target.height;
                            	pos.left = pos.left - e.target.width;
                        	}
                    	}

                    	$(this).css(pos); // apply position
                	}
            	}
			},options));
			
			if(options.width) $(".ui-menu-item",m).css("width",options.width);

		}

	    // fix arrow icon for 1st level elements
	    m.find("> .ui-menu-item > a .ui-menu-icon")
	        .toggleClass("ui-icon-carat-1-e", root.hasClass("atk-menu-dropdown-ver"))
	        .toggleClass("ui-icon-carat-1-s", root.hasClass("atk-menu-dropdown-hor"));
	}

},$.univ._import);

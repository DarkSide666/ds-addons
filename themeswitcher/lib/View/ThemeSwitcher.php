<?php
namespace themeswitcher;
class View_ThemeSwitcher extends \AbstractView{
    
    // Resources: http://blog.rbe.homeip.net/posts/jquery-themeswitcher
    
    /** Options */
    public $options=array(
		/*
        loadTheme: null,
        initialText: 'Switch Theme',
        width: 150,
        height: 200,
        buttonPreText: 'Theme: ',
        closeOnSelect: true,
        buttonHeight: 14,
        cookieName: 'jquery-ui-theme',
        onOpen: function(){},
        onClose: function(){},
        onSelect: function(){},
        useStandard:true,
        cssPrefix:"http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/",
		cssSuffix:"/jquery-ui.css",
		imgPrefix:"http://static.jquery.com/ui/themeroller/images/themeGallery/theme_90_", // theme_30_
		imgSuffix:".png",
		imageLocation:"/javascripts/jquery/themeswitcher/",
        themes:{},
        useCookie:true
		*/
    );
    
    /** Template */
    public $template_file = 'themeswitcher';
    public $cont_tag = 'Content';
    
    function init(){
		parent::init();
		
		// get URLs
		$local_jui_css = $this->api->pathfinder->locate('css','jquery-ui.css','url');
		$img_location = str_replace('icon_color_arrow.gif','',$this->api->pathfinder->locate('template','images/icon_color_arrow.gif','url'));
		
		// Add #ui-theme ID to ATK4 CSS stylesheet. Themeswitcher then will replace this ATK4 jUI stylesheet.
		$this->owner->js(true)
			->closest('html')
			->find('head>link[href="'.$local_jui_css.'"]')
			->attr('id','ui-theme');
		
		// get image location from addon templates folder
		$this->options['imageLocation'] = $img_location;
		
		// Add ATK4 theme
		$this->options['themes'] = array('ATK4 theme'=>array(
			'icon'=>$img_location.'base.png',
			'css'=>$local_jui_css
		));

    }
    
    // Load JS and CSS on rendering and execute 
	function render(){
		$this->owner->js(true)
			->_load('themeswitchertool')
			->themeswitcher($this->options)
		;
		return parent::render();
	}

    /** Default template of menu */
    function defaultTemplate(){
    	$this->addLocations(); // add addon files to pathfinder
		return array($this->template_file, $this->cont_tag);
	}

    /** Add addon files to pathfinder */
	function addLocations(){
        $l = $this->api->locate('addons', __NAMESPACE__, 'location');
		$addon = $this->api->locate('addons', __NAMESPACE__);
        $this->api->pathfinder->addLocation($addon, array(
        	'template' => 'templates',
            //'css' => 'templates/css',
            'js' => 'js',
        ))->setParent($l);
	}
}
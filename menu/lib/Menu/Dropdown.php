<?php
namespace menu;
class Menu_Dropdown extends \listers\Lister_Tree{
    
    /** Configuration */
	public $type = 'horizontal'; // type of menu horizontal|vertical
	public $pos = 'left'; // position of menu left|right
	public $options = array(); // jUI menu widget options
	
    /** Data arrays */
	protected $pages = array();
	protected $stack = array(undefined);

	/** Triggers */
	protected $_ext_source = false; // is external (Lister_Tree) source enabled

    /** Template tags */
    protected $list_tag='list';
    protected $item_tag='item';
    protected $cont_tag='MenuDropdown';
    protected $template_file='menu_dropdown';
    
    /** <li> element classes */
    //protected $default_class='ui-state-default'; // default
    //protected $current_class='ui-state-active'; // active
    
    /** Container CSS classes */
    protected $type_class=array('horizontal'=>'atk-menu-dropdown-hor','vertical'=>'atk-menu-dropdown-ver'); // menu container classes
    protected $pos_class=array('left'=>'atk-menu-dropdown-left','right'=>'atk-menu-dropdown-right'); // menu container classes

	// adds new menu item
	function addMenuItem($page, $name=null){
		$this->pages[] = array(
			"_idx" => count($this->pages),
			"_pidx" => end($this->stack),
			"page" => $page,
			"name" => $name,
		);
		return $this;
	}

	// creates +1 level of hierarchy
	function sub(){
		$this->stack[] = count($this->pages) - 1;
		return $this;
	}
	// moves back one level in hierarchy
	function end(){
		array_pop($this->stack);
		return $this;
	}
	
	// set type of menu
	function setType($a){
		if(in_array($a=strtolower($a),array('horizontal','vertical')))
			$this->type=$a;
		return $this;
	}
	// set position of menu
	function setPosition($a){
		if(in_array($a=strtolower($a),array('left','right')))
			$this->pos=$a;
		return $this;
	}
	// set width of menu items
	function setWidth($a){
		$this->options['width']=$a;
		return $this;
	}

	// remember if we're using external source or Model
	function setSource($source,$fields=null){
		$this->_ext_source = true;
		return parent::setSource($source,$fields);
	}
	function setModel($model,$actual_fields=undefined){
		$this->_ext_source = true;
		return parent::setModel($model,$actual_fields);
	}

    // formatRow hook
    function formatRow(){
        $this->current_row['href'] = $this->current_row['page'] ? $this->api->url($this->current_row['page']) : '#';
    }
    
    // preRender
	function preRender(){
		// setSource if it's not already set externally
		if(!$this->_ext_source){
			$this->setSource($this->pages);
			$this->setRelationFields('_idx','_pidx');
		}

    	// set menu container attributes
    	$this->template->trySet('type',$this->type_class[$this->type]);
    	$this->template->trySet('position',$this->pos_class[$this->pos]);

    	// call Lister_Tree default prerendering
        parent::preRender();
    }

    // Load JS and CSS on rendering and execute 
	function render(){
		$this->js(true)
			->_load('menu_dropdown_univ')
			->_css('menu_dropdown')
			->univ()->menu_dropdown($this->options);
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
            'css' => 'templates/css',
            'js' => 'js',
        ))->setParent($l);
	}
}